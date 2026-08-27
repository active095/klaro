<?php
/**
 * KLARO - Sidebar Apprenant (Étudiant)
 */
$current_page = basename($_SERVER['PHP_SELF']);

$nav_items = [
    ['url' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
    ['url' => 'commencer-quiz.php', 'label' => 'Entrer un Code', 'icon' => 'key-round'],
    ['url' => 'mes-compositions.php', 'label' => 'Mes Compositions', 'icon' => 'file-check'],
    ['url' => 'classrooms.php', 'label' => 'Classrooms', 'icon' => 'graduation-cap'],
    ['url' => 'grimoire.php', 'label' => 'Le Grimoire', 'icon' => 'book-open'],
    ['url' => 'outils-ia.php', 'label' => 'Outils IA (Klaro AI)', 'icon' => 'sparkles'],
    ['url' => 'grimoire-ia.php', 'label' => 'Grimoire IA', 'icon' => 'scroll-text'],
    ['url' => 'historique-entrainement.php', 'label' => 'Hist. Entraînement', 'icon' => 'history'],
    ['url' => 'tous-les-menus.php', 'label' => 'Tous les menus', 'icon' => 'grid'],
];
?>

<aside class="w-full lg:w-64 bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between shrink-0 mb-6 lg:mb-0">
    <div>
        <!-- Profil rapide -->
        <div class="p-3 mb-4 rounded-xl bg-orange-50 border border-orange-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-500 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                <?= strtoupper(substr($_SESSION['prenom'] ?? 'A', 0, 1) . substr($_SESSION['nom'] ?? 'P', 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold text-slate-800 truncate"><?= e($_SESSION['prenom'] ?? '') ?> <?= e($_SESSION['nom'] ?? '') ?></p>
                <p class="text-xs font-semibold text-orange-600 uppercase tracking-wider">Apprenant</p>
            </div>
        </div>

        <nav class="space-y-1">
            <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                <i data-lucide="home" class="w-4 h-4 text-slate-400"></i>
                <span>Accueil</span>
            </a>
            <?php foreach ($nav_items as $item): 
                $is_active = ($current_page === $item['url']);
            ?>
                <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all <?= $is_active ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/30' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                    <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 <?= $is_active ? 'text-white' : 'text-slate-400' ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Déconnexion -->
    <div class="pt-4 mt-6 border-t border-slate-100">
        <a href="deconnexion.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
            <i data-lucide="log-out" class="w-4 h-4"></i>
            <span>Déconnexion</span>
        </a>
    </div>
</aside>
