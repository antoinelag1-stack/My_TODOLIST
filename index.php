<?php
require_once './functions/functions.php';  // la fonction est_connecte() y est

if (est_connecte()) {
    header('Location: todolist.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>My Todolist — Bienvenue</title>
</head>
<body class="page-accueil">

    <div class="corner-mark cm-tl"></div>
    <div class="corner-mark cm-tr"></div>
    <div class="corner-mark cm-bl"></div>
    <div class="corner-mark cm-br"></div>

    <main class="accueil-content">
        <div class="hud-box">
            <div class="hud-corner hc-tl"></div>
            <div class="hud-corner hc-tr"></div>
            <div class="hud-corner hc-bl"></div>
            <div class="hud-corner hc-br"></div>

            <p class="eyerow">Authentification requise</p>
            <h1>My <em>Todo</em>list</h1>
            <div class="title-rule"></div>
            <p class="sub">Connectez-vous pour accéder à vos tâches et reprendre là où vous vous êtes arrêté.</p>
            <div class="btns">
                <a href="./login/inscription.php" class="btn btn-primary">S'inscrire</a>
                <a href="./login/connexion.php" class="btn btn-secondary">Se connecter →</a>
            </div>
        </div>
    </main>

</body>
</html>