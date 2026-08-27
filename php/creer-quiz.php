<?php
/**
 * KLARO - Création de Quiz (Professeur)
 * Parseur de texte brut QCM & Vrai/Faux avec validation stricte
 */
require_once __DIR__ . '/includes/auth-professeur.php';

$prof_id = $_SESSION['user_id'];
$errors = [];
$parsed_questions = [];
$created_code = null;
$created_title = null;

$titre = trim($_POST['titre'] ?? '');
$type_quiz = $_POST['type_quiz'] ?? 'qcm';
$duree_minutes = max(0, (int)($_POST['duree_minutes'] ?? 0));
$texte_brut = $_POST['texte_brut'] ?? '';
$action = $_POST['action'] ?? '';

// Fonction de parsing et validation
function parseQuizText($raw_text, $type) {
    $lines = explode("\n", str_replace("\r", "", $raw_text));
    $questions = [];
    $errors = [];
    $line_num = 0;

    if ($type === 'qcm') {
        $current_q = null;
        $options = [];
        $correct_found = 0;

        foreach ($lines as $idx => $line) {
            $line_num = $idx + 1;
            $trimmed = trim($line);

            if (empty($trimmed)) {
                // Ligne vide -> fin de la question courante si elle existe
                if ($current_q !== null) {
                    if (count($options) < 2) {
                        $errors[] = "Ligne $line_num : La question '" . substr($current_q, 0, 30) . "...' doit comporter au moins 2 options.";
                    } elseif ($correct_found === 0) {
                        $errors[] = "Ligne $line_num : La question '" . substr($current_q, 0, 30) . "...' n'a aucune bonne réponse marquée par une étoile (*).";
                    } elseif ($correct_found > 1) {
                        $errors[] = "Ligne $line_num : La question '" . substr($current_q, 0, 30) . "...' a plusieurs bonnes réponses marquées par (*). Une seule est autorisée.";
                    } else {
                        $questions[] = [
                            'texte' => $current_q,
                            'type' => 'qcm',
                            'reponses' => $options
                        ];
                    }
                    $current_q = null;
                    $options = [];
                    $correct_found = 0;
                }
                continue;
            }

            // Détection début de question "Q: ..."
            if (preg_match('/^Q\s*:\s*(.+)$/i', $trimmed, $m)) {
                if ($current_q !== null) {
                    // Question précédente non terminée par ligne vide
                    if (count($options) < 2 || $correct_found !== 1) {
                        $errors[] = "Ligne $line_num : Question précédente mal formatée avant cette nouvelle question.";
                    } else {
                        $questions[] = [
                            'texte' => $current_q,
                            'type' => 'qcm',
                            'reponses' => $options
                        ];
                    }
                    $options = [];
                    $correct_found = 0;
                }
                $current_q = trim($m[1]);
            }
            // Détection option "A) ...", "B) ... *"
            elseif (preg_match('/^([A-Za-z0-9])[\)\.\-]\s*(.+)$/', $trimmed, $m)) {
                if ($current_q === null) {
                    $errors[] = "Ligne $line_num : Option trouvée sans question 'Q:' préalable.";
                    continue;
                }
                $letter = strtoupper($m[1]);
                $opt_text = trim($m[2]);
                $is_correct = false;

                if (str_ends_with($opt_text, '*') || str_contains($opt_text, '*')) {
                    $is_correct = true;
                    $opt_text = trim(str_replace('*', '', $opt_text));
                    $correct_found++;
                }

                $options[] = [
                    'lettre' => $letter,
                    'texte' => $opt_text,
                    'est_correcte' => $is_correct ? 1 : 0
                ];
            } else {
                $errors[] = "Ligne $line_num : Format non reconnu : '$trimmed'. Utilisez 'Q: ...' ou 'A) ...'.";
            }
        }

        // Fin de fichier - validation de la dernière question
        if ($current_q !== null) {
            if (count($options) < 2) {
                $errors[] = "Fin de fichier : La dernière question doit comporter au moins 2 options.";
            } elseif ($correct_found === 0) {
                $errors[] = "Fin de fichier : Aucune bonne réponse marquée par (*) pour la dernière question.";
            } elseif ($correct_found > 1) {
                $errors[] = "Fin de fichier : Plusieurs bonnes réponses pour la dernière question.";
            } else {
                $questions[] = [
                    'texte' => $current_q,
                    'type' => 'qcm',
                    'reponses' => $options
                ];
            }
        }

    } elseif ($type === 'vrai_faux') {
        foreach ($lines as $idx => $line) {
            $line_num = $idx + 1;
            $trimmed = trim($line);
            if (empty($trimmed)) continue;

            // Format : "Q: texte de la question. V" ou "Q: texte de la question. F"
            if (preg_match('/^Q\s*:\s*(.+)\.\s*([VF])$/i', $trimmed, $m)) {
                $q_text = trim($m[1]);
                $val = strtoupper($m[2]);
                $is_vrai = ($val === 'V');

                $questions[] = [
                    'texte' => $q_text,
                    'type' => 'vrai_faux',
                    'reponses' => [
                        ['lettre' => 'V', 'texte' => 'Vrai', 'est_correcte' => $is_vrai ? 1 : 0],
                        ['lettre' => 'F', 'texte' => 'Faux', 'est_correcte' => !$is_vrai ? 1 : 0]
                    ]
                ];
            } else {
                $errors[] = "Ligne $line_num : Format Vrai/Faux attendu 'Q: Votre question. V' ou 'Q: Votre question. F'. Reçu : '$trimmed'";
            }
        }
    }

    if (empty($questions) && empty($errors)) {
        $errors[] = "Le texte du quiz ne contient aucune question valide.";
    }

    return [$questions, $errors];
}

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($titre)) {
        $errors[] = "Veuillez renseigner le titre du quiz.";
    }
    if (empty($texte_brut)) {
        $errors[] = "Veuillez coller le texte du quiz.";
    }

    if (empty($errors)) {
        list($parsed_questions, $parse_errors) = parseQuizText($texte_brut, $type_quiz);
        $errors = array_merge($errors, $parse_errors);

        // Si action = publier et AUCUNE erreur
        if ($action === 'publier' && empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Génération code unique KLR-XXXXX
                do {
                    $code_acces = 'KLR-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                    $check = $pdo->prepare("SELECT id FROM compositions WHERE code_acces = ?");
                    $check->execute([$code_acces]);
                } while ($check->fetch());

                // Insertion composition
                $stmt_comp = $pdo->prepare("
                    INSERT INTO compositions (professeur_id, titre, type_quiz, code_acces, duree_minutes, texte_brut_source, actif, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                $stmt_comp->execute([$prof_id, $titre, $type_quiz, $code_acces, $duree_minutes, $texte_brut]);
                $comp_id = $pdo->lastInsertId();

                // Insertion questions & réponses
                $stmt_q = $pdo->prepare("INSERT INTO questions (composition_id, texte_question, type_question, ordre) VALUES (?, ?, ?, ?)");
                $stmt_r = $pdo->prepare("INSERT INTO reponses_possibles (question_id, lettre, texte_reponse, est_correcte) VALUES (?, ?, ?, ?)");

                foreach ($parsed_questions as $index => $q) {
                    $stmt_q->execute([$comp_id, $q['texte'], $q['type'], $index + 1]);
                    $q_id = $pdo->lastInsertId();

                    foreach ($q['reponses'] as $rep) {
                        $stmt_r->execute([$q_id, $rep['lettre'], $rep['texte'], $rep['est_correcte']]);
                    }
                }

                $pdo->commit();
                $created_code = $code_acces;
                $created_title = $titre;

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Erreur lors de l'enregistrement du quiz : " . $e->getMessage();
            }
        }
    }
}

