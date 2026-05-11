<?php
require_once '../config/db.php';
require_once '../functions/functions.php';

$success = null;
$error = null;

// On vérifi si déjà connecté pour rediriger si besoin
if (est_connecte()) {
    header('Location: ../todolist.php');
    exit;

    //requete pdo pour la connexion d'un user avec 'mail' et 'password' 
} else if (isset($_POST['POST-mail']) && isset($_POST['POST-password'])) {
    
    $req = $pdo->prepare('SELECT * FROM users WHERE mail = ?');
    $req->execute([$_POST['POST-mail']]);
    $user = $req->fetch();

    if (isset($user) && password_verify($_POST['POST-password'], $user['password'])) {
        $_SESSION['auth'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_mail'] = $user['mail'];
        header('Location: ../todolist.php');
        exit;
    } else {
        $error = 'Identifiants invalides, veuillez réessayer';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>My Todolist — Connexion</title>
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

            <p class="eyerow">Portail vers vos pensées</p>
            <h1><em>Conne</em>xion</h1>
            <div class="title-rule"></div>
            <?php if ($error): ?> 
            <p style="color:red"><?= htmlspecialchars($error) ?></p><br>
            <?php endif; ?>
                <form class="form_connect btns" method="post">
                    <label class="" for="POST-mail">Adresse mail</label>
                    <input class="form-input" type="text" id="POST-mail" name="POST-mail" value=""  placeholder="Exemple@riot-games.com">
                    <label class="" for="POST-password">Mot de passe</label>
                    <input class="form-input" type="password" id="POST-password" name="POST-password" value="" placeholder="Motdepasse123">
                    <input class="btn btn-primary" type="submit" value="Connexion">
                    <a href="./inscription.php" class="btn btn-secondary">S'inscrire →</a>
                </form>
        </div>
    </main>
</body>
</html>