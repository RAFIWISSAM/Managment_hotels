<?php
// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration du site
define('SITE_NAME', 'Système de Réservation d\'Hôtels');
define('SITE_URL', 'http://localhost/hotel_management_system');

// Configuration des sessions
session_start();

// Fonction pour gérer les erreurs
function handleError($error) {
    // À développer plus tard
    echo "Une erreur est survenue : " . $error;
}

// Fonction pour la sécurité
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
