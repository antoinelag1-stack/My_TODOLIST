<?php
function est_connecte() {
    return isset($_SESSION['auth']); // Vérifie que la variable a bien été remplie à la connexion
};
?>