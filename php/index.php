<?php
/**
 * KLARO - Page d'accueil / Landing Page (index.php)
 */
require_once __DIR__ . '/config/db.php';
$page_title = "Accueil — Révise intelligemment";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section (fond gris-bleu clair) -->
<section class="py-16 md:py-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <div class="text-center max-w-3xl mx-auto space-y-6">
        
        <!-- Badge Pill Orange Clair + Éclair -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-orange-100/80 border border-orange-200 text-orange-600 text-xs sm:text-sm font-bold tracking-wide uppercase shadow-xs">
            <i data-lucide="zap" class="w-4 h-4 text-orange-500 fill-orange-500"></i>
            <span>LA RÉVISION QUI S'ADAPTE À TOI</span>
        </div>

        <!-- Titre Énorme Bold -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.15]">
            Transforme tes cours <br>
            <span class="text-slate-900">en </span><span class="text-blue-600">réussite.</span>
        </h1>

        <!-- Paragraphe descriptif -->
        <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
            Klaro transforme tes cours, PDF et notes en quiz interactifs pour apprendre plus vite et progresser avec méthode.
        </p>

        <!-- 2 Boutons Call-To-Action -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="inscription.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-base shadow-lg shadow-blue-500/25 transition-all hover:scale-[1.02]">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                <span>Créer mon compte apprenant</span>
            </a>
            <a href="connexion.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 rounded-2xl text-slate-700 hover:text-orange-500 font-bold text-base transition-colors group">
                <span>Se connecter</span>
                <span class="group-hover:translate-x-1 transition-transform">→</span>
            </a>
        </div>

        <!-- Accès rapide au Quiz par Code -->
        <div class="pt-8">
            <div class="max-w-md mx-auto bg-white p-2.5 sm:p-3 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <i data-lucide="key-round" class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="quick_quiz_code" placeholder="Ex: KLR-78901" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 uppercase tracking-wider">
                </div>
                <button onclick="accessQuiz()" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold rounded-xl shadow-sm transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                    <span>Accéder</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
            <p id="quick_code_error" class="text-xs text-rose-500 mt-2 font-semibold hidden"></p>
        </div>
    </div>

    <!-- 3 Cartes Numérotées (01 / 02 / 03) -->
    <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Carte 01 -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 relative hover:shadow-md transition-shadow">
            <div class="text-4xl font-extrabold text-orange-500/20 mb-4 tracking-tighter">01</div>
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center mb-5">
                <i data-lucide="file-up" class="w-6 h-6"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Importe ou Entre un code</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                Rejoins les quiz créés par tes professeurs grâce à un code unique ou colle tes synthèses de cours.
            </p>
        </div>

        <!-- Carte 02 -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 relative hover:shadow-md transition-shadow">
            <div class="text-4xl font-extrabold text-blue-600/20 mb-4 tracking-tighter">02</div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5">
                <i data-lucide="timer" class="w-6 h-6"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Passe le Quiz Chronométré</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                Révise en conditions réelles d'examen avec minuteur synchronisé au serveur et sauvegarde automatique.
            </p>
        </div>

        <!-- Carte 03 -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 relative hover:shadow-md transition-shadow">
            <div class="text-4xl font-extrabold text-orange-500/20 mb-4 tracking-tighter">03</div>
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center mb-5">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Progresse grâce à Klaro AI</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                Génère des fiches de synthèse dans ton Grimoire IA, maintiens ta série quotidienne et analyse tes notes.
            </p>
        </div>
    </div>
</section>

<script>
function accessQuiz() {
    const input = document.getElementById('quick_quiz_code');
    const err = document.getElementById('quick_code_error');
    const code = input.value.trim().toUpperCase();
    
    if (!code) {
        err.textContent = "Veuillez entrer un code de quiz valide.";
        err.classList.remove('hidden');
        return;
    }
    
    window.location.href = "commencer-quiz.php?code=" + encodeURIComponent(code);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
