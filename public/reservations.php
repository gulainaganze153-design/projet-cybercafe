<?php
// Connexion à la base
$host='localhost'; $db='cybercafe'; $user='root'; $pass=''; $charset='utf8mb4';
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];

try{$pdo=new PDO($dsn,$user,$pass,$options);} 
catch(\PDOException $e){die("Erreur: ".$e->getMessage());}

// Ajouter une réservation
if(isset($_POST['add_reservation'])){
    $client_id=(int)($_POST['client_id']??0);
    $poste_id=(int)($_POST['poste_id']??0);
    $date_debut=$_POST['date_debut']??'';
    $date_fin=$_POST['date_fin']??'';

    if($client_id>0 && $poste_id>0 && $date_debut && $date_fin){
        $stmt=$pdo->prepare("INSERT INTO reservations (client_id,poste_id,date_debut,date_fin) VALUES (?,?,?,?)");
        $stmt->execute([$client_id,$poste_id,$date_debut,$date_fin]);
    }
    header("Location: reservations.php"); exit;
}

// Récupérer clients et postes pour le formulaire
$clients=$pdo->query("SELECT id, nom, prenom FROM clients ORDER BY nom")->fetchAll();
$postes=$pdo->query("SELECT id, nom FROM postes ORDER BY nom")->fetchAll();

// Récupérer toutes les réservations
$reservations=$pdo->query("
    SELECT r.id,r.date_debut,r.date_fin,c.nom AS client_nom,c.prenom AS client_prenom,p.nom AS poste_nom 
    FROM reservations r 
    JOIN clients c ON r.client_id=c.id 
    JOIN postes p ON r.poste_id=p.id 
    ORDER BY r.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Réservations</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body{margin:0;padding:20px;font-family:'Orbitron',sans-serif;background:#0b0b0f;color:#fff;}
.container{max-width:900px;margin:auto;background:#1a1a2e;padding:20px;border-radius:15px;box-shadow:0 0 30px #00fff7;}
h2{text-align:center;color:#00fff7;text-shadow:0 0 10px #00fff7;}
form input,form select,form button{padding:12px;margin:6px 0;border-radius:10px;border:none;font-size:14px;transition:0.3s;}
form input,form select{width:100%;background:#121212;color:#fff;}
form input:focus,form select:focus{outline:none;box-shadow:0 0 12px #00fff7;transform:scale(1.02);}
form button{background:#00fff7;color:#0b0b0b;cursor:pointer;width:100%;font-weight:bold;transition:0.3s;}
form button:hover{box-shadow:0 0 20px #00fff7;transform:scale(1.05);}
ul{list-style:none;padding:0;margin-top:20px;}
ul li{padding:12px;border-bottom:1px solid #00fff7;cursor:pointer;transition:0.2s;border-radius:8px;}
ul li:hover{background:#00fff7;color:#0b0b0b;transform:scale(1.02);}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);justify-content:center;align-items:center;animation:fadeIn 0.3s ease;}
.modal-content{background:#1a1a2e;padding:25px;border-radius:20px;max-width:500px;width:90%;box-shadow:0 0 30px #00fff7;position:relative;transform:scale(0.8);opacity:0;transition:0.3s;}
.modal.show .modal-content{transform:scale(1);opacity:1;}
.close{position:absolute;top:10px;right:15px;font-size:24px;color:#fff;cursor:pointer;transition:0.2s;}
.close:hover{color:#00fff7;transform:scale(1.2);}
.modal-content form button{padding:10px 15px;margin:5px;border:none;border-radius:8px;cursor:pointer;font-weight:bold;}
.modal-content form button:hover{transform:scale(1.05);}
.modal-content form button[name="update"]{background:#00fff7;color:#0b0b0b;}
.modal-content form button[name="delete"]{background:red;color:#fff;}
@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
</style>
</head>
<body>
<div class="container">
<h2>Ajouter une réservation</h2>
<form method="post">
<select name="client_id" required>
<option value="">Choisir un client</option>
<?php foreach($clients as $c): ?>
<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></option>
<?php endforeach; ?>
</select>
<select name="poste_id" required>
<option value="">Choisir un poste</option>
<?php foreach($postes as $p): ?>
<option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
<?php endforeach; ?>
</select>
<p>Date début:</p><input type="datetime-local" name="date_debut" required>
<p>Date fin:</p><input type="datetime-local" name="date_fin" required>
<button type="submit" name="add_reservation">Ajouter</button>
</form>

<h2>Liste des réservations</h2>
<ul id="reservationList">
<?php if(!empty($reservations)): ?>
    <?php foreach($reservations as $r): ?>
        <li onclick="showDetails(<?= (int)$r['id'] ?>)">
            <?= htmlspecialchars($r['client_nom'].' '.$r['client_prenom'].' - '.$r['poste_nom']) ?> 
            (<?= $r['date_debut'] ?> → <?= $r['date_fin'] ?>)
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <li>Aucune réservation</li>
<?php endif; ?>
</ul>
</div>

<!-- Modal -->
<div class="modal" id="reservationModal">
<div class="modal-content" id="modalContent">
<span class="close" onclick="closeModal()">&times;</span>
<div id="reservationDetails"></div>
</div>
</div>

<script>
function showDetails(id){
    if(!id){ return alert("ID réservation invalide"); }
    fetch('reservation_detail.php?id='+id)
    .then(r=>r.text())
    .then(data=>{
        const modal=document.getElementById('reservationModal');
        const content=document.getElementById('reservationDetails');
        content.innerHTML=data;
        modal.style.display='flex';
        modal.classList.add('show');
        attachFormListener(id);
    });
}

function attachFormListener(id){
    const form=document.querySelector('#reservationDetails form');
    if(!form) return;
    form.addEventListener('submit',function(e){
        e.preventDefault();
        const formData=new FormData(form);
        fetch('reservation_detail.php?id='+id,{method:'POST',body:formData})
        .then(r=>r.text())
        .then(data=>{
            document.getElementById('reservationDetails').innerHTML=data;
            refreshReservationList();
        });
    });
}

function refreshReservationList(){
    fetch('reservation_detail.php?list=1')
    .then(r=>r.json())
    .then(data=>{
        const ul=document.getElementById('reservationList');
        ul.innerHTML='';
        if(data.length===0){ ul.innerHTML='<li>Aucune réservation</li>'; }
        else{
            data.forEach(r=>{
                const li=document.createElement('li');
                li.textContent=r.client_nom+' '+r.client_prenom+' - '+r.poste_nom+' ('+r.date_debut+' → '+r.date_fin+')';
                li.onclick=()=>showDetails(r.id);
                ul.appendChild(li);
            });
        }
    });
}

function closeModal(){
    const modal=document.getElementById('reservationModal');
    modal.classList.remove('show');
    setTimeout(()=>{ modal.style.display='none'; },300);
}
</script>
</body>
</html>
