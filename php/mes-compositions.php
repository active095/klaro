<?php
/**
 * KLARO - Mes Compositions (Étudiant)
 * Historique des quiz passés avec score, cliquable pour le détail
 */
require_once __DIR__ . '/includes/auth.php';

$user_id = $_SESSION['user_id'];

// Récupération de l'historique complet des entraînements
$stmt = $pdo->prepare("
    SELECT e.*, c.titre as comp_titre, c.type_quiz, c.code_acces, c.duree_minutes
    FROM entrainements e
    JOIN compositions c ON e.composition_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$user_id]);
$compositions = $stmt->fetchAll();

// Détail d'un entraînement spécifique si demandé
$detail_id = isset($_GET['detail']) ? (int)$_GET['detail'] : null;
$detail_data = null;
if ($detail_id) {
    $stmt_det = $pdo->prepare("
        SELECT ru.*, q.texte_question, q.type_question,
               rp_user.texte_reponse as rep_user_texte, rp_user.lettre as rep_user_lettre,
               rp_good.texte_reponse as rep_good_texte, rp_good.lettre as rep_good_lettre
        FROM reponses_utilisateur ru
        JOIN questions q ON ru.question_id = q.id
        LEFT JOIN reponses_possibles rp_user ON ru.reponse_choisie_id = rp_user.id
        LEFT JOIN reponses_possibles rp_good ON q.id = rp_good.question_id AND rp_good.est_correcte = 1
        WHERE ru.entrainement_id = ?
        ORDER BY q.ordre ASC
    ");
    $stmt_det->execute([$detail_id]);
    $detail_data = $stmt_det->fetchAll();
}

$page_title = "Mes Compositions";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Apprenant -->
        <?php require_once __DIR__ . '/includes/sidebar-apprenant.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Mes Compositions & Notes</h1>
                    <p class="text-sm text-slate-500">Retrouve l'historique complet de tes évaluations passées et analyse tes erreurs.</p>
                </div>
                <a href="commencer-quiz.php" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    <span>Nouveau quiz</span>
                </a>
            </div>

            <!-- MODAL DÉTAIL DE LA CORRECTION (Si demandé) -->
            <?php if ($detail_id && $detail_data): ?>
                <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 border-orange-300 shadow-md space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center font-bold">
                                <i data-lucide="file-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900">Détail des réponses (Session #<?= $detail_id ?>)</h2>
                                <p class="text-xs text-slate-500">Examen question par question</p>
                            </div>
                        </div>
                        <a href="mes-compositions.php" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200">
                            Fermer le détail ✕
                        </a>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($detail_data as $idx => $d): ?>
                            <div class="p-4 rounded-2xl border <?= $d['est_correcte'] ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' ?> space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-bold text-slate-900">
                                        <span class="text-xs font-mono text-slate-400 mr-1"><?= $idx + 1 ?>.</span>
                                        <?= e($d['texte_question']) ?>
                                    </p>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold shrink-0 <?= $d['est_correcte'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                        <?= $d['est_correcte'] ? 'Correct' : 'Incorrect' ?>
                                    </span>
                                </div>
                                <div class="text-xs space-y-1 pl-4">
                                    <p class="<?= $d['est_correcte'] ? 'text-emerald-700 font-semibold' : 'text-rose-600 font-semibold' ?>">
                                        Votre réponse : <strong><?= e($d['rep_user_lettre'] ?? '-') ?>) <?= e($d['rep_user_texte'] ?? 'Non répondu') ?></strong>
                                    </p>
                                    <?php if (!$d['est_correcte']): ?>
                                        <p class="text-emerald-700 font-semibold">
                                            Bonne réponse attendue : <strong><?= e($d['rep_good_lettre']) ?>) <?= e($d['rep_good_texte']) ?></strong>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Table des Compositions Passées -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <?php if (empty($compositions)): ?>
                    <div class="text-center py-16 px-4">
                        <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="book-open-check" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Aucun quiz passé pour le moment</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Saisis le code d'un quiz pour lancer ta première évaluation et enregistrer ton score.</p>
                        <a href="commencer-quiz.php" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-xl shadow-xs">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                            <span>Entrer un code de quiz</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <tr>
                                    <th class="py-4 px-6">Quiz & Code</th>
                                    <th class="py-4 px-6">Score</th>
                                    <th class="py-4 px-6">Pourcentage</th>
                                    <th class="py-4 px-6">Temps écoulé</th>
                                    <th class="py-4 px-6">Mode Soumission</th>
                                    <th class="py-4 px-6">Date</th>
                                    <th class="py-4 px-6 text-right">Détail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php foreach ($compositions as $c): 
                                    $pct = $c['pourcentage'];
                                    $badge = $pct >= 70 ? 'bg-emerald-50 text-emerald-700' : ($pct >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-slate-900"><?= e($c['comp_titre']) ?></div>
                                            <div class="text-xs font-mono text-slate-400"><?= e($c['code_acces']) ?> · <?= strtoupper($c['type_quiz']) ?></div>
                                        </td>
                                        <td class="py-4 px-6 font-bold text-slate-800">
                                            <?= $c['score'] ?> / <?= $c['total_questions'] ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold <?= $badge ?>">
                                                <?= $c['pourcentage'] ?>%
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-600">
                                            <?= gmdate("i:s", $c['temps_ecoule']) ?> min
                                        </td>
                                        <td class="py-4 px-6 text-xs">
                                            <?= $c['soumission_type'] === 'expiration_temps' ? '<span class="text-rose-600 font-semibold">Temps expiré</span>' : '<span class="text-slate-500">Volontaire</span>' ?>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-500">
                                            <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <a href="mes-compositions.php?detail=<?= $c['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 font-bold text-xs transition-colors">
                                                <span>Voir</span>
                                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
