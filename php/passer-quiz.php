<?php
/**
 * KLARO - Passation du Quiz avec Minuteur & Sauvegarde AJAX
 */
require_once __DIR__ . '/config/db.php';

$code = strtoupper(trim($_GET['code'] ?? ($_POST['code'] ?? '')));

if (empty($code)) {
    header("Location: commencer-quiz.php");
    exit();
}

// 1. Récupération de la composition
$stmt_comp = $pdo->prepare("SELECT * FROM compositions WHERE code_acces = ? AND actif = 1");
$stmt_comp->execute([$code]);
$composition = $stmt_comp->fetch();

if (!$composition) {
    die("<div style='font-family:sans-serif;padding:30px;max-width:500px;margin:50px auto;background:#FEF2F2;border-radius:16px;text-align:center;'>
        <h2 style='color:#991B1B;'>Quiz introuvable</h2>
        <p style='color:#7F1D1D;'>Le code de quiz <strong>".e($code)."</strong> n'existe pas ou est inactif.</p>
        <a href='commencer-quiz.php' style='display:inline-block;margin-top:15px;padding:10px 20px;background:#F97316;color:white;text-decoration:none;border-radius:8px;font-weight:bold;'>Retour</a>
    </div>");
}

// Récupération des questions et réponses
$stmt_quest = $pdo->prepare("
    SELECT q.id as q_id, q.texte_question, q.type_question, q.ordre,
           r.id as r_id, r.lettre, r.texte_reponse, r.est_correcte
    FROM questions q
    JOIN reponses_possibles r ON q.id = r.question_id
    WHERE q.composition_id = ?
    ORDER BY q.ordre ASC, r.lettre ASC
");
$stmt_quest->execute([$composition['id']]);
$rows = $stmt_quest->fetchAll();

$questions = [];
foreach ($rows as $row) {
    $qid = $row['q_id'];
    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'id' => $qid,
            'texte' => $row['texte_question'],
            'type' => $row['type_question'],
            'reponses' => []
        ];
    }
    $questions[$qid]['reponses'][] = [
        'id' => $row['r_id'],
        'lettre' => $row['lettre'],
        'texte' => $row['texte_reponse'],
        'est_correcte' => $row['est_correcte']
    ];
}
$questions = array_values($questions);
$total_questions = count($questions);

// Identification de l'utilisateur (connecté ou session invitée)
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // Si l'utilisateur n'est pas connecté, créer un utilisateur temporaire ou forcer la connexion
    // Pour une expérience optimale selon le cahier des charges, rediriger vers connexion avec retour
    header("Location: connexion.php?redirect=" . urlencode("passer-quiz.php?code=" . $code));
    exit();
}

$action = $_POST['action'] ?? ($_GET['action'] ?? 'intro');
$entrainement_id = (int)($_POST['entrainement_id'] ?? ($_GET['entrainement_id'] ?? 0));
$resultat = null;

