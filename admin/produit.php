<?php
session_start();
require "../connexion.php";

if (!isset($_SESSION["administrateur"])) {
    header("Location: connexion.php");
    exit;
}

$id = filter_var($_GET["id"] ?? 0, FILTER_VALIDATE_INT);
$produit = [
    "nom" => "",
    "prix" => "",
    "description" => "",
    "photo" => ""
];

if ($id) {
    $requete = $pdo->prepare(
        "SELECT * FROM marchandises WHERE id=?"
    );
    $requete->execute([$id]);
    $produit = $requete->fetch();

    if (!$produit) {
        exit("Marchandise introuvable.");
    }
}

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $prix = $_POST["prix"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $photo = $produit["photo"];

    if ($nom === "" || $description === "" ||
        !is_numeric($prix) || (float)$prix < 0) {
        $erreur = "Veuillez saisir des informations valides.";
    } else {
        if (!empty($_FILES["photo"]["name"])) {
            if ($_FILES["photo"]["error"] !== UPLOAD_ERR_OK ||
                $_FILES["photo"]["size"] > 3000000) {
                $erreur = "Photo invalide ou supérieure à 3 Mo.";
            } else {
                $type = (new finfo(FILEINFO_MIME_TYPE))
                    ->file($_FILES["photo"]["tmp_name"]);

                $extensions = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($extensions[$type])) {
                    $erreur = "Formats autorisés : JPG, PNG, WebP.";
                } else {
                    $dossier = dirname(__DIR__) . "/images/";

                    if (!is_dir($dossier)) {
                        mkdir($dossier, 0755, true);
                    }

                    $nom_fichier = bin2hex(random_bytes(16))
                        . "." . $extensions[$type];

                    if (move_uploaded_file(
                        $_FILES["photo"]["tmp_name"],
                        $dossier . $nom_fichier
                    )) {
                        $photo = "images/" . $nom_fichier;
                    } else {
                        $erreur = "Impossible d'enregistrer la photo.";
                    }
                }
            }
        }

        if (!$erreur) {
            if ($id) {
                $requete = $pdo->prepare(
                    "UPDATE marchandises
                     SET nom=?,prix=?,description=?,photo=?
                     WHERE id=?"
                );
                $requete->execute([
                    $nom,$prix,$description,$photo,$id
                ]);
            } else {
                $requete = $pdo->prepare(
                    "INSERT INTO marchandises
                    (nom,prix,description,photo)
                    VALUES (?,?,?,?)"
                );
                $requete->execute([
                    $nom,$prix,$description,$photo
                ]);
            }

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gestion marchandise - Network Pro</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<header class="entete">
<a class="logo" href="index.php">NETWORK PRO</a>
</header>

<main class="conteneur">
<div class="carte">
<h1><?= $id ? "Modifier" : "Ajouter" ?> une marchandise</h1>

<?php if ($erreur): ?>
<p class="message erreur"><?= proteger($erreur) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="champ">
<label>Nom de la marchandise</label>
<input name="nom" required
value="<?= proteger($produit["nom"]) ?>">
</div>

<div class="champ">
<label>Prix en dollars ($)</label>
<input type="number" name="prix" step="0.01" min="0"
required value="<?= proteger($produit["prix"]) ?>">
</div>

<div class="champ">
<label>Description</label>
<textarea name="description" required><?= proteger($produit["description"]) ?></textarea>
</div>

<div class="champ">
<label>Photo du produit</label>
<input type="file" name="photo"
accept="image/jpeg,image/png,image/webp">
</div>

<?php if ($produit["photo"]): ?>
<p>Photo actuelle :</p>
<img src="../<?= proteger($produit["photo"]) ?>"
width="150" alt="Photo du produit">
<?php endif; ?>

<div class="actions">
<button type="submit">Enregistrer</button>
<a class="bouton" href="index.php">Annuler</a>
</div>
</form>
</div>
</main>
</body>
</html>
