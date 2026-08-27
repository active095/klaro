import React, { useState } from 'react';
import { LogIn, Loader2, AlertCircle } from 'lucide-react';
import { supabase } from '../lib/supabase';
import { useAuth } from '../contexts/AuthContext';
import { UserProfile } from '../types';

interface LoginViewProps {
  onLoginSuccess?: (profile: UserProfile) => void;
  onNavigate: (view: string) => void;
}

export const LoginView: React.FC<LoginViewProps> = ({ onLoginSuccess, onNavigate }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const { setDirectProfile } = useAuth();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    const cleanEmail = email.trim().toLowerCase();

    if (!cleanEmail || !password) {
      setError('Veuillez renseigner votre adresse email et votre mot de passe.');
      return;
    }

    setLoading(true);

    try {
      // 1. Authentification Supabase Auth
      const { data, error: authError } = await supabase.auth.signInWithPassword({
        email: cleanEmail,
        password: password,
      });

      if (authError) {
        console.error('[Klaro Login] Erreur Supabase Auth:', authError.message);
        if (
          authError.message.includes('Invalid login credentials') ||
          authError.message.includes('invalid_credentials') ||
          authError.message.includes('Email not confirmed')
        ) {
          setError('Adresse email ou mot de passe incorrect.');
        } else {
          setError('Impossible de se connecter pour le moment. Veuillez vérifier votre connexion.');
        }
        setLoading(false);
        return;
      }

      if (!data.user) {
        setError('Aucune session utilisateur trouvée.');
        setLoading(false);
        return;
      }

      console.log('[Klaro Login] Utilisateur authentifié:', data.user.id, data.user.email);

      // 2. Récupération du profil complet dans la table 'profiles'
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .select('*')
        .eq('id', data.user.id)
        .maybeSingle();

      console.log('[Klaro Login] Profil récupéré depuis Supabase profiles pour', data.user.id, ':', profileData, 'Erreur:', profileError);

      if (profileError) {
        console.error('[Klaro Login] Erreur requête profiles:', profileError);
        // Si une erreur RLS ou réseau se produit et qu'il n'y a pas de rôle dans les métadonnées
        if (data.user.user_metadata?.role !== 'professeur') {
          setError(`Erreur d'accès au profil (${profileError.message}). Veuillez réessayer.`);
          setLoading(false);
          return;
        }
      }

      if (!profileData && data.user.user_metadata?.role !== 'professeur') {
        console.warn('[Klaro Login] Aucun profil trouvé dans la table profiles.');
        setError('Profil utilisateur introuvable dans la base de données. Veuillez réessayer.');
        setLoading(false);
        return;
      }

      // 3. Détermination stricte du rôle
      const rawRole = String(profileData?.role || data.user.user_metadata?.role || 'apprenant').trim().toLowerCase();
      const resolvedRole = rawRole === 'professeur' ? 'professeur' : 'apprenant';

      console.log('[Klaro Login] Rôle résolu pour la session:', resolvedRole);

      const userProfile: UserProfile = {
        id: data.user.id,
        nom: profileData?.nom || data.user.user_metadata?.nom || '',
        prenom: profileData?.prenom || data.user.user_metadata?.prenom || '',
        email: cleanEmail,
        role: resolvedRole,
        credits: Number(profileData?.credits ?? (resolvedRole === 'professeur' ? 100 : 20)),
        profil_complete: Boolean(profileData?.profil_complete),
        avatar: `${(profileData?.prenom || data.user.user_metadata?.prenom || 'U')[0] || ''}${(profileData?.nom || data.user.user_metadata?.nom || '')[0] || ''}`.toUpperCase() || 'U',
      };

      // Mettre à jour l'état AuthContext directement pour éviter tout délai de propagation
      setDirectProfile(userProfile);

      if (onLoginSuccess) {
        onLoginSuccess(userProfile);
      }

      // 4. Redirection selon le rôle réel
      if (resolvedRole === 'professeur') {
        console.log('[Klaro Login] Redirection vers /dashboard-professeur');
        onNavigate('dashboard-professeur');
      } else {
        console.log('[Klaro Login] Redirection vers /dashboard (apprenant)');
        onNavigate('dashboard');
      }
    } catch (err: any) {
      console.error('[Klaro Login] Erreur inattendue de connexion:', err);
      setError('Une erreur est survenue lors de la tentative de connexion. Veuillez réessayer.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-md mx-auto px-4 py-8 sm:py-12">
      <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xl space-y-6">
        
        <div className="text-center space-y-2">
          <div className="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto shadow-xs">
            <LogIn className="w-6 h-6" />
          </div>
          <h1 className="text-2xl font-black text-slate-900">Connexion à Klaro</h1>
          <p className="text-xs text-slate-500">
            Accédez à vos quiz, statistiques et outils d'apprentissage.
          </p>
        </div>

        {error && (
          <div className="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-start gap-2">
            <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Adresse Email *
            </label>
            <input
              type="email"
              required
              autoComplete="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="ex: votre.email@etablissement.af"
              className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Mot de passe *
            </label>
            <input
              type="password"
              required
              autoComplete="current-password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="••••••••"
              className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full min-h-[44px] py-3.5 px-6 rounded-2xl bg-orange-500 hover:bg-orange-600 disabled:bg-orange-300 text-white font-black text-sm shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.01] flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed"
          >
            {loading ? (
              <>
                <Loader2 className="w-4 h-4 animate-spin" />
                <span>Connexion en cours...</span>
              </>
            ) : (
              <>
                <LogIn className="w-4 h-4" />
                <span>Se connecter</span>
              </>
            )}
          </button>
        </form>

        <div className="text-center pt-4 border-t border-slate-100">
          <p className="text-xs text-slate-500">
            Pas encore de compte apprenant ?{' '}
            <button
              type="button"
              onClick={() => onNavigate('inscription')}
              className="font-bold text-orange-600 hover:underline cursor-pointer"
            >
              Créer un compte
            </button>
          </p>
        </div>

      </div>
    </div>
  );
};