// GESTION DU DÉMARRAGE DU QUIZ (Enregistrement heure serveur)
if ($action === 'start') {
    // Créer la session d'entraînement côté serveur
    $stmt_new_train = $pdo->prepare("
        INSERT INTO entrainements (user_id, composition_id, total_questions, temps_debut, statut, created_at)
        VALUES (?, ?, ?, NOW(), 'en_cours', NOW())
    ");
    $stmt_new_train->execute([$user_id, $composition['id'], $total_questions]);
    $entrainement_id = $pdo->lastInsertId();

    header("Location: passer-quiz.php?code=" . urlencode($code) . "&action=play&entrainement_id=" . $entrainement_id);
    exit();
}

// GESTION DE LA SOUMISSION DU QUIZ (Volontaire ou Expiration temps)
if ($action === 'submit' && $entrainement_id > 0) {
    $soumission_motif = ($_POST['expiration'] ?? '0') === '1' ? 'expiration_temps' : 'volontaire';

    // 1. Récupérer l'entraînement en cours
    $stmt_check_train = $pdo->prepare("SELECT * FROM entrainements WHERE id = ? AND user_id = ?");
    $stmt_check_train->execute([$entrainement_id, $user_id]);
    $current_train = $stmt_check_train->fetch();

    if ($current_train && $current_train['statut'] === 'en_cours') {
        $reponses_post = $_POST['reponses'] ?? []; // question_id => reponse_id
        $score = 0;
        $temps_debut = strtotime($current_train['temps_debut']);
        $temps_actuel = time();
        $temps_ecoule = max(1, $temps_actuel - $temps_debut);

        // Validation serveur anti-triche du temps si durée limitée
        if ($composition['duree_minutes'] > 0) {
            $temps_max_autorise = ($composition['duree_minutes'] * 60) + 15; // 15s de marge réseau
            if ($temps_ecoule > $temps_max_autorise) {
                $soumission_motif = 'expiration_temps';
            }
        }

        // Évaluation de chaque question
        $stmt_save_rep = $pdo->prepare("
            INSERT INTO reponses_utilisateur (entrainement_id, question_id, reponse_choisie_id, est_correcte, created_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE reponse_choisie_id = VALUES(reponse_choisie_id), est_correcte = VALUES(est_correcte)
        ");

        $details_correction = [];

        foreach ($questions as $q) {
            $qid = $q['id'];
            $choix_id = isset($reponses_post[$qid]) ? (int)$reponses_post[$qid] : null;
            
            // Trouver la bonne réponse
            $bonne_reponse_id = null;
            foreach ($q['reponses'] as $rep) {
                if ($rep['est_correcte']) {
                    $bonne_reponse_id = $rep['id'];
                }
            }

            $est_correct = ($choix_id !== null && $choix_id === $bonne_reponse_id) ? 1 : 0;
            if ($est_correct) {
                $score++;
            }

            $stmt_save_rep->execute([$entrainement_id, $qid, $choix_id, $est_correct]);

            $details_correction[] = [
                'question' => $q,
                'choix_id' => $choix_id,
                'bonne_reponse_id' => $bonne_reponse_id,
                'est_correct' => $est_correct
            ];
        }

        $pourcentage = $total_questions > 0 ? round(($score / $total_questions) * 100, 2) : 0;

        // Mise à jour de l'entraînement
        $stmt_update_train = $pdo->prepare("
            UPDATE entrainements 
            SET score = ?, total_questions = ?, pourcentage = ?, temps_fin = NOW(), temps_ecoule = ?, soumission_type = ?, statut = 'termine'
            WHERE id = ?
        ");
        $stmt_update_train->execute([$score, $total_questions, $pourcentage, $temps_ecoule, $soumission_motif, $entrainement_id]);

        // Mise à jour de la série de streak (table serie_streak)
        $today = date('Y-m-d');
        $stmt_get_streak = $pdo->prepare("SELECT * FROM serie_streak WHERE user_id = ?");
        $stmt_get_streak->execute([$user_id]);
        $st = $stmt_get_streak->fetch();

        if ($st) {
            $last_date = $st['dernier_entrainement'];
            $cur_streak = (int)$st['jours_consecutifs'];
            $rec_streak = (int)$st['record_streak'];

            if ($last_date === $today) {
                // Déjà entraîné aujourd'hui, on ne change rien
            } elseif ($last_date === date('Y-m-d', strtotime('-1 day'))) {
                // Entraîné hier -> streak + 1
                $cur_streak += 1;
                $rec_streak = max($rec_streak, $cur_streak);
                $up_st = $pdo->prepare("UPDATE serie_streak SET jours_consecutifs = ?, record_streak = ?, dernier_entrainement = ? WHERE user_id = ?");
                $up_st->execute([$cur_streak, $rec_streak, $today, $user_id]);
            } else {
                // Streak cassé -> reprise à 1
                $cur_streak = 1;
                $rec_streak = max($rec_streak, 1);
                $up_st = $pdo->prepare("UPDATE serie_streak SET jours_consecutifs = ?, record_streak = ?, dernier_entrainement = ? WHERE user_id = ?");
                $up_st->execute([$cur_streak, $rec_streak, $today, $user_id]);
            }
        }

        $resultat = [
            'score' => $score,
            'total' => $total_questions,
            'pourcentage' => $pourcentage,
            'temps_ecoule' => $temps_ecoule,
            'soumission' => $soumission_motif,
            'details' => $details_correction
        ];
        $action = 'result';
    }
}

// Si en cours de jeu (play) -> vérifier le temps restant côté serveur
$temps_restant_secondes = 0;
if ($action === 'play' && $entrainement_id > 0) {
    $stmt_check_train = $pdo->prepare("SELECT * FROM entrainements WHERE id = ? AND user_id = ?");
    $stmt_check_train->execute([$entrainement_id, $user_id]);
    $current_train = $stmt_check_train->fetch();

    if (!$current_train || $current_train['statut'] === 'termine') {
        header("Location: mes-compositions.php");
        exit();
    }

    if ($composition['duree_minutes'] > 0) {
        $temps_debut = strtotime($current_train['temps_debut']);
        $temps_ecoule = time() - $temps_debut;
        $duree_totale_sec = $composition['duree_minutes'] * 60;
        $temps_restant_secondes = max(0, $duree_totale_sec - $temps_ecoule);
    }
}

$page_title = "Quiz : " . $composition['titre'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- 1. ÉCRAN D'INTRODUCTION (Avant de lancer le minuteur) -->
    <?php if ($action === 'intro'): ?>
        <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-sm text-center space-y-6">
            <div class="w-16 h-16 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto">
                <i data-lucide="award" class="w-8 h-8"></i>
            </div>

            <div>
                <span class="inline-flex px-3.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-mono font-bold uppercase tracking-wider mb-2">
                    Code : <?= e($composition['code_acces']) ?>
                </span>
                <h1 class="text-3xl font-black text-slate-900"><?= e($composition['titre']) ?></h1>
                <p class="text-sm text-slate-500 mt-2">
                    Type d'évaluation : <strong class="text-slate-700 uppercase"><?= e($composition['type_quiz']) ?></strong>
                </p>
            </div>

            <!-- Carte Durée & Informations -->
            <div class="max-w-md mx-auto grid grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-orange-50 border border-orange-100">
                    <i data-lucide="timer" class="w-5 h-5 text-orange-500 mx-auto mb-1"></i>
                    <p class="text-xs font-bold text-slate-500 uppercase">Durée du quiz</p>
                    <p class="text-lg font-black text-slate-900 mt-0.5">
                        <?= $composition['duree_minutes'] > 0 ? $composition['duree_minutes'] . ' minute(s)' : 'Temps illimité' ?>
                    </p>
                </div>
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100">
                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600 mx-auto mb-1"></i>
                    <p class="text-xs font-bold text-slate-500 uppercase">Nombre de questions</p>
                    <p class="text-lg font-black text-slate-900 mt-0.5"><?= $total_questions ?> questions</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 max-w-md mx-auto text-left space-y-1">
                <p class="font-bold text-slate-800">⚠️ Règles de passation :</p>
                <p>• Le chronomètre démarre dès que vous cliquez sur <strong>Commencer</strong>.</p>
                <p>• Vos réponses sont sauvegardées automatiquement toutes les 30 secondes.</p>
                <?php if ($composition['duree_minutes'] > 0): ?>
                    <p>• À l'expiration du temps, vos réponses déjà cochées sont validées automatiquement.</p>
                <?php endif; ?>
            </div>

            <form method="POST" action="passer-quiz.php?code=<?= urlencode($code) ?>">
                <input type="hidden" name="action" value="start">
                <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black text-base shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.02] flex items-center justify-center gap-2 mx-auto">
                    <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                    <span>Commencer le Quiz</span>
                </button>
            </form>
        </div>

    <!-- 2. ÉCRAN DE JEU (Quiz en cours avec minuteur) -->
    <?php elseif ($action === 'play'): ?>
        
        <form method="POST" action="passer-quiz.php?code=<?= urlencode($code) ?>" id="quiz_play_form" class="space-y-6">
            <input type="hidden" name="action" value="submit">
            <input type="hidden" name="entrainement_id" value="<?= $entrainement_id ?>">
            <input type="hidden" name="expiration" id="input_expiration" value="0">

            <!-- En-tête Sticky avec Minuteur & Progression -->
            <div class="sticky top-20 z-30 bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 truncate max-w-xs sm:max-w-md"><?= e($composition['titre']) ?></h2>
                    <p class="text-xs text-slate-500"><?= $total_questions ?> questions · Auto-save actif</p>
                </div>

                <!-- Minuteur MM:SS -->
                <?php if ($composition['duree_minutes'] > 0): ?>
                    <div id="timer_box" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-800 font-mono font-black text-lg sm:text-xl flex items-center gap-2 transition-colors">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                        <span id="timer_display">00:00</span>
                    </div>
                <?php else: ?>
                    <div class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold flex items-center gap-1.5">
                        <i data-lucide="infinity" class="w-4 h-4"></i>
                        <span>Illimité</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Liste des Questions -->
            <div class="space-y-6">
                <?php foreach ($questions as $idx => $q): ?>
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-xl bg-orange-500 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                <?= $idx + 1 ?>
                            </span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 pt-0.5 leading-snug">
                                <?= e($q['texte']) ?>
                            </h3>
                        </div>

                        <!-- Choix des Réponses -->
                        <div class="grid grid-cols-1 gap-2.5 pt-2 pl-0 sm:pl-11">
                            <?php foreach ($q['reponses'] as $rep): ?>
                                <label class="relative flex items-center p-3.5 rounded-2xl border-2 border-slate-200 hover:border-orange-300 hover:bg-orange-50/40 cursor-pointer transition-all has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50/70">
                                    <input type="radio" name="reponses[<?= $q['id'] ?>]" value="<?= $rep['id'] ?>" class="w-4 h-4 text-orange-500 accent-orange-500 focus:ring-orange-500 mr-3">
                                    <span class="font-bold font-mono text-slate-500 mr-2 text-sm"><?= e($rep['lettre']) ?>)</span>
                                    <span class="text-sm font-semibold text-slate-800"><?= e($rep['texte']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bouton de Soumission Final -->
            <div class="pt-4 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black text-base shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>Valider et Soumettre mes Réponses</span>
                </button>
            </div>
        </form>

        <!-- SCRIPT TIMER & AUTO-SAVE -->
        <script>
        const entrainementId = <?= (int)$entrainement_id ?>;
        let remainingSeconds = <?= (int)$temps_restant_secondes ?>;
        const hasTimer = <?= $composition['duree_minutes'] > 0 ? 'true' : 'false' ?>;

        // 1. Gestion du Minuteur
        if (hasTimer) {
            const display = document.getElementById('timer_display');
            const box = document.getElementById('timer_box');

            function updateTimer() {
                if (remainingSeconds <= 0) {
                    display.textContent = "00:00";
                    document.getElementById('input_expiration').value = "1";
                    alert("⏰ Temps écoulé ! Vos réponses sont transmises automatiquement.");
                    document.getElementById('quiz_play_form').submit();
                    return;
                }

                const m = Math.floor(remainingSeconds / 60);
                const s = remainingSeconds % 60;
                display.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

                // Changement de couleur dans la dernière minute
                if (remainingSeconds <= 30) {
                    box.className = "px-4 py-2 rounded-xl bg-rose-500 text-white font-mono font-black text-lg sm:text-xl flex items-center gap-2 animate-pulse";
                } else if (remainingSeconds <= 60) {
                    box.className = "px-4 py-2 rounded-xl bg-orange-500 text-white font-mono font-black text-lg sm:text-xl flex items-center gap-2";
                }

                remainingSeconds--;
                setTimeout(updateTimer, 1000);
            }
            updateTimer();
        }

        // 2. Sauvegarde Automatique AJAX toutes les 30 secondes
        function autoSaveResponses() {
            const form = document.getElementById('quiz_play_form');
            const formData = new FormData(form);
            const reponses = {};

            for (const [key, value] of formData.entries()) {
                const match = key.match(/reponses\[(\d+)\]/);
                if (match) {
                    reponses[match[1]] = value;
                }
            }

            fetch('api/sauvegarder-reponse.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    entrainement_id: entrainementId,
                    reponses: reponses
                })
            }).then(r => r.json()).then(data => {
                console.log('Auto-saved successfully at', data.timestamp);
            }).catch(err => console.warn('Auto-save error', err));
        }

        setInterval(autoSaveResponses, 30000); // 30 secondes
        </script>

    <!-- 3. ÉCRAN DE RÉSULTAT & CORRECTION DÉTAILLÉE -->
    <?php elseif ($action === 'result' && $resultat): ?>
        
        <div class="space-y-8 animate-fade-in">
            
            <!-- Carte Score Hero -->
            <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-lg text-center space-y-6">
                
                <div class="w-20 h-20 rounded-3xl mx-auto flex items-center justify-center text-3xl font-black <?= $resultat['pourcentage'] >= 70 ? 'bg-emerald-100 text-emerald-600' : ($resultat['pourcentage'] >= 50 ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600') ?>">
                    <?= $resultat['pourcentage'] >= 70 ? '🎉' : ($resultat['pourcentage'] >= 50 ? '👍' : '💪') ?>
                </div>

                <div>
                    <h1 class="text-3xl font-black text-slate-900">Résultat de l'évaluation</h1>
                    <p class="text-sm text-slate-500 mt-1"><?= e($composition['titre']) ?></p>
                </div>

                <!-- Score & Pourcentage -->
                <div class="max-w-md mx-auto grid grid-cols-3 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                    <div>
                        <div class="text-2xl font-black text-slate-900"><?= $resultat['score'] ?> / <?= $resultat['total'] ?></div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Score final</div>
                    </div>
                    <div class="border-x border-slate-200">
                        <div class="text-2xl font-black <?= $resultat['pourcentage'] >= 70 ? 'text-emerald-600' : 'text-orange-600' ?>"><?= $resultat['pourcentage'] ?>%</div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Réussite</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-slate-900"><?= gmdate("i:s", $resultat['temps_ecoule']) ?></div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Temps passé</div>
                    </div>
                </div>

                <?php if ($resultat['soumission'] === 'expiration_temps'): ?>
                    <p class="text-xs font-semibold text-rose-600 bg-rose-50 py-1.5 px-4 rounded-full inline-block border border-rose-200">
                        ⏱️ Soumis automatiquement par expiration du temps imparti
                    </p>
                <?php endif; ?>

                <!-- Boutons d'action après quiz -->
                <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                    <a href="dashboard.php" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md transition-all">
                        Retour au Dashboard
                    </a>
                    <a href="commencer-quiz.php?code=<?= urlencode($code) ?>" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">
                        Refaire ce quiz
                    </a>
                    <a href="outils-ia.php" class="px-6 py-3 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-sm">
                        Générer fiche de révision IA
                    </a>
                </div>
            </div>

            <!-- CORRECTION DÉTAILLÉE QUESTION PAR QUESTION -->
            <div class="space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="check-square" class="w-5 h-5 text-orange-500"></i>
                    <span>Correction détaillée</span>
                </h2>

                <?php foreach ($resultat['details'] as $idx => $d): 
                    $q = $d['question'];
                    $is_ok = $d['est_correct'];
                ?>
                    <div class="bg-white rounded-2xl p-6 border <?= $is_ok ? 'border-emerald-200 bg-emerald-50/10' : 'border-rose-200 bg-rose-50/10' ?> shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <span class="w-7 h-7 rounded-lg text-white font-black text-xs flex items-center justify-center shrink-0 <?= $is_ok ? 'bg-emerald-500' : 'bg-rose-500' ?>">
                                    <?= $idx + 1 ?>
                                </span>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 pt-0.5">
                                    <?= e($q['texte']) ?>
                                </h3>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold shrink-0 <?= $is_ok ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                <?= $is_ok ? '+1 pt Correct' : '0 pt Incorrect' ?>
                            </span>
                        </div>

                        <!-- Choix avec statut -->
                        <div class="grid grid-cols-1 gap-2 pl-0 sm:pl-10">
                            <?php foreach ($q['reponses'] as $rep): 
                                $is_chosen = ($rep['id'] === $d['choix_id']);
                                $is_right = $rep['est_correcte'];
                                
                                $card_class = 'border-slate-200 bg-white text-slate-700';
                                if ($is_right) {
                                    $card_class = 'border-emerald-400 bg-emerald-50 text-emerald-900 font-bold';
                                } elseif ($is_chosen && !$is_right) {
                                    $card_class = 'border-rose-400 bg-rose-50 text-rose-900 font-bold';
                                }
                            ?>
                                <div class="p-3 rounded-xl border text-xs flex items-center justify-between <?= $card_class ?>">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold"><?= e($rep['lettre']) ?>)</span>
                                        <span><?= e($rep['texte']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <?php if ($is_chosen): ?>
                                            <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded bg-slate-200 text-slate-800">Ta réponse</span>
                                        <?php endif; ?>
                                        <?php if ($is_right): ?>
                                            <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded bg-emerald-200 text-emerald-800">Bonne réponse</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
