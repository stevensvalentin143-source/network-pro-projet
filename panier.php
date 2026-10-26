<?php
session_start();
require "connexion.php";

if (!isset($_SESSION["panier"])) {
    $_SESSION["panier"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = filter_var(
        $_POST["id"] ?? 0,
        FILTER_VALIDATE_INT
    );

    if ($action === "vider") {
        $_SESSION["panier"] = [];
    } elseif ($id && $id > 0) {
        if ($action === "ajouter") {
            $requete = $bdd->prepare(
                "SELECT id FROM marchandises WHERE id=?"
            );
            $requete->execute([$id]);

            if ($requete->fetch()) {
                $_SESSION["panier"][$id] =
                    min(99, ($_SESSION["panier"][$id] ?? 0) + 1);
            }
        } elseif ($action === "retirer") {
            unset($_SESSION["panier"][$id]);
        }
    }
    header("Location: panier.php");
    exit;
}

$produits = [];
$total = 0;

foreach ($_SESSION["panier"] as $id => $quantite) {
    $requete = $bdd->prepare(
        "SELECT * FROM marchandises WHERE id=?"
    );
    $requete->execute([(int)$id]);
    $produit = $requete->fetch();

    if ($produit) {
        $produit["quantite"] = (int)$quantite;
        $total += $produit["prix"] * $quantite;
        $produits[] = $produit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panier - Network Pro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="entete">
 <a class="logo" href="index.php">NETWORK PRO</a>
 <nav><a href="index.php">Continuer les achats</a></nav>
</header>
<main class="conteneur">
<h1>Mon panier</h1>
<div class="carte">
<?php if (!$produits): ?>
 <p>Votre panier est vide.</p>
 <a class="bouton" href="index.php">Voir les produits</a>
<?php else: ?>
<div class="tableau">
<table>
<tr><th>Produit</th><th>Quantité</th><th>Prix</th><th>Action</th></tr>
<?php foreach ($produits as $produit): ?>
<tr>
<td><?= proteger($produit["nom"]) ?></td>
<td><?= $produit["quantite"] ?></td>
<td><?= number_format($produit["prix"] * $produit["quantite"],2) ?> $</td>
<td>
<form method="POST">
<input type="hidden" name="action" value="retirer">
<input type="hidden" name="id" value="<?= $produit["id"] ?>">
<button class="danger">Retirer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<h2>Total : <?= number_format($total,2) ?> $</h2>
<div class="actions">
<a class="bouton" href="commande.php">Passer commande</a>
<form method="POST">
<input type="hidden" name="action" value="vider">
<button class="danger">Vider le panier</button>
</form>
</div>
<?php endif; ?>
</div>
</main>
</body>
</html>
