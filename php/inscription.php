<?php
/**
 * KLARO - Inscription Apprenant (Étudiant uniquement)
 */
require_once __DIR__ . '/config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Un compte existe déjà avec cette adresse email.";
        } else {
            // Hachage sécurisé et insertion forcée en tant que rôle 'apprenant'
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $role = 'apprenant'; // Force toujours apprenant
            $credits = 50; // Crédits offerts à l'inscription

            $insert = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role, credits, derniere_connexion, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            if ($insert->execute([$nom, $prenom, $email, $hashed_password, $role, $credits])) {
                $user_id = $pdo->lastInsertId();
                
                // Initialisation de la série de streak
                $streak_stmt = $pdo->prepare("INSERT INTO serie_streak (user_id, jours_consecutifs, record_streak) VALUES (?, 0, 0)");
                $streak_stmt->execute([$user_id]);

                // Initialisation de la transaction de crédits de bienvenue
                $trans_stmt = $pdo->prepare("INSERT INTO credits_transactions (user_id, montant, motif, created_at) VALUES (?, ?, 'Crédits de bienvenue', NOW())");
                $trans_stmt->execute([$user_id, $credits]);

                // Connexion automatique et redirection vers dashboard apprenant
                $_SESSION['user_id'] = $user_id;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                header("Location: dashboard.php?welcome=1");
                exit();
            } else {
                $error = "Une erreur est survenue lors de la création du compte.";
            }
        }
    }
}

$page_title = "Inscription Apprenant";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-160px)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-500 mx-auto mb-3">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Créer un compte apprenant</h1>
            <p class="text-sm text-slate-500 mt-1">Rejoins Klaro pour réviser et réussir tes examens</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="inscription.php" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom</label>
                    <input type="text" name="prenom" required value="<?= e($_POST['prenom'] ?? '') ?>" placeholder="Jean" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nom</label>
                    <input type="text" name="nom" required value="<?= e($_POST['nom'] ?? '') ?>" placeholder="Koffi" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="etudiant@domaine.com" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mot de passe</label>
                <input type="password" name="password" required placeholder="Minimum 6 caractères" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirmer le mot de passe</label>
                <input type="password" name="password_confirm" required placeholder="Répéter le mot de passe" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all hover:scale-[1.01]">
                    Créer mon compte
                </button>
            </div>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            Déjà inscrit ? 
            <a href="connexion.php" class="font-bold text-orange-500 hover:text-orange-600 ml-1">Se connecter</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
