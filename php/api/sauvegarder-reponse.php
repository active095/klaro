<?php
/**
 * KLARO - API Sauvegarde Périodique des Réponses (AJAX toutes les 30s)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['entrainement_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$entrainement_id = (int)$input['entrainement_id'];
$reponses = $input['reponses'] ?? []; // Map question_id => reponse_id

try {
    // Vérifier que l'entraînement appartient bien à l'utilisateur et est en cours
    $stmt_check = $pdo->prepare("SELECT id, statut FROM entrainements WHERE id = ? AND user_id = ?");
    $stmt_check->execute([$entrainement_id, $user_id]);
    $entrainement = $stmt_check->fetch();

    if (!$entrainement || $entrainement['statut'] !== 'en_cours') {
        echo json_encode(['success' => false, 'error' => 'Entraînement non modifiable']);
        exit();
    }

    $stmt_save = $pdo->prepare("
        INSERT INTO reponses_utilisateur (entrainement_id, question_id, reponse_choisie_id, est_correcte, created_at)
        VALUES (?, ?, ?, (SELECT est_correcte FROM reponses_possibles WHERE id = ?), NOW())
        ON DUPLICATE KEY UPDATE 
            reponse_choisie_id = VALUES(reponse_choisie_id),
            est_correcte = (SELECT est_correcte FROM reponses_possibles WHERE id = VALUES(reponse_choisie_id))
    ");

    foreach ($reponses as $q_id => $r_id) {
        if (!empty($r_id)) {
            $stmt_save->execute([$entrainement_id, (int)$q_id, (int)$r_id, (int)$r_id]);
        }
    }

    echo json_encode(['success' => true, 'timestamp' => date('Y-m-d H:i:s')]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
