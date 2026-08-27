<?php
/**
 * KLARO - Configuration & Connexion Base de Données (PDO)
 * Réalisé par : Henri Joël HOUNKPE
 */

// Paramètres de connexion MySQL (XAMPP par défaut)
define('DB_HOST', 'localhost');
define('DB_NAME', 'klaro_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Options de sécurité et de performance PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Message propre en cas d'erreur sans fuite d'informations sensibles en production
    error_log("Erreur de connexion PDO : " . $e->getMessage());
    die("<div style='font-family:sans-serif;padding:30px;background:#FEF2F2;color:#991B1B;border-radius:12px;max-width:600px;margin:50px auto;border:1px solid #F87171;'>
        <h2 style='margin-top:0;'>⚠️ Erreur de connexion à la base de données</h2>
        <p>Impossible d'établir la connexion avec la base de données <strong>klaro_db</strong>.</p>
        <p style='font-size:14px;color:#7F1D1D;'>Vérifiez que MySQL est démarré sur XAMPP et que le script <code>klaro_schema.sql</code> a bien été importé.</p>
    </div>");
}

/**
 * Fonction de sécurisation de l'affichage HTML (XSS prevention)
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Initialisation propre de la session si non active
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
