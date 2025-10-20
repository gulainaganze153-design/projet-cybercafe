<?php
$host='localhost'; $db='cybercafe'; $user='root'; $pass=''; $charset='utf8mb4';
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
try{$pdo=new PDO($dsn,$user,$pass,$options);}catch(\PDOException $e){die("Erreur: ".$e->getMessage());}

// Actions admin : démarrer / terminer
if(isset($_POST['action'])){
    $id=(int)($_POST['id']??0);
    $action=$_POST['action'];
    if($id>0){
        if($action=='start'){
            $pdo->prepare("UPDATE reservations r JOIN postes p ON r.poste_id=p.id SET r.statut='En cours',p.statut='Occupé' WHERE r.id=?")->execute([$id]);
        }
        elseif($action=='end'){
            $pdo->prepare("UPDATE reservations r JOIN postes p ON r.poste_id=p.id SET r.statut='Terminé',p.statut='Libre' WHERE r.id=?")->execute([$id]);
        }
    }
    header("Location: admin_reservations.php"); exit;
}

// Toutes les réservations
$reservations = $pdo->query("
SELECT r.id,r.date_debut,r.date_fin,r.statut,c.nom AS client_nom,c.prenom AS client_prenom,p.nom AS poste_nom
FROM reservations r
JOIN clients c ON r.client_id=c.id
JOIN postes p ON r.poste_id=p.id
ORDER BY r.date_debut DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin – Réservations</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body{background:#0b0b0f;color:#fff;font-family:'Orbitron',sans-serif;margin:0;padding:20px;}
.container{max-width:1000px;margin:auto;background:#1a1a2e;padding:20px;border-radius:15px;box-shadow:0 0 30px #00fff7;}
h2{text-align:center;color:#00fff7;text-shadow:0 0 10px #00fff7;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{border:1px solid #00fff7;padding:10px;text-align:left;}
th{background:#121212;}
button{padding:5px 10px;margin:2px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;}
.start{background:#00ff7f;color:#0b0b0b;}
.end{background:red;color:#fff;}
button:hover{transform:scale(1.05);}
</style>
</head>
<body>
<div class="container">
<h2>Toutes les réservations</h2>
<table>
<tr><th>Client</th><th>Machine</th><th>Début</th><th>Fin</th><th>Statut</th><th>Actions</th></tr>
<?php foreach($reservations as $r): ?>
<tr>
<td><?= htmlspecialchars($r['client_nom'].' '.$r['client_prenom']) ?></td>
<td><?= htmlspecialchars($r['poste_nom']) ?></td>
<td><?= $r['date_debut'] ?></td>
<td><?= $r['date_fin'] ?></td>
<td><?= $r['statut'] ?></td>
<td>
<?php if($r['statut']=='En attente'): ?>
<form method="post" style="display:inline;">
<input type="hidden" name="id" value="<?= $r['id'] ?>">
<button type="submit" name="action" value="start" class="start">Démarrer</button>
</form>
<?php elseif($r['statut']=='En cours'): ?>
<form method="post" style="display:inline;">
<input type="hidden" name="id" value="<?= $r['id'] ?>">
<button type="submit" name="action" value="end" class="end">Terminer</button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</body>
</html>
