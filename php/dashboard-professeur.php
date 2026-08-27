<?php
/**
 * KLARO - Dashboard Professeur (dashboard-professeur.php)
 */
require_once __DIR__ . '/includes/auth-professeur.php';

$prof_id = $_SESSION['user_id'];

// 1. Récupération des infos fraîches du professeur
$stmt_prof = $pdo->prepare("SELECT nom, prenom, derniere_connexion FROM users WHERE id = ?");
$stmt_prof->execute([$prof_id]);
$prof_data = $stmt_prof->fetch();

$last_login = $prof_data['derniere_connexion'] ? date('d/m/Y H:i', strtotime($prof_data['derniere_connexion'])) : 'Aujourd\'hui';

// 2. Classrooms actifs du professeur
$stmt_classrooms = $pdo->prepare("SELECT COUNT(*) as total_classrooms FROM classrooms WHERE professeur_id = ? AND actif = 1");
$stmt_classrooms->execute([$prof_id]);
$total_classrooms = (int)$stmt_classrooms->fetch()['total_classrooms'];

// 3. Compositions créées par le professeur
$stmt_comp = $pdo->prepare("SELECT COUNT(*) as total_comp FROM compositions WHERE professeur_id = ?");
$stmt_comp->execute([$prof_id]);
$total_compositions = (int)$stmt_comp->fetch()['total_comp'];

