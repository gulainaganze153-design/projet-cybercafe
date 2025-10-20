<?php
session_start();
$host='localhost'; $db='cybercafe'; $user='root'; $pass=''; $charset='utf8mb4';
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];

try{$pdo=new PDO($dsn,$user,$pass,$options);}catch(\PDOException $e){die("Erreur: ".$e->getMessage());}

// Pour exemple : client connecté
$client_id = $_SESSION['client_id'] ?? 1; // remplacer par login réel

// Ajouter réservation
if(isset($_POST['reserve'])){
    $poste_id = (int)($_POST['poste_id']??0);
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    if($poste_id && $date_debut && $date_fin){
        $stmt=$pdo->prepare("INSERT INTO reservations (client_id,poste_id,date_debut,date_fin,statut) VALUES (?,?,?,?,?)");
        $stmt->execute([$client_id,$poste_id,$date_debut,$date_fin,'En attente']);
    }
    header("Location: client_reservations.php"); exit;
}

// Machines libres (aucune réservation en cours)
$machines = $pdo->query("
SELECT * FROM postes p 
WHERE p.statut='Libre'
AND NOT EXISTS (
    SELECT 1 FROM reservations r 
    WHERE r.poste_id=p.id AND r.statut='En cours'
)
")->fetchAll();

// Réservations du client
$reservations = $pdo->prepare("
SELECT r.id,r.date_debut,r.date_fin,r.statut,p.nom AS poste_nom
FROM reservations r
JOIN postes p ON r.poste_id=p.id
WHERE r.client_id=? ORDER BY r.date_debut DESC
");
$reservations->execute([$client_id]);
$reservations = $reservations->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mes réservations</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body{background:#0b0b0f;color:#fff;font-family:'Orbitron',sans-serif;margin:0;padding:20px;}
.container{max-width:900px;margin:auto;background:#1a1a2e;padding:20px;border-radius:15px;box-shadow:0 0 30px #00fff7;}
h2{text-align:center;color:#00fff7;text-shadow:0 0 10px #00fff7;}
form select,input,button{padding:12px;margin:6px 0;border-radius:10px;border:none;width:100%;transition:0.3s;}
form select,input{background:#121212;color:#fff;}
form input:focus,form select:focus{outline:none;box-shadow:0 0 12px #00fff7;transform:scale(1.02);}
button{background:#00fff7;color:#0b0b0b;font-weight:bold;cursor:pointer;transition:0.3s;}
button:hover{box-shadow:0 0 20px #00fff7;transform:scale(1.05);}
ul{list-style:none;padding:0;margin-top:20px;}
ul li{padding:12px;border-bottom:1px solid #00fff7;border-radius:8px;}
</style>
</head>
<body>
<div class="container">
<h2>Réserver une machine</h2>
<form method="post">
<select name="poste_id" required>
<option value="">Choisir une machine</option>
<?php foreach($machines as $m): ?>
<option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
<?php endforeach; ?>
</select>
<p>Date début:</p><input type="datetime-local" name="date_debut" required>
<p>Date fin:</p><input type="datetime-local" name="date_fin" required>
<button type="submit" name="reserve">Réserver</button>
</form>

<h2>Mes réservations</h2>
<ul>
<?php if($reservations): ?>
<?php foreach($reservations as $r): ?>
<li><?= htmlspecialchars($r['poste_nom'])." (".$r['date_debut']." → ".$r['date_fin']." | ".$r['statut'].")" ?></li>
<?php endforeach; ?>
<?php else: ?>
<li>Aucune réservation</li>
<?php endif; ?>
</ul>
</div>
</body>
</html>
