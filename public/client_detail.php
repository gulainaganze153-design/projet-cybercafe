<?php
// --- Connexion à la base ---
$host = 'localhost';
$db   = 'cybercafe';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion à la base: ".$e->getMessage());
}

// --- Liste JSON pour rafraîchir ---
if(isset($_GET['list'])){
    $clients = $pdo->query("SELECT id, nom, prenom FROM clients ORDER BY id DESC")->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($clients);
    exit;
}

// --- Récupérer ID client ---
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    echo '<p>ID client invalide</p>';
    exit;
}

// --- Actions POST ---
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id=?");
        $stmt->execute([$id]);
        echo "<p>Client supprimé avec succès</p>";
        exit;
    }
    if(isset($_POST['update'])){
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $stmt = $pdo->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=? WHERE id=?");
        $stmt->execute([$nom,$prenom,$email,$telephone,$id]);
        echo "<p>Client mis à jour avec succès</p>";
        exit;
    }
}

// --- Récupérer les données du client ---
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if(!$client){
    echo '<p>Client introuvable</p>';
    exit;
}
?>

<h2><?= htmlspecialchars($client['nom'] ?? '') . ' ' . htmlspecialchars($client['prenom'] ?? '') ?></h2>

<form method="post" id="updateForm">
    <p><strong>Nom:</strong> <input type="text" name="nom" value="<?= htmlspecialchars($client['nom'] ?? '') ?>" required></p>
    <p><strong>Prénom:</strong> <input type="text" name="prenom" value="<?= htmlspecialchars($client['prenom'] ?? '') ?>"></p>
    <p><strong>Email:</strong> <input type="email" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>"></p>
    <p><strong>Téléphone:</strong> <input type="text" name="telephone" value="<?= htmlspecialchars($client['telephone'] ?? '') ?>"></p>
    <button type="submit" name="update">Modifier</button>
    <button type="submit" name="delete" style="background:red;color:#fff;">Supprimer</button>
</form>
