<?php
/**
 * KLARO - Classrooms (Étudiant)
 * Liste des classrooms rejoints + Rejoindre via code
 */
require_once __DIR__ . '/includes/auth.php';

$user_id = $_SESSION['user_id'];
$msg = '';
$error = '';

// Traitement pour rejoindre un classroom
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code_classe'])) {
    $code_classe = strtoupper(trim($_POST['code_classe']));

    if (empty($code_classe)) {
        $error = "Veuillez saisir un code de classroom.";
    } else {
        // Trouver le classroom
        $stmt_c = $pdo->prepare("SELECT * FROM classrooms WHERE code_classe = ? AND actif = 1");
        $stmt_c->execute([$code_classe]);
        $classe = $stmt_c->fetch();

        if (!$classe) {
            $error = "Code de classroom invalide ou inactif.";
        } else {
            // Vérifier si déjà membre
            $stmt_m = $pdo->prepare("SELECT id FROM classroom_membres WHERE classroom_id = ? AND user_id = ?");
            $stmt_m->execute([$classe['id'], $user_id]);
            if ($stmt_m->fetch()) {
                $error = "Vous êtes déjà inscrit dans ce classroom.";
            } else {
                $stmt_add = $pdo->prepare("INSERT INTO classroom_membres (classroom_id, user_id, date_rejoint) VALUES (?, ?, NOW())");
                $stmt_add->execute([$classe['id'], $user_id]);
                $msg = "Félicitations ! Vous avez rejoint la classe « " . e($classe['nom']) . " ».";
            }
        }
    }
}

// Récupération des classrooms de l'étudiant
$stmt_list = $pdo->prepare("
    SELECT c.*, u.nom as prof_nom, u.prenom as prof_prenom, cm.date_rejoint,
           (SELECT COUNT(*) FROM classroom_membres WHERE classroom_id = c.id) as total_eleves
    FROM classroom_membres cm
    JOIN classrooms c ON cm.classroom_id = c.id
    JOIN users u ON c.professeur_id = u.id
    WHERE cm.user_id = ? AND c.actif = 1
    ORDER BY cm.date_rejoint DESC
");
$stmt_list->execute([$user_id]);
$my_classrooms = $stmt_list->fetchAll();

$page_title = "Mes Classrooms";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Apprenant -->
        <?php require_once __DIR__ . '/includes/sidebar-apprenant.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Mes Classrooms</h1>
                    <p class="text-sm text-slate-500">Accède aux cours et quiz organisés par tes enseignants.</p>
                </div>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span><?= $msg ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulaire Rejoindre via Code -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="space-y-1">
                        <h2 class="text-lg font-extrabold text-slate-900">Rejoindre un nouveau Classroom</h2>
                        <p class="text-xs text-slate-500">Demande le code classe à ton enseignant (ex: <code class="font-mono text-orange-600 font-bold">CLS-BIO2025</code>)</p>
                    </div>

                    <form method="POST" action="classrooms.php" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <input type="text" name="code_classe" required placeholder="Code Classroom" class="px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-mono uppercase font-bold focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-sm transition-all flex items-center justify-center gap-2">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Rejoindre</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des Classrooms Rejoints -->
            <div class="space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900">Mes Classes Actives (<?= count($my_classrooms) ?>)</h2>

                <?php if (empty($my_classrooms)): ?>
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mx-auto">
                            <i data-lucide="graduation-cap" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Tu n'as rejoint aucun classroom</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">Saisis le code de ta classe ci-dessus pour accéder aux évaluations de ton professeur.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($my_classrooms as $cls): ?>
                            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 hover:border-orange-200 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="text-[11px] font-bold text-orange-600 bg-orange-50 px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                            <?= e($cls['matiere'] ?? 'Général') ?>
                                        </span>
                                        <h3 class="text-lg font-black text-slate-900 mt-2"><?= e($cls['nom']) ?></h3>
                                        <p class="text-xs text-slate-500 mt-0.5">Enseignant : <strong>Prof. <?= e($cls['prof_prenom']) ?> <?= e($cls['prof_nom']) ?></strong></p>
                                    </div>
                                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                        <i data-lucide="book-open" class="w-5 h-5"></i>
                                    </div>
                                </div>

                                <?php if (!empty($cls['description'])): ?>
                                    <p class="text-xs text-slate-600 leading-relaxed"><?= e($cls['description']) ?></p>
                                <?php endif; ?>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                    <span>👥 <?= $cls['total_eleves'] ?> apprenant(s)</span>
                                    <span>Inscrit le <?= date('d/m/Y', strtotime($cls['date_rejoint'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
