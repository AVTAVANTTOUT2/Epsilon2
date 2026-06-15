# Epsilon v2 - Plateforme de Peer-Learning

![Version](https://img.shields.io/badge/version-2.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-6366f1)
![License](https://img.shields.io/badge/license-MIT-green)

**Epsilon** est une plateforme de peer-learning développée pour l'EPSI Lille. Les apprenants progressent à travers des parcours de défis, soumettent leurs travaux, et s'évaluent mutuellement pour gagner des badges et des rangs.

**Dépôt GitHub :** [github.com/AVTAVANTTOUT2/Epsilon2](https://github.com/AVTAVANTTOUT2/Epsilon2) (public)

---

## Architecture

```
Epsilon2/
├── public/                  # Point d'entrée web
│   ├── index.php            # Front controller
│   ├── router.php           # Routeur pour le serveur PHP intégré
│   ├── .htaccess            # Rewrite rules (Apache)
│   ├── assets/
│   │   └── css/style.css    # CSS complet (thème cosmique sombre)
│   └── uploads/             # Fichiers uploadés (protégé)
├── app/
│   ├── Core/                # Noyau du framework
│   │   ├── Database.php     # PDO wrapper (SQLite/MySQL)
│   │   ├── Router.php       # Routeur léger
│   │   ├── Session.php      # Gestion de session + remember me
│   │   ├── Validator.php    # Validation d'entrées
│   │   ├── Uploader.php     # Upload sécurisé
│   │   └── View.php         # Moteur de templates
│   ├── Middleware/
│   │   └── AuthMiddleware.php
│   ├── Controllers/
│   │   ├── AuthController.php      # Auth + profil
│   │   ├── CourseController.php    # Parcours + dashboard
│   │   ├── SubmissionController.php # Upload
│   │   └── EvaluationController.php # Évaluation
│   ├── Models/
│   │   ├── User.php
│   │   ├── Course.php
│   │   ├── Challenge.php
│   │   ├── Submission.php
│   │   ├── Evaluation.php
│   │   └── Badge.php
│   └── Helpers/
│       └── functions.php     # Utilitaires (env, e(), csrf, etc.)
├── views/                    # Templates PHP
│   ├── layout.php            # Layout principal
│   ├── home.php              # Landing page
│   ├── dashboard.php         # Tableau de bord
│   ├── auth/                 # Login, register, reset, profil
│   ├── courses/              # Liste + détail des parcours
│   ├── submissions/          # Upload + liste des soumissions
│   └── evaluations/          # Évaluation + historique
├── database/
│   ├── schema.sql            # Schéma complet
│   └── migrate.php           # Script de migration
├── tests/
│   └── run.php               # Suite de tests (31 tests)
├── storage/logs/
├── .env                      # Configuration
└── .gitignore
```

## Installation rapide

### Prérequis
- PHP 8.1+
- SQLite (par défaut) ou MySQL
- Apache avec mod_rewrite OU serveur intégré PHP

### Démarrage

```bash
# 1. Cloner le projet
git clone https://github.com/AVTAVANTTOUT2/Epsilon2.git
cd Epsilon2

# 2. Migrer la base de données
php database/migrate.php

# 3. Lancer le serveur (routeur requis pour les URLs propres)
cd public && php -S localhost:8888 router.php

# 4. Ouvrir http://localhost:8888
```

### Configuration MySQL (optionnel)

Dans `.env` :
```
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=epsilon
DB_USER=root
DB_PASS=
```

## Fonctionnalités

### Authentification
- Inscription avec vérification par email
- Connexion avec option "Rester connecté" (cookie 1 an)
- Réinitialisation de mot de passe (lien par email)
- Sessions PHP sécurisées
- Protection CSRF sur tous les formulaires

### 4 Parcours de compétences
1. **Apprentissage et transmission** - Fondamentaux du peer-learning
2. **Culture en pot** - Culture numérique (code, design, données)
3. **Art de l'ouvrage** - Excellence technique
4. **Arts Associés** - Cybersécurité & compétences transversales

Chaque parcours contient des défis répartis sur 5 niveaux de rang.

### Système de progression (AccessCode)
Basé sur le concept original d'Epsilon : chaque utilisateur a un code d'accès (ex: `2 3 0 1`) qui encode sa progression sur les 4 parcours.

- **0** : Non suivi
- **1** : Suivi (parcours rejoint)
- **2** : Apprenti
- **3** : Compagnon
- **4** : Passeur
- **5** : Guide

**Règles d'accès progressif :**
- Parcours 0 : accessible sans prérequis
- Parcours 1 : nécessite niveau Apprenti sur le parcours 1
- Parcours 2 : nécessite niveau Apprenti sur le parcours 0
- Parcours 3 : nécessite niveau Apprenti sur le parcours 0

### Upload de travaux
- Upload sécurisé par utilisateur et par défi
- Validation des types MIME (JPG, PNG, PDF, ZIP, DOC, TXT, MP4, etc.)
- Limite de taille configurable (5 Mo par défaut)
- Stockage organisé par utilisateur/parcours/défi
- Noms de fichiers aléatoires (anti-collision)
- Accessible uniquement aux utilisateurs authentifiés

### Évaluation par les pairs
- Notation de 1 à 5 étoiles
- Commentaire facultatif
- Une évaluation par soumission et par évaluateur
- Impossible d'évaluer son propre travail
- Historique complet des évaluations données et reçues

### Badges & Rangs
- 4 badges : Apprenti, Compagnon, Passeur, Guide
- Attribution automatique selon la progression
- Badge "Passeur" débloqué après 3 évaluations données
- Affichage visuel dans le dashboard et le profil

## Routes

| Méthode | URI | Description |
|---------|-----|-------------|
| GET | `/` | Landing page |
| GET/POST | `/login` | Connexion |
| GET/POST | `/register` | Inscription |
| GET | `/verify-email` | Vérification email |
| GET | `/logout` | Déconnexion |
| GET/POST | `/password-reset` | Réinitialisation mot de passe |
| GET | `/dashboard` | Tableau de bord (auth) |
| GET | `/courses` | Liste des parcours (auth) |
| GET | `/courses/{id}` | Détail parcours (auth) |
| POST | `/courses/{id}/join` | Rejoindre un parcours (auth) |
| GET/POST | `/courses/{id}/challenges/{challengeId}` | Détail + upload défi (auth) |
| GET | `/submissions` | Mes soumissions (auth) |
| GET | `/submissions/{id}` | Détail soumission (auth) |
| GET/POST | `/evaluate` | Évaluer des travaux (auth) |
| GET | `/evaluations` | Mes évaluations (auth) |
| GET/POST | `/profile` | Mon profil (auth) |

## Tests

```bash
php tests/run.php
```

31 tests couvrant :
- Validation d'entrées (12 tests)
- Logique d'accès aux parcours (10 tests)
- Intégration base de données (7 tests)
- Helpers (2 tests)

## Sécurité

- **A01** Contrôle d'accès : middleware auth sur toutes les routes protégées
- **A02** Mots de passe : hashés avec bcrypt (cost 12), jamais en clair
- **A03** Injection SQL : 100% requêtes préparées (PDO)
- **A04** CSRF : token unique par formulaire
- **A05** Debug : désactivable via `.env` (`APP_DEBUG=false`)
- **A06** Dépendances : zéro (PHP vanilla)
- **A07** Sessions : régénérées, cookies httponly + samesite
- **A08** Uploads : validation MIME, noms aléatoires, stockage hors webroot configurable
- **A09** Rate limiting : recommandé d'ajouter au niveau serveur (nginx/apache)
- **A10** XSS : échappement systématique avec `e()` (htmlspecialchars)

## Évolutions par rapport à Epsilon v1

| Aspect | v1 (epsilonPhp) | v2 (Epsilon2) |
|--------|-----------------|---------------|
| Architecture | Scripts PHP procéduraux | MVC avec autoloading |
| Base de données | 1 table user | 7 tables relationnelles |
| Auth | Cookie/session basique | Sessions + remember me + CSRF |
| Parcours | 4 parcours codés en dur | 4 parcours avec défis en BDD |
| Upload | Basique | Validation MIME, taille, nom aléatoire |
| Évaluation | Ébauche (interface) | Fonctionnelle avec notation |
| Badges | Texte uniquement | Attribués automatiquement |
| Frontend | Bootstrap + CSS basique | Thème cosmique sombre sur mesure |
| Tests | Aucun | 31 tests automatisés |
| Stockage | MySQL requis | SQLite (zero-config) ou MySQL |
| Émails | Oui (mail()) | Oui + mode dev sans SMTP |

## Journal de développement

- **2026-06-15** : Correction de la page d'accueil — la route `/` inclut désormais le layout (`View::render`) pour charger le CSS, la navbar et le footer. Ajout de `public/router.php` pour le serveur PHP intégré.
