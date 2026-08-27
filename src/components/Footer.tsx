import React from 'react';
import { Lightbulb, Heart } from 'lucide-react';

interface FooterProps {
  onNavigate: (view: string) => void;
}

export const Footer: React.FC<FooterProps> = ({ onNavigate }) => {
  return (
    <footer className="bg-white border-t border-slate-200/80 mt-16 py-12">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          
          {/* IDENTITÉ DU PRODUIT */}
          <div className="space-y-4">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-xs">
                <Lightbulb className="w-5 h-5" />
              </div>
              <span className="text-xl font-black text-orange-500 font-sans">Klaro</span>
            </div>
            <p className="text-xs text-slate-500 leading-relaxed max-w-sm">
              Plateforme de quiz adaptatifs propulsée par l'IA, conçue pour stimuler l'excellence académique et la réussite aux examens.
            </p>
            <div className="flex items-center gap-1.5 text-xs text-slate-400 font-semibold">
              <span>Fait avec</span>
              <Heart className="w-3.5 h-3.5 text-rose-500 fill-rose-500 inline" />
              <span>pour l'Afrique francophone</span>
            </div>
          </div>

          {/* LIENS APPRENANTS */}
          <div className="space-y-3">
            <h4 className="text-xs font-bold text-slate-900 uppercase tracking-wider">Espace Étudiant</h4>
            <ul className="space-y-2 text-xs font-semibold text-slate-500">
              <li>
                <button onClick={() => onNavigate('commencer-quiz')} className="hover:text-orange-600 transition-colors">
                  Passer un quiz
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('mes-compositions')} className="hover:text-orange-600 transition-colors">
                  Mes compositions
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('classrooms')} className="hover:text-orange-600 transition-colors">
                  Rejoindre un classroom
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('outils-ia')} className="hover:text-orange-600 transition-colors">
                  Klaro AI
                </button>
              </li>
            </ul>
          </div>

          {/* LIENS PROFESSEURS */}
          <div className="space-y-3">
            <h4 className="text-xs font-bold text-slate-900 uppercase tracking-wider">Espace Enseignant</h4>
            <ul className="space-y-2 text-xs font-semibold text-slate-500">
              <li>
                <button onClick={() => onNavigate('creer-quiz')} className="hover:text-blue-600 transition-colors">
                  Créer un quiz
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('compositions-professeur')} className="hover:text-blue-600 transition-colors">
                  Compositions
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('classrooms-professeur')} className="hover:text-blue-600 transition-colors">
                  Gestion des classes
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('support')} className="hover:text-blue-600 transition-colors">
                  Support
                </button>
              </li>
            </ul>
          </div>

        </div>

        {/* LIGNE DE COPYRIGHT ÉLÉGANTE */}
        <div className="mt-12 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
          <p>© {new Date().getFullYear()} Klaro — Réalisé par <span className="font-semibold text-slate-700">Henri Joël HOUNKPE</span></p>
          <div className="flex items-center gap-4">
            <button onClick={() => onNavigate('tous-les-menus')} className="hover:underline hover:text-slate-600">Plan du site</button>
          </div>
        </div>
      </div>
    </footer>
  );
};
