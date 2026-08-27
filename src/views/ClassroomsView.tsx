import React, { useState } from 'react';
import { Classroom, User } from '../types';
import { 
  GraduationCap, 
  PlusCircle, 
  UserPlus, 
  Copy, 
  Check, 
  Users, 
  BookOpen, 
  Trash2,
  CheckCircle2
} from 'lucide-react';

interface ClassroomsViewProps {
  user: User;
  classrooms: Classroom[];
  onAddClassroom: (newClass: Classroom) => void;
  onJoinClassroom: (code: string) => boolean;
  onDeleteClassroom: (id: number) => void;
}

export const ClassroomsView: React.FC<ClassroomsViewProps> = ({
  user,
  classrooms,
  onAddClassroom,
  onJoinClassroom,
  onDeleteClassroom
}) => {
  const isTeacher = user.role === 'professeur';

  // État Professeur : Création
  const [nom, setNom] = useState('');
  const [matiere, setMatiere] = useState('');
  const [description, setDescription] = useState('');

  // État Étudiant : Rejoindre
  const [codeToJoin, setCodeToJoin] = useState('');
  const [joinMsg, setJoinMsg] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
  const [copiedCode, setCopiedCode] = useState<string | null>(null);

  const handleCreateClass = (e: React.FormEvent) => {
    e.preventDefault();
    if (!nom.trim()) return;

    const code = 'CLS-' + Math.random().toString(36).substring(2, 7).toUpperCase();
    const newCls: Classroom = {
      id: Date.now(),
      professeur_id: user.id,
      nom: nom.trim(),
      matiere: matiere.trim() || 'Général',
      description: description.trim(),
      code_classe: code,
      actif: true,
      created_at: new Date().toISOString(),
      membres_count: 0,
      prof_nom: `Prof. ${user.prenom} ${user.nom}`
    };

    onAddClassroom(newCls);
    setNom('');
    setMatiere('');
    setDescription('');
  };

  const handleJoin = (e: React.FormEvent) => {
    e.preventDefault();
    const success = onJoinClassroom(codeToJoin.trim().toUpperCase());
    if (success) {
      setJoinMsg({ type: 'success', text: `Vous avez rejoint la classe avec succès !` });
      setCodeToJoin('');
    } else {
      setJoinMsg({ type: 'error', text: `Code de classroom invalide ou introuvable.` });
    }
  };

  const handleCopyCode = (code: string) => {
    navigator.clipboard.writeText(code);
    setCopiedCode(code);
    setTimeout(() => setCopiedCode(null), 2000);
  };

  return (
    <div className="space-y-8">
      
      <div>
        <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
          {isTeacher ? 'Gestion des Classrooms' : 'Mes Classrooms & Cours'}
        </h1>
        <p className="text-xs sm:text-sm text-slate-500">
          {isTeacher 
            ? 'Créez vos espaces de cours et distribuez les codes d\'accès à vos apprenants.' 
            : 'Accédez aux quiz et documents partagés par vos professeurs.'}
        </p>
      </div>

      {joinMsg && (
        <div className={`p-4 rounded-2xl border text-xs font-bold flex items-center gap-2 ${
          joinMsg.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'
        }`}>
          {joinMsg.type === 'success' ? <CheckCircle2 className="w-4 h-4 text-emerald-600" /> : <CheckCircle2 className="w-4 h-4 text-rose-600" />}
          <span>{joinMsg.text}</span>
        </div>
      )}

      {/* FORMULAIRE PROFESSEUR OU ÉTUDIANT */}
      {isTeacher ? (
        <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
          <div className="flex items-center gap-2">
            <PlusCircle className="w-5 h-5 text-orange-500" />
            <h2 className="text-base font-extrabold text-slate-900">Créer un nouveau Classroom</h2>
          </div>

          <form onSubmit={handleCreateClass} className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Nom de la classe *
              </label>
              <input
                type="text"
                required
                value={nom}
                onChange={(e) => setNom(e.target.value)}
                placeholder="Ex: Terminale D - Biologie"
                className="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Matière
              </label>
              <input
                type="text"
                value={matiere}
                onChange={(e) => setMatiere(e.target.value)}
                placeholder="Ex: Sciences Naturelles"
                className="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Description courte
              </label>
              <input
                type="text"
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                placeholder="Ex: Préparation intensive au BAC"
                className="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
              />
            </div>

            <div className="md:col-span-3 flex justify-end">
              <button
                type="submit"
                className="px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-md shadow-orange-500/20 transition-all"
              >
                Créer la classe
              </button>
            </div>
          </form>
        </div>
      ) : (
        <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <h2 className="text-base font-extrabold text-slate-900">Rejoindre un Classroom</h2>
            <p className="text-xs text-slate-500">
              Entrez le code communiqué par votre professeur (ex: <code className="font-mono text-purple-600 font-bold">CLS-SVT2025</code>).
            </p>
          </div>

          <form onSubmit={handleJoin} className="flex gap-2 w-full md:w-auto">
            <input
              type="text"
              required
              value={codeToJoin}
              onChange={(e) => setCodeToJoin(e.target.value)}
              placeholder="Code Classroom"
              className="px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-bold text-sm uppercase focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white"
            />
            <button
              type="submit"
              className="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20 transition-all flex items-center gap-1.5"
            >
              <UserPlus className="w-4 h-4" />
              <span>Rejoindre</span>
            </button>
          </form>
        </div>
      )}

      {/* GRILLE DES CLASSES */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {classrooms.map((cls) => (
          <div key={cls.id} className="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 flex flex-col justify-between hover:border-purple-200 transition-colors">
            
            <div className="space-y-2">
              <div className="flex items-start justify-between">
                <div>
                  <span className="text-[11px] font-bold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                    {cls.matiere}
                  </span>
                  <h3 className="text-lg font-black text-slate-900 mt-2">{cls.nom}</h3>
                  <p className="text-xs text-slate-500">
                    Enseignant : <strong>{cls.prof_nom || 'Professeur Klaro'}</strong>
                  </p>
                </div>

                <button
                  onClick={() => handleCopyCode(cls.code_classe)}
                  className="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-mono font-black text-xs border border-purple-200/80 transition-all flex items-center gap-1"
                  title="Copier le code classe"
                >
                  <span>{cls.code_classe}</span>
                  {copiedCode === cls.code_classe ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5" />}
                </button>
              </div>

              {cls.description && (
                <p className="text-xs text-slate-600 leading-relaxed pt-1">
                  {cls.description}
                </p>
              )}
            </div>

            <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-semibold">
              <span className="flex items-center gap-1">
                <Users className="w-3.5 h-3.5" />
                <span>{cls.membres_count || 24} apprenants</span>
              </span>

              {isTeacher && (
                <button
                  onClick={() => {
                    if (confirm(`Supprimer la classe « ${cls.nom} » ?`)) {
                      onDeleteClassroom(cls.id);
                    }
                  }}
                  className="text-rose-500 hover:text-rose-700 font-bold"
                >
                  Supprimer
                </button>
              )}
            </div>

          </div>
        ))}
      </div>

    </div>
  );
};
