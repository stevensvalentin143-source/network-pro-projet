<?php
session_start();
require "../connexion.php";

$nombre = (int)$pdo->query(
    "SELECT COUNT(*) FROM administrateurs"
)->fetchColumn();

if ($nombre > 0) {
    exit("Un administrateur existe déjà. Supprimez ce fichier.");
}

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";

    if (strlen($nom) < 3 || strlen($mot_de_passe) < 12) {
        $erreur = "Nom : 3 caractères minimum. Mot de passe : 12 caractères minimum.";
    } else {
        $hachage = password_hash(
            $mot_de_passe, PASSWORD_DEFAULT
        );

        $requete = $pdo->prepare(
            "INSERT INTO administrateurs
            (nom_utilisateur,mot_de_passe) VALUES (?,?)"
        );
        $requete->execute([$nom,$hachage]);

        header("Location: connexion.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Créer administrateur</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<main class="conteneur">
<div class="carte">
<h1>Créer un administrateur</h1>
<?php if ($erreur): ?>
<p class="message erreur"><?= proteger($erreur) ?></p>
<?php endif; ?>
<form method="POST">
<div class="champ">
<label>Nom d'utilisateur</label>
<input name="nom" required minlength="3">
</div>
<div class="champ">
    <label for="mot_de_passe">Mot de passe sécurisé</label>

    <div class="champ-mot-de-passe">
        <input
            type="password"
            id="mot_de_passe"
            name="mot_de_passe"
            placeholder="Entrez votre mot de passe"
            required
            minlength="12"
        >

        <button
            type="button"
            class="bouton-oeil"
            id="bouton-oeil"
            aria-label="Afficher le mot de passe"
            aria-pressed="false"
            onclick="afficherMotDePasse()"
        >👁</button>
    </div>
</div>

<button>Créer le compte</button>
</form>
</div>
</main>
</body><script>
function afficherMotDePasse() {
    const champ = document.getElementById("mot_de_passe");
    const bouton = document.getElementById("bouton-oeil");

    const visible = champ.type === "password";

    champ.type = visible ? "text" : "password";

    bouton.setAttribute("aria-pressed", String(visible));
    bouton.setAttribute(
        "aria-label",
        visible ? "Masquer le mot de passe" : "Afficher le mot de passe"
    );
}
</script>

</html>
