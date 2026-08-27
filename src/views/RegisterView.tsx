import React, { useState } from 'react';
import { UserPlus, Sparkles, Loader2, AlertCircle, CheckCircle2 } from 'lucide-react';
import { supabase } from '../lib/supabase';
import { useAuth } from '../contexts/AuthContext';
import { UserProfile } from '../types';

interface RegisterViewProps {
  onRegisterSuccess?: (newUser: UserProfile) => void;
  onNavigate: (view: string) => void;
}

export const RegisterView: React.FC<RegisterViewProps> = ({ onRegisterSuccess, onNavigate }) => {
  const [nom, setNom] = useState('');
  const [prenom, setPrenom] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [successMsg, setSuccessMsg] = useState('');
  const { refreshProfile } = useAuth();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccessMsg('');

    const cleanNom = nom.trim();
    const cleanPrenom = prenom.trim();
    const cleanEmail = email.trim().toLowerCase();

    if (!cleanNom || !cleanPrenom || !cleanEmail || !password) {
      setError('Veuillez renseigner tous les champs obligatoires.');
      return;
    }

    if (password.length < 6) {
      setError('Le mot de passe doit comporter au moins 6 caractères.');
      return;
    }

    if (password !== confirmPassword) {
      setError('Les deux mots de passe ne correspondent pas.');
      return;
    }

    setLoading(true);

    try {
      // 1. Inscription native Supabase Auth (jamais de rôle dans le formulaire)
      const { data, error: signUpError } = await supabase.auth.signUp({
        email: cleanEmail,
        password: password,
        options: {
          data: {
            nom: cleanNom,
            prenom: cleanPrenom,
          },
        },
      });

      if (signUpError) {
        console.error('Erreur Supabase Inscription:', signUpError.message);
        if (
          signUpError.message.includes('User already registered') ||
          signUpError.message.includes('already exists') ||
          signUpError.message.includes('unique constraint')
        ) {
          setError('Cette adresse email est déjà associée à un compte.');
        } else if (signUpError.message.includes('Password')) {
          setError('Le mot de passe doit contenir au moins 6 caractères.');
        } else {
          setError('Impossible de finaliser l\'inscription. Veuillez vérifier vos informations.');
        }
        setLoading(false);
        return;
      }

      if (!data.user) {
        setError('Une erreur est survenue lors de la création du compte.');
        setLoading(false);
        return;
      }

      // Si l'utilisateur est connecté directement (sans confirmation par email requise)
      if (data.session) {
        await refreshProfile();
        const userProfile: UserProfile = {
          id: data.user.id,
          nom: cleanNom,
          prenom: cleanPrenom,
          email: cleanEmail,
          role: 'apprenant',
          credits: 20,
          profil_complete: false,
          avatar: `${cleanPrenom.charAt(0)}${cleanNom.charAt(0)}`.toUpperCase() || 'AD',
        };

        if (onRegisterSuccess) {
          onRegisterSuccess(userProfile);
        }
        onNavigate('dashboard');
      } else {
        // Confirmation d'email éventuellement requise
        setSuccessMsg('Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
        setTimeout(() => {
          onNavigate('connexion');
        }, 2000);
      }
    } catch (err: any) {
      console.error('Exception Inscription:', err);
      setError('Une erreur inattendue est survenue. Veuillez réessayer.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-md mx-auto px-4 py-8 sm:py-12">
      <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xl space-y-6">
        
        <div className="text-center space-y-2">
          <div className="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center mx-auto shadow-xs">
            <UserPlus className="w-6 h-6" />
          </div>
          <h1 className="text-2xl font-black text-slate-900">Inscription Apprenant</h1>
          <p className="text-xs text-slate-500">
            Rejoignez gratuitement la communauté d'excellence Klaro.
          </p>
        </div>

        {/* BANDEAU RÔLE VERROUILLÉ */}
        <div className="p-3 bg-orange-50/70 border border-orange-200 rounded-2xl flex items-center gap-2.5 text-xs text-orange-900 font-semibold">
          <Sparkles className="w-4 h-4 text-orange-600 shrink-0" />
          <span>Compte Étudiant · Inscription avec 20 crédits IA offerts</span>
        </div>

        {error && (
          <div className="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-start gap-2">
            <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
            <span>{error}</span>
          </div>
        )}

        {successMsg && (
          <div className="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-start gap-2">
            <CheckCircle2 className="w-4 h-4 shrink-0 mt-0.5" />
            <span>{successMsg}</span>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Nom *
              </label>
              <input
                type="text"
                required
                autoComplete="family-name"
                value={nom}
                onChange={(e) => setNom(e.target.value)}
                placeholder="Dossou"
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all"
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Prénom *
              </label>
              <input
                type="text"
                required
                autoComplete="given-name"
                value={prenom}
                onChange={(e) => setPrenom(e.target.value)}
                placeholder="Amina"
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all"
              />
            </div>
          </div>

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
              placeholder="votre.email@exemple.com"
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
              autoComplete="new-password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Minimum 6 caractères"
              className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Confirmer le mot de passe *
            </label>
            <input
              type="password"
              required
              autoComplete="new-password"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              placeholder="Répétez le mot de passe"
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
                <span>Création du compte...</span>
              </>
            ) : (
              <>
                <UserPlus className="w-4 h-4" />
                <span>Créer mon compte</span>
              </>
            )}
          </button>
        </form>

        <div className="text-center pt-2 border-t border-slate-100">
          <p className="text-xs text-slate-500">
            Déjà inscrit sur Klaro ?{' '}
            <button
              type="button"
              onClick={() => onNavigate('connexion')}
              className="font-bold text-orange-600 hover:underline cursor-pointer"
            >
              Se connecter
            </button>
          </p>
        </div>

      </div>
    </div>
  );
};
