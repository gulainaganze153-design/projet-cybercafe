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

// --- Ajouter un client ---
if(isset($_POST['add_client'])){
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    if($nom !== ''){
        $stmt = $pdo->prepare("INSERT INTO clients (nom, prenom, email, telephone) VALUES (?,?,?,?)");
        $stmt->execute([$nom,$prenom,$email,$telephone]);
    }
    header("Location: clients.php");
    exit;
}

// --- Récupérer tous les clients ---
$clients = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des clients</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body {
    margin:0;
    padding:20px;
    font-family:'Orbitron',sans-serif;
    background:#0b0b0f;
    color:#fff;
}
.container {
    max-width:900px;
    margin:auto;
    background:#1a1a2e;
    padding:20px;
    border-radius:15px;
    box-shadow:0 0 30px #00fff7;
    transition:0.3s;
}
h2 {
    text-align:center;
    color:#00fff7;
    text-shadow:0 0 10px #00fff7;
}
form input, form button {
    padding:12px;
    margin:6px 0;
    border-radius:10px;
    border:none;
    font-size:14px;
    transition:0.3s;
}
form input {
    width:100%;
    background:#121212;
    color:#fff;
}
form input:focus {
    outline:none;
    box-shadow:0 0 12px #00fff7;
    transform:scale(1.02);
}
form button {
    background:#00fff7;
    color:#0b0b0b;
    cursor:pointer;
    width:100%;
    font-weight:bold;
    transition:0.3s;
}
form button:hover {
    box-shadow:0 0 20px #00fff7;
    transform:scale(1.05);
}
ul {
    list-style:none;
    padding:0;
    margin-top:20px;
}
ul li {
    padding:12px;
    border-bottom:1px solid #00fff7;
    cursor:pointer;
    transition:0.2s;
    border-radius:8px;
}
ul li:hover {
    background:#00fff7;
    color:#0b0b0b;
    transform:scale(1.02);
}
.modal {
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.9);
    justify-content:center;
    align-items:center;
    animation:fadeIn 0.3s ease;
}
.modal-content {
    background:#1a1a2e;
    padding:25px;
    border-radius:20px;
    max-width:500px;
    width:90%;
    box-shadow:0 0 30px #00fff7;
    position:relative;
    transform:scale(0.8);
    opacity:0;
    transition:0.3s;
}
.modal.show .modal-content {
    transform:scale(1);
    opacity:1;
}
.close {
    position:absolute;
    top:10px;
    right:15px;
    font-size:24px;
    color:#fff;
    cursor:pointer;
    transition:0.2s;
}
.close:hover {
    color:#00fff7;
    transform:scale(1.2);
}
.modal-content form button {
    padding:10px 15px;
    margin:5px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}
.modal-content form button:hover { transform:scale(1.05); }
.modal-content form button[name="update"]{ background:#00fff7;color:#0b0b0b; }
.modal-content form button[name="delete"]{ background:red;color:#fff; }
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
</style>
</head>
<body>
<div class="container">
<h2>Ajouter un client</h2>
<form method="post">
<input type="text" name="nom" placeholder="Nom" required>
<input type="text" name="prenom" placeholder="Prénom">
<input type="email" name="email" placeholder="Email">
<input type="text" name="telephone" placeholder="Téléphone">
<button type="submit" name="add_client">Ajouter</button>
</form>

<h2>Liste des clients</h2>
<ul id="clientList">
<?php if(!empty($clients)): ?>
    <?php foreach($clients as $c): ?>
        <li onclick="showDetails(<?= (int)$c['id'] ?>)">
            <?= htmlspecialchars($c['nom'] ?? '') . ' ' . htmlspecialchars($c['prenom'] ?? '') ?>
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <li>Aucun client enregistré</li>
<?php endif; ?>
</ul>
</div>

<!-- Modal -->
<div class="modal" id="clientModal">
<div class="modal-content" id="modalContent">
<span class="close" onclick="closeModal()">&times;</span>
<div id="clientDetails"></div>
</div>
</div>

<script>
function showDetails(id){
    if(!id){ return alert("ID client invalide"); }
    fetch('client_detail.php?id='+id)
    .then(response => response.text())
    .then(data => {
        const modal = document.getElementById('clientModal');
        const content = document.getElementById('clientDetails');
        content.innerHTML = data;
        modal.style.display = 'flex';
        modal.classList.add('show');
        attachFormListener(id);
    })
    .catch(err => {
        document.getElementById('clientDetails').innerHTML = "<p>Erreur de chargement</p>";
    });
}

function attachFormListener(id){
    const form = document.querySelector('#clientDetails form');
    if(!form) return;

    form.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(form);
        fetch('client_detail.php?id='+id, {
            method:'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('clientDetails').innerHTML = data;
            refreshClientList();
        });
    });
}

function refreshClientList(){
    fetch('client_detail.php?list=1')
    .then(response => response.json())
    .then(data => {
        const ul = document.getElementById('clientList');
        ul.innerHTML = '';
        if(data.length === 0){
            ul.innerHTML = '<li>Aucun client enregistré</li>';
        } else {
            data.forEach(c => {
                const li = document.createElement('li');
                li.textContent = c.nom+' '+c.prenom;
                li.onclick = () => showDetails(c.id);
                ul.appendChild(li);
            });
        }
    });
}

function closeModal(){
    const modal = document.getElementById('clientModal');
    modal.classList.remove('show');
    setTimeout(() => { modal.style.display='none'; }, 300);
}
</script>
</body>
</html>


