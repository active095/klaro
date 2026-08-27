import React from 'react';
import { UserRole } from '../types';
import {
  LayoutDashboard,
  FileCheck,
  GraduationCap,
  Sparkles,
  History,
  Grid,
  PlusCircle,
  FileText,
  Users,
  LifeBuoy,
  BookOpen,
  Play
} from 'lucide-react';

interface SidebarProps {
  role: UserRole;
  currentView: string;
  onNavigate: (view: string) => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ role, currentView, onNavigate }) => {
  if (role === 'professeur') {
    const teacherLinks = [
      { id: 'dashboard-professeur', label: 'Tableau de bord', icon: LayoutDashboard },
      { id: 'creer-quiz', label: 'Créer un quiz', icon: PlusCircle, highlight: true },
      { id: 'compositions-professeur', label: 'Compositions créées', icon: FileText },
      { id: 'classrooms-professeur', label: 'Mes Classrooms', icon: GraduationCap },
      { id: 'etudiants-professeur', label: 'Suivi des élèves', icon: Users },
      { id: 'outils-ia', label: 'Klaro AI & Fiches', icon: Sparkles },
      { id: 'support', label: 'Support & Assistance', icon: LifeBuoy },
      { id: 'tous-les-menus', label: 'Tous les menus', icon: Grid },
    ];

    return (
      <aside className="w-full lg:w-64 shrink-0 space-y-6">
        <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
          <div className="px-3 py-2 bg-blue-50/70 border border-blue-100 rounded-2xl">
            <span className="text-[11px] font-black text-blue-700 uppercase tracking-wider block">
              Espace Enseignant
            </span>
            <span className="text-xs text-slate-500 font-semibold">
              Bénin / Afrique de l'Ouest
            </span>
          </div>

          <nav className="space-y-1">
            {teacherLinks.map((item) => {
              const Icon = item.icon;
              const isActive = currentView === item.id;
              return (
                <button
                  key={item.id}
                  onClick={() => onNavigate(item.id)}
                  className={`w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all ${
                    isActive
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
                      : item.highlight
                      ? 'text-orange-600 bg-orange-50/60 hover:bg-orange-100/70'
                      : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <Icon className={`w-4 h-4 ${isActive ? 'text-white' : item.highlight ? 'text-orange-500' : 'text-slate-500'}`} />
                    <span>{item.label}</span>
                  </div>
                  {item.highlight && !isActive && (
                    <span className="w-2 h-2 rounded-full bg-orange-500"></span>
                  )}
                </button>
              );
            })}
          </nav>
        </div>
      </aside>
    );
  }

  // APPRENANT (ÉTUDIANT)
  const studentLinks = [
    { id: 'dashboard', label: 'Tableau de bord', icon: LayoutDashboard },
    { id: 'commencer-quiz', label: 'Passer un quiz', icon: Play, highlight: true },
    { id: 'mes-compositions', label: 'Mes Compositions', icon: FileCheck },
    { id: 'classrooms', label: 'Mes Classrooms', icon: GraduationCap },
    { id: 'grimoire', label: 'Le Grimoire IA', icon: BookOpen },
    { id: 'outils-ia', label: 'Klaro AI Générateur', icon: Sparkles },
    { id: 'historique-entrainement', label: 'Hist. Entraînement', icon: History },
    { id: 'tous-les-menus', label: 'Tous les menus', icon: Grid },
  ];

  return (
    <aside className="w-full lg:w-64 shrink-0 space-y-6">
      <div className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <div className="px-3 py-2 bg-orange-50/70 border border-orange-100 rounded-2xl">
          <span className="text-[11px] font-black text-orange-700 uppercase tracking-wider block">
            Espace Apprenant
          </span>
          <span className="text-xs text-slate-500 font-semibold">
            Mode Entraînement Quotidien
          </span>
        </div>

        <nav className="space-y-1">
          {studentLinks.map((item) => {
            const Icon = item.icon;
            const isActive = currentView === item.id;
            return (
              <button
                key={item.id}
                onClick={() => onNavigate(item.id)}
                className={`w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all ${
                  isActive
                    ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20'
                    : item.highlight
                    ? 'text-orange-600 bg-orange-50/60 hover:bg-orange-100/70'
                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                }`}
              >
                <div className="flex items-center gap-3">
                  <Icon className={`w-4 h-4 ${isActive ? 'text-white' : item.highlight ? 'text-orange-500' : 'text-slate-500'}`} />
                  <span>{item.label}</span>
                </div>
                {item.highlight && !isActive && (
                  <span className="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                )}
              </button>
            );
          })}
        </nav>
      </div>
    </aside>
  );
};
