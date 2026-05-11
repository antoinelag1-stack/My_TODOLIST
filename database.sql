/* Ce fichier créé la DB complète et y insère un user pour le testing */

CREATE DATABASE IF NOT EXISTS my_todolist
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE my_todolist;

CREATE TABLE IF NOT EXISTS users (
    id               INT(11)      PRIMARY KEY NOT NULL AUTO_INCREMENT,
    mail             VARCHAR(255) NOT NULL UNIQUE,
    password         VARCHAR(255) NOT NULL,
    nom              VARCHAR(25)  NOT NULL,
    date_inscription DATETIME     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id             INT(11)      PRIMARY KEY NOT NULL AUTO_INCREMENT,
    id_user        INT(11)      NOT NULL,
    titre          VARCHAR(100) NOT NULL,
    description    VARCHAR(500),
    date_echeance  DATE         NOT NULL,
    priorite       ENUM('basse', 'normale', 'haute')       NOT NULL DEFAULT 'normale',
    statut         ENUM('à faire', 'en cours', 'terminée') NOT NULL DEFAULT 'à faire',
    date_creation  DATETIME     DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_user
        FOREIGN KEY (id_user) REFERENCES users(id)
        ON DELETE CASCADE
);

-- Insertion des données de test --
INSERT INTO users (mail, password, nom) VALUES
('test@test.test', '$2y$10$SbZhLXk3fqpq8yQZORITx.GNMSKaOPF8rqQ1edvjarbZ4t2R6clqq', 'Utilisateur Test');

INSERT INTO tasks (id_user, titre, description, date_echeance, priorite, statut) VALUES
(1, 'Tâche de démonstration', 'Une tâche pour test affichage normal', '2025-12-31', 'normale', 'à faire'),
(1, 'Tâche en retard', 'Cette tâche est en retard', '2024-01-01', 'haute', 'en cours');