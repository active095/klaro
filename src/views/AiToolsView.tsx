import React, { useState } from 'react';
import { User, GrimoireItem } from '../types';
import { 
  Sparkles, 
  Coins, 
  BookOpen, 
  CheckCircle2, 
  AlertCircle, 
  Copy, 
  Check,
  ArrowRight
} from 'lucide-react';

interface AiToolsViewProps {
  user: User;
  onDeductCreditAndSaveGrimoire: (newItem: GrimoireItem) => void;
  onNavigate: (view: string) => void;
}

export const AiToolsView: React.FC<AiToolsViewProps> = ({
  user,
  onDeductCreditAndSaveGrimoire,
  onNavigate
}) => {
  const [titre, setTitre] = useState('');
  const [typeAction, setTypeAction] = useState<'resume' | 'questions_ia'>('resume');
  const [texteSource, setTexteSource] = useState('');
  const [isGenerating, setIsGenerating] = useState(false);
  const [generatedResult, setGeneratedResult] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [copied, setCopied] = useState(false);

  const handleGenerate = (e: React.FormEvent) => {
    e.preventDefault();
    if (!texteSource.trim()) {
      setError("Veuillez coller le texte de votre cours ou document.");
      return;
    }
    if (user.credits < 1) {
      setError("Solde insuffisant. Vous avez besoin d'au moins 1 crédit.");
      return;
    }

    setError(null);
    setIsGenerating(true);

    setTimeout(() => {
      let output = '';
      const finalTitre = titre.trim() || (typeAction === 'resume' ? 'Fiche Synthèse : ' : 'Quiz IA : ') + texteSource.substring(0, 30) + '...';

      if (typeAction === 'resume') {
        output = `## 📌 Fiche de Synthèse Clé — ${finalTitre}\n\n` +
          `### 1. Idée Directrice & Concepts Fondamentaux\n` +
          `• Le document aborde : "${texteSource.substring(0, 180)}..."\n` +
          `• Principe clé : Toujours mémoriser les définitions fondamentales et les formules associées.\n\n` +
          `### 2. Points Stratégiques pour l'Examen (UEMOA / BAC)\n` +
          `1. Identifier les termes techniques et éviter les approximations de vocabulaire.\n` +
          `2. Structurer chaque démonstration en : Hypothèse ➔ Raisonnement ➔ Conclusion.\n` +
          `3. Réviser cette fiche à J+1 et J+7 pour activer la mémorisation à long terme.\n\n` +
          `💡 Conseil IA : Testez-vous dès maintenant en passant un quiz d'entraînement !`;
      } else {
        output = `Q: Quelle est l'affirmation correcte concernant le passage ci-dessous ?\n` +
          `"${texteSource.substring(0, 90)}..."\n` +
          `A) Une interprétation inexacte\n` +
          `B) La proposition conforme aux principes énoncés *\n` +
          `C) Une extrapolation non démontrée\n` +
          `D) Aucune de ces réponses\n\n` +
          `Q: Le texte confirme la validité universelle de cette règle.\n` +
          `A) Vrai *\n` +
          `B) Faux`;
      }

      const newItem: GrimoireItem = {
        id: Date.now(),
        user_id: user.id,
        titre: finalTitre,
        type_contenu: typeAction,
        contenu_source: texteSource,
        contenu_genere: output,
        credits_utilises: 1,
        created_at: new Date().toISOString()
      };

      onDeductCreditAndSaveGrimoire(newItem);
      setGeneratedResult(output);
      setIsGenerating(false);
    }, 1200);
  };

  const handleCopy = (text: string) => {
    navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="max-w-4xl mx-auto space-y-8 py-6">
      
      {/* EN-TÊTE KLARO AI */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider mb-1">
            <Sparkles className="w-3.5 h-3.5" />
            <span>Moteur d'Apprentissage Automatique</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-black text-slate-900">
            Klaro AI — Synthèse & Quiz Automatiques
          </h1>
          <p className="text-xs sm:text-sm text-slate-500">
            Collez n'importe quel cours ou texte pour extraire les notions essentielles ou créer un quiz d'entraînement.
          </p>
        </div>

        <div className="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-2 font-bold text-xs text-slate-700">
          <Coins className="w-4 h-4 text-amber-500" />
          <span>Solde : <strong className="text-slate-900 font-mono text-sm">{user.credits}</strong> crédits</span>
        </div>
      </div>

      {error && (
        <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
          <AlertCircle className="w-4 h-4 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {/* FORMULAIRE DE GÉNÉRATION */}
      <div className="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        
        <form onSubmit={handleGenerate} className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Titre de la fiche (optionnel)
              </label>
              <input
                type="text"
                value={titre}
                onChange={(e) => setTitre(e.target.value)}
                placeholder="Ex: Synthèse Histoire - Chapitre 4"
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Type de document souhaité
              </label>
              <select
                value={typeAction}
                onChange={(e) => setTypeAction(e.target.value as 'resume' | 'questions_ia')}
                className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
              >
                <option value="resume">📄 Synthèse & Fiche Essentielle (1 crédit)</option>
                <option value="questions_ia">❓ Questions de Quiz Automatiques (1 crédit)</option>
              </select>
            </div>
          </div>

          <div>
            <div className="flex items-center justify-between mb-1.5">
              <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Contenu du cours / notes de classe *
              </label>
              <span className="text-xs text-slate-400">Extrait de cours, leçon ou texte PDF</span>
            </div>
            <textarea
              rows={8}
              required
              value={texteSource}
              onChange={(e) => setTexteSource(e.target.value)}
              placeholder="Collez ici le texte complet de votre leçon ou document..."
              className="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white leading-relaxed"
            />
          </div>

          <div className="flex justify-end">
            <button
              type="submit"
              disabled={isGenerating}
              className="px-8 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 text-white font-bold text-sm shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2"
            >
              <Sparkles className="w-4 h-4" />
              <span>{isGenerating ? 'Traitement IA en cours...' : 'Générer avec Klaro AI (1 crédit)'}</span>
            </button>
          </div>
        </form>

        {/* RÉSULTAT GÉNÉRÉ */}
        {generatedResult && (
          <div className="pt-6 border-t border-slate-100 space-y-4 animate-fade-in">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2 text-sm font-bold text-emerald-700">
                <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                <span>Génération enregistrée dans votre Grimoire</span>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => handleCopy(generatedResult)}
                  className="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 transition-all"
                >
                  {copied ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5" />}
                  <span>{copied ? 'Copié' : 'Copier'}</span>
                </button>
                <button
                  onClick={() => onNavigate('grimoire')}
                  className="px-3 py-1.5 rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 text-xs font-bold transition-all flex items-center gap-1"
                >
                  <span>Voir le Grimoire</span>
                  <ArrowRight className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <div className="p-6 rounded-2xl bg-slate-50 border border-slate-200 font-mono text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">
              {generatedResult}
            </div>
          </div>
        )}

      </div>

    </div>
  );
};
