import { User, Composition, Classroom, Entrainement, GrimoireItem, TicketSupport } from '../types';

export const INITIAL_USERS: User[] = [
  {
    id: 1,
    nom: 'Mensah',
    prenom: 'Koffi',
    email: 'professeur@klaro.af',
    role: 'professeur',
    credits: 100,
    derniere_connexion: new Date().toISOString(),
    avatar: 'KM'
  },
  {
    id: 2,
    nom: 'Dossou',
    prenom: 'Amina',
    email: 'amina.dossou@etudiant.klaro.af',
    role: 'apprenant',
    credits: 20,
    derniere_connexion: new Date().toISOString(),
    avatar: 'AD'
  },
  {
    id: 3,
    nom: 'Diallo',
    prenom: 'Ibrahima',
    email: 'ibrahima.diallo@etudiant.klaro.af',
    role: 'apprenant',
    credits: 15,
    derniere_connexion: new Date(Date.now() - 86400000).toISOString(),
    avatar: 'ID'
  }
];

export const INITIAL_CLASSROOMS: Classroom[] = [
  {
    id: 1,
    professeur_id: 1,
    nom: 'Terminale D - Sciences de la Vie et de la Terre',
    description: 'Programme officiel UEMOA - Génétique, Immunologie et Géologie',
    code_classe: 'CLS-SVT2025',
    matiere: 'SVT',
    actif: true,
    created_at: '2025-01-10T08:00:00Z',
    membres_count: 34,
    prof_nom: 'Prof. Koffi Mensah'
  },
  {
    id: 2,
    professeur_id: 1,
    nom: 'Première C - Mathématiques & Algèbre',
    description: 'Polynômes, Équations différentielles et Trigonométrie',
    code_classe: 'CLS-MATH1C',
    matiere: 'Mathématiques',
    actif: true,
    created_at: '2025-01-15T09:30:00Z',
    membres_count: 28,
    prof_nom: 'Prof. Koffi Mensah'
  }
];

export const INITIAL_COMPOSITIONS: Composition[] = [
  {
    id: 1,
    professeur_id: 1,
    classroom_id: 1,
    titre: 'Génétique Moléculaire & Synthèse des Protéines',
    code_acces: 'KLR-BIO01',
    type_quiz: 'qcm',
    duree_minutes: 5,
    actif: true,
    created_at: '2025-02-01T10:00:00Z',
    nb_participants: 18,
    score_moyen: 78.5,
    questions: [
      {
        id: 101,
        texte: "Quel acide nucléique est directement traduit en protéine par les ribosomes dans le cytoplasme ?",
        type_question: 'qcm',
        reponses: [
          { id: 1, lettre: 'A', texte: "L'ADN génomique", est_correcte: false },
          { id: 2, lettre: 'B', texte: "L'ARN messager (ARNm)", est_correcte: true },
          { id: 3, lettre: 'C', texte: "L'ARN de transfert uniquement", est_correcte: false },
          { id: 4, lettre: 'D', texte: "Les histones", est_correcte: false }
        ]
      },
      {
        id: 102,
        texte: "Combien de nucléotides composent un codon du code génétique universel ?",
        type_question: 'qcm',
        reponses: [
          { id: 5, lettre: 'A', texte: "2 nucléotides", est_correcte: false },
          { id: 6, lettre: 'B', texte: "3 nucléotides (un triplet)", est_correcte: true },
          { id: 7, lettre: 'C', texte: "4 nucléotides", est_correcte: false },
          { id: 8, lettre: 'D', texte: "6 nucléotides", est_correcte: false }
        ]
      },
      {
        id: 103,
        texte: "Quelle base azotée remplace la thymine dans les brins d'ARN ?",
        type_question: 'qcm',
        reponses: [
          { id: 9, lettre: 'A', texte: "La cytosine", est_correcte: false },
          { id: 10, lettre: 'B', texte: "La guanine", est_correcte: false },
          { id: 11, lettre: 'C', texte: "L'uracile", est_correcte: true },
          { id: 12, lettre: 'D', texte: "L'adénine", est_correcte: false }
        ]
      },
      {
        id: 104,
        texte: "Le codon d'initiation le plus fréquent chez les eucaryotes code pour quel acide aminé ?",
        type_question: 'qcm',
        reponses: [
          { id: 13, lettre: 'A', texte: "La valine", est_correcte: false },
          { id: 14, lettre: 'B', texte: "La lysine", est_correcte: false },
          { id: 15, lettre: 'C', texte: "La méthionine (AUG)", est_correcte: true },
          { id: 16, lettre: 'D', texte: "L'alanine", est_correcte: false }
        ]
      }
    ]
  },
  {
    id: 2,
    professeur_id: 1,
    classroom_id: 2,
    titre: 'Évaluation Vrai/Faux : Fonctions & Dérivées',
    code_acces: 'KLR-VF2025',
    type_quiz: 'vrai_faux',
    duree_minutes: 3,
    actif: true,
    created_at: '2025-02-05T14:20:00Z',
    nb_participants: 12,
    score_moyen: 66.7,
    questions: [
      {
        id: 201,
        texte: "La dérivée d'une fonction constante est égale à 1.",
        type_question: 'vrai_faux',
        reponses: [
          { id: 21, lettre: 'A', texte: "Vrai", est_correcte: false },
          { id: 22, lettre: 'B', texte: "Faux (elle est nulle)", est_correcte: true }
        ]
      },
      {
        id: 202,
        texte: "Si la dérivée f'(x) est strictement positive sur un intervalle, alors f est strictement croissante sur cet intervalle.",
        type_question: 'vrai_faux',
        reponses: [
          { id: 23, lettre: 'A', texte: "Vrai", est_correcte: true },
          { id: 24, lettre: 'B', texte: "Faux", est_correcte: false }
        ]
      },
      {
        id: 203,
        texte: "La dérivée de ln(x) pour tout x > 0 est égale à 1/x.",
        type_question: 'vrai_faux',
        reponses: [
          { id: 25, lettre: 'A', texte: "Vrai", est_correcte: true },
          { id: 26, lettre: 'B', texte: "Faux", est_correcte: false }
        ]
      }
    ]
  },
  {
    id: 3,
    professeur_id: 1,
    titre: 'Histoire & Géographie de l\'Afrique de l\'Ouest',
    code_acces: 'KLR-HISTAF',
    type_quiz: 'qcm',
    duree_minutes: 0, // illimité
    actif: true,
    created_at: '2025-02-12T11:15:00Z',
    nb_participants: 25,
    score_moyen: 82.0,
    questions: [
      {
        id: 301,
        texte: "Quel ancien empire florissant avait pour capitale la ville de Niani au XIIIe siècle ?",
        type_question: 'qcm',
        reponses: [
          { id: 31, lettre: 'A', texte: "L'Empire Songhaï", est_correcte: false },
          { id: 32, lettre: 'B', texte: "L'Empire du Mali", est_correcte: true },
          { id: 33, lettre: 'C', texte: "L'Empire du Ghana", est_correcte: false },
          { id: 34, lettre: 'D', texte: "Le Royaume du Danhomè", est_correcte: false }
        ]
      },
      {
        id: 302,
        texte: "Quel est le plus long fleuve d'Afrique de l'Ouest traversant 5 pays ?",
        type_question: 'qcm',
        reponses: [
          { id: 35, lettre: 'A', texte: "Le fleuve Sénégal", est_correcte: false },
          { id: 36, lettre: 'B', texte: "Le fleuve Volta", est_correcte: false },
          { id: 37, lettre: 'C', texte: "Le fleuve Niger (4180 km)", est_correcte: true },
          { id: 38, lettre: 'D', texte: "Le fleuve Mono", est_correcte: false }
        ]
      }
    ]
  }
];

