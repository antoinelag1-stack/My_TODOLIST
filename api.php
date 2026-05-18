<?php
require_once 'config/db.php';
require_once 'functions/functions.php';

// On indique au navigateur que tout ce qui sort de ce fichier est du JSON
header('Content-Type: application/json');

// Sécurité : aucun endpoint n'est accessible sans être connecté
if (!est_connecte()) {
    http_response_code(401);
    echo json_encode(['erreur' => 'Non autorisé']);
    exit;
}

// On récupère l'action demandée et la méthode HTTP utilisée
$method = $_SERVER['REQUEST_METHOD']; // GET, POST, PUT, DELETE ou PATCH
$action = $_GET['action'] ?? '';

// Redirection vers la bonne fonction selon méthode + action
if ($method === 'GET' && $action === 'liste') {
    get_taches();
} elseif ($method === 'POST' && $action === 'creer') {
    creer_tache();
} elseif ($method === 'PUT' && $action === 'modifier') {
    modifier_tache();
} elseif ($method === 'PATCH' && $action === 'statut') {
    changer_statut();
} elseif ($method === 'DELETE' && $action === 'supprimer') {
    supprimer_tache();
} else {
    http_response_code(400);
    echo json_encode(['erreur' => 'Action demandée inconnue']);
    exit;
}

// ─── Fonctions ───────────────────────────────────────

function get_taches() {
    global $pdo;

    $id_user = $_SESSION['user_id'];

    // Récupération des filtres envoyés par le JS
    $statut   = $_GET['statut']   ?? null; // 'à faire', 'en cours', 'terminée', vide ou null = toutes
    $priorite = $_GET['priorite'] ?? null; // 'basse', 'normale', 'haute', vide ou null = toutes
    $tri      = $_GET['tri']      ?? 'date_echeance';
    $ordre    = $_GET['ordre']    ?? 'DESC'; // ASC ou DESC

    // Pagination
    $limite = (int)($_GET['limite'] ?? 10);  // nombre de tâches par page choisi, par défaut sera 10
    $page   = (int)($_GET['page']   ?? 1);   // page demandée
    $offset = ($page - 1) * $limite;         // calcul de l'offset SQL

    // Whitelist des valeurs autorisées pour éviter les injections via tri/ordre
    // On ne peut pas utiliser des paramètres PDO pour ORDER BY
    $tris_autorises   = ['date_creation', 'date_echeance', 'priorite'];
    $ordres_autorises = ['ASC', 'DESC'];
    if (!in_array($tri, $tris_autorises))     $tri    = 'date_creation';
    if (!in_array($ordre, $ordres_autorises)) $ordre  = 'DESC';

    // Construction dynamique de la requête '$sql' selon les filtres actifs
    $sql    = 'SELECT * FROM tasks WHERE id_user = ?';
    $params = [$id_user];

    if (!empty($statut)) {
        $sql     .= ' AND statut = ?'; 
        $params[] = $statut;
    }

    if (!empty($priorite)) {
        $sql     .= ' AND priorite = ?';
        $params[] = $priorite;
    }

    // ORDER BY et LIMIT ne peuvent pas être des paramètres PDO - on les concatène 
    // mais uniquement après la whitelist ci-dessus, donc sans risque d'injection
    $sql .= " ORDER BY $tri $ordre LIMIT $limite OFFSET $offset";

    try {
        $req = $pdo->prepare($sql);
        $req->execute($params);
        $taches = $req->fetchAll();

    } catch (PDOException $e) {         // Comme tous les PDO du code, on les entoure d'un try/catch avec gestion d'erreur
        http_response_code(500);
        echo json_encode(['erreur' => 'Erreur serveur']);
        exit;
    }

    http_response_code(200); // Code de réussite
    echo json_encode($taches);  // Sortie en json de la variable
}

function creer_tache() {
    global $pdo;

    // On récupère les valeurs du formulaire dans des variables
    $id_user        = $_SESSION['user_id'];
    $titre          = $_POST['titre']         ?? null;
    $description    = $_POST['description']   ?? null;
    $date_echeance  = $_POST['date_echeance'] ?? null;
    $priorite       = $_POST['priorite']      ?? 'normale';
    $statut         = $_POST['statut']        ?? 'à faire';

    // Vérification des champs obligatoires titre et date_echeance
    if (empty($titre) || empty($date_echeance)) {
        http_response_code(400);
        echo json_encode(['erreur' => 'Le titre et la date d\'échéance sont obligatoires']);
        exit;
    }

    // Requête d'insertion en base
    $sql = 'INSERT INTO tasks (id_user, titre, description, date_echeance, priorite, statut)
            VALUES (?, ?, ?, ?, ?, ?)';
    $params = [$id_user, $titre, $description, $date_echeance, $priorite, $statut];

    try {          // Comme toutes les PDO du code, on les entoure d'un try/catch et gestion d'erreur
        $req = $pdo->prepare($sql);
        $req->execute($params);

        // On récupère la tâche créé pour l'afficher
        $id_nouv = $pdo->lastInsertId(); // fonction qui récupère l'id de la dernière insertion
        $req2    = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $req2->execute([$id_nouv]);
        $nouv_tache = $req2->fetch(); // stockage de la tache ajoutée en variable
    
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erreur' => 'Erreur serveur']);
        exit;
    }

    http_response_code(201); // Code de réussite de creation
    echo json_encode($nouv_tache); // Sortie en json de la variable
}

