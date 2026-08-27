import React, { createContext, useContext, useEffect, useState } from 'react';
import { User as SupabaseUser, Session } from '@supabase/supabase-js';
import { supabase } from '../lib/supabase';
import { UserProfile, UserRole } from '../types';

interface AuthContextType {
  user: SupabaseUser | null;
  session: Session | null;
  profile: UserProfile | null;
  loading: boolean;
  signOut: () => Promise<void>;
  refreshProfile: () => Promise<UserProfile | null>;
  setDirectProfile: (profile: UserProfile | null) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<SupabaseUser | null>(null);
  const [session, setSession] = useState<Session | null>(null);
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState<boolean>(true);

  const fetchProfile = async (userId: string, userEmail?: string, metadata?: any): Promise<UserProfile | null> => {
    try {
      console.log('[Klaro Auth] Récupération du profil dans la table profiles pour ID:', userId);
      const { data, error } = await supabase
        .from('profiles')
        .select('*')
        .eq('id', userId)
        .maybeSingle();

      console.log('[Klaro Auth] Résultat Supabase profiles:', data, 'Erreur:', error);

      if (error) {
        console.error('[Klaro Auth] Erreur lors de la récupération du profil Supabase:', error.message);
        // Si une erreur survient, on vérifie d'abord les métadonnées de l'utilisateur
        const metaRole = (metadata?.role || '').trim().toLowerCase();
        if (metaRole === 'professeur') {
          const profProfile: UserProfile = {
            id: userId,
            nom: metadata?.nom || '',
            prenom: metadata?.prenom || '',
            email: userEmail || '',
            role: 'professeur',
            credits: 100,
            profil_complete: true,
            avatar: `${(metadata?.prenom || 'P')[0] || ''}${(metadata?.nom || '')[0] || ''}`.toUpperCase() || 'P',
          };
          setProfile(profProfile);
          return profProfile;
        }
        return null;
      }

      if (data) {
        const rawRole = String(data.role || metadata?.role || 'apprenant').trim().toLowerCase();
        const resolvedRole: UserRole = rawRole === 'professeur' ? 'professeur' : 'apprenant';
        const userProfile: UserProfile = {
          id: data.id,
          nom: data.nom || metadata?.nom || '',
          prenom: data.prenom || metadata?.prenom || '',
          email: userEmail || data.email || '',
          role: resolvedRole,
          credits: Number(data.credits ?? (resolvedRole === 'professeur' ? 100 : 20)),
          profil_complete: Boolean(data.profil_complete),
          created_at: data.created_at,
          updated_at: data.updated_at,
          avatar: `${(data.prenom || metadata?.prenom || 'U')[0] || ''}${(data.nom || metadata?.nom || '')[0] || ''}`.toUpperCase() || 'U',
        };
        console.log('[Klaro Auth] Profil finalisé avec rôle:', userProfile.role);
        setProfile(userProfile);
        return userProfile;
      } else {
        // Aucune ligne trouvée dans profiles
        console.warn('[Klaro Auth] Aucune ligne trouvée dans profiles pour cet ID.');
        if (metadata?.role === 'professeur') {
          const profProfile: UserProfile = {
            id: userId,
            nom: metadata?.nom || '',
            prenom: metadata?.prenom || '',
            email: userEmail || '',
            role: 'professeur',
            credits: 100,
            profil_complete: true,
          };
          setProfile(profProfile);
          return profProfile;
        }
      }
    } catch (err) {
      console.error('[Klaro Auth] Exception récupération profil:', err);
    }
    return null;
  };

  const refreshProfile = async (): Promise<UserProfile | null> => {
    if (!user) return null;
    return await fetchProfile(user.id, user.email, user.user_metadata);
  };

  const setDirectProfile = (newProfile: UserProfile | null) => {
    setProfile(newProfile);
  };

  useEffect(() => {
    let mounted = true;

    // 1. Récupération initiale de la session
    supabase.auth.getSession().then(({ data: { session } }) => {
      if (!mounted) return;
      setSession(session);
      setUser(session?.user ?? null);
      if (session?.user) {
        fetchProfile(session.user.id, session.user.email, session.user.user_metadata).finally(() => {
          if (mounted) setLoading(false);
        });
      } else {
        setProfile(null);
        setLoading(false);
      }
    });

    // 2. Écoute des changements d'état d'authentification en temps réel
    const { data: { subscription } } = supabase.auth.onAuthStateChange(async (event, newSession) => {
      if (!mounted) return;
      setSession(newSession);
      setUser(newSession?.user ?? null);

      if (newSession?.user) {
        await fetchProfile(newSession.user.id, newSession.user.email, newSession.user.user_metadata);
      } else {
        setProfile(null);
      }
      setLoading(false);
    });

    return () => {
      mounted = false;
      subscription.unsubscribe();
    };
  }, []);

  const signOut = async () => {
    setLoading(true);
    try {
      await supabase.auth.signOut();
    } catch (err) {
      console.error('Erreur déconnexion:', err);
    } finally {
      setUser(null);
      setSession(null);
      setProfile(null);
      setLoading(false);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        session,
        profile,
        loading,
        signOut,
        refreshProfile,
        setDirectProfile,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth doit être utilisé à l\'intérieur d\'un AuthProvider');
  }
  return context;
};
