<?php
session_start();
require_once __DIR__ . "/../connexion.php";
if (!isset($pdo) || !($pdo instanceof PDO)) {
    exit("erreur : connexion PDO non disponible.");
}
if (isset($_SESSION["administrateur"])) {
    header("Location: index.php");
    exit;
}

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";

    $requete = $pdo->prepare(
        "SELECT * FROM administrateurs
         WHERE nom_utilisateur=?"
    );
    $requete->execute([$nom]);
    $administrateur = $requete->fetch();

    if ($administrateur &&
        password_verify(
            $mot_de_passe,
            $administrateur["mot_de_passe"]
        )) {
        session_regenerate_id(true);
        $_SESSION["administrateur"] = $administrateur["id"];
        header("Location: index.php");
        exit;
    }
    $erreur = "Identifiants incorrects.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Connexion - Network Pro</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<header class="entete">
<a class="logo" href="../index.php">NETWORK PRO</a>
</header>
<main class="conteneur">
<div class="carte">
<h1>Connexion administrateur</h1>
<?php if ($erreur): ?>
<p class="message erreur"><?= proteger($erreur) ?></p>
<?php endif; ?>
<form method="POST">
<div class="champ">
<label>Nom d'utilisateur</label>
<input name="nom" required>
</div>
<div class="champ">
<label>Mot de passe</label>
<input type="password" name="mot_de_passe" required>
</div>
<button>Se connecter</button>
</form>
</div>
</main>
</body>
</html>
