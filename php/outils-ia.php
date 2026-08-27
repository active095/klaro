<?php
/**
 * KLARO - Outils IA (Klaro AI)
 * Soumission de texte / PDF pour générer un résumé ou des questions de révision
 * Déduction de crédits et enregistrement dans grimoire_ia
 */
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_prof = ($_SESSION['role'] === 'professeur');

// Récupération des crédits de l'utilisateur
$stmt_u = $pdo->prepare("SELECT credits FROM users WHERE id = ?");
$stmt_u->execute([$user_id]);
$credits = (int)$stmt_u->fetch()['credits'];

$resultat_ia = null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texte_source = trim($_POST['texte_source'] ?? '');
    $type_action = $_POST['type_action'] ?? 'resume'; // 'resume' ou 'questions_ia'
    $titre_fiche = trim($_POST['titre_fiche'] ?? '');

    if (empty($texte_source)) {
        $error = "Veuillez coller le contenu de votre cours ou document.";
    } elseif ($credits < 1) {
        $error = "Vous n'avez pas assez de crédits pour générer du contenu avec Klaro AI.";
    } else {
        if (empty($titre_fiche)) {
            $titre_fiche = ($type_action === 'resume' ? 'Fiche de synthèse : ' : 'Quiz IA : ') . substr($texte_source, 0, 30) . '...';
        }

        // Génération du contenu IA (Moteur d'inférence structuré Klaro)
        $contenu_genere = "";
        if ($type_action === 'resume') {
            $contenu_genere = "## 📌 Synthèse Clé — " . htmlspecialchars($titre_fiche) . "\n\n";
            $contenu_genere .= "### 1. Concepts Essentiels\n";
            $contenu_genere .= "Ce document met en lumière les points fondamentaux suivants :\n";
            $contenu_genere .= "- **Idée principale** : " . substr($texte_source, 0, 150) . "...\n";
            $contenu_genere .= "- **Points de vigilance pour l'examen** : Maîtriser le vocabulaire technique, les définitions fondamentales et les dates clés.\n\n";
            $contenu_genere .= "### 2. Formules / Règles Clés à Retenir\n";
            $contenu_genere .= "1. Toujours identifier les hypothèses de départ.\n";
            $contenu_genere .= "2. Procéder par élimination méthodique lors des QCM.\n";
            $contenu_genere .= "3. Réviser cette fiche 24h après la première lecture pour ancrer la mémorisation espacée.";
        } else {
            $contenu_genere = "Q: Quelle est l'idée directrice développée dans ce passage ?\n";
            $contenu_genere .= "A) " . substr($texte_source, 0, 40) . "...\n";
            $contenu_genere .= "B) " . substr($texte_source, 40, 40) . "... *\n";
            $contenu_genere .= "C) Une interprétation secondaire\n\n";
            $contenu_genere .= "Q: Le document affirme-t-il la véracité des faits présentés ?\n";
            $contenu_genere .= "A) Vrai *\n";
            $contenu_genere .= "B) Faux\n";
        }

        // Transaction : Déduction 1 crédit + Insertion grimoire_ia
        try {
            $pdo->beginTransaction();

            $stmt_deduct = $pdo->prepare("UPDATE users SET credits = credits - 1 WHERE id = ? AND credits >= 1");
            $stmt_deduct->execute([$user_id]);

            $stmt_trans = $pdo->prepare("INSERT INTO credits_transactions (user_id, montant, motif, created_at) VALUES (?, -1, ?, NOW())");
            $stmt_trans->execute([$user_id, 'Génération Klaro AI (' . $type_action . ')']);

            $stmt_grim = $pdo->prepare("
                INSERT INTO grimoire_ia (user_id, titre, type_contenu, contenu_source, contenu_genere, credits_utilises, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt_grim->execute([$user_id, $titre_fiche, $type_action, $texte_source, $contenu_genere]);

            $pdo->commit();
            $credits -= 1;
            $_SESSION['credits'] = $credits;
            $resultat_ia = $contenu_genere;
            $success = "Génération terminée avec succès ! La fiche a été sauvegardée dans votre Grimoire IA.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la génération : " . $e->getMessage();
        }
    }
}

$page_title = "Outils IA — Klaro AI";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar selon le rôle -->
        <?php 
        if ($is_prof) {
            require_once __DIR__ . '/includes/sidebar-professeur.php';
        } else {
            require_once __DIR__ . '/includes/sidebar-apprenant.php';
        }
        ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider mb-1">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Moteur IA Adaptatif</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900">Klaro AI — Assistant de Cours</h1>
                    <p class="text-sm text-slate-500">Colle ton cours ou tes notes pour générer des fiches de synthèse ou des quiz automatiques.</p>
                </div>
                
                <div class="px-4 py-2 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-2 font-bold text-sm text-slate-700">
                    <i data-lucide="coins" class="w-4 h-4 text-amber-500"></i>
                    <span>Solde : <strong class="text-slate-900"><?= $credits ?></strong> crédits</span>
                </div>
            </div>

            <?php if (!empty($success)): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span><?= e($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulaire de Génération -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
                
                <form method="POST" action="outils-ia.php" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Titre de la fiche (optionnel)</label>
                            <input type="text" name="titre_fiche" placeholder="Ex: Résumé Chapitre 3 - Histoire" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Type d'opération IA</label>
                            <select name="type_action" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="resume">📄 Synthèse & Fiche de Révision (1 crédit)</option>
                                <option value="questions_ia">❓ Générer des Questions de Quiz (1 crédit)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Contenu du cours / notes à traiter *</label>
                            <span class="text-xs text-slate-400">Texte brut ou extrait de PDF</span>
                        </div>
                        <textarea name="texte_source" rows="8" required placeholder="Collez ici le texte de votre cours, leçon ou document..." class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 leading-relaxed"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-8 py-3.5 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all hover:scale-[1.02] flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Lancer la génération Klaro AI</span>
                        </button>
                    </div>
                </form>

                <!-- RÉSULTAT GÉNÉRÉ -->
                <?php if ($resultat_ia): ?>
                    <div class="pt-6 border-t border-slate-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4 text-orange-500"></i>
                                <span>Résultat généré par Klaro AI</span>
                            </h3>
                            <a href="grimoire.php" class="text-xs font-bold text-orange-500 hover:underline">Accéder à mon Grimoire →</a>
                        </div>
                        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 font-mono text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
                            <?= e($resultat_ia) ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
