<?php
require_once '../config/db.php'; // On accède au session_start avec ce fichier

$_SESSION = [];         // On vide la variable du $user de la connexion
session_destroy();      // On supprime les datas de la session

header('Location: ../index.php'); // Redirection à l'index
exit;
?>