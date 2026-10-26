<?php
session_start();
require "connexion.php";

$panier = $_SESSION["panier"] ?? [];
if (!$panier) {
    header("Location: panier.php");
    exit;
}

$erreur = "";
$succes = false;
$numero_commande = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $adresse = trim($_POST["adresse"] ?? "");
    $paiement = $_POST["paiement"] ?? "";

    $modes = [
        "M-Pesa", "Airtel Money",
        "Orange Money", "Paiement à la livraison"
    ];

    if (!$nom || !$telephone || !$adresse ||
        !in_array($paiement, $modes, true)) {
        $erreur = "Veuillez remplir correctement les champs.";
    } else {
        try {
            $bdd->beginTransaction();
            $total = 0;
            $lignes = [];

            foreach ($panier as $id => $quantite) {
                $requete = $bdd->prepare(
                    "SELECT * FROM marchandises WHERE id=?"
                );
                $requete->execute([(int)$id]);
                $produit = $requete->fetch();

                if (!$produit || $quantite < 1 || $quantite > 99) {
                    throw new Exception("Produit ou quantité invalide.");
                }

                $total += $produit["prix"] * $quantite;
                $lignes[] = [$produit, $quantite];
            }

            $requete = $bdd->prepare(
                "INSERT INTO commandes
                (nom_client,telephone,adresse,mode_paiement,montant_total)
                VALUES (?,?,?,?,?)"
            );
            $requete->execute([
                $nom,$telephone,$adresse,$paiement,$total
            ]);

            $numero_commande = $bdd->lastInsertId();

            $requete = $bdd->prepare(
                "INSERT INTO details_commandes
                (commande_id,marchandise_id,nom_marchandise,
                 quantite,prix_unitaire)
                 VALUES (?,?,?,?,?)"
            );

            foreach ($lignes as [$produit,$quantite]) {
                $requete->execute([
                    $numero_commande,
                    $produit["id"],
                    $produit["nom"],
                    $quantite,
                    $produit["prix"]
                ]);
            }

            $bdd->commit();
            $_SESSION["panier"] = [];
            $succes = true;
        } catch (Throwable $e) {
            if ($bdd->inTransaction()) {
                $bdd->rollBack();
            }
            error_log($e->getMessage());
            $erreur = "Impossible d'enregistrer la commande.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Commande - Network Pro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="entete">
<a class="logo" href="index.php">NETWORK PRO</a>
</header>
<main class="conteneur">
<div class="carte">
<h1>Finaliser la commande</h1>

<?php if ($succes): ?>
<div class="message">
Commande n° <?= (int)$numero_commande ?> enregistrée.
Le paiement est en attente de confirmation.
</div>
<a class="bouton" href="index.php">Retour à la boutique</a>
<?php else: ?>
<?php if ($erreur): ?>
<p class="message erreur"><?= proteger($erreur) ?></p>
<?php endif; ?>

<form method="POST">
<div class="champ">
<label>Nom complet</label>
<input name="nom" required maxlength="150">
</div>
<div class="champ">
<label>Numéro de téléphone</label>
<input name="telephone" required maxlength="30">
</div>
<div class="champ">
<label>Adresse de livraison</label>
<textarea name="adresse" required></textarea>
</div>
<div class="champ">
<label>Mode de paiement</label>
<select name="paiement" required>
<option value="">Choisir un paiement</option>
<option>M-Pesa</option>
<option>Airtel Money</option>
<option>Orange Money</option>
<option>Paiement à la livraison</option>
</select>
</div>
<p>Les paiements mobiles ne sont pas encore
automatisés. Une confirmation manuelle sera nécessaire.</p>
<button type="submit">Confirmer la commande</button>
</form>
<?php endif; ?>
</div>
</main>
</body>
</html>