// 4. Nombre d'étudiants uniques ayant passé ses compositions
$stmt_students = $pdo->prepare("
    SELECT COUNT(DISTINCT e.user_id) as total_students,
           COALESCE(AVG(e.pourcentage), 0) as moyenne_reussite,
           COUNT(e.id) as total_participations
    FROM entrainements e
    JOIN compositions c ON e.composition_id = c.id
    WHERE c.professeur_id = ? AND e.statut = 'termine'
");
$stmt_students->execute([$prof_id]);
$student_stats = $stmt_students->fetch();

$total_etudiants = (int)$student_stats['total_students'];
$moyenne_reussite = round((float)$student_stats['moyenne_reussite'], 1);
$total_participations = (int)$student_stats['total_participations'];

// 5. Étudiants actifs sur les 30 derniers jours
$stmt_active_30d = $pdo->prepare("
    SELECT COUNT(DISTINCT e.user_id) as active_students
    FROM entrainements e
    JOIN compositions c ON e.composition_id = c.id
    WHERE c.professeur_id = ? AND e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$stmt_active_30d->execute([$prof_id]);
$active_students_30d = (int)$stmt_active_30d->fetch()['active_students'];

// 6. Formule du Taux d'Engagement :
// Ratio entre le nombre réel de passages et le volume de compositions créées.
// Formule : Si total_compositions > 0 : min(100, round((total_participations / (total_compositions * 3)) * 100)) sinon 0%
$taux_engagement = 0;
if ($total_compositions > 0) {
    // Si en moyenne chaque composition a été passée au moins 5 fois, l'engagement est à 100%
    $taux_engagement = min(100, round(($total_participations / max(1, $total_compositions * 5)) * 100));
}

// 7. Dernières compositions avec statistiques de passage
$stmt_recent_comp = $pdo->prepare("
    SELECT c.*, 
           COUNT(DISTINCT q.id) as nb_questions,
           COUNT(DISTINCT e.id) as nb_participants
    FROM compositions c
    LEFT JOIN questions q ON c.id = q.composition_id
    LEFT JOIN entrainements e ON c.id = e.composition_id AND e.statut = 'termine'
    WHERE c.professeur_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 4
");
$stmt_recent_comp->execute([$prof_id]);
$recent_compositions = $stmt_recent_comp->fetchAll();

$page_title = "Dashboard Enseignant";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Professeur -->
        <?php require_once __DIR__ . '/includes/sidebar-professeur.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <!-- CARTE HERO ORANGE -->
            <div class="rounded-3xl bg-gradient-to-r from-orange-500 to-amber-500 p-6 sm:p-8 text-white shadow-lg shadow-orange-500/20 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Badge Classrooms Actifs -->
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white border border-white/30">
                                🎓 <?= $total_classrooms ?> classroom<?= $total_classrooms > 1 ? 's' : '' ?> actif<?= $total_classrooms > 1 ? 's' : '' ?>
                            </span>

                            <!-- Badge Dernière Connexion Réelle -->
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-white/90 border border-white/20">
                                🕒 Dernière connexion : <?= e($last_login) ?>
                            </span>
                        </div>

                        <div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                Bonjour <?= e($_SESSION['prenom']) ?> <?= e($_SESSION['nom']) ?>
                            </h1>
                            <p class="text-orange-100 text-xs font-semibold uppercase tracking-wider mt-0.5">
                                Espace Enseignant · Bénin
                            </p>
                        </div>
                        <p class="text-orange-100 text-sm max-w-md">
                            Gère tes évaluations formatives, génère des codes de révision et suis la réussite de tes apprenants.
                        </p>

                        <!-- Boutons d'action hero -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="creer-quiz.php" class="px-5 py-2.5 rounded-xl bg-white text-orange-600 font-bold text-sm shadow-md hover:bg-orange-50 transition-all hover:scale-[1.02] flex items-center gap-2">
                                <i data-lucide="plus-circle" class="w-4 h-4 text-orange-600"></i>
                                <span>Nouvelle composition</span>
                            </a>
                            <a href="classrooms-professeur.php" class="px-5 py-2.5 rounded-xl bg-orange-600/60 hover:bg-orange-600/80 text-white font-bold text-sm border border-white/30 transition-all flex items-center gap-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                <span>Créer un classroom</span>
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
                            <div class="text-xl sm:text-2xl font-black text-white count-up" data-target="<?= $total_etudiants ?>"><?= $total_etudiants ?></div>
                            <div class="text-[11px] font-bold text-orange-100 uppercase tracking-wider mt-0.5">Étudiants</div>
                        </div>
                        <div class="border-x border-white/20 px-2 sm:px-4">
                            <div class="text-xl sm:text-2xl font-black text-white count-up" data-target="<?= $total_compositions ?>"><?= $total_compositions ?></div>
                            <div class="text-[11px] font-bold text-orange-100 uppercase tracking-wider mt-0.5">Compositions</div>
                        </div>
                        <div>
                            <div class="text-xl sm:text-2xl font-black text-white"><span class="count-up" data-target="<?= $moyenne_reussite ?>"><?= $moyenne_reussite ?></span>%</div>
                            <div class="text-[11px] font-bold text-orange-100 uppercase tracking-wider mt-0.5">Réussite moy.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 CARTES STATISTIQUES AVEC ANIMATION COUNT-UP -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Classrooms Actifs -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-orange-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Classrooms</span>
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 count-up" data-target="<?= $total_classrooms ?>">0</div>
                    <p class="text-xs text-slate-500 mt-1">Classes actives</p>
                </div>

                <!-- Étudiants Actifs (30j) -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-blue-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Étudiants Actifs</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 count-up" data-target="<?= $active_students_30d ?>">0</div>
                    <p class="text-xs text-slate-500 mt-1">Actifs sur 30 jours</p>
                </div>

                <!-- Compositions Publiées -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-emerald-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Compositions</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="file-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 count-up" data-target="<?= $total_compositions ?>">0</div>
                    <p class="text-xs text-slate-500 mt-1">Quiz avec code d'accès</p>
                </div>

                <!-- Taux d'Engagement -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 hover:border-amber-200 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Engagement</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900"><span class="count-up" data-target="<?= $taux_engagement ?>">0</span>%</div>
                    <p class="text-xs text-slate-500 mt-1">Participation relative</p>
                </div>
            </div>

            <!-- COMPOSITIONS RÉCENTES CRÉÉES -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-extrabold text-slate-900">Dernières Compositions Publiées</h2>
                    <a href="compositions-professeur.php" class="text-xs font-bold text-orange-500 hover:text-orange-600">Gérer tout →</a>
                </div>

                <?php if (empty($recent_compositions)): ?>
                    <div class="text-center py-10 px-4 rounded-xl bg-slate-50 border border-dashed border-slate-200">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="plus-circle" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Aucune composition créée</p>
                        <p class="text-xs text-slate-500 mt-1">Colle ton premier quiz pour obtenir un code d'accès à partager à tes élèves.</p>
                        <a href="creer-quiz.php" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-xl shadow-xs">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Créer un quiz maintenant</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                    <th class="pb-3">Code & Titre</th>
                                    <th class="pb-3">Type</th>
                                    <th class="pb-3">Questions</th>
                                    <th class="pb-3">Participants</th>
                                    <th class="pb-3">Durée</th>
                                    <th class="pb-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($recent_compositions as $comp): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5">
                                            <div class="flex items-center gap-2">
                                                <button onclick="copyCode('<?= e($comp['code_acces']) ?>')" title="Cliquer pour copier" class="px-2 py-1 rounded bg-orange-100 text-orange-700 text-xs font-mono font-bold hover:bg-orange-200 transition-colors flex items-center gap-1">
                                                    <span><?= e($comp['code_acces']) ?></span>
                                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                                </button>
                                                <span class="font-bold text-slate-800"><?= e($comp['titre']) ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3.5">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $comp['type_quiz'] === 'qcm' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' ?>">
                                                <?= strtoupper($comp['type_quiz']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 font-semibold text-slate-600">
                                            <?= $comp['nb_questions'] ?> question<?= $comp['nb_questions'] > 1 ? 's' : '' ?>
                                        </td>
                                        <td class="py-3.5 font-semibold text-slate-600">
                                            <?= $comp['nb_participants'] ?> participant<?= $comp['nb_participants'] > 1 ? 's' : '' ?>
                                        </td>
                                        <td class="py-3.5 text-xs text-slate-500">
                                            <?= $comp['duree_minutes'] > 0 ? $comp['duree_minutes'] . ' min' : 'Illimitée' ?>
                                        </td>
                                        <td class="py-3.5 text-right">
                                            <a href="commencer-quiz.php?code=<?= urlencode($comp['code_acces']) ?>" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700">
                                                <span>Tester</span>
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
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

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert("Code copié dans le presse-papier : " + code);
    }).catch(() => {
        prompt("Copiez le code ci-dessous :", code);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.count-up');
    counters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target')) || 0;
        const duration = 1200;
        const startTime = performance.now();
        const isFloat = target % 1 !== 0;

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
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
