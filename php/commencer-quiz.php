<?php
/**
 * KLARO - Accès au Quiz par Code (commencer-quiz.php)
 */
require_once __DIR__ . '/config/db.php';

$error = '';
$code_input = trim($_GET['code'] ?? ($_POST['code_acces'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET['code'])) {
    if (empty($code_input)) {
        $error = "Veuillez entrer un code de quiz valide.";
    } else {
        $code_clean = strtoupper($code_input);
        // Vérifier si le code existe et est actif
        $stmt = $pdo->prepare("SELECT id, titre, type_quiz, duree_minutes FROM compositions WHERE code_acces = ? AND actif = 1");
        $stmt->execute([$code_clean]);
        $composition = $stmt->fetch();

        if ($composition) {
            // Code valide -> redirige directement vers la passation du quiz
            header("Location: passer-quiz.php?code=" . urlencode($code_clean));
            exit();
        } else {
            $error = "Code de quiz invalide ou inactif. Veuillez vérifier le code fourni par votre enseignant.";
        }
    }
}

$page_title = "Commencer un quiz";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-160px)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-sm border border-slate-200/80 p-8 sm:p-10 text-center space-y-6">
        
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto">
            <i data-lucide="key-round" class="w-8 h-8"></i>
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Accéder à une évaluation</h1>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">
                Saisis le code unique partagé par ton professeur (ex: <code class="font-mono text-orange-600 font-bold">KLR-XXXXX</code>) pour lancer ton quiz.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium flex items-center gap-3 text-left">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="commencer-quiz.php" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2">Entrer le code du quiz</label>
                <input type="text" name="code_acces" required value="<?= e($code_input) ?>" placeholder="KLR-XXXXX" class="w-full text-center px-6 py-4 rounded-2xl bg-slate-50 border-2 border-slate-200 text-slate-900 text-2xl font-mono font-black placeholder-slate-300 uppercase tracking-widest focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
            </div>

            <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black text-base shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.01] flex items-center justify-center gap-2">
                <span>Accéder au quiz</span>
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </button>
        </form>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-center gap-2 text-xs text-slate-400">
            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
            <span>Session sécurisée & chronomètre synchronisé</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
