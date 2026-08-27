import React, { useState } from 'react';
import { Composition, Classroom, Question, QuestionAnswer } from '../types';
import { 
  PlusCircle, 
  Sparkles, 
  AlertCircle, 
  CheckCircle2, 
  Copy, 
  Check, 
  Eye, 
  HelpCircle,
  Clock,
  ArrowRight,
  Play
} from 'lucide-react';

interface CreateQuizViewProps {
  userId: string | number;
  classrooms: Classroom[];
  onQuizCreated: (newComp: Composition) => void;
  onNavigate: (view: string) => void;
}

const DEFAULT_QCM_SAMPLE = `Q: Quel est l'organe principal responsable de la photosynthèse chez les végétaux ?
A) Les racines
B) La feuille *
C) La tige
D) Les fleurs

Q: Quel est le rôle principal de l'hémoglobine dans le sang humain ?
A) Le transport du dioxygène (O2) *
B) La digestion des glucides
C) La coagulation sanguine
D) La production d'insuline`;

const DEFAULT_VF_SAMPLE = `Q: La photosynthèse produit du dioxygène et du glucose. V
Q: L'eau pure bout toujours à 50°C à la pression atmosphérique normale. F
Q: Le cœur humain comporte quatre cavités principales. V`;

export const CreateQuizView: React.FC<CreateQuizViewProps> = ({
  userId,
  classrooms,
  onQuizCreated,
  onNavigate
}) => {
  const [titre, setTitre] = useState('');
  const [typeQuiz, setTypeQuiz] = useState<'qcm' | 'vrai_faux'>('qcm');
  const [dureeMinutes, setDureeMinutes] = useState<number>(5);
  const [classroomId, setClassroomId] = useState<string>('');
  const [rawText, setRawText] = useState(DEFAULT_QCM_SAMPLE);
  
  const [createdComposition, setCreatedComposition] = useState<Composition | null>(null);
  const [copied, setCopied] = useState(false);

  // Fonction de parsing robuste
  const parseQuestions = (): { questions: Question[]; errors: string[] } => {
    const lines = rawText.split('\n');
    const questions: Question[] = [];
    const errors: string[] = [];

    let currentQ: { texte: string; reponses: QuestionAnswer[] } | null = null;

    if (typeQuiz === 'qcm') {
      for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;

        if (line.startsWith('Q:') || line.startsWith('q:')) {
          if (currentQ) {
            // Valider la question précédente
            const hasGood = currentQ.reponses.some(r => r.est_correcte);
            if (currentQ.reponses.length < 2) {
              errors.push(`Question "${currentQ.texte.substring(0, 30)}..." : au moins 2 options (A, B) requises.`);
            } else if (!hasGood) {
              errors.push(`Question "${currentQ.texte.substring(0, 30)}..." : aucune bonne réponse marquée avec '*'.`);
            } else {
              questions.push({
                id: Date.now() + questions.length,
                texte: currentQ.texte,
                type_question: 'qcm',
                reponses: currentQ.reponses
              });
            }
          }
          currentQ = {
            texte: line.substring(2).trim(),
            reponses: []
          };
        } else if (/^[A-Da-d]\)/.test(line)) {
          if (!currentQ) {
            errors.push(`Ligne ${i + 1} : option trouvée avant toute question ("${line}").`);
            continue;
          }
          const lettre = line.charAt(0).toUpperCase();
          const isCorrect = line.endsWith('*');
          const texte = line.substring(2).replace(/\*$/, '').trim();
          currentQ.reponses.push({
            id: Date.now() + Math.floor(Math.random() * 100000),
            lettre,
            texte,
            est_correcte: isCorrect
          });
        } else {
          errors.push(`Ligne ${i + 1} invalide : format non reconnu ("${line}").`);
        }
      }

      // Valider la dernière question
      if (currentQ) {
        const hasGood = currentQ.reponses.some(r => r.est_correcte);
        if (currentQ.reponses.length < 2) {
          errors.push(`Dernière question : au moins 2 options requises.`);
        } else if (!hasGood) {
          errors.push(`Dernière question : aucune bonne réponse marquée avec '*'.`);
        } else {
          questions.push({
            id: Date.now() + questions.length,
            texte: currentQ.texte,
            type_question: 'qcm',
            reponses: currentQ.reponses
          });
        }
      }
    } else {
      // VRAI / FAUX
      for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;

        if (line.startsWith('Q:') || line.startsWith('q:')) {
          const body = line.substring(2).trim();
          const isVrai = body.endsWith('. V') || body.endsWith('. v') || body.endsWith(' V') || body.endsWith(' v');
          const isFaux = body.endsWith('. F') || body.endsWith('. f') || body.endsWith(' F') || body.endsWith(' f');

          if (!isVrai && !isFaux) {
            errors.push(`Ligne ${i + 1} : la question Vrai/Faux doit se terminer par ". V" ou ". F".`);
            continue;
          }

          const cleanText = body.replace(/\.\s*[VvFf]$/, '').replace(/\s+[VvFf]$/, '').trim();
          questions.push({
            id: Date.now() + questions.length,
            texte: cleanText,
            type_question: 'vrai_faux',
            reponses: [
              { id: Date.now() + 1, lettre: 'A', texte: 'Vrai', est_correcte: isVrai },
              { id: Date.now() + 2, lettre: 'B', texte: 'Faux', est_correcte: isFaux }
            ]
          });
        } else {
          errors.push(`Ligne ${i + 1} : chaque ligne doit commencer par "Q:".`);
        }
      }
    }

    return { questions, errors };
  };

  const { questions: parsedQuestions, errors: parseErrors } = parseQuestions();

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault();
    if (!titre.trim()) {
      alert("Veuillez renseigner un titre pour cette composition.");
      return;
    }
    if (parsedQuestions.length === 0) {
      alert("Veuillez corriger le format de vos questions avant de valider.");
      return;
    }

    // Génération d'un code unique KLR-XXXXX
    const code = 'KLR-' + Math.random().toString(36).substring(2, 7).toUpperCase();

    const newComp: Composition = {
      id: Date.now(),
      professeur_id: userId,
      classroom_id: classroomId ? parseInt(classroomId) : null,
      titre: titre.trim(),
      code_acces: code,
      type_quiz: typeQuiz,
      duree_minutes: dureeMinutes,
      questions: parsedQuestions,
      actif: true,
      created_at: new Date().toISOString(),
      nb_participants: 0,
      score_moyen: 0
    };

    onQuizCreated(newComp);
    setCreatedComposition(newComp);
  };

  const handleCopy = (code: string) => {
    navigator.clipboard.writeText(code);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  // ÉCRAN DE SUCCÈS APRÈS CRÉATION
  if (createdComposition) {
    return (
      <div className="max-w-2xl mx-auto py-8">
        <div className="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/90 shadow-xl text-center space-y-6 animate-fade-in">
          
          <div className="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-xs">
            <CheckCircle2 className="w-10 h-10" />
          </div>

          <div>
            <span className="text-[11px] font-black text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full uppercase tracking-wider">
              Quiz Créé & Publié avec Succès
            </span>
            <h1 className="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
              {createdComposition.titre}
            </h1>
            <p className="text-xs text-slate-500 mt-1">
              {createdComposition.questions.length} questions · {createdComposition.duree_minutes > 0 ? `${createdComposition.duree_minutes} minutes` : 'Temps illimité'}
            </p>
          </div>

          {/* CODE D'ACCÈS PROÉMINENT */}
          <div className="p-6 rounded-2xl bg-orange-50 border-2 border-dashed border-orange-300 space-y-3">
            <span className="text-xs font-bold text-orange-800 uppercase tracking-wider block">
              Code d'accès pour vos élèves
            </span>
            <div className="flex items-center justify-center gap-3">
              <span className="text-3xl sm:text-4xl font-black text-orange-600 font-mono tracking-wider">
                {createdComposition.code_acces}
              </span>
              <button
                onClick={() => handleCopy(createdComposition.code_acces)}
                className="p-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white shadow-sm transition-all"
                title="Copier le code"
              >
                {copied ? <Check className="w-5 h-5" /> : <Copy className="w-5 h-5" />}
              </button>
            </div>
            <p className="text-xs text-slate-600">
              Transmettez ce code à vos élèves. Ils pourront passer le quiz directement depuis la page d'accueil.
            </p>
          </div>

          <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
            <button
              onClick={() => onNavigate('compositions-professeur')}
              className="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all"
            >
              Voir mes compositions
            </button>
            <button
              onClick={() => {
                setCreatedComposition(null);
                setTitre('');
              }}
              className="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all"
            >
              Créer un autre quiz
            </button>
          </div>

        </div>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto space-y-8 py-6">
      
      <div>
        <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
          Créer un Nouveau Quiz d'Évaluation
        </h1>
        <p className="text-xs sm:text-sm text-slate-500">
          Utilisez la syntaxe rapide Klaro pour générer un quiz complet en quelques secondes.
        </p>
      </div>

      <form onSubmit={handleCreate} className="space-y-6">
        
        {/* PARAMÈTRES GÉNÉRAUX */}
        <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
          <h2 className="text-base font-extrabold text-slate-900 flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-orange-500"></span>
            <span>1. Paramètres de la Composition</span>
          </h2>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div className="md:col-span-2">
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Titre de l'évaluation *
              </label>
              <input
                type="text"
                required
                value={titre}
                onChange={(e) => setTitre(e.target.value)}
                placeholder="Ex: Contrôle Continu - Génétique & SVT"
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Type d'évaluation
              </label>
              <select
                value={typeQuiz}
                onChange={(e) => {
                  const val = e.target.value as 'qcm' | 'vrai_faux';
                  setTypeQuiz(val);
                  setRawText(val === 'qcm' ? DEFAULT_QCM_SAMPLE : DEFAULT_VF_SAMPLE);
                }}
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
              >
                <option value="qcm">QCM (Options A, B, C, D)</option>
                <option value="vrai_faux">Vrai / Faux (. V ou . F)</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Durée du chronomètre
              </label>
              <select
                value={dureeMinutes}
                onChange={(e) => setDureeMinutes(parseInt(e.target.value))}
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
              >
                <option value={3}>3 minutes (Flash)</option>
                <option value={5}>5 minutes (Standard)</option>
                <option value={10}>10 minutes</option>
                <option value={15}>15 minutes</option>
                <option value={0}>Illimité (Sans chrono)</option>
              </select>
            </div>

            <div className="md:col-span-2">
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Associer à un Classroom (optionnel)
              </label>
              <select
                value={classroomId}
                onChange={(e) => setClassroomId(e.target.value)}
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
              >
                <option value="">-- Aucun (Quiz public par code) --</option>
                {classrooms.map(cls => (
                  <option key={cls.id} value={cls.id}>
                    {cls.nom} ({cls.code_classe})
                  </option>
                ))}
              </select>
            </div>

          </div>
        </div>

        {/* ZONE DE TEXTE & ANALYSEUR SYNTAXIQUE EN DIRECT */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          {/* SAISIE DE TEXTE */}
          <div className="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-sm font-black text-slate-900 uppercase tracking-wider">
                2. Texte Brut des Questions
              </h2>
              <span className="text-xs font-mono text-orange-600 font-bold">
                {typeQuiz === 'qcm' ? 'Format QCM (* pour bonne réponse)' : 'Format Vrai/Faux (. V ou . F)'}
              </span>
            </div>

            <textarea
              rows={12}
              value={rawText}
              onChange={(e) => setRawText(e.target.value)}
              className="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white leading-relaxed"
            />

            {/* AVERTISSEMENTS / ERREURS DE SYNTAXE EN TEMPS RÉEL */}
            {parseErrors.length > 0 && (
              <div className="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <div className="flex items-center gap-1.5 font-bold text-rose-900">
                  <AlertCircle className="w-4 h-4" />
                  <span>Erreurs de syntaxe détectées :</span>
                </div>
                <ul className="list-disc list-inside space-y-0.5 text-[11px] pl-1">
                  {parseErrors.map((err, idx) => (
                    <li key={idx}>{err}</li>
                  ))}
                </ul>
              </div>
            )}
          </div>

          {/* PRÉVISUALISATION EN TEMPS RÉEL */}
          <div className="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 flex flex-col justify-between">
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <h2 className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                  <Eye className="w-4 h-4 text-blue-600" />
                  <span>Aperçu en Direct ({parsedQuestions.length} questions)</span>
                </h2>
                <span className="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">
                  {parseErrors.length === 0 ? '✓ Syntaxe Valide' : 'Ajustements Requis'}
                </span>
              </div>

              <div className="max-h-80 overflow-y-auto space-y-3 pr-1">
                {parsedQuestions.map((q, idx) => (
                  <div key={idx} className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <p className="text-xs font-bold text-slate-900">
                      <span className="text-orange-500 mr-1">{idx + 1}.</span>
                      {q.texte}
                    </p>
                    <div className="grid grid-cols-1 gap-1.5 pl-3 text-xs">
                      {q.reponses.map((r, rIdx) => (
                        <div 
                          key={rIdx} 
                          className={`p-1.5 px-2.5 rounded-lg flex items-center justify-between ${
                            r.est_correcte ? 'bg-emerald-100 text-emerald-900 font-bold border border-emerald-300' : 'text-slate-600'
                          }`}
                        >
                          <span>{r.lettre}) {r.texte}</span>
                          {r.est_correcte && (
                            <span className="text-[10px] uppercase font-black text-emerald-700 bg-white px-1.5 py-0.5 rounded">
                              Bonne Réponse ✓
                            </span>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <button
              type="submit"
              disabled={parsedQuestions.length === 0 || parseErrors.length > 0}
              className="w-full py-3.5 px-6 rounded-2xl bg-orange-500 hover:bg-orange-600 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-sm shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 mt-4"
            >
              <PlusCircle className="w-4 h-4" />
              <span>Générer et Obtenir le Code d'Accès</span>
            </button>
          </div>

        </div>

      </form>

    </div>
  );
};
