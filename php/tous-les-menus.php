<?php
/**
 * KLARO - Tous les Menus (Plan du site interactif)
 */
require_once __DIR__ . '/config/db.php';

$is_logged = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? null;

$page_title = "Tous les Menus";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar si connecté -->
        <?php if ($is_logged): ?>
            <?php if ($user_role === 'professeur'): ?>
                <?php require_once __DIR__ . '/includes/sidebar-professeur.php'; ?>
            <?php else: ?>
                <?php require_once __DIR__ . '/includes/sidebar-apprenant.php'; ?>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-50 text-orange-600 text-xs font-bold uppercase tracking-wider mb-1">
                    <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                    <span>Plan de Navigation</span>
                </div>
                <h1 class="text-3xl font-black text-slate-900">Tous les Menus de Klaro</h1>
                <p class="text-sm text-slate-500">Accède rapidement à toutes les fonctionnalités et modules de la plateforme.</p>
            </div>

            <!-- SECTION 1 : GÉNÉRAL & PUBLIC -->
            <div class="space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <span>Général & Public</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="index.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center mb-3">
                            <i data-lucide="home" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Page d'Accueil</h3>
                        <p class="text-xs text-slate-500 mt-1">Présentation de la plateforme et accès rapide.</p>
                    </a>

                    <a href="commencer-quiz.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center mb-3">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Commencer un quiz par Code</h3>
                        <p class="text-xs text-slate-500 mt-1">Saisie du code d'accès délivré par l'enseignant.</p>
                    </a>

                    <a href="connexion.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Authentification</h3>
                        <p class="text-xs text-slate-500 mt-1">Connexion unique Apprenant et Professeur.</p>
                    </a>
                </div>
            </div>

            <!-- SECTION 2 : ESPACE APPRENANT -->
            <div class="space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    <span>Espace Apprenant (Étudiant)</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="dashboard.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center mb-3">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Dashboard Apprenant</h3>
                        <p class="text-xs text-slate-500 mt-1">Série streak, statistiques de réussite et actions rapides.</p>
                    </a>

                    <a href="mes-compositions.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                            <i data-lucide="file-check" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Mes Compositions</h3>
                        <p class="text-xs text-slate-500 mt-1">Historique des quiz passés avec scores et détails.</p>
                    </a>

                    <a href="classrooms.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Classrooms</h3>
                        <p class="text-xs text-slate-500 mt-1">Rejoindre une classe et accéder aux devoirs.</p>
                    </a>

                    <a href="grimoire.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Le Grimoire</h3>
                        <p class="text-xs text-slate-500 mt-1">Consultation et gestion des synthèses de révision.</p>
                    </a>

                    <a href="historique-entrainement.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center mb-3">
                            <i data-lucide="history" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Hist. Entraînement</h3>
                        <p class="text-xs text-slate-500 mt-1">Historique filtrable par date et composition.</p>
                    </a>
                </div>
            </div>

            <!-- SECTION 3 : ESPACE PROFESSEUR -->
            <div class="space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <span>Espace Enseignant (Professeur)</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="dashboard-professeur.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Dashboard Professeur</h3>
                        <p class="text-xs text-slate-500 mt-1">Classrooms actifs, engagement et suivi de réussite.</p>
                    </a>

                    <a href="creer-quiz.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center mb-3">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Créer un Quiz</h3>
                        <p class="text-xs text-slate-500 mt-1">Parseur de texte brut QCM / Vrai-Faux et code d'accès.</p>
                    </a>

                    <a href="classrooms-professeur.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Mes Classrooms</h3>
                        <p class="text-xs text-slate-500 mt-1">Gestion des classes et liste des membres inscrits.</p>
                    </a>

                    <a href="compositions-professeur.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Compositions Créées</h3>
                        <p class="text-xs text-slate-500 mt-1">Tous les quiz publiés, codes d'accès et statistiques.</p>
                    </a>

                    <a href="etudiants-professeur.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Gestion des Étudiants</h3>
                        <p class="text-xs text-slate-500 mt-1">Roster des élèves et moyennes individuelles.</p>
                    </a>

                    <a href="support.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3">
                            <i data-lucide="life-buoy" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Support Enseignant</h3>
                        <p class="text-xs text-slate-500 mt-1">Formulaire de contact et suivi des tickets.</p>
                    </a>
                </div>
            </div>

            <!-- SECTION 4 : OUTILS IA -->
            <div class="space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>Intelligence Artificielle (Klaro AI)</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="outils-ia.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-amber-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Klaro AI — Moteur Génératif</h3>
                        <p class="text-xs text-slate-500 mt-1">Génération automatique de fiches de révision et questions à partir de notes ou PDF.</p>
                    </a>

                    <a href="grimoire-ia.php" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-amber-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center mb-3">
                            <i data-lucide="scroll-text" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Grimoire IA</h3>
                        <p class="text-xs text-slate-500 mt-1">Bibliothèque de toutes vos synthèses enregistrées.</p>
                    </a>
                </div>
            </div>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
