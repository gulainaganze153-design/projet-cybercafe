<?php
$host = 'localhost';
$db='cybercafe';
$user='root';
$pass='';
$charset='utf8mb4';
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];

try{$pdo=new PDO($dsn,$user,$pass,$options);}
catch(\PDOException $e){die("Erreur: ".$e->getMessage());}

if(isset($_GET['list'])){
    $postes=$pdo->query("SELECT id, nom, statut FROM postes ORDER BY id DESC")->fetchAll();
    header('Content-Type: application/json'); echo json_encode($postes); exit;
}

$id=isset($_GET['id'])?(int)$_GET['id']:0;
if($id<=0){echo '<p>ID poste invalide</p>'; exit;}

// POST actions
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $stmt=$pdo->prepare("DELETE FROM postes WHERE id=?");
        $stmt->execute([$id]); echo "<p>Poste supprimé</p>"; exit;
    }
    if(isset($_POST['update'])){
        $nom=$_POST['nom'] ?? '';
        $statut=$_POST['statut'] ?? 'Libre';
        $stmt=$pdo->prepare("UPDATE postes SET nom=?, statut=? WHERE id=?");
        $stmt->execute([$nom,$statut,$id]); echo "<p>Poste mis à jour</p>"; exit;
    }
}

// Récupérer le poste
$stmt=$pdo->prepare("SELECT * FROM postes WHERE id=?");
$stmt->execute([$id]); $poste=$stmt->fetch();
if(!$poste){echo '<p>Poste introuvable</p>'; exit;}
?>

<h2><?= htmlspecialchars($poste['nom']) ?></h2>
<form method="post">
<p><strong>Nom:</strong> <input type="text" name="nom" value="<?= htmlspecialchars($poste['nom']) ?>"></p>
<p><strong>Statut:</strong> 
<select name="statut">
<option value="Libre" <?= $poste['statut']=='Libre'?'selected':'' ?>>Libre</option>
<option value="Occupé" <?= $poste['statut']=='Occupé'?'selected':'' ?>>Occupé</option>
</select>
</p>
<button type="submit" name="update">Modifier</button>
<button type="submit" name="delete">Supprimer</button>
</form>
