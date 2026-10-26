<?php
session_start();
require "connexion.php";

$recherche = trim($_GET["recherche"] ?? "");

if ($recherche !== "") {
    $requete = $pdo->prepare(
        "SELECT * FROM marchandises
         WHERE nom LIKE ? OR description LIKE ?
         ORDER BY id DESC"
    );
    $requete->execute([
        "%$recherche%", "%$recherche%"
    ]);
} else {
    $requete = $pdo->query(
        "SELECT * FROM marchandises ORDER BY id DESC"
    );
}
$marchandises = $requete->fetchAll();
$nombre_panier = array_sum($_SESSION["panier"] ?? []);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Network Pro | Matériel réseau</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="entete">
 <a class="logo" href="index.php">
    <img
        src="images/logo.png"
        alt="Network Pro"
        class="logo-network"
        style="width:90px !important;
               height:70px !important;
               object-fit:contain;
               display:block;"
    >
</a>

 <nav>
  <a href="index.php">Accueil</a>
  <a href="#produits">Produits</a>
  <a href="panier.php">🛒 Panier (<?= $nombre_panier ?>)</a>
  <a href="admin/connexion.php">Administration</a>
 </nav>
</header>

<section class="banniere">
 <div>
  <p>Votre partenaire en connectivité</p>
  <h1>Vente de matériel de réseaux informatique</h1>
  <p>Routeurs, switchs, antennes, racks et accessoires
  pour vos installations réseau.</p>
  <a class="bouton" href="#produits">Découvrir nos produits</a>
 </div>
 <div class="illustration">
    <img
    src="images/affiche.png"
    alt="affiche publicitaire Network pro"
    class="affiche-network">

 </div>
</section>

<main class="conteneur" id="produits">
 <div class="titre-section">
  <h2>Nos équipements réseau</h2>
  <p>Matériel informatique pour professionnels et particuliers</p>
 </div>

 <form method="GET" class="recherche">
  <input type="search" name="recherche"
   placeholder="Rechercher un routeur, switch..."
   value="<?= proteger($recherche) ?>">
  <button type="submit">Rechercher</button>
 </form>

 <div class="grille">
 <?php foreach ($marchandises as $produit): ?>
  <article class="produit">
   <?php


$nom = $produit["nom"];

$photo = $produit["photo"] ?? "";

$photo = ltrim(str_replace("\\", "/", $photo), "/");

// Compatibilité avec les anciens noms de fichiers
if ($photo !== "" && strpos($photo, "images/") !== 0) {
    $photo = "images/" . $photo;
}

$photo_existe = $photo !== ""
    && is_file(__DIR__ . "/" . $photo);
?>

<div class="photo">
    <?php if ($photo_existe): ?>
        <img
            src="<?= proteger($photo) ?>"
            alt="<?= proteger($nom) ?>"
            class="image-produit"
            loading="lazy"
        >
    <?php else: ?>
        <span>📡</span>
    <?php endif; ?>
</div>


   <div class="details">
    <h3><?= proteger($produit["nom"]) ?></h3>
    <p><?= proteger($produit["description"]) ?></p>
    <strong><?= number_format($produit["prix"],2) ?> $</strong>
    <form method="POST" action="panier.php">
     <input type="hidden" name="action" value="ajouter">
     <input type="hidden" name="id"
            value="<?= (int)$produit["id"] ?>">
     <button class="bouton" type="submit">
       Ajouter au panier🛒
     </button>
    </form>
   </div>
  </article>
 <?php endforeach; ?>
 </div>
 <?php if (!$marchandises): ?>
 <p>Aucun produit trouvé.</p>
 <?php endif; ?>
</main>

<footer>
 <h3>NETWORK PRO</h3>
 <p>Vente de matériel de réseaux informatique</p>
 <p>© <?= date("Y") ?> Network Pro</p>
</footer>
<script src="script.js"></script>
</body>
</html>
