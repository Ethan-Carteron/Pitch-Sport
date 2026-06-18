# Pitch_Sport

Pitch_Sport est une application de gestion sportive développée avec **Symfony 7.4** et **PHP 8.5+**. Elle permet d'assurer le suivi des joueurs, de leur bien-être, de leur charge de travail et d'analyser leurs performances ou statistiques, avec un système de notifications et d'import de données.

## 📖 À propos du projet (Contexte et Objectifs)

Ce projet a été initié pour répondre aux besoins de **suivi médico-sportif et d'optimisation de la performance** au sein d'un club (conçu dans le cadre du projet pour le Dijon FCO - DFCO). 
Dans le sport de haut niveau, la gestion de la charge d'entraînement (Workload) et du bien-être (Wellness) est cruciale pour **prévenir les blessures** (comme la surcharge musculaire) et **optimiser les pics de forme** des joueurs.

**Pitch_Sport** permet de centraliser ces données :
- Il numérise le ressenti quotidien des joueurs (via le RPE - *Rating of Perceived Exertion*).
- Il fournit au staff technique et médical des tableaux de bord et des indicateurs fiables (Monotonie, Contrainte, Ratio Aigu/Chronique).
- Il fluidifie la communication en alertant automatiquement le staff via Telegram lorsqu'un joueur présente un risque de blessure avéré.

## 🕹️ Guide d'utilisation (Comment l'utiliser ?)

L'application est pensée pour être utilisée conjointement par les **Joueurs** et le **Staff Technique/Médical**.

### 👤 Pour les Joueurs (Routine quotidienne)
1. **Connexion** : Le joueur se connecte à son espace personnel depuis son smartphone ou son ordinateur.
2. **Formulaire "Wellness" (Bien-être)** : Chaque matin, le joueur remplit un questionnaire rapide sur sa qualité de sommeil, son niveau de fatigue, son stress et ses douleurs musculaires.
3. **Formulaire "Workload" (Charge de travail)** : Après chaque séance d'entraînement ou match, le joueur évalue la difficulté de la séance (RPE) et renseigne sa durée.

### 📋 Pour le Staff (Analyse et Suivi)
1. **Tableau de bord (Dashboard / Stats)** : Le coach ou le préparateur physique se connecte pour consulter l'état global du groupe.
2. **Indicateurs de fatigue** : Le staff accède aux calculs statistiques qui mettent en surbrillance les joueurs en état de sous-entraînement ou de surmenage.
3. **Alertes Telegram** : Si le système détecte des valeurs critiques (baisse drastique du bien-être ou pic anormal de la charge de travail), une alerte est automatiquement envoyée sur l'application Telegram du staff pour une prise de décision rapide (ex: adapter l'entraînement du joueur).
4. **Importation de données (CSV)** : Le staff peut importer des historiques de données ou des logs de séances passées via l'outil d'import CSV.

## 🛠 Technologies et Stack

*   **Framework** : Symfony 7.4
*   **Langage** : PHP 8.5+
*   **Base de données** : ORM Doctrine (compatible MySQL / PostgreSQL)
*   **Serveur Web** : FrankenPHP & Caddy (via Docker) / Vercel PHP Runtime (en production)
*   **Frontend** : Twig, Symfony AssetMapper
*   **Outils tiers** :
    *   `league/csv` pour l'import de fichiers CSV.
    *   `symfony/telegram-notifier` pour les notifications Telegram.
    *   `symfony/security-bundle` pour l'authentification et l'autorisation.

## 📁 Architecture du projet

Le projet suit l'architecture standard d'une application Symfony, avec une logique métier bien séparée :

*   **`src/Entity/`** : Les modèles de données Doctrine.
    *   `Club`, `Player`, `User` : Gestion des acteurs du système.
    *   `WellnessQuestions`, `Workload` : Entités liées au suivi physique et mental des joueurs.
*   **`src/Controller/`** : Les points d'entrée HTTP.
    *   `Index`, `Calcul`, `Stat` : Consultation des données et calculs statistiques.
    *   `TeamManager`, `Player` : Gestion des équipes et joueurs.
    *   `Questionnaire` : Saisie des retours de bien-être.
    *   `Import` : Importation de données en masse.
    *   `Registration`, `Security` : Inscription et authentification des utilisateurs.
    *   `Telegram` : Intégration pour les alertes.
*   **`src/Service/`** : La logique métier déportée (Clean Code).
    *   `CalculService`, `PlayerService`, `CsvImportService`, `AlertAdviceService`.

## 🚀 Installation locale (avec Docker / FrankenPHP)

Le projet est préconfiguré pour fonctionner avec Docker et FrankenPHP, offrant des performances optimales et le HTTPS automatique en local.

### Prérequis
*   Docker et Docker Compose (v2.10+)

### Étapes
1. Clonez le dépôt et naviguez dans le dossier du projet :
   ```bash
   git clone <url-du-repo> Pitch_Sport
   cd Pitch_Sport
   ```
2. Installez et démarrez les conteneurs :
   ```bash
   docker compose build --pull --no-cache
   docker compose up -d
   ```
3. Installez les dépendances Composer (si non fait automatiquement par le conteneur) :
   ```bash
   docker compose exec php composer install
   ```
4. Configurez votre fichier `.env.local` (ou modifiez le `.env`) avec vos identifiants de base de données.
5. Créez la base de données et exécutez les migrations :
   ```bash
   docker compose exec php bin/console doctrine:database:create
   docker compose exec php bin/console doctrine:migrations:migrate
   ```
6. Accédez à l'application via [https://localhost](https://localhost) (Acceptez le certificat TLS auto-généré si votre navigateur vous avertit).

*Pour arrêter les conteneurs : `docker compose down --remove-orphans`*

## ☁️ Déploiement en production (Vercel)

Le projet est configuré pour être déployable sur la plateforme serverless **Vercel** grâce au fichier `vercel.json` présent à la racine.

*   **Runtime utilisé** : `vercel-php@0.8.0`
*   **Point d'entrée API** : Les requêtes PHP sont redirigées vers le sous-dossier `api/index.php`.
*   **Ressources statiques** : Gérées directement pour un chargement rapide depuis `/public/`.

Pour déployer :
1. Installez le CLI Vercel : `npm i -g vercel`
2. Lancez le déploiement depuis le dossier racine : `vercel` (ou connectez simplement votre repository GitHub/GitLab à Vercel).

## 📊 Fonctionnalités principales

1.  **Gestion d'utilisateurs et de rôles** : Authentification et sécurité gérées nativement.
2.  **Tableau de bord et suivi d'équipe** : Monitoring des joueurs (`Player`) et clubs (`Club`).
3.  **Suivi physiologique** : Formulaires quotidiens de santé (`WellnessQuestions`) et de charge d'entraînement (`Workload`).
4.  **Outils d'import** : Possibilité d'intégrer des données massives issues de fichiers CSV (`CsvImportService`).
5.  **Analyse et Statistiques** : Traitement des données physiologiques pour générer des alertes de fatigue et optimiser la performance (`CalculService`, `AlertAdviceService`).
6.  **Notifications** : Intégration Telegram pour alerter les coachs en temps réel si une charge de travail ou un état de fatigue atteint un seuil critique.

## 📄 Licence

Projet sous architecture de base Symfony. Se référer au fichier `LICENSE` pour plus de détails.
