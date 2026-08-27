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
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<SupabaseUser | null>(null);
  const [session, setSession] = useState<Session | null>(null);
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState<boolean>(true);

  const fetchProfile = async (userId: string, userEmail?: string): Promise<UserProfile | null> => {
    try {
      const { data, error } = await supabase
        .from('profiles')
        .select('*')
        .eq('id', userId)
        .single();

      if (error) {
        console.error('Erreur lors de la récupération du profil Supabase:', error.message);
        // Si le profil n'est pas encore créé par le trigger ou introuvable, profil de secours
        const fallbackProfile: UserProfile = {
          id: userId,
          nom: 'Utilisateur',
          prenom: '',
          email: userEmail || '',
          role: 'apprenant',
          credits: 20,
          profil_complete: false,
        };
        setProfile(fallbackProfile);
        return fallbackProfile;
      }

      if (data) {
        const fullProfile: UserProfile = {
          id: data.id,
          nom: data.nom || '',
          prenom: data.prenom || '',
          email: userEmail || data.email || '',
          role: (data.role as UserRole) || 'apprenant',
          credits: Number(data.credits ?? 20),
          profil_complete: Boolean(data.profil_complete),
          created_at: data.created_at,
          updated_at: data.updated_at,
          avatar: `${(data.prenom || 'U')[0] || ''}${(data.nom || '')[0] || ''}`.toUpperCase() || 'U',
        };
        setProfile(fullProfile);
        return fullProfile;
      }
    } catch (err) {
      console.error('Exception récupération profil:', err);
    }
    return null;
  };

  const refreshProfile = async (): Promise<UserProfile | null> => {
    if (!user) return null;
    return await fetchProfile(user.id, user.email);
  };

  useEffect(() => {
    let mounted = true;

    // 1. Récupération initiale de la session
    supabase.auth.getSession().then(({ data: { session } }) => {
      if (!mounted) return;
      setSession(session);
      setUser(session?.user ?? null);
      if (session?.user) {
        fetchProfile(session.user.id, session.user.email).finally(() => {
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
        await fetchProfile(newSession.user.id, newSession.user.email);
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
