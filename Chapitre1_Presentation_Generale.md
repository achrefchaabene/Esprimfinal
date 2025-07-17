# CHAPITRE 1 : PRÉSENTATION GÉNÉRALE DU PROJET

## 1.1 Introduction

Dans un contexte économique en constante évolution et face aux défis du marché de l'emploi en Tunisie, les demandeurs d'emploi et les entreprises rencontrent des difficultés croissantes pour se connecter efficacement. Les méthodes traditionnelles de recherche d'emploi et de recrutement montrent leurs limites dans un environnement digitalisé où la rapidité, l'efficacité et la personnalisation sont devenues essentielles.

C'est dans cette perspective que s'inscrit notre projet de fin d'études : **ESPRIM CAREER**, une plateforme web innovante dédiée à la mise en relation entre candidats et entreprises en Tunisie.

## 1.2 Problématique

### 1.2.1 Défis pour les demandeurs d'emploi

- **Dispersion des offres d'emploi** : Les opportunités sont éparpillées sur différentes plateformes, rendant la recherche inefficace
- **Manque de préparation aux entretiens** : Absence d'outils d'aide à la préparation et d'entraînement personnalisé
- **Difficulté de suivi des candidatures** : Manque de traçabilité et de gestion centralisée des applications
- **Accès limité aux informations sur les entreprises** : Méconnaissance de la culture d'entreprise et des opportunités réelles

### 1.2.2 Défis pour les entreprises

- **Processus de recrutement chronophage** : Tri manuel des candidatures et gestion dispersée
- **Difficulté à atteindre les bons candidats** : Canaux de diffusion limités et ciblage imprécis
- **Manque de visibilité** : Difficulté à promouvoir leur marque employeur et leurs valeurs
- **Communication fragmentée** : Absence d'outils de communication directe avec les candidats

### 1.2.3 Problème central

Comment créer un écosystème numérique qui facilite la rencontre entre l'offre et la demande d'emploi tout en offrant des outils modernes de préparation professionnelle et de communication ?

## 1.3 Solution proposée

### 1.3.1 Concept général

**ESPRIM CAREER** est une plateforme web complète qui révolutionne l'approche traditionnelle du recrutement en Tunisie. Elle combine :

- **Un portail d'emplois intelligent** avec système de recherche avancée
- **Des outils d'IA pour l'assistance** avec chatbot spécialisé (Tchala)
- **Un système de préparation aux entretiens** avec questions comportementales et défis techniques
- **Une messagerie intégrée** pour faciliter la communication
- **Des profils d'entreprise enrichis** pour une meilleure transparence

### 1.3.2 Fonctionnalités clés

#### Pour les demandeurs d'emploi :
- Recherche et filtrage intelligent des offres
- Système de candidature simplifié
- Outils de préparation aux entretiens avec IA
- Suivi personnalisé des candidatures
- Messagerie directe avec les recruteurs
- Génération de cartes de visite professionnelles

#### Pour les entreprises :
- Publication et gestion des offres d'emploi
- Consultation des candidatures avec filtres avancés
- Communication directe avec les candidats
- Gestion du processus de recrutement
- Tableau de bord analytique

#### Administration :
- Gestion des utilisateurs et modération
- Validation des offres d'emploi
- Statistiques et analyses de la plateforme

## 1.4 Objectifs du projet

### 1.4.1 Objectif principal

Développer une plateforme web moderne et efficace qui optimise le processus de recrutement en Tunisie en connectant de manière intelligente les demandeurs d'emploi et les entreprises.

### 1.4.2 Objectifs spécifiques

#### Objectifs techniques :
- Concevoir une architecture web robuste basée sur le framework Symfony
- Implémenter une interface utilisateur moderne et responsive
- Intégrer des technologies d'intelligence artificielle (Gemini API)
- Développer un système de communication temps réel
- Assurer la sécurité et la protection des données

#### Objectifs fonctionnels :
- Simplifier le processus de recherche d'emploi
- Optimiser la gestion des candidatures pour les entreprises
- Fournir des outils d'aide à la préparation professionnelle
- Faciliter la communication entre candidats et recruteurs
- Créer un écosystème transparent et efficace

#### Objectifs pédagogiques :
- Maîtriser le développement web avec Symfony 7.2
- Appliquer les principes du modèle MVC
- Intégrer des APIs externes et des services web
- Gérer un projet de développement complet
- Acquérir une expérience en gestion de base de données

## 1.5 Étude d'existence

### 1.5.1 Analyse du marché existant

#### Plateformes internationales :
- **LinkedIn** : Réseau professionnel global mais pas spécialisé emploi local
- **Indeed** : Moteur de recherche d'emploi international
- **Glassdoor** : Avis d'entreprises et offres d'emploi

#### Plateformes locales tunisiennes :
- **Tanitjobs** : Portail d'emploi tunisien traditionnel
- **Emploi.nat.tn** : Plateforme gouvernementale
- **Bayt.com** : Plateforme régionale MENA

### 1.5.2 Analyse comparative

| Critère | Plateformes existantes | ESPRIM CAREER |
|---------|------------------------|---------------|
| IA intégrée | ❌ Limitée | ✅ Chatbot spécialisé |
| Préparation entretiens | ❌ Absente | ✅ Outils dédiés |
| Communication directe | ⚠️ Basique | ✅ Messagerie avancée |
| Focus Tunisie | ⚠️ Partiel | ✅ Spécialisée |
| Interface moderne | ⚠️ Variable | ✅ Design contemporain |

