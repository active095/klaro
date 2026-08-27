<?php
/**
 * KLARO - Protection de session pour les Apprenants (Étudiants)
 */
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: connexion.php?msg=session_expired");
    exit();
}

if ($_SESSION['role'] !== 'apprenant') {
    // Si l'utilisateur est connecté en tant que professeur, le rediriger vers son espace
    if ($_SESSION['role'] === 'professeur') {
        header("Location: dashboard-professeur.php");
        exit();
    }
    header("Location: connexion.php?msg=unauthorized");
    exit();
}
