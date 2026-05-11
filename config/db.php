<?php   // Ce fichier se connecte à la DB selon les données de config.php ou rend une erreur 500 si echec
        // session_start() est ici et nulle part ailleurs, on appelle ce fichier sur toute page affichée
session_start();
require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Connexion BDD impossible : ' . $e->getMessage()]);
    exit;
}
?>