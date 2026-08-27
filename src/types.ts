export type UserRole = 'apprenant' | 'professeur';

export interface UserProfile {
  id: string;
  nom: string;
  prenom: string;
  email?: string;
  role: UserRole;
  credits: number;
  profil_complete?: boolean;
  created_at?: string;
  updated_at?: string;
  derniere_connexion?: string;
  avatar?: string;
}

export interface User {
  id: string | number;
  nom: string;
  prenom: string;
  email: string;
  role: UserRole;
  credits: number;
  derniere_connexion?: string;
  avatar?: string;
  profil_complete?: boolean;
}

export interface QuestionAnswer {
  id: number | string;
  lettre: string;
  texte: string;
  est_correcte: boolean;
}

export interface Question {
  id: number | string;
  texte: string;
  type_question: 'qcm' | 'vrai_faux';
  reponses: QuestionAnswer[];
}

export interface Composition {
  id: number | string;
  professeur_id: number | string;
  classroom_id?: number | string | null;
  titre: string;
  code_acces: string;
  type_quiz: 'qcm' | 'vrai_faux';
  duree_minutes: number;
  questions: Question[];
  actif: boolean;
  created_at: string;
  nb_participants?: number;
  score_moyen?: number;
}

export interface Entrainement {
  id: number | string;
  user_id: number | string;
  composition_id: number | string;
  score: number;
  total_questions: number;
  pourcentage: number;
  temps_ecoule: number; // en secondes
  soumission_type: 'volontaire' | 'expiration_temps';
  statut: 'en_cours' | 'termine';
  created_at: string;
  comp_titre?: string;
  comp_code?: string;
  reponses_details?: {
    question_id: number | string;
    reponse_choisie_id: number | string | null;
    est_correcte: boolean;
  }[];
}

export interface Classroom {
  id: number | string;
  professeur_id: number | string;
  nom: string;
  description: string;
  code_classe: string;
  matiere: string;
  actif: boolean;
  created_at: string;
  membres_count?: number;
  prof_nom?: string;
}

export interface GrimoireItem {
  id: number | string;
  user_id: number | string;
  titre: string;
  type_contenu: 'resume' | 'questions_ia';
  contenu_source: string;
  contenu_genere: string;
  credits_utilises: number;
  created_at: string;
}

export interface TicketSupport {
  id: number | string;
  user_id: number | string;
  sujet: string;
  message: string;
  statut: 'ouvert' | 'en_cours' | 'resolu';
  created_at: string;
}
