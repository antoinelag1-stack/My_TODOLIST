<?php 
require_once 'config/db.php';
require_once 'functions/functions.php';

if (!est_connecte()) {                  // Check sécurité et redirection index si non identifié
    header('Location: ./index.php');
    exit;
}    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="javascript" href="functions/functions.js">
    <title>My Todolist — Tableau de bord</title>
</head>

<body class="page-app">
    <?php require_once 'functions/menu.php'; //Appel du menu ?>

    <div class="corner-mark cm-tl"></div>
    <div class="corner-mark cm-tr"></div>
    <div class="corner-mark cm-bl"></div>
    <div class="corner-mark cm-br"></div>

    <main>
        <p>Contenu de la todolist à venir.</p>
    </main>

</body>
</html>
 