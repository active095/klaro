<?php
/**
 * KLARO - Dashboard Apprenant (Étudiant)
 */
require_once __DIR__ . '/includes/auth.php';

$user_id = $_SESSION['user_id'];

// 1. Récupération des informations fraîches de l'utilisateur (Crédits, etc.)
$stmt_user = $pdo->prepare("SELECT nom, prenom, credits FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$current_user = $stmt_user->fetch();
$credits = $current_user['credits'] ?? 0;

// 2. Récupération de la série de streak
$stmt_streak = $pdo->prepare("SELECT jours_consecutifs, dernier_entrainement, record_streak FROM serie_streak WHERE user_id = ?");
$stmt_streak->execute([$user_id]);
$streak = $stmt_streak->fetch();
$streak_days = $streak['jours_consecutifs'] ?? 0;
$last_training = $streak['dernier_entrainement'] ?? null;
$today = date('Y-m-d');

// Vérifier si entraîné aujourd'hui
$stmt_today = $pdo->prepare("SELECT COUNT(*) as cnt FROM entrainements WHERE user_id = ? AND DATE(created_at) = CURDATE()");
$stmt_today->execute([$user_id]);
$trained_today = ($stmt_today->fetch()['cnt'] > 0);

// 3. Calcul SQL des statistiques :
// - Nombre total de compositions passées
// - Taux de réussite moyen (pourcentage moyen)
// - Nombre total de questions répondues
// - Nombre total d'entraînements terminés
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(id) as total_entrainements,
        COUNT(DISTINCT composition_id) as total_compositions,
        COALESCE(AVG(pourcentage), 0) as moyenne_reussite,
        COALESCE(SUM(total_questions), 0) as total_questions
    FROM entrainements 
    WHERE user_id = ? AND statut = 'termine'
");
$stmt_stats->execute([$user_id]);
$stats = $stmt_stats->fetch();

$total_compositions = (int)$stats['total_compositions'];
$moyenne_reussite = round((float)$stats['moyenne_reussite'], 1);
$total_questions = (int)$stats['total_questions'];
$total_entrainements = (int)$stats['total_entrainements'];

// 4. Nombre de fiches dans le Grimoire IA
$stmt_grimoire = $pdo->prepare("SELECT COUNT(*) as total_grimoire FROM grimoire_ia WHERE user_id = ?");
$stmt_grimoire->execute([$user_id]);
$total_grimoire = (int)$stmt_grimoire->fetch()['total_grimoire'];

// 5. Derniers entraînements
$stmt_recent = $pdo->prepare("
    SELECT e.*, c.titre as comp_titre, c.type_quiz, c.code_acces
    FROM entrainements e
    JOIN compositions c ON e.composition_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.created_at DESC
    LIMIT 4
");
$stmt_recent->execute([$user_id]);
$recent_trainings = $stmt_recent->fetchAll();

$page_title = "Dashboard Apprenant";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Apprenant -->
        <?php require_once __DIR__ . '/includes/sidebar-apprenant.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <!-- CARTE HERO ORANGE -->
            <div class="rounded-3xl bg-gradient-to-r from-orange-500 to-amber-500 p-6 sm:p-8 text-white shadow-lg shadow-orange-500/20 relative overflow-hidden">
                <!-- Motif décoratif d'arrière plan -->
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Badge Streak -->
                            <?php if ($streak_days > 0): ?>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-extrabold text-white border border-white/30">
                                    🔥 <?= $streak_days ?> <?= $streak_days > 1 ? 'jours' : 'jour' ?> d'affilée
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white/90 border border-white/20">
                                    ❄️ Aucune série en cours
                                </span>
                            <?php endif; ?>

                            <!-- Badge Entraîné aujourd'hui -->
                            <?php if ($trained_today): ?>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-500/30 backdrop-blur-md text-xs font-bold text-emerald-100 border border-emerald-300/40">
                                    ✅ Entraîné aujourd'hui
                                </span>
                            <?php endif; ?>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            Bonjour <?= e($_SESSION['prenom']) ?> <?= e($_SESSION['nom']) ?>
                        </h1>
                        <p class="text-orange-100 text-sm max-w-md">
                            Prêt à booster tes connaissances ? Choisis un quiz ou génère une fiche IA.
                        </p>

                        <!-- Boutons d'action hero -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="commencer-quiz.php" class="px-5 py-2.5 rounded-xl bg-white text-orange-600 font-bold text-sm shadow-md hover:bg-orange-50 transition-all hover:scale-[1.02] flex items-center gap-2">
                                <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                                <span>S'entraîner</span>
                            </a>
                            <a href="commencer-quiz.php" class="px-5 py-2.5 rounded-xl bg-orange-600/60 hover:bg-orange-600/80 text-white font-bold text-sm border border-white/30 transition-all flex items-center gap-2">
                                <i data-lucide="key-round" class="w-4 h-4"></i>
                                <span>Code de quiz</span>
                            </a>
                            <a href="outils-ia.php" class="px-5 py-2.5 rounded-xl bg-orange-600/60 hover:bg-orange-600/80 text-white font-bold text-sm border border-white/30 transition-all flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Klaro AI</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3 Stats Rapides à Droite -->
                    <div class="grid grid-cols-3 gap-3 sm:gap-4 bg-white/15 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-white/20 w-full xl:w-auto shrink-0 text-center">
                        <div>
                            <div class="text-xl sm:text-2xl font-black text-white count-up" data-target="<?= $total_compositions ?>"><?= $total_compositions ?></div>
                            <div class="text-[11px] font-bold text-orange-100 uppercase tracking-wider mt-0.5">Compositions</div>
                        </div>
                        <div class="border-x border-white/20 px-2 sm:px-4">
                            <div class="text-xl sm:text-2xl font-black text-white"><span class="count-up" data-target="<?= $moyenne_reussite ?>"><?= $moyenne_reussite ?></span>%</div>
                            <div class="text-[11px] font-bold text-orange-100 uppercase tracking-wider mt-0.5">Réussite</div>
                        </div>
                        <div>
                            <div class="text-xl sm:text-2xl font-black text-white count-up" data-target="<?= $credits ?>"><?= $credits ?></div>
                            <div class="text-[11px] font-bold text-orange-100 uppercase tracking-wider mt-0.5">Crédits</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 CARTES STATISTIQUES AVEC ANIMATION COUNT-UP -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Questions Répondues -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-orange-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Questions</span>
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                            <i data-lucide="help-circle" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 count-up" data-target="<?= $total_questions ?>">0</div>
                    <p class="text-xs text-slate-500 mt-1">Questions complétées</p>
                </div>

                <!-- Entraînements -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-blue-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Entraînements</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="timer" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 count-up" data-target="<?= $total_entrainements ?>">0</div>
                    <p class="text-xs text-slate-500 mt-1">Sessions terminées</p>
                </div>

                <!-- Grimoire IA -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Grimoire IA</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="scroll-text" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 count-up" data-target="<?= $total_grimoire ?>">0</div>
                    <p class="text-xs text-slate-500 mt-1">Fiches enregistrées</p>
                </div>

                <!-- Crédits Disponibles -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-amber-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Crédits</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i data-lucide="coins" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 count-up" data-target="<?= $credits ?>">0</div>
                    <p class="text-xs text-slate-500 mt-1">Solde pour Klaro AI</p>
                </div>
            </div>

            <!-- ACTIONS RAPIDES EN GRID -->
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 mb-4">Actions Rapides</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    <a href="commencer-quiz.php" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <i data-lucide="key-round" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-orange-500 transition-colors">Passer un quiz par Code</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Accède instantanément à l'évaluation donnée par ton professeur.</p>
                        </div>
                    </a>

                    <a href="outils-ia.php" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition-all flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <i data-lucide="sparkles" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">Générer avec Klaro AI</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Transforme tes notes de cours et synthèses en quiz automatique.</p>
                        </div>
                    </a>

                    <a href="classrooms.php" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-purple-300 hover:shadow-md transition-all flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-purple-600 transition-colors">Rejoindre un Classroom</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Connecte-toi à ta classe pour retrouver tous les devoirs.</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- DERNIERS ENTRAÎNEMENTS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-extrabold text-slate-900">Dernières Compositions Passées</h2>
                    <a href="historique-entrainement.php" class="text-xs font-bold text-orange-500 hover:text-orange-600">Voir tout →</a>
                </div>

                <?php if (empty($recent_trainings)): ?>
                    <div class="text-center py-10 px-4 rounded-xl bg-slate-50 border border-dashed border-slate-200">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="file-question" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Aucun entraînement pour le moment</p>
                        <p class="text-xs text-slate-500 mt-1">Saisis un code de quiz pour commencer ta première évaluation !</p>
                        <a href="commencer-quiz.php" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-xl shadow-xs">
                            <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
                            <span>Démarrer un quiz</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    <th class="pb-3">Composition</th>
                                    <th class="pb-3">Score</th>
                                    <th class="pb-3">Résultat</th>
                                    <th class="pb-3">Temps</th>
                                    <th class="pb-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($recent_trainings as $item): 
                                    $pct = $item['pourcentage'];
                                    $color = $pct >= 70 ? 'text-emerald-600 bg-emerald-50' : ($pct >= 50 ? 'text-amber-600 bg-amber-50' : 'text-rose-600 bg-rose-50');
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 font-bold text-slate-800">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded"><?= e($item['code_acces']) ?></span>
                                                <span><?= e($item['comp_titre']) ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 font-semibold text-slate-600">
                                            <?= $item['score'] ?> / <?= $item['total_questions'] ?>
                                        </td>
                                        <td class="py-3.5">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold <?= $color ?>">
                                                <?= $item['pourcentage'] ?>%
                                            </span>
                                        </td>
                                        <td class="py-3.5 text-xs text-slate-500 font-medium">
                                            <?= gmdate("i:s", $item['temps_ecoule']) ?> min
                                        </td>
                                        <td class="py-3.5 text-xs text-slate-500">
                                            <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
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

<!-- Script Count-up Animation -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.count-up');
    counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target')) || 0;
        const duration = 1200; // ms
        const startTime = performance.now();
        const isFloat = target % 1 !== 0;

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const current = target * easeProgress;

            counter.textContent = isFloat ? current.toFixed(1) : Math.floor(current);

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                counter.textContent = isFloat ? target.toFixed(1) : target;
            }
        }

        requestAnimationFrame(update);
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
