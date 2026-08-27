-- ==========================================================
-- KLARO — Base de données MySQL / MariaDB (utf8mb4)
-- Plateforme de quiz adaptatifs par IA pour l'Afrique francophone
-- Réalisé par : Henri Joël HOUNKPE
-- Compatible : XAMPP / WAMP / MySQL 5.7+ / MySQL 8.0+ / MariaDB
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table: tickets_support
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tickets_support`;
CREATE TABLE `tickets_support` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `sujet` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `statut` ENUM('ouvert', 'en_cours', 'resolu') NOT NULL DEFAULT 'ouvert',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tickets_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: grimoire_ia
-- --------------------------------------------------------
DROP TABLE IF EXISTS `grimoire_ia`;
CREATE TABLE `grimoire_ia` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `titre` VARCHAR(255) NOT NULL,
  `type_contenu` ENUM('resume', 'questions_ia', 'fiche_revision') NOT NULL DEFAULT 'resume',
  `contenu_source` TEXT NULL,
  `contenu_genere` LONGTEXT NOT NULL,
  `credits_utilises` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_grimoire_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: classroom_membres
-- --------------------------------------------------------
DROP TABLE IF EXISTS `classroom_membres`;
CREATE TABLE `classroom_membres` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `classroom_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `date_rejoint` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_classroom_user` (`classroom_id`, `user_id`),
  INDEX `idx_membres_user` (`user_id`),
  INDEX `idx_membres_classroom` (`classroom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: classrooms
-- --------------------------------------------------------
DROP TABLE IF EXISTS `classrooms`;
CREATE TABLE `classrooms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `professeur_id` INT UNSIGNED NOT NULL,
  `nom` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `code_classe` VARCHAR(20) NOT NULL,
  `matiere` VARCHAR(100) NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code_classe` (`code_classe`),
  INDEX `idx_classrooms_prof` (`professeur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: credits_transactions
-- --------------------------------------------------------
DROP TABLE IF EXISTS `credits_transactions`;
CREATE TABLE `credits_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `montant` INT NOT NULL,
  `motif` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_credits_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: serie_streak
-- --------------------------------------------------------
DROP TABLE IF EXISTS `serie_streak`;
CREATE TABLE `serie_streak` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `jours_consecutifs` INT UNSIGNED NOT NULL DEFAULT 0,
  `dernier_entrainement` DATE NULL,
  `record_streak` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_streak_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: reponses_utilisateur
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reponses_utilisateur`;
CREATE TABLE `reponses_utilisateur` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entrainement_id` INT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `reponse_choisie_id` INT UNSIGNED NULL,
  `est_correcte` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_train_quest` (`entrainement_id`, `question_id`),
  INDEX `idx_rep_train` (`entrainement_id`),
  INDEX `idx_rep_quest` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: entrainements
-- --------------------------------------------------------
DROP TABLE IF EXISTS `entrainements`;
CREATE TABLE `entrainements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `composition_id` INT UNSIGNED NOT NULL,
  `score` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_questions` INT UNSIGNED NOT NULL DEFAULT 0,
  `pourcentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `temps_debut` DATETIME NOT NULL,
  `temps_fin` DATETIME NULL,
  `temps_ecoule` INT UNSIGNED NOT NULL DEFAULT 0,
  `soumission_type` ENUM('volontaire', 'expiration_temps') NOT NULL DEFAULT 'volontaire',
  `statut` ENUM('en_cours', 'termine') NOT NULL DEFAULT 'en_cours',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_entrainements_user` (`user_id`),
  INDEX `idx_entrainements_comp` (`composition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: reponses_possibles
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reponses_possibles`;
CREATE TABLE `reponses_possibles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` INT UNSIGNED NOT NULL,
  `lettre` VARCHAR(5) NOT NULL,
  `texte_reponse` TEXT NOT NULL,
  `est_correcte` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_reponses_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: questions
-- --------------------------------------------------------
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `composition_id` INT UNSIGNED NOT NULL,
  `texte_question` TEXT NOT NULL,
  `type_question` ENUM('qcm', 'vrai_faux') NOT NULL DEFAULT 'qcm',
  `ordre` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_questions_comp` (`composition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: compositions
-- --------------------------------------------------------
DROP TABLE IF EXISTS `compositions`;
CREATE TABLE `compositions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `professeur_id` INT UNSIGNED NOT NULL,
  `titre` VARCHAR(255) NOT NULL,
  `type_quiz` ENUM('qcm', 'vrai_faux') NOT NULL DEFAULT 'qcm',
  `code_acces` VARCHAR(20) NOT NULL,
  `duree_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
  `texte_brut_source` LONGTEXT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code_acces` (`code_acces`),
  INDEX `idx_compositions_prof` (`professeur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('apprenant', 'professeur') NOT NULL DEFAULT 'apprenant',
  `credits` INT UNSIGNED NOT NULL DEFAULT 50,
  `derniere_connexion` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Contraintes de clés étrangères (Foreign Keys)
-- --------------------------------------------------------
ALTER TABLE `compositions`
  ADD CONSTRAINT `fk_compositions_prof` FOREIGN KEY (`professeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_comp` FOREIGN KEY (`composition_id`) REFERENCES `compositions` (`id`) ON DELETE CASCADE;

ALTER TABLE `reponses_possibles`
  ADD CONSTRAINT `fk_reponses_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

ALTER TABLE `entrainements`
  ADD CONSTRAINT `fk_train_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_train_comp` FOREIGN KEY (`composition_id`) REFERENCES `compositions` (`id`) ON DELETE CASCADE;

ALTER TABLE `reponses_utilisateur`
  ADD CONSTRAINT `fk_rep_user_train` FOREIGN KEY (`entrainement_id`) REFERENCES `entrainements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rep_user_quest` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rep_user_choice` FOREIGN KEY (`reponse_choisie_id`) REFERENCES `reponses_possibles` (`id`) ON DELETE SET NULL;

ALTER TABLE `serie_streak`
  ADD CONSTRAINT `fk_streak_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `credits_transactions`
  ADD CONSTRAINT `fk_credits_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `classrooms`
  ADD CONSTRAINT `fk_classrooms_prof` FOREIGN KEY (`professeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `classroom_membres`
  ADD CONSTRAINT `fk_membres_classroom` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_membres_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `grimoire_ia`
  ADD CONSTRAINT `fk_grimoire_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `tickets_support`
  ADD CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------
-- INSERTION UNIQUE OBLIGATOIRE : Compte Professeur par défaut
-- Identifiants par défaut :
-- Email : professeur@klaro.af
-- Mot de passe : KlaroProf2025! (hash bcrypt standard PHP)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `credits`, `derniere_connexion`, `created_at`) VALUES
(1, 'HOUNKPE', 'Henri Joël', 'professeur@klaro.af', '$2y$10$tZ2.Q2xJgIq6jE1qI9c.iOG38v/F3nkWmN02Uo6a1ePcqjT8wQ0qW', 'professeur', 500, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