export const INITIAL_ENTRAINEMENTS: Entrainement[] = [
  {
    id: 1,
    user_id: 2, // Amina
    composition_id: 1,
    score: 4,
    total_questions: 4,
    pourcentage: 100,
    temps_ecoule: 142,
    soumission_type: 'volontaire',
    statut: 'termine',
    created_at: '2025-02-14T15:30:00Z',
    comp_titre: 'Génétique Moléculaire & Synthèse des Protéines',
    comp_code: 'KLR-BIO01'
  },
  {
    id: 2,
    user_id: 2, // Amina
    composition_id: 2,
    score: 2,
    total_questions: 3,
    pourcentage: 66.7,
    temps_ecoule: 110,
    soumission_type: 'volontaire',
    statut: 'termine',
    created_at: '2025-02-15T09:12:00Z',
    comp_titre: 'Évaluation Vrai/Faux : Fonctions & Dérivées',
    comp_code: 'KLR-VF2025'
  },
  {
    id: 3,
    user_id: 3, // Ibrahima
    composition_id: 1,
    score: 3,
    total_questions: 4,
    pourcentage: 75.0,
    temps_ecoule: 215,
    soumission_type: 'volontaire',
    statut: 'termine',
    created_at: '2025-02-15T11:45:00Z',
    comp_titre: 'Génétique Moléculaire & Synthèse des Protéines',
    comp_code: 'KLR-BIO01'
  }
];

export const INITIAL_GRIMOIRE: GrimoireItem[] = [
  {
    id: 1,
    user_id: 2,
    titre: 'Synthèse : Les Lois de Mendel & Hérédité',
    type_contenu: 'resume',
    contenu_source: 'Les trois lois de Gregor Mendel sur la transmission des caractères héréditaires : ségrégation, indépendance...',
    contenu_genere: "## 📌 Fiche Essentielle : Génétique Mendelienne\n\n1. Loi d'uniformité des hybrides de F1.\n2. Loi de pureté des gamètes (ségrégation allèlique 3:1).\n3. Loi de disjonction indépendante des caractères (ratio 9:3:3:1 en F2).\n\n⚠️ Point Clé pour le Bac : Toujours vérifier si les gènes sont liés ou indépendants !",
    credits_utilises: 1,
    created_at: '2025-02-12T16:00:00Z'
  },
  {
    id: 2,
    user_id: 2,
    titre: 'Quiz IA : Immunologie et Anticorps',
    type_contenu: 'questions_ia',
    contenu_source: 'L immunité adaptative fait intervenir les lymphocytes B qui sécrètent des anticorps neutralisants...',
    contenu_genere: "Q: Quelle cellule immunitaire sécrète directement les anticorps circulants ?\nA) Les lymphocytes T CD8\nB) Les plasmocytes (lymphocytes B différenciés) *\nC) Les macrophages\n\nQ: Les anticorps sont des lipides complexes.\nA) Vrai\nB) Faux (ce sont des protéines/immunoglobulines) *",
    credits_utilises: 1,
    created_at: '2025-02-13T10:30:00Z'
  }
];

export const INITIAL_TICKETS: TicketSupport[] = [
  {
    id: 101,
    user_id: 1,
    sujet: "Proposition d'intégration avec WhatsApp pour les notifications de devoirs",
    message: "Bonjour, serait-il possible de permettre l'envoi direct du code de quiz aux élèves via un bouton de partage WhatsApp ?",
    statut: 'en_cours',
    created_at: '2025-02-10T14:00:00Z'
  }
];
