<?php
session_start();
unset($_SESSION["administrateur"]);
session_regenerate_id(true);

header("Location: connexion.php");
exit;
?>
