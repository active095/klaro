<?php
/**
 * KLARO - Footer commun
 * "© [année dynamique] Klaro — Réalisé par Henri Joël HOUNKPE"
 */
?>
<footer class="bg-white border-t border-slate-200/80 mt-auto py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- IDENTITÉ DU PRODUIT -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-xs">
                        <i data-lucide="lightbulb" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-orange-500">Klaro</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed max-w-sm">
                    Plateforme de quiz adaptatifs propulsée par l'IA, conçue pour stimuler l'excellence académique et la réussite aux examens.
                </p>
                <div class="flex items-center gap-1.5 text-xs text-slate-400 font-semibold">
                    <span>Fait avec</span>
                    <i data-lucide="heart" class="w-3.5 h-3.5 text-rose-500 fill-rose-500 inline"></i>
                    <span>pour l'Afrique francophone</span>
                </div>
            </div>

            <!-- LIENS APPRENANTS -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Espace Étudiant</h4>
                <ul class="space-y-2 text-xs font-semibold text-slate-500">
                    <li>
                        <a href="commencer-quiz.php" class="hover:text-orange-600 transition-colors">
                            Passer un quiz
                        </a>
                    </li>
                    <li>
                        <a href="mes-compositions.php" class="hover:text-orange-600 transition-colors">
                            Mes compositions
                        </a>
                    </li>
                    <li>
                        <a href="classrooms.php" class="hover:text-orange-600 transition-colors">
                            Rejoindre un classroom
                        </a>
                    </li>
                    <li>
                        <a href="outils-ia.php" class="hover:text-orange-600 transition-colors">
                            Klaro AI
                        </a>
                    </li>
                </ul>
            </div>

            <!-- LIENS PROFESSEURS -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Espace Enseignant</h4>
                <ul class="space-y-2 text-xs font-semibold text-slate-500">
                    <li>
                        <a href="creer-quiz.php" class="hover:text-blue-600 transition-colors">
                            Créer un quiz
                        </a>
                    </li>
                    <li>
                        <a href="compositions-professeur.php" class="hover:text-blue-600 transition-colors">
                            Compositions
                        </a>
                    </li>
                    <li>
                        <a href="classrooms-professeur.php" class="hover:text-blue-600 transition-colors">
                            Gestion des classes
                        </a>
                    </li>
                    <li>
                        <a href="support.php" class="hover:text-blue-600 transition-colors">
                            Support
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <!-- COPYRIGHT -->
        <div class="mt-12 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <p>© <?= date('Y') ?> Klaro — Réalisé par <span class="font-semibold text-slate-700">Henri Joël HOUNKPE</span></p>
            <div class="flex items-center gap-4">
                <a href="tous-les-menus.php" class="hover:underline hover:text-slate-600">Plan du site</a>
            </div>
        </div>
    </div>
</footer>

<script>
    // Initialisation des icônes Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
</body>
</html>
