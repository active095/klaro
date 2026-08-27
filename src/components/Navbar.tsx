import React from 'react';
import { User } from '../types';
import { 
  Lightbulb, 
  LogIn, 
  LogOut, 
  UserPlus, 
  Sparkles, 
  Play, 
  LayoutDashboard,
  Coins
} from 'lucide-react';

interface NavbarProps {
  currentUser: User | null;
  currentView: string;
  onNavigate: (view: string) => void;
  onLogout: () => void;
}

export const Navbar: React.FC<NavbarProps> = ({
  currentUser,
  currentView,
  onNavigate,
  onLogout,
}) => {
  // Génération dynamique des initiales à partir du prénom et nom réels
  const getInitials = (prenom?: string, nom?: string) => {
    const p = prenom?.trim()?.charAt(0) || '';
    const n = nom?.trim()?.charAt(0) || '';
    return (p + n).toUpperCase() || 'U';
  };

  const isTeacher = currentUser?.role === 'professeur';
  const dashboardViewId = isTeacher ? 'dashboard-professeur' : 'dashboard';
  const isDashboardActive = currentView === 'dashboard' || currentView === 'dashboard-professeur';
  const isAiActive = currentView === 'outils-ia';

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          
          {/* LOGO KLARO AVEC TAGLINE */}
          <div 
            onClick={() => onNavigate(currentUser ? dashboardViewId : 'landing')}
            className="flex items-center gap-2.5 cursor-pointer group"
          >
            <div className="w-10 h-10 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-md shadow-orange-500/20 group-hover:scale-105 transition-transform">
              <Lightbulb className="w-6 h-6 stroke-[2.5]" />
            </div>
            <div className="flex flex-col">
              <span className="text-2xl font-black tracking-tight text-orange-500 font-sans leading-none">
                Klaro
              </span>
              <span className="text-[10px] font-bold text-slate-400 tracking-wider uppercase">
                Quiz adaptatif IA
              </span>
            </div>
          </div>

          {/* LIENS DE NAVIGATION CENTRAUX */}
          <nav className="hidden md:flex items-center gap-1">
            {!currentUser ? (
              <>
                <button
                  onClick={() => onNavigate('landing')}
                  className={`px-3.5 py-2 rounded-xl text-sm font-bold transition-colors ${
                    currentView === 'landing' ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                  }`}
                >
                  Accueil
                </button>
                <button
                  onClick={() => onNavigate('commencer-quiz')}
                  className={`px-3.5 py-2 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors ${
                    currentView === 'commencer-quiz' ? 'text-orange-600 bg-orange-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                  }`}
                >
                  <Play className="w-4 h-4 fill-orange-500 text-orange-500" />
                  <span>Passer un quiz</span>
                </button>
              </>
            ) : (
              <>
                <button
                  onClick={() => onNavigate(dashboardViewId)}
                  className={`px-3.5 py-2 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors ${
                    isDashboardActive
                      ? 'text-orange-600 bg-orange-50/80 font-extrabold'
                      : 'text-slate-700 hover:bg-slate-100'
                  }`}
                >
                  <LayoutDashboard className="w-4 h-4 text-orange-500" />
                  <span>Tableau de bord</span>
                </button>

                <button
                  onClick={() => onNavigate('outils-ia')}
                  className={`px-3.5 py-2 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors ${
                    isAiActive
                      ? 'text-blue-600 bg-blue-50/80 font-extrabold'
                      : 'text-slate-700 hover:bg-slate-100'
                  }`}
                >
                  <Sparkles className="w-4 h-4 text-blue-500" />
                  <span>Klaro AI</span>
                </button>
              </>
            )}
          </nav>

          {/* ACTIONS & PROFIL À DROITE */}
          <div className="flex items-center gap-3">
            {!currentUser ? (
              <div className="flex items-center gap-2">
                <button
                  onClick={() => onNavigate('connexion')}
                  className="px-4 py-2 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-100 transition-colors flex items-center gap-1.5"
                >
                  <LogIn className="w-4 h-4" />
                  <span>Connexion</span>
                </button>
                <button
                  onClick={() => onNavigate('inscription')}
                  className="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/20 transition-all flex items-center gap-1.5"
                >
                  <UserPlus className="w-4 h-4" />
                  <span>S'inscrire</span>
                </button>
              </div>
            ) : (
              <div className="flex items-center gap-3">
                {/* SOLDE DE CRÉDITS RÉEL */}
                <div className="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-bold shadow-2xs">
                  <Coins className="w-3.5 h-3.5 text-amber-600" />
                  <span>{currentUser.credits} crédits</span>
                </div>

                {/* AVATAR DYNAMIQUE, NOM RÉEL ET RÔLE RÉEL */}
                <div className="flex items-center gap-2.5 pl-2 sm:pl-3 border-l border-slate-200">
                  <div className={`w-8 h-8 rounded-xl font-black text-xs flex items-center justify-center shadow-2xs ${
                    isTeacher 
                      ? 'bg-blue-100 text-blue-700' 
                      : 'bg-orange-100 text-orange-700'
                  }`}>
                    {getInitials(currentUser.prenom, currentUser.nom)}
                  </div>
                  <div className="hidden sm:flex flex-col text-left">
                    <span className="text-xs font-bold text-slate-900 leading-tight">
                      {currentUser.prenom} {currentUser.nom}
                    </span>
                    <span className="text-[10px] font-semibold text-slate-400 capitalize">
                      {isTeacher ? 'Professeur' : 'Apprenant'}
                    </span>
                  </div>
                </div>

                {/* BOUTON DÉCONNEXION */}
                <button
                  onClick={onLogout}
                  className="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                  title="Se déconnecter"
                  aria-label="Se déconnecter"
                >
                  <LogOut className="w-4 h-4" />
                </button>
              </div>
            )}

          </div>

        </div>
      </div>
    </header>
  );
};
