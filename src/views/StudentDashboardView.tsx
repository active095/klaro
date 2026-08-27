import React, { useEffect, useState } from 'react';
import { User, Entrainement, GrimoireItem } from '../types';
import { 
  Flame, 
  CheckCircle2, 
  Play, 
  FileCheck, 
  Sparkles, 
  GraduationCap, 
  Clock, 
  Trophy, 
  TrendingUp, 
  ArrowRight,
  BookOpen
} from 'lucide-react';

interface StudentDashboardViewProps {
  user: User;
  entrainements: Entrainement[];
  grimoire: GrimoireItem[];
  onNavigate: (view: string) => void;
  onViewTrainingDetail?: (trainId: number) => void;
}

export const StudentDashboardView: React.FC<StudentDashboardViewProps> = ({
  user,
  entrainements,
  grimoire,
  onNavigate,
  onViewTrainingDetail
}) => {
  const myTrainings = entrainements.filter(e => e.user_id === user.id && e.statut === 'termine');
  const myGrimoire = grimoire.filter(g => g.user_id === user.id);

  // Calculs statistiques réels
  const totalQuiz = myTrainings.length;
  const scoreMoyen = totalQuiz > 0 
    ? (myTrainings.reduce((acc, curr) => acc + curr.pourcentage, 0) / totalQuiz).toFixed(1)
    : '0';
  const totalTempsSec = myTrainings.reduce((acc, curr) => acc + curr.temps_ecoule, 0);
  const totalMinutes = Math.round(totalTempsSec / 60);

  // Calcul dynamique de la série de streak
  const trainingDates = Array.from(new Set(myTrainings.map(t => new Date(t.created_at).toISOString().split('T')[0]))).sort().reverse();
  const todayStr = new Date().toISOString().split('T')[0];
  const trainedToday = trainingDates.includes(todayStr);
  const realStreak = trainingDates.length > 0 ? trainingDates.length : 0;

  const [streakCount, setStreakCount] = useState(0);
  const [statQuizCount, setStatQuizCount] = useState(0);
  const [statScoreCount, setStatScoreCount] = useState(0);

  // Animation count-up
  useEffect(() => {
    const targetStreak = realStreak;
    const targetScore = parseFloat(scoreMoyen);
    
    let currentS = 0;
    const interval = setInterval(() => {
      currentS++;
      if (currentS <= targetStreak) setStreakCount(currentS);
      if (currentS <= totalQuiz) setStatQuizCount(currentS);
      if (currentS <= targetScore) setStatScoreCount(Math.min(targetScore, currentS));
      if (currentS >= Math.max(targetStreak, totalQuiz, targetScore)) clearInterval(interval);
    }, 40);

    return () => clearInterval(interval);
  }, [totalQuiz, scoreMoyen, realStreak]);

  return (
    <div className="space-y-8">
      
      {/* SECTION SUPÉRIEURE : PROFIL & STREAK 🔥 */}
      <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <span className="px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-700 text-xs font-bold uppercase tracking-wider">
              Apprenant Klaro
            </span>
            <span className="text-xs text-slate-400">
              Connecté : {user.prenom} {user.nom}
            </span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
            Bon retour, {user.prenom} ! 👋
          </h1>
          <p className="text-xs sm:text-sm text-slate-500">
            Prêt pour ta session d'évaluation quotidienne ? Maintiens ta série d'entraînement active.
          </p>
        </div>

        {/* BLOC SÉRIE DE STREAK FLAMME */}
        <div className="flex items-center gap-4 p-4 rounded-2xl bg-orange-50 border border-orange-200/80 shrink-0">
          <div className="w-14 h-14 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/30 animate-bounce">
            <Flame className="w-8 h-8 fill-current" />
          </div>
          <div>
            <div className="flex items-baseline gap-1.5">
              <span className="text-3xl font-black text-orange-950 font-mono">
                {streakCount}
              </span>
              <span className="text-xs font-bold text-orange-800 uppercase tracking-wider">
                Jours de série
              </span>
            </div>
            <div className="flex items-center gap-1.5 text-xs font-bold mt-0.5">
              {trainedToday ? (
                <div className="flex items-center gap-1 text-emerald-700">
                  <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                  <span>Entraîné aujourd'hui</span>
                </div>
              ) : (
                <div className="flex items-center gap-1 text-orange-700">
                  <Clock className="w-4 h-4 text-orange-600" />
                  <span>Entraînement du jour en attente</span>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* 4 CARTES DE STATISTIQUES AVEC ANIMATION COUNT-UP */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        {/* CARTE 1 : TOTAL QUIZ */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <Trophy className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-slate-900 font-mono">{statQuizCount}</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Quiz terminés</p>
          </div>
        </div>

        {/* CARTE 2 : SCORE MOYEN */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <TrendingUp className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-emerald-600 font-mono">{scoreMoyen}%</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Moyenne générale</p>
          </div>
        </div>

        {/* CARTE 3 : TEMPS TOTAL */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <Clock className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-slate-900 font-mono">{totalMinutes} min</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Temps passé</p>
          </div>
        </div>

        {/* CARTE 4 : FICHES GRIMOIRE */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <BookOpen className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-slate-900 font-mono">{myGrimoire.length}</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Fiches Grimoire</p>
          </div>
        </div>

      </div>

      {/* ACTIONS RAPIDES */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <button
          onClick={() => onNavigate('commencer-quiz')}
          className="p-5 rounded-3xl bg-orange-500 hover:bg-orange-600 text-white text-left shadow-lg shadow-orange-500/20 transition-all hover:scale-[1.02] flex flex-col justify-between h-36"
        >
          <Play className="w-6 h-6 fill-current" />
          <div>
            <h3 className="text-base font-black">Passer un quiz</h3>
            <p className="text-xs text-orange-100">Entrer un code d'évaluation</p>
          </div>
        </button>

        <button
          onClick={() => onNavigate('mes-compositions')}
          className="p-5 rounded-3xl bg-white hover:bg-slate-50 text-slate-800 text-left border border-slate-200/80 shadow-sm transition-all hover:border-slate-300 flex flex-col justify-between h-36"
        >
          <FileCheck className="w-6 h-6 text-emerald-600" />
          <div>
            <h3 className="text-base font-black text-slate-900">Mes Notes</h3>
            <p className="text-xs text-slate-500">Historique & corrections</p>
          </div>
        </button>

        <button
          onClick={() => onNavigate('classrooms')}
          className="p-5 rounded-3xl bg-white hover:bg-slate-50 text-slate-800 text-left border border-slate-200/80 shadow-sm transition-all hover:border-slate-300 flex flex-col justify-between h-36"
        >
          <GraduationCap className="w-6 h-6 text-purple-600" />
          <div>
            <h3 className="text-base font-black text-slate-900">Mes Classrooms</h3>
            <p className="text-xs text-slate-500">Classes & professeurs</p>
          </div>
        </button>

        <button
          onClick={() => onNavigate('outils-ia')}
          className="p-5 rounded-3xl bg-blue-600 hover:bg-blue-700 text-white text-left shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] flex flex-col justify-between h-36"
        >
          <Sparkles className="w-6 h-6" />
          <div>
            <h3 className="text-base font-black">Klaro AI</h3>
            <p className="text-xs text-blue-100">Résumés de cours & quiz</p>
          </div>
        </button>
      </div>

      {/* TABLEAU DES DERNIERS ENTRAÎNEMENTS */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div className="p-6 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 className="text-lg font-black text-slate-900">Dernières Évaluations Passées</h2>
            <p className="text-xs text-slate-500">Suivi en direct de tes résultats d'évaluation.</p>
          </div>
          <button 
            onClick={() => onNavigate('mes-compositions')}
            className="text-xs font-bold text-orange-600 hover:underline flex items-center gap-1"
          >
            <span>Voir tout</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </button>
        </div>

        {myTrainings.length === 0 ? (
          <div className="text-center py-12 px-4 space-y-3">
            <Trophy className="w-10 h-10 text-slate-300 mx-auto" />
            <p className="text-sm font-bold text-slate-700">Aucun quiz passé pour l'instant</p>
            <p className="text-xs text-slate-500">Tape un code de quiz pour commencer ton premier entraînement.</p>
            <button
              onClick={() => onNavigate('commencer-quiz')}
              className="px-5 py-2.5 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-orange-600 transition-colors"
            >
              Lancer un quiz
            </button>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                  <th className="py-3.5 px-6">Quiz & Code</th>
                  <th className="py-3.5 px-6">Score</th>
                  <th className="py-3.5 px-6">Pourcentage</th>
                  <th className="py-3.5 px-6">Temps passé</th>
                  <th className="py-3.5 px-6">Date</th>
                  <th className="py-3.5 px-6 text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 font-medium">
                {myTrainings.slice(0, 5).map((t) => {
                  const pct = t.pourcentage;
                  const badgeClass = pct >= 70 ? 'bg-emerald-50 text-emerald-700' : (pct >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                  return (
                    <tr key={t.id} className="hover:bg-slate-50/80 transition-colors">
                      <td className="py-4 px-6">
                        <div className="font-bold text-slate-900">{t.comp_titre || 'Quiz Évaluation'}</div>
                        <span className="text-xs font-mono text-slate-400">{t.comp_code}</span>
                      </td>
                      <td className="py-4 px-6 font-bold text-slate-800">
                        {t.score} / {t.total_questions}
                      </td>
                      <td className="py-4 px-6">
                        <span className={`inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold ${badgeClass}`}>
                          {t.pourcentage}%
                        </span>
                      </td>
                      <td className="py-4 px-6 text-xs text-slate-600">
                        {Math.floor(t.temps_ecoule / 60)}m {t.temps_ecoule % 60}s
                      </td>
                      <td className="py-4 px-6 text-xs text-slate-500">
                        {new Date(t.created_at).toLocaleDateString('fr-FR')}
                      </td>
                      <td className="py-4 px-6 text-right">
                        <button
                          onClick={() => {
                            if (onViewTrainingDetail) onViewTrainingDetail(t.id);
                            else onNavigate('mes-compositions');
                          }}
                          className="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors"
                        >
                          Détails
                        </button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

    </div>
  );
};