$page_title = "Créer un quiz";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Professeur -->
        <?php require_once __DIR__ . '/includes/sidebar-professeur.php'; ?>

        <!-- Contenu Principal -->
        <main class="flex-1 space-y-8">
            
            <!-- ÉCRAN DE CONFIRMATION AVEC CODE (Si publié) -->
            <?php if ($created_code): ?>
                <div class="bg-white rounded-3xl p-8 border border-emerald-200 shadow-lg text-center space-y-6 animate-fade-in">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                        <i data-lucide="check-circle-2" class="w-8 h-8"></i>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl font-black text-slate-900">Quiz publié avec succès !</h2>
                        <p class="text-sm text-slate-600">Le quiz « <span class="font-bold text-slate-800"><?= e($created_title) ?></span> » est prêt pour tes apprenants.</p>
                    </div>

                    <div class="max-w-md mx-auto p-6 bg-slate-50 border-2 border-dashed border-orange-300 rounded-2xl">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">CODE D'ACCÈS DU QUIZ</p>
                        <div class="text-4xl sm:text-5xl font-black font-mono text-orange-500 tracking-wider my-3" id="final_code">
                            <?= e($created_code) ?>
                        </div>
                        <p class="text-xs text-slate-500">Transmets ce code à tes apprenants pour qu'ils accèdent au quiz.</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                        <button onclick="copyToClipboard('<?= e($created_code) ?>')" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md flex items-center gap-2">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            <span id="copy_btn_text">Copier le code</span>
                        </button>
                        <a href="commencer-quiz.php?code=<?= urlencode($created_code) ?>" target="_blank" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md flex items-center gap-2">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            <span>Tester le quiz</span>
                        </a>
                        <a href="compositions-professeur.php" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm">
                            Voir mes compositions
                        </a>
                    </div>
                </div>
            <?php else: ?>

                <!-- Formulaire de Création -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-black text-slate-900">Créer une composition de Quiz</h1>
                            <p class="text-sm text-slate-500">Colle ton quiz en texte brut et valide le format instantanément.</p>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center">
                            <i data-lucide="file-edit" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <!-- Affichage des Erreurs de parsing -->
                    <?php if (!empty($errors)): ?>
                        <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-sm text-rose-900">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                                <span>Erreurs de format détectées (veuillez corriger avant de publier) :</span>
                            </div>
                            <ul class="list-disc list-inside text-xs space-y-1 pl-2">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="creer-quiz.php" id="quiz_form" class="space-y-6">
                        
                        <!-- Informations Générales -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Titre du Quiz *</label>
                                <input type="text" name="titre" required value="<?= e($titre) ?>" placeholder="Ex: Évaluation Biologie Cellulaire" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Type de Quiz</label>
                                <select name="type_quiz" id="type_quiz" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500" onchange="updatePlaceholder()">
                                    <option value="qcm" <?= $type_quiz === 'qcm' ? 'selected' : '' ?>>QCM (Choix Multiple)</option>
                                    <option value="vrai_faux" <?= $type_quiz === 'vrai_faux' ? 'selected' : '' ?>>Vrai / Faux</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Durée (minutes, optionnel)</label>
                                <input type="number" name="duree_minutes" min="0" value="<?= $duree_minutes > 0 ? $duree_minutes : '' ?>" placeholder="0 = Sans limite" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <!-- Guide de formatage rétractable -->
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-2">
                            <div class="font-bold flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-amber-700"></i>
                                <span>Instructions de formatage attendu :</span>
                            </div>
                            <div id="guide_qcm" class="<?= $type_quiz === 'qcm' ? '' : 'hidden' ?>">
                                <p class="font-mono bg-white/70 p-2 rounded-lg border border-amber-200">
                                    Q: Quelle est la capitale du Bénin ?<br>
                                    A) Cotonou<br>
                                    B) Porto-Novo *<br>
                                    C) Parakou<br>
                                    <br>
                                    Q: Quel organe pompe le sang ?<br>
                                    A) Le foie<br>
                                    B) Le cœur *
                                </p>
                                <p class="mt-1 text-[11px] text-amber-800">Note : Ajoutez <code>*</code> à la fin de la bonne réponse. Séparez les questions par une ligne vide.</p>
                            </div>
                            <div id="guide_vf" class="<?= $type_quiz === 'vrai_faux' ? '' : 'hidden' ?>">
                                <p class="font-mono bg-white/70 p-2 rounded-lg border border-amber-200">
                                    Q: L'eau gèle à zéro degré Celsius. V<br>
                                    Q: La lune tourne autour du soleil directement. F
                                </p>
                                <p class="mt-1 text-[11px] text-amber-800">Note : Terminez chaque ligne par <code>. V</code> pour Vrai ou <code>. F</code> pour Faux.</p>
                            </div>
                        </div>

                        <!-- Textarea Unique -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Texte brut du Quiz *</label>
                                <button type="button" onclick="loadExample()" class="text-xs text-orange-600 font-bold hover:underline">Charger un exemple</button>
                            </div>
                            <textarea name="texte_brut" id="texte_brut" rows="12" required placeholder="Collez votre quiz ici selon le format..." class="w-full p-4 font-mono text-sm rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500 leading-relaxed"><?= e($texte_brut) ?></textarea>
                        </div>

                        <!-- Boutons d'Action (Aperçu / Publier) -->
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <button type="submit" name="action" value="apercu" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                <span>Générer l'Aperçu</span>
                            </button>

                            <?php if (!empty($parsed_questions) && empty($errors)): ?>
                                <button type="submit" name="action" value="publier" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all hover:scale-[1.02] flex items-center gap-2">
                                    <i data-lucide="send" class="w-4 h-4"></i>
                                    <span>Valider et Publier le Quiz</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- ZONE D'APERÇU DES QUESTIONS PARSÉES -->
                    <?php if (!empty($parsed_questions) && empty($errors)): ?>
                        <div class="pt-8 border-t border-slate-200 space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                                    <i data-lucide="check" class="w-5 h-5 text-emerald-500"></i>
                                    <span>Aperçu du Quiz (<?= count($parsed_questions) ?> question<?= count($parsed_questions) > 1 ? 's' : '' ?> validée<?= count($parsed_questions) > 1 ? 's' : '' ?>)</span>
                                </h3>
                                <span class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-bold">Prêt pour publication</span>
                            </div>

                            <div class="space-y-4">
                                <?php foreach ($parsed_questions as $q_idx => $q): ?>
                                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                        <div class="flex items-start gap-3">
                                            <span class="w-7 h-7 rounded-lg bg-orange-500 text-white text-xs font-black flex items-center justify-center shrink-0">
                                                <?= $q_idx + 1 ?>
                                            </span>
                                            <p class="text-sm font-bold text-slate-900 pt-0.5"><?= e($q['texte']) ?></p>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-10">
                                            <?php foreach ($q['reponses'] as $rep): ?>
                                                <div class="p-2.5 rounded-xl border text-xs font-semibold flex items-center justify-between <?= $rep['est_correcte'] ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-white border-slate-200 text-slate-700' ?>">
                                                    <span class="flex items-center gap-2">
                                                        <span class="font-bold font-mono text-slate-500"><?= e($rep['lettre']) ?>)</span>
                                                        <span><?= e($rep['texte']) ?></span>
                                                    </span>
                                                    <?php if ($rep['est_correcte']): ?>
                                                        <span class="text-[10px] bg-emerald-200 text-emerald-800 px-2 py-0.5 rounded-full font-black">BONNE RÉPONSE</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script>
