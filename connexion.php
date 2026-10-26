<?php
$hote = "localhost";
$nom_base = "network_pro";
$utilisateur = "root";
$mot_de_passe = "";

try {
    $pdo = new PDO(
        "mysql:host=$hote;dbname=$nom_base;charset=utf8mb4",
        $utilisateur,
        $mot_de_passe,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $erreur) {
    error_log($erreur->getMessage());
    http_response_code(500);
    exit("Connexion à la base de données impossible.");
}

function proteger($texte) {
    return htmlspecialchars(
        (string)$texte,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
