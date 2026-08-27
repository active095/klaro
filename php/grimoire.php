<?php
/**
 * KLARO - Le Grimoire / Grimoire IA
 * Notes et synthèses générées par l'IA, consultables et supprimables
 */
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_prof = ($_SESSION['role'] === 'professeur');

// Suppression d'une fiche
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt_del = $pdo->prepare("DELETE FROM grimoire_ia WHERE id = ? AND user_id = ?");
    $stmt_del->execute([$del_id, $user_id]);
    header("Location: " . basename($_SERVER['PHP_SELF']) . "?msg=deleted");
    exit();
}

// Recherche & Récupération
$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM grimoire_ia WHERE user_id = ? AND (titre LIKE ? OR contenu_genere LIKE ?) ORDER BY created_at DESC");
    $stmt->execute([$user_id, "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM grimoire_ia WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
}
$fiches = $stmt->fetchAll();

$page_title = "Le Grimoire IA";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar -->
        <?php 
        if ($is_prof) {
            require_once __DIR__ . '/includes/sidebar-professeur.php';
        } else {
            require_once __DIR__ . '/includes/sidebar-apprenant.php';
        }
        ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-6">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Le Grimoire IA</h1>
                    <p class="text-sm text-slate-500">Toutes tes fiches de révision et synthèses générées par l'intelligence artificielle.</p>
                </div>
                <a href="outils-ia.php" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Nouvelle fiche IA</span>
                </a>
            </div>

            <!-- Barre de Recherche -->
            <form method="GET" action="<?= basename($_SERVER['PHP_SELF']) ?>" class="flex gap-2">
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher dans mes fiches..." class="flex-1 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl">
                    Rechercher
                </button>
            </form>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Fiche supprimée avec succès.</span>
                </div>
            <?php endif; ?>

            <!-- Grille des Fiches -->
            <?php if (empty($fiches)): ?>
                <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                        <i data-lucide="scroll-text" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Aucune fiche dans votre Grimoire</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Utilisez l'outil Klaro AI pour résumer vos cours ou générer des quiz sur-mesure.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($fiches as $f): ?>
                        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 flex flex-col justify-between hover:border-orange-200 transition-colors">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-orange-600 bg-orange-50 px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                        <?= $f['type_contenu'] === 'resume' ? 'Synthèse' : 'Quiz IA' ?>
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        <?= date('d/m/Y', strtotime($f['created_at'])) ?>
                                    </span>
                                </div>
                                <h3 class="text-base font-extrabold text-slate-900"><?= e($f['titre']) ?></h3>
                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-xs text-slate-700 whitespace-pre-wrap font-mono line-clamp-6 leading-relaxed">
                                    <?= e($f['contenu_genere']) ?>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <button onclick="alert(<?= json_encode($f['contenu_genere']) ?>)" class="text-xs font-bold text-blue-600 hover:underline">
                                    Lire en entier ↗
                                </button>
                                <a href="<?= basename($_SERVER['PHP_SELF']) ?>?delete=<?= $f['id'] ?>" onclick="return confirm('Supprimer cette fiche du grimoire ?')" class="text-xs text-rose-500 font-bold hover:underline">
                                    Supprimer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