function updatePlaceholder() {
    const type = document.getElementById('type_quiz').value;
    const gQcm = document.getElementById('guide_qcm');
    const gVf = document.getElementById('guide_vf');
    if (type === 'qcm') {
        gQcm.classList.remove('hidden');
        gVf.classList.add('hidden');
    } else {
        gQcm.classList.add('hidden');
        gVf.classList.remove('hidden');
    }
}

function loadExample() {
    const type = document.getElementById('type_quiz').value;
    const txt = document.getElementById('texte_brut');
    if (type === 'qcm') {
        txt.value = `Q: Quelle est la capitale constitutionnelle du Bénin ?
A) Cotonou
B) Porto-Novo *
C) Ouidah
D) Parakou

Q: Quel est le fleuve le plus long d'Afrique de l'Ouest ?
A) Le fleuve Sénégal
B) Le fleuve Niger *
C) Le fleuve Volta
D) Le fleuve Mono

Q: En quelle année a été créée l'Union Africaine ?
A) 1963
B) 2002 *
C) 1990`;
    } else {
        txt.value = `Q: Le mont Kilimandjaro est le plus haut sommet d'Afrique. V
Q: La langue officielle du Bénin est le portugais. F
Q: Le FCFA est utilisé dans 8 pays de l'UEMOA. V
Q: Le Nil se jette dans l'océan Atlantique. F`;
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copy_btn_text');
        btn.textContent = 'Copié !';
        setTimeout(() => { btn.textContent = 'Copier le code'; }, 2000);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