function modifier_tache() {
    global $pdo;

    $id_user        = $_SESSION['user_id'];

    // On récupère la data, la méthode PUT necessite cette fonction on ne peut pas faire comme pour GET ou POST
    $data           = json_decode(file_get_contents('php://input'), true); 
    $id_tache       = $data['id'];

    // Vérification : l'id de la tâche est obligatoire
    if (empty($id_tache)) {
        http_response_code(400);
        echo json_encode(['erreur' => 'ID de la tâche manquant']);
        exit;
    }

    // Sécurité : vérification que la tâche appartient bien à l'utilisateur connecté
    $check = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND id_user = ?');
    $check->execute([$id_tache, $id_user]);
    if (!$check->fetch()) {
        http_response_code(401);
        echo json_encode(['erreur' => 'Non autorisé']);
        exit;
    }

    // Construction dynamique : on ne met à jour que les champs envoyés
    $champs  = [];
    $params  = [];

    if (isset($data['titre'])) {
        $champs[]  = 'titre = ?';
        $params[]  = $data['titre'];
    }
    if (isset($data['description'])) {
        $champs[]  = 'description = ?';
        $params[]  = $data['description'];
    }
    if (isset($data['date_echeance'])) {
        $champs[]  = 'date_echeance = ?';
        $params[]  = $data['date_echeance'];
    }
    if (isset($data['priorite'])) {
        $champs[]  = 'priorite = ?';
        $params[]  = $data['priorite'];
    }
    if (isset($data['statut'])) {
        $champs[]  = 'statut = ?';
        $params[]  = $data['statut'];
    }

    // Traitement du cas où tous les champs sont nulls
    if (empty($champs)) {
        http_response_code(400);
        echo json_encode(['erreur' => 'Aucun champ à modifier']);
        exit;
    }

    // On ajoute l'id en dernier paramètre pour le WHERE de la requête
    $params[] = $id_tache;

    // implode sépare les champs avec une virgule : idéal pour construire la requête
    $sql = 'UPDATE tasks SET ' . implode(', ', $champs) . ' WHERE id = ?';

    try {
        $req = $pdo->prepare($sql);
        $req->execute($params);

        // On retourne la tâche modifiée
        $req2 = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $req2->execute([$id_tache]);
        $tache_modif = $req2->fetch();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erreur' => 'Erreur serveur']);
        exit;
    }

    http_response_code(200); // Code de réussite
    echo json_encode($tache_modif);
}

function changer_statut() {
    global $pdo;

    $id_user        = $_SESSION['user_id'];

    // On récupère la data, la méthode PUT necessite cette fonction on ne peut pas faire comme pour GET ou POST
    $data           = json_decode(file_get_contents('php://input'), true); 
    $id_tache       = $data['id'];
    $statut         = $data['statut'];

    // Vérification : l'id et le statut de la tâche sont obligatoires
    if (empty($id_tache)) {
        http_response_code(400);
        echo json_encode(['erreur' => 'ID de la tâche manquant']);
        exit;
    }

    if (empty($statut)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Statut manquant']);
    exit;
    }

    // Sécurité : vérification que la tâche appartient bien à l'utilisateur connecté
    $check = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND id_user = ?');
    $check->execute([$id_tache, $id_user]);
    if (!$check->fetch()) {
        http_response_code(401);
        echo json_encode(['erreur' => 'Non autorisé']);
        exit;
    }

    // Requête de modification du statut en base
    $sql = 'UPDATE tasks SET statut = ? WHERE id = ?'; 

    try {
        $req = $pdo->prepare($sql);
        $req->execute([$statut, $id_tache]);

        // On retourne la tâche modifiée
        $req2 = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $req2->execute([$id_tache]);
        $tache_modif_statut = $req2->fetch();
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erreur' => 'Erreur serveur']);
            exit;
        }

    http_response_code(200);
    echo json_encode($tache_modif_statut);
}

function supprimer_tache() {
    global $pdo;

    $id_user        = $_SESSION['user_id'];

    // On récupère la data, la méthode PUT necessite cette fonction on ne peut pas faire comme pour GET ou POST
    $data           = json_decode(file_get_contents('php://input'), true);
    $id_tache       = $data['id'];

    // Vérification : l'id et le statut de la tâche sont obligatoires
    if (empty($id_tache)) {
        http_response_code(400);
        echo json_encode(['erreur' => 'ID de la tâche manquant']);
        exit;
    }

    // Sécurité : vérification que la tâche appartient bien à l'utilisateur connecté
    $check = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND id_user = ?');
    $check->execute([$id_tache, $id_user]);
    if (!$check->fetch()) {
        http_response_code(401);
        echo json_encode(['erreur' => 'Non autorisé']);
        exit;
    }

    // Requête de suppression en base
    $sql = 'DELETE FROM tasks WHERE id = ?'; 

    try {
        $req = $pdo->prepare($sql);
        $req->execute([$id_tache]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erreur' => 'Erreur serveur']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['succes' => 'Tâche supprimée']);
}
?>