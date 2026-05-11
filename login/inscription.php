<?php
require_once '../config/db.php';
require_once '../functions/functions.php';

$success = null;
$error = null;

// Vérification que tous les champs soient remplis et correspondent puis ajout en base et message de retour au user
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = $_POST['POST-mail'] ?? '';
    $password = $_POST['POST-password'] ?? '';
    $nom = $_POST['POST-nom'] ?? '';

    if (empty($mail) || empty($password) || empty($nom)) {
        $error = "Tous les champs sont requis.";
    } elseif ($password !== $_POST['POST-password-check']) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (mail,password,nom) VALUES (?, ?, ?)");
        $stmt->execute([$mail, $hashed, $nom]);
        $success = "Utilisateur ajouté avec succès !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>My Todolist — Inscription</title>
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

            <p class="eyerow">vos meilleurs projets commencent ici</p>
            <h1><em>Inscrip</em>tion</h1>
            <div class="title-rule"></div>
                <?php if ($error): ?> 
                    <p style="color:red"><?= htmlspecialchars($error) ?></p><br>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p style="color:green"><?= htmlspecialchars($success) ?></p><br>
                <?php endif; ?>

                <form class="form_connect btns" method="post" action="./inscription.php">
                    <label class="" for="POST-nom">Nom</label>
                    <input class="form-input" type="text" id="POST-nom" name="POST-nom" value=""  placeholder="Antoine LAGIER">
                    <label class="" for="POST-mail">Adresse mail</label>
                    <input class="form-input" type="text" id="POST-mail" name="POST-mail" value=""  placeholder="Exemple@riot-games.com">
                    <label class="" for="POST-password">Créez votre mot de passe</label>
                    <input class="form-input" type="password" id="POST-password" name="POST-password" value="" placeholder="Motdepasse123">
                    <label class="" for="POST-password-check">Confirmez votre mot de passe</label>
                    <input class="form-input" type="password" id="POST-password-check" name="POST-password-check" value="" placeholder="Motdepasse123">
                    <input class="btn btn-primary" type="submit" value="Inscription">
                    <a href="./connexion.php" class="btn btn-secondary">Se connecter →</a>
                </form>
        </div>
    </main>
</body>
</html>