### 1.5.3 Avantages concurrentiels

- **Spécialisation géographique** : Focus exclusif sur le marché tunisien
- **Intelligence artificielle** : Assistant virtuel pour conseils personnalisés
- **Préparation complète** : Outils d'entraînement aux entretiens
- **Communication fluide** : Système de messagerie intégré
- **Expérience utilisateur** : Interface moderne et intuitive

## 1.6 Processus de développement - Architecture MVC

### 1.6.1 Choix du modèle MVC

Le projet ESPRIM CAREER adopte l'architecture **Modèle-Vue-Contrôleur (MVC)** via le framework **Symfony 7.2**. Ce choix est motivé par :

- **Séparation des responsabilités** : Code organisé et maintenable
- **Réutilisabilité** : Composants modulaires et extensibles
- **Collaboration efficace** : Développement parallèle des composants
- **Évolutivité** : Architecture scalable pour futures fonctionnalités

### 1.6.2 Implémentation MVC dans Symfony

#### Modèle (Model) - Couche Données
```
src/Entity/
├── User.php              # Gestion des utilisateurs
├── Job.php               # Offres d'emploi
├── Application.php       # Candidatures
├── Interview.php         # Entretiens
├── Conversation.php      # Conversations
└── Message.php           # Messages
```

**Responsabilités** :
- Définition de la structure des données
- Relations entre entités
- Logique métier et validation
- Interaction avec la base de données via Doctrine ORM

#### Vue (View) - Couche Présentation
```
templates/
├── base.html.twig        # Template de base
├── job_seeker/           # Vues demandeurs d'emploi
├── entreprise/           # Vues entreprises
├── admin/                # Interface administration
└── chatbot/              # Interface chatbot
```

**Responsabilités** :
- Affichage des données utilisateur
- Interface utilisateur responsive
- Templates Twig réutilisables
- Gestion des formulaires

#### Contrôleur (Controller) - Couche Logique
```
src/Controller/
├── JobSeekerController.php      # Logique demandeurs
├── EntrepriseController.php     # Logique entreprises
├── ChatbotController.php        # Gestion IA
├── ConversationController.php   # Communication
└── AdminController.php          # Administration
```

**Responsabilités** :
- Traitement des requêtes HTTP
- Coordination entre Modèle et Vue
- Gestion de la logique applicative
- Routage et navigation

### 1.6.3 Services et composants additionnels

#### Services métier
```
src/Service/
├── GeminiApiService.php    # Intégration IA
├── EmailService.php        # Gestion emails
└── FileUploadService.php   # Upload fichiers
```

#### Sécurité
```
src/Security/
└── UserAuthenticator.php   # Authentification
```

### 1.6.4 Flux de données MVC

1. **Requête utilisateur** → Routeur Symfony
2. **Routeur** → Contrôleur approprié
3. **Contrôleur** → Services métier et Entités
4. **Entités** → Base de données (via Doctrine)
5. **Contrôleur** → Template Twig
6. **Vue** → Réponse HTML/JSON

## 1.7 Technologies utilisées

### 1.7.1 Backend
- **PHP 8.2+** : Langage de programmation principal
- **Symfony 7.2** : Framework web MVC
- **Doctrine ORM** : Mapping objet-relationnel
- **MySQL** : Système de gestion de base de données

### 1.7.2 Frontend
- **Twig** : Moteur de templates
- **Stimulus** : Framework JavaScript léger
- **Turbo** : Navigation rapide SPA-like
- **Bootstrap** : Framework CSS responsive

### 1.7.3 APIs et services externes
- **Gemini API** : Intelligence artificielle pour chatbot
- **Mercure** : Communication temps réel
- **Symfony Mailer** : Gestion des emails

### 1.7.4 Outils de développement
- **Webpack Encore** : Build assets
- **PHPUnit** : Tests unitaires
- **Docker** : Containerisation
- **Git** : Versioning et collaboration

## 1.8 Structure organisationnelle

### 1.8.1 Types d'utilisateurs

1. **Demandeurs d'emploi** : Recherche et candidature aux offres
2. **Entreprises** : Publication d'offres et recrutement
3. **Administrateurs** : Gestion et modération de la plateforme

### 1.8.2 Modules principaux

- **Module Authentification** : Inscription, connexion, gestion profils
- **Module Emploi** : Gestion offres, candidatures, recherche
- **Module Communication** : Messagerie, notifications
- **Module IA** : Chatbot, assistance personnalisée
- **Module Administration** : Gestion utilisateurs, modération

## 1.9 Conclusion

Le projet ESPRIM CAREER représente une solution innovante aux défis du marché de l'emploi tunisien. En combinant les avantages d'une architecture MVC robuste avec des technologies modernes et l'intelligence artificielle, la plateforme offre une expérience utilisateur optimale pour tous les acteurs du recrutement.

L'adoption du framework Symfony garantit un développement structuré, maintenable et évolutif, tandis que l'intégration d'outils d'IA modernise l'approche traditionnelle de la recherche d'emploi et du recrutement.

Ce premier chapitre pose les fondements théoriques et techniques du projet. Les chapitres suivants détailleront l'analyse fonctionnelle, la conception technique, l'implémentation et les tests de cette solution.