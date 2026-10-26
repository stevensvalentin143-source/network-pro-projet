<?php
session_start();
require "../connexion.php";

if (!isset($_SESSION["administrateur"])) {
    header("Location: connexion.php");
    exit;
}

$produits = $pdo->query(
    "SELECT * FROM marchandises ORDER BY id DESC"
)->fetchAll();

$commandes = $pdo->query(
    "SELECT * FROM commandes ORDER BY id DESC LIMIT 30"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Administration - Network Pro</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<header class="entete">
<a class="logo" href="../index.php">NETWORK PRO</a>
<nav>
<a href="produit.php">Ajouter un produit</a>
<a href="deconnexion.php">Déconnexion</a>
</nav>
</header>
<main class="conteneur">
<h1>Tableau de bord administrateur</h1>
<p>Gestion des marchandises et des commandes.</p>
<a class="bouton" href="produit.php">+ Ajouter une marchandise</a>

<div class="carte">
<h2>Marchandises (<?= count($produits) ?>)</h2>
<div class="tableau">
<table>
<tr>
<th>Nom</th><th>Prix</th><th>Actions</th>
</tr>
<?php foreach ($produits as $produit): ?>
<tr>
<td><?= proteger($produit["nom"]) ?></td>
<td><?= number_format($produit["prix"],2) ?> $</td>
<td>
<div class="actions">
<a class="bouton" href="produit.php?id=<?= $produit["id"] ?>">
Modifier
</a>
<form method="POST"
action="/network_pro/admin/suprimer.php"
onsubmit="return confirm('vouslez-vous supprimer ce produit ?');">
<input type="hidden" name="id" value="<?= $produit["id"] ?>">
<button class="danger confirmer-suppression">
Supprimer
</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<div class="carte">
<h2>Commandes reçues</h2>
<div class="tableau">
<table>
<tr>
<th>N°</th><th>Client</th><th>Téléphone</th>
<th>Adresse</th><th>Paiement</th><th>Total</th><th>Statut</th>
</tr>
<?php foreach ($commandes as $commande): ?>
<tr>
<td><?= $commande["id"] ?></td>
<td><?= proteger($commande["nom_client"]) ?></td>
<td><?= proteger($commande["telephone"]) ?></td>
<td><?= proteger($commande["adresse"]) ?></td>
<td><?= proteger($commande["mode_paiement"]) ?></td>
<td><?= number_format($commande["montant_total"],2) ?> $</td>
<td><?= proteger($commande["statut"]) ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
</main>
<script src="../script.js"></script>
</body>
</html>
