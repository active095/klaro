<?php
/**
 * KLARO - Formulaire de Connexion Unique
 * Redirige automatiquement selon le rôle :
 * - apprenant -> dashboard.php
 * - professeur -> dashboard-professeur.php
 */
require_once __DIR__ . '/config/db.php';

$error = '';
$info = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'session_expired') {
        $info = "Votre session a expiré. Veuillez vous reconnecter.";
    } elseif ($_GET['msg'] === 'unauthorized') {
        $error = "Accès non autorisé pour ce profil.";
    } elseif ($_GET['msg'] === 'logged_out') {
        $info = "Vous avez été déconnecté avec succès.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez renseigner votre email et votre mot de passe.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Mettre à jour la date de dernière connexion
            $update = $pdo->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            // Initialiser les données de session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['credits'] = $user['credits'];

            // Redirection selon le rôle
            if ($user['role'] === 'professeur') {
                header("Location: dashboard-professeur.php");
                exit();
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Identifiants incorrects. Veuillez vérifier votre email et mot de passe.";
        }
    }
}

$page_title = "Connexion";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[calc(100vh-160px)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-500 mx-auto mb-3">
                <i data-lucide="log-in" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Bienvenue sur Klaro</h1>
            <p class="text-sm text-slate-500 mt-1">Connecte-toi à ton espace d'apprentissage</p>
        </div>

        <?php if (!empty($info)): ?>
            <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-sm font-medium flex items-center gap-3">
                <i data-lucide="info" class="w-5 h-5 shrink-0"></i>
                <span><?= e($info) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="connexion.php" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="mon-email@domaine.com" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mot de passe</label>
                </div>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all hover:scale-[1.01]">
                    Se connecter
                </button>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-sm text-slate-500">
            Tu es apprenant et n'as pas de compte ? 
            <a href="inscription.php" class="font-bold text-orange-500 hover:text-orange-600 ml-1">S'inscrire</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
