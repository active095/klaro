<?php
/**
 * KLARO - Header commun & Navigation sticky
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_role = $is_logged ? ($_SESSION['role'] ?? 'apprenant') : null;
$dashboard_link = ($user_role === 'professeur') ? 'dashboard-professeur.php' : 'dashboard.php';
$current_page = basename($_SERVER['PHP_SELF']);
$is_landing_page = ($current_page === 'index.php');

$prenom = $_SESSION['prenom'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$credits = $_SESSION['credits'] ?? 0;

// Requête PDO préparée pour rafraîchir les informations réelles de la base
if ($is_logged && isset($pdo)) {
    try {
        $stmt_fresh = $pdo->prepare("SELECT prenom, nom, credits, role FROM users WHERE id = ?");
        $stmt_fresh->execute([$_SESSION['user_id']]);
        $fresh_user = $stmt_fresh->fetch();
        if ($fresh_user) {
            $prenom = $fresh_user['prenom'];
            $nom = $fresh_user['nom'];
            $credits = (int)$fresh_user['credits'];
            $user_role = $fresh_user['role'];
            $dashboard_link = ($user_role === 'professeur') ? 'dashboard-professeur.php' : 'dashboard.php';
        }
    } catch (Exception $e) {
        // En cas d'erreur de connexion, les variables $_SESSION servent de secours
    }
}

// Initiales calculées dynamiquement à partir du prénom et nom réels
$p_init = !empty($prenom) ? mb_substr(trim($prenom), 0, 1, 'UTF-8') : '';
$n_init = !empty($nom) ? mb_substr(trim($nom), 0, 1, 'UTF-8') : '';
$initials = strtoupper($p_init . $n_init);
if (empty($initials)) {
    $initials = ($user_role === 'professeur') ? 'PR' : 'ET';
}
$role_label = ($user_role === 'professeur') ? 'Professeur' : 'Apprenant';

if (!function_exists('e')) {
    function e($val) {
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' | Klaro' : 'Klaro — Quiz adaptatif IA' ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        klaro: {
                            orange: '#F97316',
                            orangeHover: '#EA580C',
                            orangeLight: '#FFF7ED',
                            blue: '#3B82F6',
                            blueHover: '#2563EB',
                            bg: '#EEF1F6',
                            card: '#FFFFFF',
                            text: '#1E293B',
                            muted: '#64748B',
                        }
                    },
                    borderRadius: {
                        'xl': '0.875rem',
                        '2xl': '1.25rem',
                        '3xl': '1.75rem',
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #EEF1F6;
            color: #1E293B;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen antialiased bg-[#EEF1F6]">

<!-- Header Blanc Sticky -->
<header class="sticky top-0 z-40 w-full bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Logo Klaro avec Tagline "Quiz adaptatif IA" -->
        <a href="<?= $is_logged ? $dashboard_link : 'index.php' ?>" class="flex items-center gap-2.5 group">
            <div class="w-10 h-10 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-md shadow-orange-500/20 group-hover:scale-105 transition-transform">
                <i data-lucide="lightbulb" class="w-5 h-5 stroke-[2.5]"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-black text-orange-500 tracking-tight leading-none">Klaro</span>
                <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">Quiz adaptatif IA</span>
            </div>
        </a>

        <?php if ($is_landing_page && !$is_logged): ?>
            <!-- NAVIGATION PUBLIQUE (LANDING PAGE SANS SESSION) -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="index.php" class="text-sm font-bold text-orange-500 transition-colors">
                    Accueil
                </a>
                <a href="commencer-quiz.php" class="text-sm font-bold text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1.5">
                    <i data-lucide="play" class="w-4 h-4 fill-orange-500 text-orange-500"></i>
                    <span>Passer un quiz</span>
                </a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="connexion.php" class="text-sm font-bold text-slate-700 hover:text-orange-500 px-3 py-2 transition-colors">
                    Se connecter
                </a>
                <a href="inscription.php" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold shadow-md shadow-orange-500/20 transition-all hover:scale-[1.02]">
                    S'inscrire
                </a>
            </div>

        <?php else: ?>
            <!-- NAVIGATION CONNECTÉE OU HEADER DASHBOARD -->
            <nav class="hidden md:flex items-center gap-1">
                <?php if ($is_logged): ?>
                    <a href="<?= $dashboard_link ?>" class="px-3.5 py-2 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors <?= (strpos($current_page, 'dashboard') !== false) ? 'text-orange-600 bg-orange-50/80 font-extrabold' : 'text-slate-700 hover:bg-slate-100' ?>">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-orange-500"></i>
                        <span>Tableau de bord</span>
                    </a>

                    <a href="outils-ia.php" class="px-3.5 py-2 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors <?= ($current_page === 'outils-ia.php' || $current_page === 'grimoire-ia.php') ? 'text-blue-600 bg-blue-50/80 font-extrabold' : 'text-slate-700 hover:bg-slate-100' ?>">
                        <i data-lucide="sparkles" class="w-4 h-4 text-blue-500"></i>
                        <span>Klaro AI</span>
                    </a>
                <?php else: ?>
                    <a href="index.php" class="px-3.5 py-2 rounded-xl text-sm font-bold <?= ($current_page === 'index.php') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        Accueil
                    </a>
                    <a href="commencer-quiz.php" class="px-3.5 py-2 rounded-xl text-sm font-bold flex items-center gap-1.5 <?= ($current_page === 'commencer-quiz.php') ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' ?>">
                        <i data-lucide="play" class="w-4 h-4 fill-orange-500 text-orange-500"></i>
                        <span>Passer un quiz</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="flex items-center gap-3">
                <?php if ($is_logged): ?>
                    <!-- Badge Crédits Réel -->
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-bold shadow-2xs">
                        <i data-lucide="coins" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span><?= $credits ?> crédits</span>
                    </div>

                    <!-- Avatar dynamique, Nom réel et Rôle réel -->
                    <div class="flex items-center gap-2.5 pl-2 sm:pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-xl font-black text-xs flex items-center justify-center shadow-2xs <?= ($user_role === 'professeur') ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' ?>">
                            <?= e($initials) ?>
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-bold text-slate-900 leading-tight">
                                <?= e($prenom) ?> <?= e($nom) ?>
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">
                                <?= e($role_label) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Bouton Déconnexion -->
                    <a href="deconnexion.php" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Se déconnecter" aria-label="Se déconnecter">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </a>
                <?php else: ?>
                    <a href="connexion.php" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-100 transition-colors flex items-center gap-1.5">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Connexion</span>
                    </a>
                    <a href="inscription.php" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>S'inscrire</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</header>
