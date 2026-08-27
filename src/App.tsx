import React, { useState, useEffect } from 'react';
import { User, Composition, Classroom, Entrainement, GrimoireItem, UserProfile } from './types';
import { INITIAL_USERS, INITIAL_COMPOSITIONS, INITIAL_CLASSROOMS, INITIAL_ENTRAINEMENTS, INITIAL_GRIMOIRE } from './data/mockData';
import { Navbar } from './components/Navbar';
import { Sidebar } from './components/Sidebar';
import { Footer } from './components/Footer';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';

// Views
import { LandingView } from './views/LandingView';
import { LoginView } from './views/LoginView';
import { RegisterView } from './views/RegisterView';
import { StudentDashboardView } from './views/StudentDashboardView';
import { TeacherDashboardView } from './views/TeacherDashboardView';
import { CreateQuizView } from './views/CreateQuizView';
import { TakeQuizView } from './views/TakeQuizView';
import { ClassroomsView } from './views/ClassroomsView';
import { AiToolsView } from './views/AiToolsView';
import { GrimoireView } from './views/GrimoireView';
import { HistoryView } from './views/HistoryView';
import { AllMenusView } from './views/AllMenusView';
import { SupportView } from './views/SupportView';
import { TeacherStudentsView } from './views/TeacherStudentsView';

