import React, { useState } from 'react';
import { 
  Play, 
  Sparkles, 
  ArrowRight, 
  GraduationCap, 
  ShieldCheck, 
  Flame, 
  CheckCircle2, 
  Zap,
  Users
} from 'lucide-react';

interface LandingViewProps {
  onNavigate: (view: string) => void;
  onStartQuizWithCode: (code: string) => void;
}

export const LandingView: React.FC<LandingViewProps> = ({ onNavigate, onStartQuizWithCode }) => {
  const [inputCode, setInputCode] = useState('');

  const handleSubmitCode = (e: React.FormEvent) => {
    e.preventDefault();
    if (inputCode.trim()) {
      onStartQuizWithCode(inputCode.trim().toUpperCase());
    }
  };

  return (
    <div className="space-y-16 py-8 sm:py-12">
      
      {/* HERO SECTION */}
      <section className="text-center max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
        
        <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100/70 border border-orange-200 text-orange-800 text-xs font-bold shadow-xs">
          <Sparkles className="w-4 h-4 text-orange-600" />
          <span>Plateforme d'excellence éducative pour l'Afrique francophone</span>
        </div>

        <h1 className="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.1]">
          Révisez intelligemment.<br />
          <span className="text-orange-500">Réussissez chaque évaluation.</span>
        </h1>

        <p className="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
          Klaro combine quiz adaptatifs, chronométrage anti-triche et génération de synthèses par IA pour propulser les lycéens et étudiants vers le succès aux examens nationaux.
        </p>

        {/* CARTE D'ACCÈS RAPIDE PAR CODE (CŒUR DE L'EXPÉRIENCE) */}
        <div className="max-w-md mx-auto pt-4">
          <div className="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xl space-y-4">
            <div className="text-left">
              <span className="text-[11px] font-bold text-orange-600 uppercase tracking-wider block">
                Accès immédiat
              </span>
              <h2 className="text-lg font-black text-slate-900">
                Vous avez un code de quiz ?
              </h2>
              <p className="text-xs text-slate-500">
                Entrez le code fourni par votre enseignant pour démarrer l'évaluation.
              </p>
            </div>

            <form onSubmit={handleSubmitCode} className="space-y-3">
              <div className="relative">
                <input
                  type="text"
                  value={inputCode}
                  onChange={(e) => setInputCode(e.target.value)}
                  placeholder="Ex: KLR-BIO01"
                  required
                  className="w-full px-4 py-3.5 rounded-2xl bg-slate-50 border-2 border-slate-200 text-slate-900 font-mono font-bold text-base uppercase text-center tracking-widest placeholder:text-slate-400 placeholder:normal-case placeholder:tracking-normal focus:outline-none focus:border-orange-500 focus:bg-white transition-all"
                />
              </div>

              <button
                type="submit"
                className="w-full py-3.5 px-6 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black text-sm shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.01] flex items-center justify-center gap-2"
              >
                <Play className="w-4 h-4 fill-current" />
                <span>Rejoindre et Passer le Quiz</span>
              </button>
            </form>

            <div className="flex items-center justify-center gap-2 text-xs text-slate-400 font-medium pt-1">
              <span>Codes de session disponibles :</span>
              <button 
                onClick={() => { setInputCode('KLR-BIO01'); }}
                className="font-mono font-bold text-orange-600 hover:underline bg-orange-50 px-2 py-0.5 rounded-md"
              >
                KLR-BIO01
              </button>
              <button 
                onClick={() => { setInputCode('KLR-VF2025'); }}
                className="font-mono font-bold text-blue-600 hover:underline bg-blue-50 px-2 py-0.5 rounded-md"
              >
                KLR-VF2025
              </button>
            </div>
          </div>
        </div>

        {/* CTA SECONDAIRE */}
        <div className="pt-2 flex flex-wrap items-center justify-center gap-4">
          <button
            onClick={() => onNavigate('inscription')}
            className="px-6 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2"
          >
            <span>Créer un compte apprenant</span>
            <ArrowRight className="w-4 h-4" />
          </button>
          <button
            onClick={() => onNavigate('connexion')}
            className="px-6 py-3 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border border-slate-200 shadow-xs transition-all"
          >
            Espace Enseignant
          </button>
        </div>

      </section>

      {/* 3 PILIERS / FONCTIONNALITÉS NUMÉROTÉES */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div className="text-center space-y-2">
          <span className="text-xs font-bold text-orange-600 uppercase tracking-widest">
            Comment ça marche
          </span>
          <h2 className="text-3xl font-black text-slate-900">
            Une expérience pensée pour l'apprenant africain
          </h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          
          {/* CARTE 01 */}
          <div className="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm space-y-4 relative overflow-hidden group hover:border-orange-300 transition-colors">
            <span className="text-4xl font-black text-orange-200 block font-mono">01</span>
            <h3 className="text-lg font-black text-slate-900">
              Accès instantané par Code
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Pas de frictions inutiles. Les élèves accèdent aux évaluations officielles avec un code court (ex: <code className="font-mono font-bold text-orange-600">KLR-BIO01</code>) distribué par leur enseignant.
            </p>
            <div className="pt-2 flex items-center gap-2 text-xs font-bold text-orange-600">
              <Zap className="w-4 h-4" />
              <span>Chronomètre temps réel synchronisé</span>
            </div>
          </div>

          {/* CARTE 02 */}
          <div className="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm space-y-4 relative overflow-hidden group hover:border-blue-300 transition-colors">
            <span className="text-4xl font-black text-blue-200 block font-mono">02</span>
            <h3 className="text-lg font-black text-slate-900">
              Chronomètre & Anti-triche
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Le minuteur serveur empêche toute tricherie JS. Les réponses sont auto-sauvegardées toutes les 30s et soumises automatiquement à expiration.
            </p>
            <div className="pt-2 flex items-center gap-2 text-xs font-bold text-blue-600">
              <ShieldCheck className="w-4 h-4" />
              <span>Validation sécurisée côté serveur</span>
            </div>
          </div>

          {/* CARTE 03 */}
          <div className="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm space-y-4 relative overflow-hidden group hover:border-amber-300 transition-colors">
            <span className="text-4xl font-black text-amber-200 block font-mono">03</span>
            <h3 className="text-lg font-black text-slate-900">
              Klaro AI & Fiches Grimoire
            </h3>
            <p className="text-xs text-slate-600 leading-relaxed">
              Transformez instantanément n'importe quel cours ou PDF en résumé structuré et fiches de synthèse enregistrées dans votre Grimoire personnel.
            </p>
            <div className="pt-2 flex items-center gap-2 text-xs font-bold text-amber-600">
              <Sparkles className="w-4 h-4" />
              <span>Mémorisation espacée et streak actif</span>
            </div>
          </div>

        </div>
      </section>

    </div>
  );
};
