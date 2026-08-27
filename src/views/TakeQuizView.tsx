import React, { useState, useEffect, useRef } from 'react';
import { Composition, Entrainement, Question } from '../types';
import { 
  Play, 
  Clock, 
  CheckCircle2, 
  AlertTriangle, 
  HelpCircle, 
  Award, 
  Flame, 
  RotateCcw, 
  ArrowRight,
  ShieldCheck
} from 'lucide-react';

interface TakeQuizViewProps {
  composition: Composition;
  userId: string | number;
  onQuizCompleted: (newTrain: Entrainement) => void;
  onNavigate: (view: string) => void;
}

export const TakeQuizView: React.FC<TakeQuizViewProps> = ({
  composition,
  userId,
  onQuizCompleted,
  onNavigate
}) => {
  const [step, setStep] = useState<'intro' | 'play' | 'result'>('intro');
  const [selectedAnswers, setSelectedAnswers] = useState<{ [qId: string]: string | number }>({});
  const [startTime, setStartTime] = useState<number>(0);
  const [remainingSeconds, setRemainingSeconds] = useState<number>(composition.duree_minutes * 60);
  const [completedTraining, setCompletedTraining] = useState<Entrainement | null>(null);
  const [isAutoSaving, setIsAutoSaving] = useState(false);
  const [lastSavedTime, setLastSavedTime] = useState<string | null>(null);

  const timerRef = useRef<NodeJS.Timeout | null>(null);

  // 1. DÉMARRAGE DU QUIZ (L'HORLOGE NE DÉMARRE QU'AU CLIC)
  const handleStart = () => {
    const now = Date.now();
    setStartTime(now);
    setRemainingSeconds(composition.duree_minutes > 0 ? composition.duree_minutes * 60 : 999999);
    setStep('play');
  };

  // 2. GESTION DU COMPTE À REBOURS
  useEffect(() => {
    if (step === 'play' && composition.duree_minutes > 0) {
      timerRef.current = setInterval(() => {
        setRemainingSeconds((prev) => {
          if (prev <= 1) {
            if (timerRef.current) clearInterval(timerRef.current);
            handleAutoSubmitOnTimeout();
            return 0;
          }
          return prev - 1;
        });
      }, 1000);

      return () => {
        if (timerRef.current) clearInterval(timerRef.current);
      };
    }
  }, [step, composition.duree_minutes]);

  // 3. SAUVEGARDE PÉRIODIQUE ASYNCHRONE (TOUTES LES 30S)
  useEffect(() => {
    if (step === 'play') {
      const autoSaveInterval = setInterval(() => {
        setIsAutoSaving(true);
        setTimeout(() => {
          setIsAutoSaving(false);
          setLastSavedTime(new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
        }, 600);
      }, 30000); // 30 secondes

      return () => clearInterval(autoSaveInterval);
    }
  }, [step]);

  // 4. SÉLECTION D'UNE RÉPONSE
  const handleSelectOption = (questionId: number, answerId: number) => {
    setSelectedAnswers(prev => ({
      ...prev,
      [questionId]: answerId
    }));
  };

  // 5. SOUMISSION AUTOMATIQUE PAR EXPIRATION DE TEMPS
  const handleAutoSubmitOnTimeout = () => {
    finishQuiz('expiration_temps');
  };

  // 6. SOUMISSION VOLONTAIRE
  const handleManualSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    finishQuiz('volontaire');
  };

  // CALCUL ET ENREGISTREMENT DU RÉSULTAT
  const finishQuiz = (motif: 'volontaire' | 'expiration_temps') => {
    if (timerRef.current) clearInterval(timerRef.current);

    const now = Date.now();
    const elapsedSeconds = Math.max(1, Math.round((now - startTime) / 1000));
    
    let correctCount = 0;
    const totalQ = composition.questions.length;

    const details = composition.questions.map(q => {
      const chosenId = selectedAnswers[q.id] || null;
      const goodOption = q.reponses.find(r => r.est_correcte);
      const isCorrect = (chosenId !== null && goodOption !== undefined && chosenId === goodOption.id);
      if (isCorrect) correctCount++;
      return {
        question_id: q.id,
        reponse_choisie_id: chosenId,
        est_correcte: isCorrect
      };
    });

    const pct = totalQ > 0 ? Math.round((correctCount / totalQ) * 100 * 10) / 10 : 0;

    const newTraining: Entrainement = {
      id: Date.now(),
      user_id: userId,
      composition_id: composition.id,
      score: correctCount,
      total_questions: totalQ,
      pourcentage: pct,
      temps_ecoule: elapsedSeconds,
      soumission_type: motif,
      statut: 'termine',
      created_at: new Date().toISOString(),
      comp_titre: composition.titre,
      comp_code: composition.code_acces,
      reponses_details: details
    };

    onQuizCompleted(newTraining);
    setCompletedTraining(newTraining);
    setStep('result');
  };

  // -------------------------------------------------------------
  // ÉCRAN 1 : INTRODUCTION AU QUIZ
  // -------------------------------------------------------------
  if (step === 'intro') {
    return (
      <div className="max-w-2xl mx-auto py-8">
        <div className="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-lg text-center space-y-6 animate-fade-in">
          
          <div className="w-16 h-16 rounded-3xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto shadow-xs">
            <Award className="w-9 h-9" />
          </div>

          <div>
            <span className="inline-flex px-3.5 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-mono font-bold uppercase tracking-wider mb-2">
              Code : {composition.code_acces}
            </span>
            <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
              {composition.titre}
            </h1>
            <p className="text-xs text-slate-500 mt-1">
              Type : <strong className="uppercase text-slate-700">{composition.type_quiz}</strong>
            </p>
          </div>

          {/* INFORMATIONS CLÉS */}
          <div className="grid grid-cols-2 gap-4 max-w-md mx-auto">
            <div className="p-4 rounded-2xl bg-orange-50 border border-orange-100">
              <Clock className="w-5 h-5 text-orange-600 mx-auto mb-1" />
              <p className="text-[11px] font-bold text-slate-500 uppercase">Durée impartie</p>
              <p className="text-lg font-black text-slate-900 mt-0.5">
                {composition.duree_minutes > 0 ? `${composition.duree_minutes} minutes` : 'Temps illimité'}
              </p>
            </div>
            <div className="p-4 rounded-2xl bg-blue-50 border border-blue-100">
              <HelpCircle className="w-5 h-5 text-blue-600 mx-auto mb-1" />
              <p className="text-[11px] font-bold text-slate-500 uppercase">Total Questions</p>
              <p className="text-lg font-black text-slate-900 mt-0.5">
                {composition.questions.length} questions
              </p>
            </div>
          </div>

          {/* RÈGLES DE PASSATION */}
          <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 max-w-md mx-auto text-left space-y-1.5">
            <p className="font-bold text-slate-800 flex items-center gap-1.5">
              <ShieldCheck className="w-4 h-4 text-orange-500" />
              <span>Consignes de l'évaluation :</span>
            </p>
            <p>• Le chronomètre officiel démarre dès que vous cliquez sur <strong>Commencer</strong>.</p>
            <p>• Vos réponses sont sauvegardées automatiquement toutes les 30 secondes.</p>
            {composition.duree_minutes > 0 && (
              <p>• À l'expiration du temps, le formulaire est validé automatiquement.</p>
            )}
          </div>

          <button
            onClick={handleStart}
            className="w-full sm:w-auto px-10 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black text-base shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.02] flex items-center justify-center gap-2 mx-auto"
          >
            <Play className="w-5 h-5 fill-current" />
            <span>Commencer l'Évaluation</span>
          </button>

        </div>
      </div>
    );
  }

  // -------------------------------------------------------------
  // ÉCRAN 2 : JEU EN DIRECT AVEC CHRONO MM:SS
  // -------------------------------------------------------------
  if (step === 'play') {
    const minutes = Math.floor(remainingSeconds / 60);
    const seconds = remainingSeconds % 60;
    const formattedTime = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

    const isUrgent = remainingSeconds <= 30;
    const isWarning = remainingSeconds <= 60 && !isUrgent;

    return (
      <form onSubmit={handleManualSubmit} className="max-w-3xl mx-auto space-y-6 py-6">
        
        {/* BANDEAU STICKY AVEC MINUTEUR */}
        <div className="sticky top-20 z-30 bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center justify-between gap-4">
          <div>
            <h2 className="text-sm font-bold text-slate-900 truncate max-w-xs sm:max-w-md">
              {composition.titre}
            </h2>
            <div className="flex items-center gap-2 text-xs text-slate-500">
              <span>{composition.questions.length} questions</span>
              <span>·</span>
              <span className="text-emerald-600 font-semibold flex items-center gap-1">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                {isAutoSaving ? 'Sauvegarde...' : 'Auto-save actif'}
              </span>
            </div>
          </div>

          {/* CHRONOMÈTRE MM:SS */}
          {composition.duree_minutes > 0 ? (
            <div className={`px-4 py-2 rounded-xl font-mono font-black text-lg sm:text-xl flex items-center gap-2 transition-colors ${
              isUrgent 
                ? 'bg-rose-500 text-white animate-pulse shadow-md shadow-rose-500/25' 
                : isWarning 
                ? 'bg-orange-500 text-white' 
                : 'bg-slate-100 text-slate-800'
            }`}>
              <Clock className="w-5 h-5" />
              <span>{formattedTime}</span>
            </div>
          ) : (
            <div className="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold flex items-center gap-1.5">
              <span>Illimité</span>
            </div>
          )}
        </div>

        {/* LISTE DES QUESTIONS */}
        <div className="space-y-6">
          {composition.questions.map((q, idx) => (
            <div key={q.id} className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
              
              <div className="flex items-start gap-3">
                <span className="w-8 h-8 rounded-xl bg-orange-500 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                  {idx + 1}
                </span>
                <h3 className="text-base sm:text-lg font-bold text-slate-900 pt-0.5 leading-snug">
                  {q.texte}
                </h3>
              </div>

              {/* OPTIONS DE RÉPONSE */}
              <div className="grid grid-cols-1 gap-2.5 pt-2 pl-0 sm:pl-11">
                {q.reponses.map((rep) => {
                  const isChecked = selectedAnswers[q.id] === rep.id;
                  return (
                    <label 
                      key={rep.id}
                      className={`relative flex items-center p-3.5 rounded-2xl border-2 cursor-pointer transition-all ${
                        isChecked 
                          ? 'border-orange-500 bg-orange-50/70 text-slate-900 shadow-xs' 
                          : 'border-slate-200 hover:border-orange-200 hover:bg-slate-50 text-slate-700'
                      }`}
                    >
                      <input
                        type="radio"
                        name={`q_${q.id}`}
                        value={rep.id}
                        checked={isChecked}
                        onChange={() => handleSelectOption(q.id, rep.id)}
                        className="w-4 h-4 text-orange-500 accent-orange-500 focus:ring-orange-500 mr-3"
                      />
                      <span className="font-mono font-bold text-slate-400 mr-2 text-sm">
                        {rep.lettre})
                      </span>
                      <span className="text-sm font-semibold text-slate-800">
                        {rep.texte}
                      </span>
                    </label>
                  );
                })}
              </div>

            </div>
          ))}
        </div>

        {/* BOUTON DE SOUMISSION */}
        <div className="pt-4 flex justify-end">
          <button
            type="submit"
            className="w-full sm:w-auto px-8 py-4 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black text-base shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.02] flex items-center justify-center gap-2"
          >
            <CheckCircle2 className="w-5 h-5" />
            <span>Valider et Soumettre mes Réponses</span>
          </button>
        </div>

      </form>
    );
  }

  // -------------------------------------------------------------
  // ÉCRAN 3 : RÉSULTAT & CORRECTION QUESTION PAR QUESTION
  // -------------------------------------------------------------
  if (step === 'result' && completedTraining) {
    const isSuccess = completedTraining.pourcentage >= 70;
    const isAverage = completedTraining.pourcentage >= 50 && !isSuccess;

    return (
      <div className="max-w-3xl mx-auto space-y-8 py-6 animate-fade-in">
        
        {/* CARTE HERO RÉSULTAT */}
        <div className="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-lg text-center space-y-6">
          
          <div className={`w-20 h-20 rounded-3xl mx-auto flex items-center justify-center text-3xl font-black ${
            isSuccess ? 'bg-emerald-100 text-emerald-600' : isAverage ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600'
          }`}>
            {isSuccess ? '🎉' : isAverage ? '👍' : '💪'}
          </div>

          <div>
            <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
              Résultats de votre Évaluation
            </h1>
            <p className="text-xs sm:text-sm text-slate-500 mt-1">
              {composition.titre}
            </p>
          </div>

          {/* SCORES DÉTAILLÉS */}
          <div className="max-w-md mx-auto grid grid-cols-3 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
            <div>
              <div className="text-2xl font-black text-slate-900 font-mono">
                {completedTraining.score} / {completedTraining.total_questions}
              </div>
              <div className="text-[10px] font-bold text-slate-400 uppercase">Score</div>
            </div>
            <div className="border-x border-slate-200">
              <div className={`text-2xl font-black font-mono ${isSuccess ? 'text-emerald-600' : 'text-orange-600'}`}>
                {completedTraining.pourcentage}%
              </div>
              <div className="text-[10px] font-bold text-slate-400 uppercase">Réussite</div>
            </div>
            <div>
              <div className="text-2xl font-black text-slate-900 font-mono">
                {Math.floor(completedTraining.temps_ecoule / 60)}m {completedTraining.temps_ecoule % 60}s
              </div>
              <div className="text-[10px] font-bold text-slate-400 uppercase">Temps</div>
            </div>
          </div>

          {completedTraining.soumission_type === 'expiration_temps' && (
            <p className="text-xs font-semibold text-rose-600 bg-rose-50 py-1.5 px-4 rounded-full inline-block border border-rose-200">
              ⏱️ Soumis automatiquement par expiration du temps imparti
            </p>
          )}

          {/* ACTIONS APRÈS ÉVALUATION */}
          <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
            <button
              onClick={() => onNavigate('dashboard')}
              className="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-md shadow-orange-500/20 transition-all"
            >
              Retour au Dashboard
            </button>
            <button
              onClick={() => {
                setSelectedAnswers({});
                setStep('intro');
              }}
              className="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all flex items-center gap-1.5"
            >
              <RotateCcw className="w-3.5 h-3.5" />
              <span>Refaire ce quiz</span>
            </button>
            <button
              onClick={() => onNavigate('outils-ia')}
              className="px-6 py-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition-all"
            >
              Générer fiche IA sur ce sujet
            </button>
          </div>

        </div>

        {/* CORRECTION QUESTION PAR QUESTION */}
        <div className="space-y-4">
          <h2 className="text-lg font-black text-slate-900 flex items-center gap-2">
            <CheckCircle2 className="w-5 h-5 text-orange-500" />
            <span>Correction Détaillée</span>
          </h2>

          {composition.questions.map((q, idx) => {
            const userChoiceId = selectedAnswers[q.id];
            const goodOption = q.reponses.find(r => r.est_correcte);
            const isCorrect = userChoiceId !== undefined && goodOption !== undefined && userChoiceId === goodOption.id;

            return (
              <div 
                key={q.id} 
                className={`bg-white rounded-3xl p-6 border shadow-xs space-y-4 ${
                  isCorrect ? 'border-emerald-200 bg-emerald-50/10' : 'border-rose-200 bg-rose-50/10'
                }`}
              >
                <div className="flex items-start justify-between gap-4">
                  <div className="flex items-start gap-3">
                    <span className={`w-7 h-7 rounded-xl text-white font-black text-xs flex items-center justify-center shrink-0 ${
                      isCorrect ? 'bg-emerald-500' : 'bg-rose-500'
                    }`}>
                      {idx + 1}
                    </span>
                    <h3 className="text-sm sm:text-base font-bold text-slate-900 pt-0.5">
                      {q.texte}
                    </h3>
                  </div>

                  <span className={`px-2.5 py-1 rounded-full text-xs font-bold shrink-0 ${
                    isCorrect ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
                  }`}>
                    {isCorrect ? '+1 pt Correct' : '0 pt Incorrect'}
                  </span>
                </div>

                {/* OPTIONS DÉTAILLÉES */}
                <div className="grid grid-cols-1 gap-2 pl-0 sm:pl-10">
                  {q.reponses.map((rep) => {
                    const isChosen = rep.id === userChoiceId;
                    const isGood = rep.est_correcte;

                    let rowClass = 'border-slate-200 bg-white text-slate-700';
                    if (isGood) {
                      rowClass = 'border-emerald-400 bg-emerald-50 text-emerald-900 font-bold';
                    } else if (isChosen && !isGood) {
                      rowClass = 'border-rose-400 bg-rose-50 text-rose-900 font-bold';
                    }

                    return (
                      <div key={rep.id} className={`p-3 rounded-2xl border text-xs flex items-center justify-between ${rowClass}`}>
                        <div className="flex items-center gap-2">
                          <span className="font-mono font-bold">{rep.lettre})</span>
                          <span>{rep.texte}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          {isChosen && (
                            <span className="text-[10px] uppercase font-black px-2 py-0.5 rounded bg-slate-200 text-slate-800">
                              Ta réponse
                            </span>
                          )}
                          {isGood && (
                            <span className="text-[10px] uppercase font-black px-2 py-0.5 rounded bg-emerald-200 text-emerald-800">
                              Bonne réponse ✓
                            </span>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>

              </div>
            );
          })}
        </div>

      </div>
    );
  }

  return null;
};
