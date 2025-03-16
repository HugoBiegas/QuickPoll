# Plateforme de Gestion de Quiz - ESTIAM

Une application web moderne et interactive pour la création, la gestion et la participation à des quiz.

## Vue d'ensemble

Ce projet est composé de deux parties principales :
- **estiam-back** : API backend en Symfony 6.3 et système de rendu de pages
- **estiam-front** : Interface utilisateur avec composants Vue.js

L'architecture hybride offre à la fois des points d'entrée API RESTful et un rendu côté serveur avec des composants Vue.js intégrés.

## Fonctionnalités

### Gestion des utilisateurs
- Inscription et connexion sécurisées
- Authentification par token JWT
- Gestion des sessions utilisateurs

### Gestion des quiz
- Création de quiz personnalisés
- Ajout de questions à choix multiples
- Modification et suppression de quiz
- Partage de quiz via des liens uniques

### Participation aux quiz
- Interface interactive pour répondre aux questions
- Soumission des réponses
- Affichage des résultats

### Statistiques et analyses
- Visualisation des statistiques de réponses
- Analyse des performances des quiz

## Architecture technique

### Backend (estiam-back)

#### Technologies
- PHP 8.1+
- Symfony 6.3
- Doctrine ORM
- Doctrine MongoDB ODM
- Lexik JWT Authentication Bundle
- Nelmio CORS Bundle
- Twig Template Engine

#### Structure des données
- **Entités relationnelles**
  - User : gestion des utilisateurs
  - Quizz : structure des quiz
  - Question : questions à choix multiples
  - Response : réponses des participants
  - Token : authentification par token

- **Documents MongoDB**
  - Logiciel : informations sur les logiciels liés au système
  - ComptoirDuLibreSoftware : données complémentaires

#### Points d'entrée API
- `/api/register` : inscription d'un utilisateur
- `/api/login` : connexion avec génération de token
- `/api/quizz` : création, consultation, modification et suppression de quiz
- `/api/quizz/{quizzId}/submit` : soumission de réponses
- `/api/user/{userId}/quizz/stats` : statistiques des quiz

#### Pages contrôlées par le serveur
- `/login`, `/register` : authentification
- `/homePage` : liste des quiz de l'utilisateur
- `/createQuizz` : création de quiz
- `/modifQuizz/{idQuizz}` : modification d'un quiz
- `/showQuizz/{idQuizz}` : affichage d'un quiz pour participation
- `/stats` : visualisation des statistiques

### Frontend (estiam-front)

#### Technologies
- Vue.js
- Axios pour les requêtes HTTP
- Vue Router pour la navigation
- Tailwind CSS pour les styles

#### Composants Vue.js
- **Authentification**
  - `Login` : formulaire de connexion
  - `Register` : formulaire d'inscription

- **Navigation et structure**
  - `Header` : navigation principale
  - `Welcome` : page d'accueil

- **Gestion des quiz**
  - `QuizzList` : liste des quiz de l'utilisateur
  - `CreateQuizz` : création de nouveaux quiz
  - `EditQuizz` : modification de quiz existants
  - `QuizzDetails` : affichage détaillé d'un quiz

- **Statistiques**
  - `Stats` : visualisation des données de participation

## Sécurité

- **Authentification**
  - Tokens JWT stockés dans des cookies sécurisés
  - Vérification par middleware à chaque requête
  - Expiration automatique des sessions

- **Protection CSRF**
  - Génération de tokens CSRF pour les actions sensibles
  - Validation côté serveur

- **Bonnes pratiques**
  - Hashage sécurisé des mots de passe
  - Purge automatique des tokens expirés
  - Protection CORS configurée

## Installation

### Prérequis
- PHP 8.1+
- Composer
- Node.js et npm/yarn
- Base de données PostgreSQL/MySQL
- MongoDB
- Serveur web (Apache, Nginx, ou Symfony CLI)

### Installation du backend (estiam-back)

1. Cloner le dépôt
```bash
git clone [URL_REPO]/estiam-back.git
cd estiam-back
```

2. Installer les dépendances
```bash
composer install
```

3. Configurer l'environnement
```bash
cp .env .env.local
# Éditer .env.local avec les paramètres de connexion aux bases de données
```

4. Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:create
```

5. Lancer le serveur
```bash
symfony serve
# ou
php -S localhost:8000 -t public
```

### Installation du frontend (estiam-front)

1. Cloner le dépôt
```bash
git clone [URL_REPO]/estiam-front.git
cd estiam-front
```

2. Installer les dépendances
```bash
npm install
# ou
yarn install
```

3. Configurer l'environnement
```bash
cp .env.example .env
# Éditer .env avec l'URL de l'API backend
```

4. Développement
```bash
npm run dev
# ou
yarn dev
```

5. Build pour production
```bash
npm run build
# ou
yarn build
```

## Déploiement

### Backend

1. Configurer l'environnement de production
```bash
composer install --no-dev --optimize-autoloader
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
```

2. Configurer le serveur web (Apache/Nginx) pour pointer vers le dossier `public/`

### Frontend

1. Build pour production
```bash
npm run build
```

2. Déployer le contenu du dossier `dist/` sur un serveur web statique

## Contribuer au projet

1. Forker le dépôt
2. Créer une branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commiter les changements (`git commit -m 'Ajout d'une nouvelle fonctionnalité'`)
4. Pousser vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrir une Pull Request

## Structure des fichiers

### Backend (estiam-back)
```
estiam-back/
├── bin/                  # Exécutables et commandes Symfony
├── config/               # Configuration de l'application
├── migrations/           # Migrations de base de données
├── public/               # Point d'entrée web (index.php)
├── src/                  # Code source
│   ├── Controller/       # Contrôleurs Symfony
│   ├── Entity/           # Entités relationnelles
│   ├── Document/         # Documents MongoDB
│   ├── EventListener/    # Écouteurs d'événements
│   ├── Middleware/       # Middlewares d'authentification
│   ├── Repository/       # Accès aux données
│   └── Kernel.php        # Noyau Symfony
├── templates/            # Templates Twig
│   ├── Authentification/ # Templates d'authentification
│   ├── PageQuizz/        # Templates de gestion des quiz
│   └── base.html.twig    # Template de base
├── tests/                # Tests automatisés
├── .env                  # Configuration d'environnement
└── composer.json         # Dépendances PHP
```

### Frontend (estiam-front)
```
estiam-front/
├── assets/               # Ressources statiques
├── config/               # Configuration
├── public/               # Ressources publiques statiques
├── src/                  # Code source Vue.js
│   ├── components/       # Composants Vue réutilisables
│   ├── views/            # Pages Vue
│   ├── router/           # Configuration des routes
│   ├── services/         # Services (API, etc.)
│   ├── store/            # Gestion d'état (si utilisé)
│   └── App.vue           # Composant racine
├── .env                  # Variables d'environnement
├── package.json          # Dépendances JavaScript
└── webpack.config.js     # Configuration de build
```