function AppContent() {
  const { user, profile, loading, signOut } = useAuth();

  // Active user derived from Supabase Auth & profiles table
  const currentUser: User | null = profile
    ? {
        id: profile.id,
        nom: profile.nom || '',
        prenom: profile.prenom || '',
        email: profile.email || user?.email || '',
        role: profile.role,
        credits: profile.credits ?? 20,
        avatar: profile.avatar || `${(profile.prenom || 'U')[0] || ''}${(profile.nom || '')[0] || ''}`.toUpperCase(),
        derniere_connexion: profile.updated_at || new Date().toISOString(),
        profil_complete: profile.profil_complete,
      }
    : null;

  // Local state for quizzes, classrooms, history, grimoire
  const [compositions, setCompositions] = useState<Composition[]>(INITIAL_COMPOSITIONS);
  const [classrooms, setClassrooms] = useState<Classroom[]>(INITIAL_CLASSROOMS);
  const [entrainements, setEntrainements] = useState<Entrainement[]>(INITIAL_ENTRAINEMENTS);
  const [grimoire, setGrimoire] = useState<GrimoireItem[]>(INITIAL_GRIMOIRE);

  const [currentView, setCurrentView] = useState<string>('landing');
  const [activeQuizToTake, setActiveQuizToTake] = useState<Composition | null>(null);

  // Auto-redirect to appropriate dashboard upon login if on landing or auth pages
  useEffect(() => {
    if (profile && (currentView === 'landing' || currentView === 'connexion' || currentView === 'inscription')) {
      if (profile.role === 'professeur') {
        setCurrentView('dashboard-professeur');
      } else {
        setCurrentView('dashboard');
      }
    }
  }, [profile]);

  // Navigation Handler
  const handleNavigate = (view: string) => {
    if (view === 'commencer-quiz') {
      if (compositions.length > 0) {
        setActiveQuizToTake(compositions[0]);
        setCurrentView('passer-quiz');
        return;
      }
    }
    setCurrentView(view);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Logout Handler
  const handleLogout = async () => {
    await signOut();
    setCurrentView('landing');
  };

  // Login Success Handler
  const handleLoginSuccess = (userProfile: UserProfile) => {
    if (userProfile.role === 'professeur') {
      setCurrentView('dashboard-professeur');
    } else {
      setCurrentView('dashboard');
    }
  };

  // Start Quiz with Code
  const handleStartQuizWithCode = (code: string) => {
    const clean = code.trim().toUpperCase();
    const found = compositions.find(c => c.code_acces.toUpperCase() === clean);
    if (found) {
      setActiveQuizToTake(found);
      setCurrentView('passer-quiz');
    } else {
      alert(`Le code « ${code} » n'existe pas ou le quiz est inactif. Veuillez vérifier le code fourni par votre enseignant.`);
    }
  };

  // Quiz Created
  const handleQuizCreated = (newComp: Composition) => {
    setCompositions(prev => [newComp, ...prev]);
  };

  // Quiz Completed
  const handleQuizCompleted = (newTrain: Entrainement) => {
    setEntrainements(prev => [newTrain, ...prev]);
    // Met à jour les statistiques de la composition
    setCompositions(prev => prev.map(c => {
      if (c.id === newTrain.composition_id) {
        const newCount = (c.nb_participants || 0) + 1;
        return {
          ...c,
          nb_participants: newCount
        };
      }
      return c;
    }));
  };

  // Classroom Handlers
  const handleAddClassroom = (newClass: Classroom) => {
    setClassrooms(prev => [newClass, ...prev]);
  };

  const handleJoinClassroom = (code: string): boolean => {
    const found = classrooms.find(c => c.code_classe.toUpperCase() === code.toUpperCase());
    if (found) {
      setClassrooms(prev => prev.map(c => {
        if (c.id === found.id) {
          return { ...c, membres_count: (c.membres_count || 0) + 1 };
        }
        return c;
      }));
      return true;
    }
    return false;
  };

  const handleDeleteClassroom = (id: number | string) => {
    setClassrooms(prev => prev.filter(c => c.id !== id));
  };

  const handleDeleteComposition = (id: number | string) => {
    setCompositions(prev => prev.filter(c => c.id !== id));
  };

  // AI Deduct credit and save to Grimoire
  const handleDeductCreditAndSaveGrimoire = (newItem: GrimoireItem) => {
    setGrimoire(prev => [newItem, ...prev]);
  };

  const handleDeleteGrimoireItem = (id: number | string) => {
    setGrimoire(prev => prev.filter(g => g.id !== id));
  };

  // Render View Content
  const renderContent = () => {
    if (currentView === 'landing') {
      return (
        <LandingView 
          onNavigate={handleNavigate}
          onStartQuizWithCode={handleStartQuizWithCode}
        />
      );
    }

    if (currentView === 'connexion') {
      return (
        <LoginView
          onLoginSuccess={handleLoginSuccess}
          onNavigate={handleNavigate}
        />
      );
    }

    if (currentView === 'inscription') {
      return (
        <RegisterView
          onRegisterSuccess={handleLoginSuccess}
          onNavigate={handleNavigate}
        />
      );
    }

    if (currentView === 'tous-les-menus') {
      return (
        <AllMenusView
          onNavigate={handleNavigate}
        />
      );
    }

    // MODE QUIZ EN COURS
    if (currentView === 'passer-quiz' && activeQuizToTake) {
      return (
        <TakeQuizView
          composition={activeQuizToTake}
          userId={currentUser?.id || 'anonyme'}
          onQuizCompleted={handleQuizCompleted}
          onNavigate={handleNavigate}
        />
      );
    }

    // ESPACE PROFESSEUR (PROTECTED ROLE 'professeur')
    if (
      currentView === 'dashboard-professeur' ||
      currentView === 'creer-quiz' ||
      currentView === 'compositions-professeur' ||
      currentView === 'classrooms-professeur' ||
      currentView === 'etudiants-professeur' ||
      currentView === 'support'
    ) {
      return (
        <ProtectedRoute
          requiredRole="professeur"
          currentView={currentView}
          onNavigate={handleNavigate}
        >
          {currentUser && (
            <>
              {currentView === 'dashboard-professeur' && (
                <TeacherDashboardView
                  user={currentUser}
                  compositions={compositions}
                  classrooms={classrooms}
                  entrainements={entrainements}
                  onNavigate={handleNavigate}
                  onDeleteComposition={handleDeleteComposition}
                  onStartQuizWithCode={handleStartQuizWithCode}
                />
              )}
              {currentView === 'creer-quiz' && (
                <CreateQuizView
                  userId={currentUser.id}
                  classrooms={classrooms}
                  onQuizCreated={handleQuizCreated}
                  onNavigate={handleNavigate}
                />
              )}
              {currentView === 'compositions-professeur' && (
                <TeacherDashboardView
                  user={currentUser}
                  compositions={compositions}
                  classrooms={classrooms}
                  entrainements={entrainements}
                  onNavigate={handleNavigate}
                  onDeleteComposition={handleDeleteComposition}
                  onStartQuizWithCode={handleStartQuizWithCode}
                />
              )}
              {currentView === 'classrooms-professeur' && (
                <ClassroomsView
                  user={currentUser}
                  classrooms={classrooms}
                  onAddClassroom={handleAddClassroom}
                  onJoinClassroom={handleJoinClassroom}
                  onDeleteClassroom={handleDeleteClassroom}
                />
              )}
              {currentView === 'etudiants-professeur' && (
                <TeacherStudentsView
                  users={INITIAL_USERS}
                  entrainements={entrainements}
                />
              )}
              {currentView === 'support' && (
                <SupportView user={currentUser} />
              )}
            </>
          )}
        </ProtectedRoute>
      );
    }

    // ESPACE APPRENANT / COMMUN (PROTECTED ROLE 'apprenant')
    return (
      <ProtectedRoute
        requiredRole="apprenant"
        currentView={currentView}
        onNavigate={handleNavigate}
      >
        {currentUser && (
          <>
            {currentView === 'dashboard' && (
              <StudentDashboardView
                user={currentUser}
                entrainements={entrainements}
                grimoire={grimoire}
                onNavigate={handleNavigate}
              />
            )}
            {currentView === 'commencer-quiz' && (
              <LandingView
                onNavigate={handleNavigate}
                onStartQuizWithCode={handleStartQuizWithCode}
              />
            )}
            {(currentView === 'mes-compositions' || currentView === 'historique-entrainement') && (
              <HistoryView
                user={currentUser}
                entrainements={entrainements}
                onNavigate={handleNavigate}
              />
            )}
            {currentView === 'classrooms' && (
              <ClassroomsView
                user={currentUser}
                classrooms={classrooms}
                onAddClassroom={handleAddClassroom}
                onJoinClassroom={handleJoinClassroom}
                onDeleteClassroom={handleDeleteClassroom}
              />
            )}
            {currentView === 'grimoire' && (
              <GrimoireView
                user={currentUser}
                grimoire={grimoire}
                onDeleteGrimoireItem={handleDeleteGrimoireItem}
                onNavigate={handleNavigate}
              />
            )}
            {currentView === 'outils-ia' && (
              <AiToolsView
                user={currentUser}
                onDeductCreditAndSaveGrimoire={handleDeductCreditAndSaveGrimoire}
                onNavigate={handleNavigate}
              />
            )}
          </>
        )}
      </ProtectedRoute>
    );
  };

  const showSidebar = currentUser && currentView !== 'landing' && currentView !== 'connexion' && currentView !== 'inscription' && currentView !== 'passer-quiz';

  return (
    <div className="min-h-screen bg-[#EEF1F6] text-[#1E293B] flex flex-col font-sans selection:bg-orange-500 selection:text-white">
      {/* NAVBAR SUPÉRIEURE */}
      <Navbar
        currentUser={currentUser}
        currentView={currentView}
        onNavigate={handleNavigate}
        onLogout={handleLogout}
      />

      {/* CONTENEUR PRINCIPAL */}
      <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div className="flex flex-col lg:flex-row gap-8">
          {/* MENU LATÉRAL SÉCURISÉ SELON LE RÔLE */}
          {showSidebar && (
            <Sidebar
              role={currentUser.role}
              currentView={currentView}
              onNavigate={handleNavigate}
            />
          )}

          {/* ZONE D'AFFICHAGE DU CONTENU */}
          <div className="flex-1 w-full min-w-0">
            {renderContent()}
          </div>
        </div>
      </main>

      {/* FOOTER */}
      <Footer onNavigate={handleNavigate} />
    </div>
  );
}

export function App() {
  return (
    <AuthProvider>
      <AppContent />
    </AuthProvider>
  );
}

export default App;
