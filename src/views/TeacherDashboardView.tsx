import React, { useState, useEffect } from 'react';
import { User, Composition, Classroom, Entrainement } from '../types';
import { 
  PlusCircle, 
  FileText, 
  Users, 
  GraduationCap, 
  Percent, 
  Copy, 
  Check, 
  Play, 
  Trash2,
  TrendingUp,
  Sparkles,
  ArrowRight
} from 'lucide-react';

interface TeacherDashboardViewProps {
  user: User;
  compositions: Composition[];
  classrooms: Classroom[];
  entrainements: Entrainement[];
  onNavigate: (view: string) => void;
  onDeleteComposition: (id: string | number) => void;
  onStartQuizWithCode: (code: string) => void;
}

export const TeacherDashboardView: React.FC<TeacherDashboardViewProps> = ({
  user,
  compositions,
  classrooms,
  entrainements,
  onNavigate,
  onDeleteComposition,
  onStartQuizWithCode
}) => {
  const myCompositions = compositions.filter(c => c.professeur_id === user.id);
  const myClassrooms = classrooms.filter(c => c.professeur_id === user.id);

  // Calculs statistiques
  const totalQuiz = myCompositions.length;
  const totalClasses = myClassrooms.length;
  
  // Apprenants distincts ayant passé les quiz de ce professeur
  const uniqueStudents = new Set(entrainements.map(e => e.user_id)).size;
  
  // Taux d'engagement (%) : ratio participants sur élèves inscrits
  const totalInscrits = myClassrooms.reduce((acc, c) => acc + (c.membres_count || 0), 0);
  const totalPassations = myCompositions.reduce((acc, c) => acc + (c.nb_participants || 0), 0);
  const tauxEngagement = totalInscrits > 0 
    ? Math.min(100, Math.round((totalPassations / totalInscrits) * 100))
    : (totalPassations > 0 ? 100 : 0);

  // Animations count-up
  const [countQuiz, setCountQuiz] = useState(0);
  const [countClasses, setCountClasses] = useState(0);
  const [countStudents, setCountStudents] = useState(0);
  const [copiedCode, setCopiedCode] = useState<string | null>(null);

  useEffect(() => {
    let current = 0;
    const interval = setInterval(() => {
      current++;
      if (current <= totalQuiz) setCountQuiz(current);
      if (current <= totalClasses) setCountClasses(current);
      if (current <= uniqueStudents) setCountStudents(current);
      if (current >= Math.max(totalQuiz, totalClasses, uniqueStudents)) clearInterval(interval);
    }, 40);
    return () => clearInterval(interval);
  }, [totalQuiz, totalClasses, uniqueStudents]);

  const handleCopyCode = (code: string) => {
    navigator.clipboard.writeText(code);
    setCopiedCode(code);
    setTimeout(() => setCopiedCode(null), 2000);
  };

  return (
    <div className="space-y-8">
      
      {/* HEADER PROFESSEUR */}
      <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <span className="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider">
              Enseignant Certifié
            </span>
            <span className="text-xs text-slate-400">
              Dernière connexion : Aujourd'hui à {new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
            </span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
            Tableau de Bord Enseignant
          </h1>
          <p className="text-xs sm:text-sm text-slate-500">
            Bienvenue, Prof. {user.prenom} {user.nom}. Gérez vos évaluations et suivez la progression de vos classes.
          </p>
        </div>

        <div className="flex flex-wrap gap-3">
          <button
            onClick={() => onNavigate('creer-quiz')}
            className="px-5 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/25 transition-all hover:scale-[1.02] flex items-center gap-2"
          >
            <PlusCircle className="w-4 h-4" />
            <span>Nouveau Quiz</span>
          </button>
        </div>
      </div>

      {/* 4 CARTES DE STATISTIQUES PROFESSEUR */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        {/* CARTE 1 : TOTAL QUIZ */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
            <FileText className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-slate-900 font-mono">{countQuiz}</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Quiz publiés</p>
          </div>
        </div>

        {/* CARTE 2 : CLASSES ACTIVES */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <GraduationCap className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-slate-900 font-mono">{countClasses}</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Classes actives</p>
          </div>
        </div>

        {/* CARTE 3 : APPRENANTS UNIQUES */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <Users className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-slate-900 font-mono">{countStudents}</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Élèves suivis</p>
          </div>
        </div>

        {/* CARTE 4 : ENGAGEMENT */}
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-2">
          <div className="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <Percent className="w-5 h-5" />
          </div>
          <div>
            <span className="text-2xl font-black text-emerald-600 font-mono">{tauxEngagement}%</span>
            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">Taux d'engagement</p>
          </div>
        </div>

      </div>

      {/* TABLEAU DES COMPOSITIONS CRÉÉES */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div className="p-6 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 className="text-lg font-black text-slate-900">Mes Compositions & Codes d'Accès</h2>
            <p className="text-xs text-slate-500">Partagez le code avec vos élèves pour qu'ils puissent passer l'évaluation.</p>
          </div>
          <button 
            onClick={() => onNavigate('compositions-professeur')}
            className="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
          >
            <span>Gérer toutes les compositions</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </button>
        </div>

        {myCompositions.length === 0 ? (
          <div className="text-center py-12 px-4 space-y-3">
            <FileText className="w-10 h-10 text-slate-300 mx-auto" />
            <p className="text-sm font-bold text-slate-700">Aucune composition créée</p>
            <button
              onClick={() => onNavigate('creer-quiz')}
              className="px-5 py-2.5 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-xs"
            >
              Créer mon premier quiz
            </button>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                  <th className="py-3.5 px-6">Code d'accès</th>
                  <th className="py-3.5 px-6">Titre de la Composition</th>
                  <th className="py-3.5 px-6">Type</th>
                  <th className="py-3.5 px-6">Questions</th>
                  <th className="py-3.5 px-6">Participants</th>
                  <th className="py-3.5 px-6">Score Moyen</th>
                  <th className="py-3.5 px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 font-medium">
                {myCompositions.map((c) => (
                  <tr key={c.id} className="hover:bg-slate-50/80 transition-colors">
                    {/* BOUTON COPIER CODE D'ACCÈS */}
                    <td className="py-4 px-6">
                      <button
                        onClick={() => handleCopyCode(c.code_acces)}
                        className="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 font-mono font-black text-xs border border-orange-200/80 transition-all"
                        title="Cliquer pour copier le code"
                      >
                        <span>{c.code_acces}</span>
                        {copiedCode === c.code_acces ? (
                          <Check className="w-3.5 h-3.5 text-emerald-600" />
                        ) : (
                          <Copy className="w-3.5 h-3.5 text-orange-500" />
                        )}
                      </button>
                    </td>

                    <td className="py-4 px-6 font-bold text-slate-900">
                      {c.titre}
                    </td>

                    <td className="py-4 px-6">
                      <span className={`px-2 py-0.5 rounded-full text-xs font-bold ${
                        c.type_quiz === 'qcm' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'
                      }`}>
                        {c.type_quiz.toUpperCase()}
                      </span>
                    </td>

                    <td className="py-4 px-6 text-slate-600">
                      {c.questions.length} questions
                    </td>

                    <td className="py-4 px-6 font-semibold text-slate-700">
                      {c.nb_participants || 0}
                    </td>

                    <td className="py-4 px-6">
                      <span className="text-xs font-bold text-emerald-600">
                        {c.score_moyen !== undefined && c.score_moyen !== null ? `${c.score_moyen}%` : '—'}
                      </span>
                    </td>

                    <td className="py-4 px-6 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <button
                          onClick={() => onStartQuizWithCode(c.code_acces)}
                          className="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors"
                          title="Tester le quiz"
                        >
                          <Play className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => {
                            if (confirm(`Supprimer la composition « ${c.titre} » ?`)) {
                              onDeleteComposition(c.id);
                            }
                          }}
                          className="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors"
                          title="Supprimer"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

    </div>
  );
};
