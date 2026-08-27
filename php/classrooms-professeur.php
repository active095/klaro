<?php
/**
 * KLARO - Gestion des Classrooms (Professeur)
 * CRUD complet, liste des étudiants inscrits, code d'accès classroom
 */
require_once __DIR__ . '/includes/auth-professeur.php';

$prof_id = $_SESSION['user_id'];
$msg = '';
$error = '';

// 1. Création d'un Classroom
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'creer') {
    $nom = trim($_POST['nom'] ?? '');
    $matiere = trim($_POST['matiere'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($nom)) {
        $error = "Le nom du classroom est obligatoire.";
    } else {
        // Génération d'un code de classe unique (ex: CLS-ABC12)
        do {
            $code_classe = 'CLS-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $chk = $pdo->prepare("SELECT id FROM classrooms WHERE code_classe = ?");
            $chk->execute([$code_classe]);
        } while ($chk->fetch());

        $stmt = $pdo->prepare("INSERT INTO classrooms (professeur_id, nom, description, code_classe, matiere, actif, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        if ($stmt->execute([$prof_id, $nom, $description, $code_classe, $matiere])) {
            $msg = "Classroom « " . e($nom) . " » créé avec succès ! Code d'accès : <strong>$code_classe</strong>";
        }
    }
}

// 2. Suppression d'un Classroom
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt_del = $pdo->prepare("DELETE FROM classrooms WHERE id = ? AND professeur_id = ?");
    $stmt_del->execute([$del_id, $prof_id]);
    header("Location: classrooms-professeur.php?msg=deleted");
    exit();
}

// 3. Récupération des classrooms du professeur
$stmt_list = $pdo->prepare("
    SELECT c.*, 
           COUNT(cm.user_id) as total_membres
    FROM classrooms c
    LEFT JOIN classroom_membres cm ON c.id = cm.classroom_id
    WHERE c.professeur_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt_list->execute([$prof_id]);
$classrooms = $stmt_list->fetchAll();

// 4. Détail des étudiants d'un classroom
$view_class_id = isset($_GET['view']) ? (int)$_GET['view'] : null;
$class_students = [];
$view_class_info = null;

if ($view_class_id) {
    $stmt_cinfo = $pdo->prepare("SELECT * FROM classrooms WHERE id = ? AND professeur_id = ?");
    $stmt_cinfo->execute([$view_class_id, $prof_id]);
    $view_class_info = $stmt_cinfo->fetch();

    if ($view_class_info) {
        $stmt_st = $pdo->prepare("
            SELECT u.id, u.nom, u.prenom, u.email, cm.date_rejoint,
                   (SELECT COUNT(*) FROM entrainements WHERE user_id = u.id) as total_quiz_faits
            FROM classroom_membres cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.classroom_id = ?
            ORDER BY u.nom ASC
        ");
        $stmt_st->execute([$view_class_id]);
        $class_students = $stmt_st->fetchAll();
    }
}

$page_title = "Mes Classrooms Enseignant";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Professeur -->
        <?php require_once __DIR__ . '/includes/sidebar-professeur.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Gestion des Classrooms</h1>
                    <p class="text-sm text-slate-500">Crée des espaces de classe, distribue les codes et supervise tes étudiants inscrits.</p>
                </div>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span><?= $msg ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>Classroom supprimé avec succès.</span>
                </div>
            <?php endif; ?>

            <!-- Formulaire Nouveau Classroom -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center font-bold text-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-lg font-black text-slate-900">Créer un nouveau Classroom</h2>
                </div>

                <form method="POST" action="classrooms-professeur.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" name="action" value="creer">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nom de la classe *</label>
                        <input type="text" name="nom" required placeholder="Ex: Terminale S - SVT" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Matière</label>
                        <input type="text" name="matiere" placeholder="Ex: Biologie & Géologie" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description courte</label>
                        <input type="text" name="description" placeholder="Ex: Préparation aux examens nationaux" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Créer la classe</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- MODAL / SECTION ÉTUDIANTS DU CLASSROOM -->
            <?php if ($view_class_info): ?>
                <div class="bg-white rounded-3xl p-6 sm:p-8 border-2 border-orange-300 shadow-md space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Apprenants inscrits : « <?= e($view_class_info['nom']) ?> »</h2>
                            <p class="text-xs text-slate-500">Code d'accès : <strong class="font-mono text-orange-600"><?= e($view_class_info['code_classe']) ?></strong></p>
                        </div>
                        <a href="classrooms-professeur.php" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200">
                            Fermer ✕
                        </a>
                    </div>

                    <?php if (empty($class_students)): ?>
                        <p class="text-sm text-slate-500 py-6 text-center">Aucun apprenant n'a encore rejoint cette classe avec le code.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b">
                                        <th class="pb-2">Nom & Prénom</th>
                                        <th class="pb-2">Email</th>
                                        <th class="pb-2">Quiz terminés</th>
                                        <th class="pb-2">Date d'inscription</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($class_students as $st): ?>
                                        <tr>
                                            <td class="py-3 font-bold text-slate-800"><?= e($st['nom']) ?> <?= e($st['prenom']) ?></td>
                                            <td class="py-3 text-slate-600"><?= e($st['email']) ?></td>
                                            <td class="py-3 font-semibold text-orange-600"><?= $st['total_quiz_faits'] ?> quiz</td>
                                            <td class="py-3 text-xs text-slate-500"><?= date('d/m/Y', strtotime($st['date_rejoint'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Table des Classrooms Existants -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <?php if (empty($classrooms)): ?>
                    <div class="text-center py-16 px-4">
                        <p class="text-sm font-bold text-slate-700">Aucun classroom actif</p>
                        <p class="text-xs text-slate-500 mt-1">Crée ton premier classroom pour organiser tes élèves.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <tr>
                                    <th class="py-4 px-6">Code Classe</th>
                                    <th class="py-4 px-6">Nom du Classroom</th>
                                    <th class="py-4 px-6">Matière</th>
                                    <th class="py-4 px-6">Membres</th>
                                    <th class="py-4 px-6">Date de création</th>
                                    <th class="py-4 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php foreach ($classrooms as $cls): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-4 px-6">
                                            <button onclick="copyCode('<?= e($cls['code_classe']) ?>')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-purple-100 text-purple-700 font-mono font-black text-xs hover:bg-purple-200 transition-all">
                                                <span><?= e($cls['code_classe']) ?></span>
                                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </td>
                                        <td class="py-4 px-6 font-bold text-slate-900">
                                            <?= e($cls['nom']) ?>
                                        </td>
                                        <td class="py-4 px-6 text-slate-600">
                                            <?= e($cls['matiere'] ?? 'Général') ?>
                                        </td>
                                        <td class="py-4 px-6 font-semibold text-slate-700">
                                            👥 <?= $cls['total_membres'] ?> élève(s)
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-500">
                                            <?= date('d/m/Y', strtotime($cls['created_at'])) ?>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="classrooms-professeur.php?view=<?= $cls['id'] ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir les élèves">
                                                    <i data-lucide="users" class="w-4 h-4"></i>
                                                </a>
                                                <a href="classrooms-professeur.php?delete=<?= $cls['id'] ?>" onclick="return confirm('Supprimer ce classroom ?')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Supprimer">
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
        alert("Code de classroom copié : " + code);
    }).catch(() => {
        prompt("Copier le code :", code);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
