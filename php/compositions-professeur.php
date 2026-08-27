<?php
/**
 * KLARO - Compositions Créées (Professeur)
 * Liste complète avec titre, type, questions, CODE D'ACCÈS avec bouton copier, date, participants
 */
require_once __DIR__ . '/includes/auth-professeur.php';

$prof_id = $_SESSION['user_id'];

// Suppression d'une composition
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt_del = $pdo->prepare("DELETE FROM compositions WHERE id = ? AND professeur_id = ?");
    $stmt_del->execute([$del_id, $prof_id]);
    header("Location: compositions-professeur.php?msg=deleted");
    exit();
}

// Récupération de toutes les compositions du professeur avec agrégation
$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(DISTINCT q.id) as nb_questions,
           COUNT(DISTINCT e.id) as nb_participants,
           COALESCE(AVG(e.pourcentage), 0) as score_moyen
    FROM compositions c
    LEFT JOIN questions q ON c.id = q.composition_id
    LEFT JOIN entrainements e ON c.id = e.composition_id AND e.statut = 'termine'
    WHERE c.professeur_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute([$prof_id]);
$compositions = $stmt->fetchAll();

$page_title = "Compositions créées";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Professeur -->
        <?php require_once __DIR__ . '/includes/sidebar-professeur.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-6">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Mes Compositions de Quiz</h1>
                    <p class="text-sm text-slate-500">Gère tes quiz, partage les codes d'accès et observe la réussite de tes apprenants.</p>
                </div>
                <a href="creer-quiz.php" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Créer un quiz</span>
                </a>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Composition supprimée avec succès.</span>
                </div>
            <?php endif; ?>

            <!-- Table des Compositions -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <?php if (empty($compositions)): ?>
                    <div class="text-center py-16 px-4">
                        <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="file-plus" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Aucune composition pour le moment</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Colle ton premier quiz pour obtenir un code d'accès à distribuer à tes élèves.</p>
                        <a href="creer-quiz.php" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-xl shadow-xs">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Créer ma première composition</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <tr>
                                    <th class="py-4 px-6">Code d'accès</th>
                                    <th class="py-4 px-6">Titre de la Composition</th>
                                    <th class="py-4 px-6">Type</th>
                                    <th class="py-4 px-6">Questions</th>
                                    <th class="py-4 px-6">Participants</th>
                                    <th class="py-4 px-6">Score Moyen</th>
                                    <th class="py-4 px-6">Date</th>
                                    <th class="py-4 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php foreach ($compositions as $comp): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <!-- Code d'accès avec bouton Copier -->
                                        <td class="py-4 px-6">
                                            <button onclick="copyCode('<?= e($comp['code_acces']) ?>')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-orange-100 text-orange-700 font-mono font-black text-xs hover:bg-orange-200 transition-all">
                                                <span><?= e($comp['code_acces']) ?></span>
                                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </td>
                                        <td class="py-4 px-6 font-bold text-slate-900">
                                            <?= e($comp['titre']) ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $comp['type_quiz'] === 'qcm' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' ?>">
                                                <?= strtoupper($comp['type_quiz']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-slate-600">
                                            <?= $comp['nb_questions'] ?>
                                        </td>
                                        <td class="py-4 px-6 text-slate-600">
                                            <?= $comp['nb_participants'] ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="text-xs font-bold <?= $comp['score_moyen'] >= 70 ? 'text-emerald-600' : ($comp['score_moyen'] >= 50 ? 'text-amber-600' : 'text-slate-500') ?>">
                                                <?= round($comp['score_moyen'], 1) ?>%
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-500">
                                            <?= date('d/m/Y', strtotime($comp['created_at'])) ?>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="commencer-quiz.php?code=<?= urlencode($comp['code_acces']) ?>" target="_blank" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Tester">
                                                    <i data-lucide="play" class="w-4 h-4"></i>
                                                </a>
                                                <a href="compositions-professeur.php?delete=<?= $comp['id'] ?>" onclick="return confirm('Supprimer définitivement ce quiz ?')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Supprimer">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </a>
                                            </div>
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

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert("Code de quiz copié : " + code);
    }).catch(() => {
        prompt("Copier le code :", code);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
