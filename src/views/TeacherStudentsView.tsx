import React from 'react';
import { User, Entrainement } from '../types';
import { Users, GraduationCap, Trophy, CheckCircle2, Search } from 'lucide-react';

interface TeacherStudentsViewProps {
  users: User[];
  entrainements: Entrainement[];
}

export const TeacherStudentsView: React.FC<TeacherStudentsViewProps> = ({ users, entrainements }) => {
  const students = users.filter(u => u.role === 'apprenant');

  return (
    <div className="space-y-8 py-4">
      
      <div>
        <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
          Suivi des Apprenants & Résultats
        </h1>
        <p className="text-xs sm:text-sm text-slate-500">
          Consultez la liste des élèves enregistrés, leurs évaluations passées et leurs moyennes.
        </p>
      </div>

      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div className="p-6 border-b border-slate-100 flex items-center justify-between">
          <h2 className="text-base font-extrabold text-slate-900">
            Élèves Inscrits ({students.length})
          </h2>
          <span className="text-xs font-bold text-slate-400">Rôle : Apprenant</span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
              <tr>
                <th className="py-3.5 px-6">Apprenant</th>
                <th className="py-3.5 px-6">Email</th>
                <th className="py-3.5 px-6">Quiz Passés</th>
                <th className="py-3.5 px-6">Moyenne Générale</th>
                <th className="py-3.5 px-6">Crédits IA</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 font-medium">
              {students.map((st) => {
                const myTrainings = entrainements.filter(e => e.user_id === st.id);
                const avg = myTrainings.length > 0
                  ? (myTrainings.reduce((acc, c) => acc + c.pourcentage, 0) / myTrainings.length).toFixed(1)
                  : 'N/A';

                return (
                  <tr key={st.id} className="hover:bg-slate-50/80 transition-colors">
                    <td className="py-4 px-6 flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-orange-100 text-orange-700 font-bold text-xs flex items-center justify-center">
                        {st.avatar || 'ET'}
                      </div>
                      <div className="font-bold text-slate-900">
                        {st.prenom} {st.nom}
                      </div>
                    </td>

                    <td className="py-4 px-6 font-mono text-xs text-slate-600">
                      {st.email}
                    </td>

                    <td className="py-4 px-6 font-semibold text-slate-700">
                      {myTrainings.length}
                    </td>

                    <td className="py-4 px-6">
                      <span className="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">
                        {avg !== 'N/A' ? `${avg}%` : 'Aucun quiz'}
                      </span>
                    </td>

                    <td className="py-4 px-6 text-xs text-slate-500 font-mono">
                      {st.credits} crédits
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

    </div>
  );
};
