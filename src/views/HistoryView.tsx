import React, { useState } from 'react';
import { Entrainement, User } from '../types';
import { 
  History, 
  Search, 
  Filter, 
  Trophy, 
  Clock, 
  Calendar, 
  ArrowRight,
  CheckCircle2,
  AlertCircle
} from 'lucide-react';

interface HistoryViewProps {
  user: User;
  entrainements: Entrainement[];
  onNavigate: (view: string) => void;
}

export const HistoryView: React.FC<HistoryViewProps> = ({
  user,
  entrainements,
  onNavigate
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [filterScore, setFilterScore] = useState<string>('all');
  const [selectedTraining, setSelectedTraining] = useState<Entrainement | null>(null);

  const myTrainings = entrainements.filter(e => e.user_id === user.id);

  const filtered = myTrainings.filter(t => {
    const matchesSearch = (t.comp_titre?.toLowerCase().includes(searchTerm.toLowerCase()) || 
                           t.comp_code?.toLowerCase().includes(searchTerm.toLowerCase()));
    if (!matchesSearch) return false;

    if (filterScore === 'reussi') return t.pourcentage >= 70;
    if (filterScore === 'moyen') return t.pourcentage >= 50 && t.pourcentage < 70;
    if (filterScore === 'echec') return t.pourcentage < 50;
    return true;
  });

  return (
    <div className="space-y-8 py-4">
      
      <div>
        <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
          Historique Complet des Entraînements
        </h1>
        <p className="text-xs sm:text-sm text-slate-500">
          Consultez l'ensemble de vos passages de quiz et analysez votre progression.
        </p>
      </div>

      {/* FILTRES DE RECHERCHE */}
      <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <div className="relative w-full sm:w-72">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
          <input
            type="text"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder="Rechercher par titre ou code..."
            className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
          />
        </div>

        <div className="flex items-center gap-2 w-full sm:w-auto">
          <Filter className="w-4 h-4 text-slate-400" />
          <select
            value={filterScore}
            onChange={(e) => setFilterScore(e.target.value)}
            className="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <option value="all">Tous les résultats</option>
            <option value="reussi">Score ≥ 70% (Réussi)</option>
            <option value="moyen">Score 50% - 69% (Moyen)</option>
            <option value="echec">Score &lt; 50% (À revoir)</option>
          </select>
        </div>

      </div>

      {/* TABLEAU HISTORIQUE */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        {filtered.length === 0 ? (
          <div className="text-center py-12 px-4 space-y-3">
            <History className="w-10 h-10 text-slate-300 mx-auto" />
            <p className="text-sm font-bold text-slate-700">Aucun enregistrement trouvé</p>
            <p className="text-xs text-slate-500">Ajustez vos filtres ou effectuez un nouveau quiz.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                  <th className="py-3.5 px-6">Quiz</th>
                  <th className="py-3.5 px-6">Score</th>
                  <th className="py-3.5 px-6">Pourcentage</th>
                  <th className="py-3.5 px-6">Durée</th>
                  <th className="py-3.5 px-6">Date</th>
                  <th className="py-3.5 px-6">Mode</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 font-medium">
                {filtered.map((t) => {
                  const pct = t.pourcentage;
                  const badgeClass = pct >= 70 ? 'bg-emerald-50 text-emerald-700' : (pct >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                  return (
                    <tr key={t.id} className="hover:bg-slate-50/80 transition-colors">
                      <td className="py-4 px-6">
                        <div className="font-bold text-slate-900">{t.comp_titre}</div>
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
                        {new Date(t.created_at).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })}
                      </td>
                      <td className="py-4 px-6 text-xs font-medium">
                        {t.soumission_type === 'expiration_temps' ? (
                          <span className="text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md font-semibold">
                            Chrono expiré
                          </span>
                        ) : (
                          <span className="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md font-semibold">
                            Volontaire
                          </span>
                        )}
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
