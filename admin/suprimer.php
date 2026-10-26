<?php
session_start();
require "../connexion.php";

if (!isset($_SESSION["administrateur"])) {
    header("Location: connexion.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Méthode non autorisée.");
}

$id = filter_var(
    $_POST["id"] ?? 0,
    FILTER_VALIDATE_INT
);

if ($id && $id > 0) {
    $requete = $pdo->prepare(
        "DELETE FROM marchandises WHERE id=?"
    );
    $requete->execute([$id]);
}

header("Location: index.php");
exit;
?>
