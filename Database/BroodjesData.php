<?php

require_once __DIR__ . '/Connection.php';

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS broodjes_week_bestellingen (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        naam VARCHAR(100) NOT NULL,
        week_start DATE NOT NULL,
        dag ENUM('maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag') NOT NULL,
        broodje ENUM('kaas', 'vlees', 'hagelslag', 'krentenbol') NOT NULL,
        vrijdag_keuze ENUM('kroket', 'frikandel') DEFAULT NULL,
        UNIQUE KEY unieke_persoon_week_dag (naam, week_start, dag)
    )"
);
