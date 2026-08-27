import React from 'react';
import { useAuth } from '../contexts/AuthContext';
import { UserRole } from '../types';
import { Loader2, ShieldAlert } from 'lucide-react';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRole?: UserRole;
  currentView: string;
  onNavigate: (view: string) => void;
}

export const ProtectedRoute: React.FC<ProtectedRouteProps> = ({
  children,
  requiredRole,
  onNavigate,
}) => {
  const { user, profile, loading } = useAuth();

  // 1. État de chargement de la session Supabase
  if (loading) {
    return (
      <div className="min-h-[50vh] flex flex-col items-center justify-center gap-3 p-8">
        <Loader2 className="w-8 h-8 text-orange-500 animate-spin" />
        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
          Vérification de la session sécurisée...
        </p>
      </div>
    );
  }

  // 2. Non connecté -> Redirection vers la vue de connexion
  if (!user || !profile) {
    return (
      <div className="max-w-md mx-auto my-12 p-8 bg-white rounded-3xl border border-slate-200 text-center space-y-4 shadow-sm">
        <div className="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
          <ShieldAlert className="w-6 h-6" />
        </div>
        <h2 className="text-lg font-bold text-slate-900">Session requise</h2>
        <p className="text-xs text-slate-500">
          Vous devez être connecté à votre compte Klaro pour accéder à cet espace.
        </p>
        <button
          onClick={() => onNavigate('connexion')}
          className="w-full py-3 px-4 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
        >
          Se connecter
        </button>
      </div>
    );
  }

  // 3. Vérification du rôle requis
  if (requiredRole && profile.role !== requiredRole) {
    return (
      <div className="max-w-md mx-auto my-12 p-8 bg-white rounded-3xl border border-rose-200 text-center space-y-4 shadow-sm">
        <div className="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
          <ShieldAlert className="w-6 h-6" />
        </div>
        <h2 className="text-lg font-bold text-slate-900">Accès restreint</h2>
        <p className="text-xs text-slate-500">
          Cet espace est réservé aux {requiredRole === 'professeur' ? 'enseignants' : 'étudiants'}. Vous allez être redirigé vers votre espace dédié.
        </p>
        <button
          onClick={() => onNavigate(profile.role === 'professeur' ? 'dashboard-professeur' : 'dashboard')}
          className="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
        >
          Accéder à mon tableau de bord ({profile.role === 'professeur' ? 'Enseignant' : 'Étudiant'})
        </button>
      </div>
    );
  }

  return <>{children}</>;
};
