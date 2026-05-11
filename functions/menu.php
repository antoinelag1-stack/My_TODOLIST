<?php require_once __DIR__ . '/functions.php'; 
// DIR permet de toujours remettre le path à partir de menu.php pour ses effets peu importe la page où il est appelé ?>

<nav class="hud-menu">
    <a href="/my_todolist/todolist.php" class="menu-logo">My <em>Todo</em>list</a>
    <div class="menu-right">
        <span class="menu-user"><?= htmlspecialchars($_SESSION['user_nom']) ?></span>
        <div class="menu-sep"></div>
        <a href="/my_todolist/login/deconnexion.php" class="btn btn-secondary">Se déconnecter</a>
    </div>
</nav>