<?php
$host='localhost';$db='cybercafe';$user='root';$pass='';$charset='utf8mb4';
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
try{$pdo=new PDO($dsn,$user,$pass,$options);}catch(\PDOException $e){die("Erreur: ".$e->getMessage());}

if(isset($_GET['list'])){
    $data=$pdo->query("SELECT r.id,c.nom AS client_nom,c.prenom AS client_prenom,p.nom AS poste_nom,r.date_debut,r.date_fin 
    FROM reservations r JOIN clients c ON r.client_id=c.id 
    JOIN postes p ON r.poste_id=p.id ORDER BY r.id DESC")->fetchAll();
    header('Content-Type: application/json'); echo json_encode($data); exit;
}

$id=isset($_GET['id'])?(int)$_GET['id']:0;
if($id<=0){echo '<p>ID invalide</p>'; exit;}

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $stmt=$pdo->prepare("DELETE FROM reservations WHERE id=?");
        $stmt->execute([$id]); echo "<p>Réservation supprimée</p>"; exit;
    }
    if(isset($_POST['update'])){
        $client_id=(int)($_POST['client_id']??0);
        $poste_id=(int)($_POST['poste_id']??0);
        $date_debut=$_POST['date_debut']??'';
        $date_fin=$_POST['date_fin']??'';
        $stmt=$pdo->prepare("UPDATE reservations SET client_id=?,poste_id=?,date_debut=?,date_fin=? WHERE id=?");
        $stmt->execute([$client_id,$poste_id,$date_debut,$date_fin,$id]);
        echo "<p>Réservation mise à jour</p>"; exit;
    }
}

// Récupérer réservation
$stmt=$pdo->prepare("SELECT * FROM reservations WHERE id=?");
$stmt->execute([$id]); $res=$stmt->fetch();
if(!$res){echo '<p>Réservation introuvable</p>'; exit;}

// Récupérer clients et postes
$clients=$pdo->query("SELECT id, nom, prenom FROM clients")->fetchAll();
$postes=$pdo->query("SELECT id, nom FROM postes")->fetchAll();
?>

<h2>Réservation #<?= $res['id'] ?></h2>
<form method="post">
<p>Client:
<select name="client_id" required>
<?php foreach($clients as $c): ?>
<option value="<?= $c['id'] ?>" <?= $c['id']==$res['client_id']?'selected':'' ?>>
<?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></option>
<?php endforeach; ?>
</select></p>

<p>Poste:
<select name="poste_id" required>
<?php foreach($postes as $p): ?>
<option value="<?= $p['id'] ?>" <?= $p['id']==$res['poste_id']?'selected':'' ?>><?= htmlspecialchars($p['nom']) ?></option>
<?php endforeach; ?>
</select></p>

<p>Date début:<input type="datetime-local" name="date_debut" value="<?= str_replace(' ','T',$res['date_debut']) ?>"></p>
<p>Date fin:<input type="datetime-local" name="date_fin" value="<?= str_replace(' ','T',$res['date_fin']) ?>"></p>

<button type="submit" name="update">Modifier</button>
<button type="submit" name="delete">Supprimer</button>
</form>
