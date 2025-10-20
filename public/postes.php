<?php
// Connexion à la base
$host = 'localhost';
$db   = 'cybercafe';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

try { $pdo = new PDO($dsn,$user,$pass,$options); } 
catch (\PDOException $e) { die("Erreur de connexion : ".$e->getMessage()); }

// Ajouter un poste
if(isset($_POST['add_poste'])){
    $nom = $_POST['nom'] ?? '';
    if($nom!==''){
        $stmt = $pdo->prepare("INSERT INTO postes (nom, statut) VALUES (?, 'Libre')");
        $stmt->execute([$nom]);
    }
    header("Location: postes.php"); exit;
}

// Récupérer tous les postes
$postes = $pdo->query("SELECT * FROM postes ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des postes</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body{margin:0;padding:20px;font-family:'Orbitron',sans-serif;background:#0b0b0f;color:#fff;}
.container{max-width:900px;margin:auto;background:#1a1a2e;padding:20px;border-radius:15px;box-shadow:0 0 30px #00fff7;}
h2{text-align:center;color:#00fff7;text-shadow:0 0 10px #00fff7;}
form input,form button{padding:12px;margin:6px 0;border-radius:10px;border:none;font-size:14px;transition:0.3s;}
form input{width:100%;background:#121212;color:#fff;}
form input:focus{outline:none;box-shadow:0 0 12px #00fff7;transform:scale(1.02);}
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
<h2>Ajouter un poste</h2>
<form method="post">
<input type="text" name="nom" placeholder="Nom du poste" required>
<button type="submit" name="add_poste">Ajouter</button>
</form>

<h2>Liste des postes</h2>
<ul id="posteList">
<?php if(!empty($postes)): ?>
    <?php foreach($postes as $p): ?>
        <li onclick="showDetails(<?= (int)$p['id'] ?>)">
            <?= htmlspecialchars($p['nom'] ?? '') ?> - <?= $p['statut'] ?>
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <li>Aucun poste enregistré</li>
<?php endif; ?>
</ul>
</div>

<!-- Modal -->
<div class="modal" id="posteModal">
<div class="modal-content" id="modalContent">
<span class="close" onclick="closeModal()">&times;</span>
<div id="posteDetails"></div>
</div>
</div>

<script>
function showDetails(id){
    if(!id){ return alert("ID poste invalide"); }
    fetch('poste_detail.php?id='+id)
    .then(response=>response.text())
    .then(data=>{
        const modal=document.getElementById('posteModal');
        const content=document.getElementById('posteDetails');
        content.innerHTML=data;
        modal.style.display='flex';
        modal.classList.add('show');
        attachFormListener(id);
    });
}

function attachFormListener(id){
    const form=document.querySelector('#posteDetails form');
    if(!form) return;
    form.addEventListener('submit',function(e){
        e.preventDefault();
        const formData=new FormData(form);
        fetch('poste_detail.php?id='+id,{method:'POST',body:formData})
        .then(response=>response.text())
        .then(data=>{
            document.getElementById('posteDetails').innerHTML=data;
            refreshPosteList();
        });
    });
}

function refreshPosteList(){
    fetch('poste_detail.php?list=1')
    .then(response=>response.json())
    .then(data=>{
        const ul=document.getElementById('posteList');
        ul.innerHTML='';
        if(data.length===0){ ul.innerHTML='<li>Aucun poste enregistré</li>'; }
        else{
            data.forEach(p=>{
                const li=document.createElement('li');
                li.textContent=p.nom+' - '+p.statut;
                li.onclick=()=>showDetails(p.id);
                ul.appendChild(li);
            });
        }
    });
}

function closeModal(){
    const modal=document.getElementById('posteModal');
    modal.classList.remove('show');
    setTimeout(()=>{ modal.style.display='none'; },300);
}
</script>
</body>
</html>
