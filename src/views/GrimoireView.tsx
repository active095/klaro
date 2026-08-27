import React, { useState } from 'react';
import { GrimoireItem, User } from '../types';
import { 
  BookOpen, 
  Sparkles, 
  Trash2, 
  Copy, 
  Check, 
  Calendar, 
  FileText,
  ArrowRight
} from 'lucide-react';

interface GrimoireViewProps {
  user: User;
  grimoire: GrimoireItem[];
  onDeleteGrimoireItem: (id: number) => void;
  onNavigate: (view: string) => void;
}

export const GrimoireView: React.FC<GrimoireViewProps> = ({
  user,
  grimoire,
  onDeleteGrimoireItem,
  onNavigate
}) => {
  const myItems = grimoire.filter(g => g.user_id === user.id);
  const [selectedItem, setSelectedItem] = useState<GrimoireItem | null>(myItems[0] || null);
  const [copied, setCopied] = useState(false);

  const handleCopy = (text: string) => {
    navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="space-y-8 py-4">
      
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-bold uppercase tracking-wider mb-1">
            <BookOpen className="w-3.5 h-3.5" />
            <span>Fiches & Mémorisation Espacée</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
            Le Grimoire Klaro
          </h1>
          <p className="text-xs sm:text-sm text-slate-500">
            Retrouvez toutes vos fiches de cours synthétisées et vos fiches de révision enregistrées.
          </p>
        </div>

        <button
          onClick={() => onNavigate('outils-ia')}
          className="px-5 py-2.5 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-md shadow-orange-500/20 transition-all flex items-center gap-1.5"
        >
          <Sparkles className="w-4 h-4" />
          <span>Nouvelle Fiche IA</span>
        </button>
      </div>

      {myItems.length === 0 ? (
        <div className="bg-white rounded-3xl p-12 border border-slate-200/80 shadow-sm text-center space-y-4">
          <BookOpen className="w-12 h-12 text-slate-300 mx-auto" />
          <h3 className="text-base font-bold text-slate-900">Votre Grimoire est vide</h3>
          <p className="text-xs text-slate-500 max-w-sm mx-auto">
            Utilisez l'outil Klaro AI pour générer vos premières fiches de révision à partir de vos leçons.
          </p>
          <button
            onClick={() => onNavigate('outils-ia')}
            className="px-6 py-3 bg-orange-500 text-white text-xs font-bold rounded-xl shadow-xs hover:bg-orange-600 transition-colors"
          >
            Créer ma première fiche
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          {/* LISTE DES FICHES */}
          <div className="space-y-3 lg:col-span-1">
            {myItems.map((item) => {
              const isSelected = selectedItem?.id === item.id;
              return (
                <button
                  key={item.id}
                  onClick={() => setSelectedItem(item)}
                  className={`w-full text-left p-4 rounded-2xl border transition-all ${
                    isSelected
                      ? 'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-500/20 font-bold'
                      : 'bg-white hover:bg-slate-50 border-slate-200/80 text-slate-800'
                  }`}
                >
                  <div className="flex items-center justify-between text-[11px] mb-1">
                    <span className={`px-2 py-0.5 rounded-md font-mono ${
                      isSelected ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'
                    }`}>
                      {item.type_contenu === 'resume' ? 'Synthèse' : 'Quiz IA'}
                    </span>
                    <span className={isSelected ? 'text-amber-100' : 'text-slate-400'}>
                      {new Date(item.created_at).toLocaleDateString('fr-FR')}
                    </span>
                  </div>
                  <h4 className="text-sm font-bold truncate">{item.titre}</h4>
                </button>
              );
            })}
          </div>

          {/* DÉTAIL DE LA FICHE SÉLECTIONNÉE */}
          <div className="lg:col-span-2">
            {selectedItem ? (
              <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6 animate-fade-in">
                
                <div className="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                  <div>
                    <h2 className="text-xl font-black text-slate-900">{selectedItem.titre}</h2>
                    <p className="text-xs text-slate-400 mt-0.5">
                      Enregistré le {new Date(selectedItem.created_at).toLocaleString('fr-FR')}
                    </p>
                  </div>

                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => handleCopy(selectedItem.contenu_genere)}
                      className="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 transition-all"
                    >
                      {copied ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5" />}
                      <span>{copied ? 'Copié' : 'Copier'}</span>
                    </button>
                    <button
                      onClick={() => {
                        if (confirm('Supprimer cette fiche du Grimoire ?')) {
                          onDeleteGrimoireItem(selectedItem.id);
                          setSelectedItem(myItems.find(i => i.id !== selectedItem.id) || null);
                        }
                      }}
                      className="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs transition-colors"
                      title="Supprimer"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>

                <div className="bg-slate-50 p-6 rounded-2xl border border-slate-200 font-sans text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
                  {selectedItem.contenu_genere}
                </div>

              </div>
            ) : (
              <div className="p-8 text-center text-slate-400 bg-white rounded-3xl border border-slate-200">
                Sélectionnez une fiche pour afficher son contenu.
              </div>
            )}
          </div>

        </div>
      )}

    </div>
  );
};
