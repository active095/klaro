<?php
/**
 * KLARO - Liste des Étudiants & Statistiques (Professeur)
 */
require_once __DIR__ . '/includes/auth-professeur.php';

$prof_id = $_SESSION['user_id'];

// Récupération des étudiants ayant interagi avec les compositions ou classrooms du professeur
$stmt = $pdo->prepare("
    SELECT u.id, u.nom, u.prenom, u.email, u.derniere_connexion,
           COUNT(DISTINCT e.id) as total_quiz_passes,
           COALESCE(AVG(e.pourcentage), 0) as moyenne_etudiant,
           MAX(e.created_at) as dernier_quiz_date
    FROM users u
    JOIN entrainements e ON u.id = e.user_id
    JOIN compositions c ON e.composition_id = c.id
    WHERE c.professeur_id = ?
    GROUP BY u.id
    ORDER BY u.nom ASC
");
$stmt->execute([$prof_id]);
$etudiants = $stmt->fetchAll();

$page_title = "Gestion des Étudiants";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Professeur -->
        <?php require_once __DIR__ . '/includes/sidebar-professeur.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-6">
            
            <div>
                <h1 class="text-2xl font-black text-slate-900">Suivi des Apprenants</h1>
                <p class="text-sm text-slate-500">Consulte les performances et la fréquence d'entraînement de tes élèves.</p>
            </div>

            <!-- Table des Étudiants -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <?php if (empty($etudiants)): ?>
                    <div class="text-center py-16 px-4">
                        <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="users" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Aucun apprenant enregistré</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Dès que tes élèves passeront tes quiz avec leur code, leurs statistiques apparaîtront ici.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <tr>
                                    <th class="py-4 px-6">Apprenant</th>
                                    <th class="py-4 px-6">Email</th>
                                    <th class="py-4 px-6">Quiz Passés</th>
                                    <th class="py-4 px-6">Moyenne</th>
                                    <th class="py-4 px-6">Dernier Quiz</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php foreach ($etudiants as $st): 
                                    $avg = round($st['moyenne_etudiant'], 1);
                                    $badge = $avg >= 70 ? 'bg-emerald-50 text-emerald-700' : ($avg >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-4 px-6 font-bold text-slate-900">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs">
                                                    <?= strtoupper(substr($st['prenom'], 0, 1) . substr($st['nom'], 0, 1)) ?>
                                                </div>
                                                <span><?= e($st['nom']) ?> <?= e($st['prenom']) ?></span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-slate-600 text-xs font-mono">
                                            <?= e($st['email']) ?>
                                        </td>
                                        <td class="py-4 px-6 font-bold text-slate-800">
                                            <?= $st['total_quiz_passes'] ?> quiz
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold <?= $badge ?>">
                                                <?= $avg ?>%
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-500">
                                            <?= $st['dernier_quiz_date'] ? date('d/m/Y H:i', strtotime($st['dernier_quiz_date'])) : '-' ?>
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
