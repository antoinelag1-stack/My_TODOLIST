<?php
require_once 'config/db.php';
require_once 'functions/functions.php';

if (!est_connecte()) {
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
    <title>My Todolist — Tableau de bord</title>
</head>
<body class="page-app">

    <?php require_once 'functions/menu.php'; ?>

    <div class="corner-mark cm-tl"></div>
    <div class="corner-mark cm-tr"></div>
    <div class="corner-mark cm-bl"></div>
    <div class="corner-mark cm-br"></div>

    <!-- Barre de filtres -->
    <div class="filters-bar">
        <div class="filter-group">
            <span class="filter-label">Priorité</span>
            <select class="filter-select" id="filtre-priorite">
                <option value="">Toutes</option>
                <option value="haute">Haute</option>
                <option value="normale">Normale</option>
                <option value="basse">Basse</option>
            </select>
        </div>
        <div class="filter-sep"></div>
        <div class="filter-group">
            <span class="filter-label">Trier par</span>
            <select class="filter-select" id="filtre-tri">
                <option value="date_creation">Date création</option>
                <option value="date_echeance">Date échéance</option>
                <option value="priorite">Priorité</option>
            </select>
        </div>
        <div class="filter-sep"></div>
        <div class="filter-group">
            <span class="filter-label">Résultats</span>
            <select class="filter-select" id="filtre-limite">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="9999">Toutes</option>
            </select>
        </div>
        <button class="btn btn-primary btn-new" id="btn-nouvelle-tache">+ Nouvelle tâche</button>
    </div>

    <!-- Zone de notification -->
    <div id="notif" class="notif" style="display:none;"></div>

    <!-- Kanban -->
    <div class="kanban">

        <!-- Colonne À faire -->
        <div class="kanban-col" id="col-afaire">
            <div class="col-header">
                <span class="col-title">À faire</span>
                <span class="col-count" id="count-afaire">0</span>
            </div>
            <div class="col-cards" id="cards-afaire">
                <!-- Les cards seront injectées ici par le JS -->
            </div>
        </div>

        <!-- Colonne En cours -->
        <div class="kanban-col" id="col-encours">
            <div class="col-header">
                <span class="col-title">En cours</span>
                <span class="col-count" id="count-encours">0</span>
            </div>
            <div class="col-cards" id="cards-encours">
                <!-- Les cards seront injectées ici par le JS -->
            </div>
        </div>

        <!-- Colonne Terminée -->
        <div class="kanban-col" id="col-terminee">
            <div class="col-header">
                <span class="col-title">Terminée</span>
                <span class="col-count" id="count-terminee">0</span>
            </div>
            <div class="col-cards" id="cards-terminee">
                <!-- Les cards seront injectées ici par le JS -->
            </div>
        </div>

    </div>

    <!-- Modale création / modification -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal-box">
            <div class="hud-corner hc-tl"></div>
            <div class="hud-corner hc-tr"></div>
            <div class="hud-corner hc-bl"></div>
            <div class="hud-corner hc-br"></div>

            <p class="eyerow" id="modal-eyerow">Nouvelle tâche</p>
            <h2 id="modal-titre-label">Créer une tâche</h2>
            <div class="title-rule"></div>

            <form id="form-tache">
                <!-- Champ caché pour l'id lors d'une modification -->
                <input type="hidden" id="tache-id">

                <label for="tache-titre">Titre</label>
                <input class="form-input" type="text" id="tache-titre" placeholder="Titre de la tâche">

                <label for="tache-description">Description</label>
                <textarea class="form-input form-textarea" id="tache-description" placeholder="Description (optionnelle)"></textarea>

                <label for="tache-date">Date d'échéance</label>
                <input class="form-input" type="date" id="tache-date">

                <label for="tache-priorite">Priorité</label>
                <select class="form-input" id="tache-priorite">
                    <option value="normale">Normale</option>
                    <option value="haute">Haute</option>
                    <option value="basse">Basse</option>
                </select>

                <div class="modal-btns">
                    <button type="submit" class="btn btn-primary" id="btn-submit-tache">Créer</button>
                    <button type="button" class="btn btn-secondary" id="btn-annuler">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script src="app.js"></script>

</body>
</html>