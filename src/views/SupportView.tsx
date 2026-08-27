import React, { useState } from 'react';
import { LifeBuoy, Send, CheckCircle2, MessageSquare, ShieldCheck, Mail } from 'lucide-react';
import { User } from '../types';

interface SupportViewProps {
  user: User;
}

export const SupportView: React.FC<SupportViewProps> = ({ user }) => {
  const [objet, setObjet] = useState('');
  const [message, setMessage] = useState('');
  const [sent, setSent] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!objet.trim() || !message.trim()) return;
    setSent(true);
  };

  return (
    <div className="max-w-3xl mx-auto space-y-8 py-6">
      
      <div>
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider mb-1">
          <LifeBuoy className="w-3.5 h-3.5" />
          <span>Assistance Pédagogique & Technique</span>
        </div>
        <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
          Support Enseignants Klaro
        </h1>
        <p className="text-xs sm:text-sm text-slate-500">
          Une question sur la création de quiz, la gestion de classe ou le déploiement ? Notre équipe vous répond sous 24h.
        </p>
      </div>

      <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        
        {sent ? (
          <div className="p-8 text-center space-y-4 animate-fade-in">
            <div className="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
              <CheckCircle2 className="w-8 h-8" />
            </div>
            <h3 className="text-lg font-black text-slate-900">Message envoyé avec succès !</h3>
            <p className="text-xs text-slate-600 max-w-md mx-auto">
              Merci Prof. {user.prenom} {user.nom}. Votre demande a été enregistrée avec la référence <strong>#KL-{Date.now().toString().slice(-4)}</strong>. Une réponse vous sera adressée à <strong>{user.email}</strong>.
            </p>
            <button
              onClick={() => {
                setSent(false);
                setObjet('');
                setMessage('');
              }}
              className="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-colors"
            >
              Envoyer un autre message
            </button>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Objet de votre demande *
              </label>
              <input
                type="text"
                required
                value={objet}
                onChange={(e) => setObjet(e.target.value)}
                placeholder="Ex: Question sur l'importation de quiz en masse"
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Message détaillé *
              </label>
              <textarea
                rows={6}
                required
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder="Décrivez votre besoin ou le problème rencontré avec précision..."
                className="w-full p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white leading-relaxed"
              />
            </div>

            <div className="flex justify-end">
              <button
                type="submit"
                className="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition-all flex items-center gap-2"
              >
                <Send className="w-4 h-4" />
                <span>Transmettre au Support</span>
              </button>
            </div>
          </form>
        )}

      </div>

    </div>
  );
};
