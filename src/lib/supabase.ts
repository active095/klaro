import { createClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL || 'https://etnwkbiwrpdivakpczdw.supabase.co';
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY || 'sb_publishable_mFV42N_bqNlVyeLq1ZJSOA_6GQb6FlC';

if (!supabaseUrl || !supabaseAnonKey) {
  console.warn('Supabase URL ou clé anonyme manquante dans les variables d\'environnement.');
}

export const supabase = createClient(supabaseUrl, supabaseAnonKey, {
  auth: {
    persistSession: true,
    autoRefreshToken: true,
    detectSessionInUrl: true,
  },
});
