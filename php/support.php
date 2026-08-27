<?php
/**
 * KLARO - Support & Contact (Professeur)
 * Formulaire de contact simple enregistré en base (table tickets_support)
 */
require_once __DIR__ . '/includes/auth-professeur.php';

$prof_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($sujet) || empty($message)) {
        $error = "Veuillez renseigner le sujet et le message de votre demande.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tickets_support (user_id, sujet, message, statut, created_at) VALUES (?, ?, ?, 'ouvert', NOW())");
        if ($stmt->execute([$prof_id, $sujet, $message])) {
            $success = "Votre ticket de support #".$pdo->lastInsertId()." a bien été transmis à l'équipe Klaro.";
        } else {
            $error = "Erreur lors de l'envoi de votre demande.";
        }
    }
}

$stmt_tickets = $pdo->prepare("SELECT * FROM tickets_support WHERE user_id = ? ORDER BY created_at DESC");
$stmt_tickets->execute([$prof_id]);
$my_tickets = $stmt_tickets->fetchAll();

$page_title = "Support Enseignant";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Professeur -->
        <?php require_once __DIR__ . '/includes/sidebar-professeur.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <div>
                <h1 class="text-2xl font-black text-slate-900">Centre d'Assistance & Support</h1>
                <p class="text-sm text-slate-500">Besoin d'aide sur la création de quiz, l'IA ou vos classrooms ? Contactez notre équipe technique.</p>
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

            <!-- Formulaire de contact -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
                <h2 class="text-lg font-extrabold text-slate-900">Ouvrir un ticket d'assistance</h2>

                <form method="POST" action="support.php" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Sujet de votre demande *</label>
                        <input type="text" name="sujet" required placeholder="Ex: Problème lors de la publication d'un quiz QCM" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description détaillée du problème *</label>
                        <textarea name="message" rows="5" required placeholder="Décrivez votre situation avec précision..." class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 leading-relaxed"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Envoyer la demande</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Historique des tickets -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900">Mes Demandes Précédentes</h2>
                
                <?php if (empty($my_tickets)): ?>
                    <p class="text-xs text-slate-500">Aucun ticket ouvert actuellement.</p>
                <?php else: ?>
                    <div class="divide-y divide-slate-100">
                        <?php foreach ($my_tickets as $tk): ?>
                            <div class="py-3.5 space-y-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-800">#<?= $tk['id'] ?> — <?= e($tk['sujet']) ?></h3>
                                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full <?= $tk['statut'] === 'resolu' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>">
                                        <?= strtoupper($tk['statut']) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600"><?= e($tk['message']) ?></p>
                                <span class="text-[10px] text-slate-400">Posté le <?= date('d/m/Y H:i', strtotime($tk['created_at'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
