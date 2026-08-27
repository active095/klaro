import React from 'react';
import { 
  Grid, 
  ShieldCheck, 
  Sparkles, 
  GraduationCap, 
  FileText, 
  LifeBuoy, 
  BookOpen, 
  Play, 
  History,
  LayoutDashboard,
  ExternalLink
} from 'lucide-react';

interface AllMenusViewProps {
  onNavigate: (view: string) => void;
}

export const AllMenusView: React.FC<AllMenusViewProps> = ({ onNavigate }) => {
  const sections = [
    {
      title: 'Espace Public & Authentification',
      color: 'border-slate-300 bg-slate-50/50',
      links: [
        { id: 'landing', label: 'Accueil / Landing Page', desc: 'Présentation de la plateforme et accès rapide par code', icon: Play },
        { id: 'connexion', label: 'Connexion Unique', desc: 'Accès sécurisé pour Apprenants et Professeurs', icon: ShieldCheck },
        { id: 'inscription', label: 'Inscription Apprenant', desc: 'Création de compte avec 20 crédits IA offerts', icon: Sparkles },
      ]
    },
    {
      title: 'Espace Apprenant (Étudiant)',
      color: 'border-orange-200 bg-orange-50/30',
      links: [
        { id: 'dashboard', label: 'Tableau de Bord Étudiant', desc: 'Série streak, statistiques de réussite et raccourcis', icon: LayoutDashboard },
        { id: 'commencer-quiz', label: 'Passer un Quiz par Code', desc: 'Minuteur synchronisé, auto-save et validation', icon: Play },
        { id: 'mes-compositions', label: 'Mes Compositions & Notes', desc: 'Historique de vos évaluations passées', icon: FileText },
        { id: 'classrooms', label: 'Mes Classrooms', desc: 'Rejoindre une classe et suivre les cours', icon: GraduationCap },
        { id: 'grimoire', label: 'Le Grimoire Klaro', desc: 'Bibliothèque des fiches de synthèse enregistrées', icon: BookOpen },
        { id: 'outils-ia', label: 'Klaro AI Générateur', desc: 'Synthèses de cours et questions de révision IA', icon: Sparkles },
        { id: 'historique-entrainement', label: 'Historique d\'Entraînement', desc: 'Analyse détaillée de progression', icon: History },
      ]
    },
    {
      title: 'Espace Enseignant (Professeur)',
      color: 'border-blue-200 bg-blue-50/30',
      links: [
        { id: 'dashboard-professeur', label: 'Tableau de Bord Enseignant', desc: 'Indicateurs clés, classes actives et suivi', icon: LayoutDashboard },
        { id: 'creer-quiz', label: 'Créer un Quiz (QCM & V/F)', desc: 'Éditeur de quiz avec code d\'accès automatique', icon: Sparkles },
        { id: 'compositions-professeur', label: 'Compositions Créées', desc: 'Gestion des codes d\'accès et des publications', icon: FileText },
        { id: 'classrooms-professeur', label: 'Gestion des Classrooms', desc: 'Création et administration des classes', icon: GraduationCap },
        { id: 'etudiants-professeur', label: 'Suivi des Apprenants', desc: 'Liste des élèves et moyennes individuelles', icon: Grid },
        { id: 'support', label: 'Support & Assistance', desc: 'Assistance pédagogique et technique dédiée', icon: LifeBuoy },
      ]
    }
  ];

  return (
    <div className="space-y-8 py-4">
      
      <div>
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 text-slate-800 text-xs font-bold uppercase tracking-wider mb-1">
          <Grid className="w-3.5 h-3.5" />
          <span>Plan de Navigation</span>
        </div>
        <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
          Plan du site Klaro
        </h1>
        <p className="text-xs sm:text-sm text-slate-500">
          Vue d'ensemble complète et accès direct à chaque module de la plateforme.
        </p>
      </div>

      <div className="space-y-6">
        {sections.map((section, idx) => (
          <div key={idx} className={`rounded-3xl p-6 sm:p-8 border ${section.color} space-y-4`}>
            <h2 className="text-base font-extrabold text-slate-900 uppercase tracking-wider">
              {section.title}
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {section.links.map((link) => {
                const Icon = link.icon;
                return (
                  <button
                    key={link.id}
                    onClick={() => onNavigate(link.id)}
                    className="p-5 rounded-2xl bg-white hover:bg-slate-50 border border-slate-200/80 text-left shadow-xs transition-all hover:scale-[1.01] hover:border-slate-300 flex flex-col justify-between h-32 group cursor-pointer"
                  >
                    <div className="flex items-center justify-between">
                      <div className="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-orange-500 group-hover:text-white text-slate-700 flex items-center justify-center transition-colors">
                        <Icon className="w-4 h-4" />
                      </div>
                      <ExternalLink className="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-600 transition-colors" />
                    </div>
                    <div>
                      <h3 className="text-xs font-bold text-slate-900 group-hover:text-orange-600 transition-colors">
                        {link.label}
                      </h3>
                      <p className="text-[11px] text-slate-500 leading-tight mt-0.5">
                        {link.desc}
                      </p>
                    </div>
                  </button>
                );
              })}
            </div>
          </div>
        ))}
      </div>

    </div>
  );
};
