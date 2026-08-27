<?php
/**
 * KLARO - Protection de session pour le Professeur
 */
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: connexion.php?msg=session_expired");
    exit();
}

if ($_SESSION['role'] !== 'professeur') {
    // Si c'est un étudiant, le rediriger vers son espace étudiant
    if ($_SESSION['role'] === 'apprenant') {
        header("Location: dashboard.php");
        exit();
    }
    header("Location: connexion.php?msg=unauthorized");
    exit();
}
