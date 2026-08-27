<?php
/**
 * KLARO - Historique d'Entraînement Filtrable (Étudiant)
 */
require_once __DIR__ . '/includes/auth.php';

$user_id = $_SESSION['user_id'];

$filter_date = $_GET['date'] ?? '';
$filter_comp = $_GET['composition'] ?? '';

// Construction de la requête filtrée
$sql = "
    SELECT e.*, c.titre as comp_titre, c.code_acces, c.type_quiz
    FROM entrainements e
    JOIN compositions c ON e.composition_id = c.id
    WHERE e.user_id = ?
";
$params = [$user_id];

if (!empty($filter_date)) {
    $sql .= " AND DATE(e.created_at) = ?";
    $params[] = $filter_date;
}
if (!empty($filter_comp)) {
    $sql .= " AND (c.titre LIKE ? OR c.code_acces LIKE ?)";
    $params[] = "%$filter_comp%";
    $params[] = "%$filter_comp%";
}

$sql .= " ORDER BY e.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trainings = $stmt->fetchAll();

$page_title = "Historique d'Entraînement";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Apprenant -->
        <?php require_once __DIR__ . '/includes/sidebar-apprenant.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-6">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Historique d'Entraînement</h1>
                    <p class="text-sm text-slate-500">Filtre et consulte l'ensemble de tes sessions d'évaluation.</p>
                </div>
            </div>

            <!-- Filtres (Date & Composition) -->
            <form method="GET" action="historique-entrainement.php" class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Date exacte</label>
                    <input type="date" name="date" value="<?= e($filter_date) ?>" class="w-full px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Quiz / Code</label>
                    <input type="text" name="composition" value="<?= e($filter_comp) ?>" placeholder="Titre ou KLR-..." class="w-full px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm rounded-xl transition-colors">
                        Filtrer
                    </button>
                    <a href="historique-entrainement.php" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl">
                        Réinitialiser
                    </a>
                </div>
            </form>

            <!-- Table des Résultats -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <?php if (empty($trainings)): ?>
                    <div class="text-center py-16 px-4">
                        <p class="text-sm font-bold text-slate-700">Aucun résultat trouvé pour ces critères</p>
                        <p class="text-xs text-slate-500 mt-1">Essaie d'élargir tes filtres de recherche.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <tr>
                                    <th class="py-4 px-6">Date & Heure</th>
                                    <th class="py-4 px-6">Composition</th>
                                    <th class="py-4 px-6">Score</th>
                                    <th class="py-4 px-6">Pourcentage</th>
                                    <th class="py-4 px-6">Temps Écoulé</th>
                                    <th class="py-4 px-6">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php foreach ($trainings as $t): 
                                    $pct = $t['pourcentage'];
                                    $badge = $pct >= 70 ? 'bg-emerald-50 text-emerald-700' : ($pct >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-4 px-6 text-xs text-slate-500">
                                            <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                        </td>
                                        <td class="py-4 px-6 font-bold text-slate-900">
                                            <div><?= e($t['comp_titre']) ?></div>
                                            <span class="text-xs font-mono text-slate-400"><?= e($t['code_acces']) ?></span>
                                        </td>
                                        <td class="py-4 px-6 text-slate-700 font-semibold">
                                            <?= $t['score'] ?> / <?= $t['total_questions'] ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold <?= $badge ?>">
                                                <?= $t['pourcentage'] ?>%
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-600">
                                            <?= gmdate("i:s", $t['temps_ecoule']) ?> min
                                        </td>
                                        <td class="py-4 px-6 text-xs">
                                            <?= $t['soumission_type'] === 'expiration_temps' ? '<span class="text-rose-600 font-semibold">Temps expiré</span>' : '<span class="text-emerald-600 font-semibold">Terminé</span>' ?>
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
