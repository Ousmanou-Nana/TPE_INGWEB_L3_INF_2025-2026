# OthTime — Générateur d'Emploi du Temps Automatique

> **Fiche de Projet TPE** | Ingénierie des Applications Web   
> **Étudiant :** OUSMANOU NANA &nbsp;|&nbsp; **Matricule :** 22B707FS &nbsp;|&nbsp; **Enseignant :** M. Kotva/ Pr Dayang

---

## Sommaire

1. [Contexte et Objectif](#1-contexte-et-objectif)
2. [Règles de Génération](#2-règles-de-génération)
3. [Description de la Solution](#3-description-de-la-solution)
4. [Installation et Commandes](#4-installation-et-commandes)
5. [Structure du Projet](#5-structure-du-projet)
6. [Dépendances](#6-dépendances)
7. [Technologies Utilisées](#7-technologies-utilisées)
8. [Algorithme Utilisé](#8-algorithme-utilisé)
9. [Diagrammes](#9-diagrammes)
10. [Jeux de Données](#10-jeux-de-données)
11. [Tests et Performances](#11-tests-et-performances)
12. [Résultats Attendus](#12-résultats-attendus)
13. [Limites du Système](#13-limites-du-système)
14. [Améliorations Possibles](#14-améliorations-possibles)
15. [Conclusion](#15-conclusion)

---

## 1. Contexte et Objectif

### Objectif du projet

Construire une application web capable de **générer automatiquement des emplois du temps** selon les préférences des enseignants, en utilisant un algorithme de recuit simulé pour optimiser la planification sous contraintes multiples.

### Problème traité

La planification manuelle des emplois du temps est **chronophage**, sujette aux **erreurs** et difficile à optimiser pour satisfaire les contraintes multiples des enseignants et des salles. Cela entraîne des conflits horaires et une inefficacité dans l'organisation scolaire.

### Acteurs du système

| Rôle | Accès | Responsabilités |
|---|---|---|
| **Administrateur** | `/admin/*` | Gère enseignants, matières, classes, salles, assignations. Lance la génération des EDT. |
| **Enseignant** | `/teacher/*` | Saisit ses disponibilités (score −5 à +5). Consulte son EDT généré. |

---

## 2. Règles de Génération

Les contraintes suivantes sont appliquées pour évaluer et scorer les emplois du temps :

| Règle | Description | Impact sur le score |
|---|---|---|
| **R1** — Pas de chevauchement | Aucun enseignant, salle ou classe ne peut être planifié deux fois au même créneau | −1 000 par conflit |
| **R2** — Disponibilités | Respect des disponibilités déclarées par les enseignants | Score de −5 à +5 |
| **R3** — Préférences horaires | Priorité aux préférences (matinée, après-midi) | +3 bonus si enseignant satisfait |
| **R4** — Trous horaires | Minimisation des créneaux vides entre deux cours dans la même journée | −10 par trou |
| **R5** — Répartition équilibrée | Variance du nombre de cours par jour inférieure à 2 | +5 bonus |

---

## 3. Description de la Solution

L'application collecte les données via un formulaire web, applique un **algorithme de recuit simulé** pour générer un emploi du temps optimal, puis l'affiche avec un code couleur indiquant la satisfaction des préférences enseignants.

Le processus inclut la validation automatique des contraintes et la comparaison avec l'EDT actif pour n'activer que le meilleur résultat.

---

## 4. Installation et Commandes

### Prérequis

- [Docker](https://www.docker.com/) (version 20+)
- Docker Compose (v2)

### Configuration — fichier `.env`

Créer un fichier `.env` à la racine du projet :

```env
APACHE_PORT=8080
MYSQL_HOST=mysql
MYSQL_PORT=3306
MYSQL_DATABASE=othtime
MYSQL_USER=othtime_user
MYSQL_PASSWORD=secret
MYSQL_ROOT_PASSWORD=root_secret
```

### Commandes Docker

| Commande | Description |
|---|---|
| `docker compose up --build -d` | **Première fois** — Construit les images et démarre tous les conteneurs en arrière-plan |
| `docker compose up -d` | Démarre les conteneurs existants (sans recompilation) |
| `docker compose down` | Arrête et supprime les conteneurs (les données MySQL sont préservées) |
| `docker compose down -v` | Arrête tout et **supprime les volumes** MySQL (reset complet) |
| `docker compose logs -f web` | Affiche les logs du serveur web en temps réel |
| `docker compose ps` | Affiche l'état de tous les conteneurs |
| `docker compose exec mysql mysql -u othtime_user -p othtime` | Accède directement à la base de données MySQL |

### Accès à l'application

Une fois les conteneurs démarrés :

- **URL :** `http://localhost:8080`
- **Compte admin (seeds) :** `admin@ecole.fr` / `password`
- **Compte enseignant (seeds) :** `prof@ecole.fr` / `password`

---

## 5. Structure du Projet

```
.
├── app/
│   ├── controllers/
│   │   ├── AdminController.php       # Gestion admin complète (CRUD + génération)
│   │   ├── AuthController.php        # Authentification (login / logout)
│   │   └── TeacherController.php     # Espace enseignant (préférences + EDT)
│   ├── models/
│   │   └── TimetableGenerator.php    # Algorithme de recuit simulé
│   └── views/
│       ├── admin/
│       │   ├── assignments.php       # Assignations enseignant ↔ matière / classe ↔ matière
│       │   ├── classes.php           # Gestion des classes
│       │   ├── dashboard.php         # Tableau de bord avec statistiques
│       │   ├── generate.php          # Interface de lancement de génération
│       │   ├── rooms.php             # Gestion des salles
│       │   ├── subjects.php          # Gestion des matières
│       │   ├── teachers.php          # Gestion des enseignants
│       │   └── timetable.php         # Visualisation des EDT par classe
│       ├── auth/
│       │   └── login.php             # Page de connexion
│       ├── layout_top.php            # En-tête commun (nav + sidebar)
│       ├── layout_bottom.php         # Pied de page commun
│       └── teacher/
│           └── preferences.php       # Grille de saisie des préférences (sliders −5 à +5)
├── config/
│   └── database.php                  # Configuration PDO (connexion MySQL)
├── core/
│   ├── Database.php                  # Couche d'abstraction base de données
│   └── Router.php                    # Routeur HTTP simple (dispatch par URI)
├── database/
│   ├── schema.sql                    # Définition des tables (DDL)
│   └── seeds.sql                     # Données initiales (admin + exemples)
├── docker-compose.yml                # Orchestration Docker (web + mysql)
├── .env                              # Variables d'environnement (à créer)
└── public/
    ├── assets/
    │   └── style.css                 # Feuille de style globale (variables CSS, thème)
    └── index.php                     # Point d'entrée unique (front controller)
```

---

## 6. Dépendances

### Conteneurs Docker

| Service | Image | Rôle |
|---|---|---|
| `othTime_web` | `php:8.2-apache` | Serveur web Apache + PHP 8.2 avec extensions `pdo_mysql` et `mod_rewrite` |
| `othTime_mysql` | `mysql:8.0` | Base de données relationnelle — initialisée automatiquement avec `schema.sql` et `seeds.sql` |

### Extensions PHP installées automatiquement

- `pdo` — couche d'abstraction base de données
- `pdo_mysql` — driver MySQL pour PDO

### Modules Apache activés automatiquement

- `mod_rewrite` — réécriture d'URL pour le routeur front-controller

### Aucune dépendance Composer

Le projet est volontairement **sans framework ni Composer**. Toute la logique est écrite en PHP natif.

---

## 7. Technologies Utilisées

| Technologie | Version | Rôle |
|---|---|---|
| ![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white) **Docker** | 20+ | Conteneurisation et isolation de l'environnement |
| ![Docker Compose](https://img.shields.io/badge/Docker_Compose-2496ED?logo=docker&logoColor=white) **Docker Compose** | v2 | Orchestration des services web et base de données |
| ![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white) **PHP** | 8.2 | Langage serveur — logique métier, MVC, algorithme |
| ![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white) **MySQL** | 8.0 | Base de données relationnelle |
| ![Apache](https://img.shields.io/badge/Apache-D22128?logo=apache&logoColor=white) **Apache** | 2.4 | Serveur HTTP — routage via mod_rewrite |
| ![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white) **HTML5** | — | Structure des vues côté client |
| ![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white) **CSS3** | — | Mise en forme — variables CSS, grilles, thème |
| ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black) **JavaScript** | ES6+ | Interactivité — sliders, toggle, AJAX pour la génération |

---

## 8. Algorithme Utilisé

### Recuit Simulé (Simulated Annealing)

> Référence : [Wikipedia — Simulated Annealing](https://en.wikipedia.org/wiki/Simulated_annealing)

Le recuit simulé est une méthode d'optimisation **métaheuristique** inspirée du processus physique de recuit des métaux. Il permet de résoudre efficacement des problèmes d'optimisation combinatoire complexes avec de nombreuses contraintes, comme la planification d'emplois du temps.

> **Note :** La fiche initiale mentionnait un "algorithme génétique" — le code source réel (`TimetableGenerator.php`) implémente bien du **recuit simulé** (`SA_INITIAL_TEMP`, `SA_COOLING_RATE`, `SA_RESTARTS`).

### Paramètres de l'implémentation

| Paramètre | Valeur |
|---|---|
| Température initiale (`SA_INITIAL_TEMP`) | `10 000` |
| Taux de refroidissement (`SA_COOLING_RATE`) | `× 0,995` par étape |
| Température minimale (`SA_MIN_TEMP`) | `0,1` |
| Redémarrages indépendants (`SA_RESTARTS`) | `50` |

### Étapes de fonctionnement

```
1. GÉNÉRATION INITIALE ALÉATOIRE
   └─ Placement aléatoire de tous les cours (classe × matière × heures/semaine)
      en choisissant un enseignant habilité et une salle adaptée à l'effectif.

2. CALCUL DU SCORE (fitness)
   └─ Score = Σ(préférences) + bonus satisfaction
              − pénalités conflits − pénalités trous + bonus répartition

3. BOUCLE DE RECUIT (× 50 redémarrages indépendants)
   ├─ Perturbation aléatoire (voisinage) :
   │   · Type 0 : déplacer un cours vers un créneau aléatoire
   │   · Type 1 : échanger les créneaux de deux cours
   │   · Type 2 : changer l'enseignant assigné à un cours
   ├─ Si ΔScore > 0 → accepter toujours
   └─ Si ΔScore ≤ 0 → accepter avec probabilité e^(Δ/T)
      (permet d'échapper aux optima locaux)

4. REFROIDISSEMENT
   └─ T = T × 0,995 jusqu'à T < 0,1
      Arrêt anticipé si score > 0 et 0 conflit.

5. SÉLECTION DU MEILLEUR
   └─ Meilleur résultat parmi les 50 redémarrages conservé.
      Comparaison avec l'EDT actif → activation automatique du meilleur.
```

### Règles de scoring détaillées

| Critère | Points |
|---|---|
| Préférence enseignant pour le créneau | −5 à +5 |
| Bonus si préférence positive | +3 |
| Conflit enseignant (double réservation) | −1 000 |
| Conflit salle (double réservation) | −1 000 |
| Conflit classe (double réservation) | −1 000 |
| Trou horaire dans la journée | −10 |
| Répartition équilibrée (variance < 2) | +5 |

---

## 9. Diagrammes

### 9.1 Diagramme de Cas d'Utilisation

```
┌─────────────────────────────────────────────────────────────┐
│                    Système OthTime                          │
│                                                             │
│  ┌──────────────────────┐    ┌───────────────────────────┐  │
│  │    Administrateur    │    │       Enseignant          │  │
│  ├──────────────────────┤    ├───────────────────────────┤  │
│  │ • Se connecter       │    │ • Se connecter            │  │
│  │ • Gérer enseignants  │    │ • Saisir disponibilités   │  │
│  │ • Gérer matières     │    │   (scores −5 à +5)        │  │
│  │ • Gérer classes      │    │ • Modifier ses préférences│  │
│  │ • Gérer salles       │    │ • Consulter son EDT       │  │
│  │ • Gérer assignations │    └───────────────────────────┘  │
│  │ • Lancer génération  │                                   │
│  │ • Consulter EDT      │                                   │
│  │ • Supprimer génération│                                  │
│  └──────────────────────┘                                   │
└─────────────────────────────────────────────────────────────┘
```

### 9.2 Diagramme de Séquence — Génération d'un EDT

```
Admin          Frontend          Router          TimetableGenerator        BDD
  │                │                │                    │                  │
  │ Clic "Générer" │                │                    │                  │
  │───────────────>│                │                    │                  │
  │                │ POST /run-gen  │                    │                  │
  │                │───────────────>│                    │                  │
  │                │                │ dispatch()         │                  │
  │                │                │───────────────────>│                  │
  │                │                │                    │ loadData()       │
  │                │                │                    │─────────────────>│
  │                │                │                    │<─────────────────│
  │                │                │                    │ buildSlots()     │
  │                │                │                    │──────┐           │
  │                │                │                    │<─────┘           │
  │                │                │                    │ runSA() ×50      │
  │                │                │                    │──────┐           │
  │                │                │                    │<─────┘           │
  │                │                │                    │ INSERT generation│
  │                │                │                    │─────────────────>│
  │                │                │                    │ INSERT timetable  │
  │                │                │                    │─────────────────>│
  │                │                │ JSON response      │                  │
  │                │<───────────────│                    │                  │
  │ Résultat affiché               │                    │                  │
  │<───────────────│                │                    │                  │
```

### 9.3 Schéma de Base de Données

```
                    ┌───────────────────────────────┐
                    │             users             │
                    ├───────────────────────────────┤
                    │ id            INT  PK AI       │
                    │ nom           VARCHAR(100)     │
                    │ email         VARCHAR(150) UQ  │
                    │ mot_de_passe  VARCHAR(255)     │
                    │ role          ENUM(admin,      │
                    │               teacher)         │
                    │ created_at    TIMESTAMP        │
                    └──────────────┬────────────────┘
                                   │ 
                                   │(UNIQUE — CASCADE)
                                   │   
                    ┌──────────────┴────────────────┐
                    │           teachers            │
                    ├───────────────────────────────┤
                    │ id       INT  PK AI            
                    │ user_id  INT  FK UQ → users   │
                    └──────┬────┬─────┬─────────────┘
                           │    |     │
              ┌────────────┘    |     └──────────────────┐
              │                 |                        │
              │                 |                        │
┌─────────────┴──────────────┐  |       ┌────────────────┴──────────┐
│      teacher_subject       │  |       │        preferences        │
├────────────────────────────┤  |       ├───────────────────────────┤
│ teacher_id  INT FK PK      |  |       │ id          INT  PK AI    │
│ subject_id  INT FK PK      │  │       │ teacher_id  INT  FK       │
└────────────┬───────────────┘  │       │             → teachers    │
             │                  │       │ jour        TINYINT(1-5)  │
             │                  │       │ periode     TINYINT(1-6)  │
┌────────────┴───────────────┐  │       │ score       TINYINT(-5/+5)│
│          subjects          │  │       │ UQ(teacher_id,jour,period)│
├────────────────────────────┤  │       └───────────────────────────┘
│ id               INT PK AI │  │
│ nom              VARCHAR   │  │
│ couleur          VARCHAR(7)│  │
│ heures_par_semaine  INT    │  │
└────────────┬───────────────┘  │
             │                  │
             │                  │
┌────────────┴───────────────┐  │
│       class_subject        │  │
├────────────────────────────┤  │
│ class_id   INT FK PK       │  │
│ subject_id INT FK PK       |  │
│ heures_par_semaine  INT    │  │
└────────────┬───────────────┘  │
             │                  │
             │                  │
┌────────────┴───────────────┐  │
│          classes           │  │
├────────────────────────────┤  │
│ id        INT  PK AI       │  │
│ nom       VARCHAR(50)      │  │
│ effectif  INT              │  │
└────────────────────────────┘  │
                                │
 ┌──────────────────────────────┘  
 │
 │   ┌───────────────────────────────────────────────────────────────┐
 │   │               timetable_generations                           │
 │   ├───────────────────────────────────────────────────────────────┤
 │   │ id           INT  PK AI                                       │
 │   │ nom          VARCHAR(100)                                     │
 │   │ score_total  INT                                              │
 │   │ nb_conflits  INT                                              │
 │   │ is_active    TINYINT                                          │
 │   │ created_at   TIMESTAMP                                        │
 │   └───────────────────────┬───────────────────────────────────────┘
 │                           │ 
 │                           │
 │                           │ 
 │   ┌───────────────────────┴──────────────────────────────────────┐
 │   │                      timetable                               │
 │   ├──────────────────────────────────────────────────────────────┤
 └───|  teacher_id  INT  FK → teachers                              │
     │  generation_id INT FK → timetable_generations                │
     │  class_id    INT  FK → classes                               │
     │  subject_id  INT  FK → subjects                              │
     │  room_id     INT  FK → rooms                                 │
     │  jour        TINYINT (1–5)                                   │
     │  periode     TINYINT (1–6)                                   │
     │  id          INT  PK AI                                      │
     └──────────────────────────────────┬───────────────────────────┘
                                        │ 
                                        │
                           ┌────────────┴────────────┐
                           │          rooms          │
                           ├─────────────────────────┤
                           │ id        INT  PK AI    │
                           │ nom       VARCHAR(50)   │
                           │ capacite  INT           │
                           └─────────────────────────┘

Légende :  PK = Clé primaire   FK = Clé étrangère
           AI = Auto-increment  UQ = Unique
           CASCADE = suppression en cascade
```

--- 

## 10. Jeux de Données

### Exemple de configuration typique

| Entité | Données | Contraintes |
|---|---|---|
| Enseignant A | Mme. Martin — Mathématiques | Disponible lundi-mercredi matin (score +4). Refuse vendredi après-midi (score −3). |
| Enseignant B | M. Dupont — Physique & Chimie | Préfère les matinées (score +3). Neutre sur les après-midis (score 0). |
| Matière : Algèbre | 3h/semaine | Enseignant : Mme. Martin. Classe : 3ème A (28 élèves). |
| Matière : Physique | 2h/semaine | Enseignant : M. Dupont. Classe : 3ème A (28 élèves). |
| Salle 101 | Capacité : 30 places | Convient pour la classe 3ème A. |
| Salle Labo | Capacité : 24 places | Convient pour les groupes de TP (max 24 élèves). |
| Classe 3ème A | 28 élèves | Mathématiques : 3h, Physique : 2h/semaine. |

### Créneaux disponibles

| Période | Horaire |
|---|---|
| 1 | 8h–9h |
| 2 | 9h–10h |
| 3 | 10h–11h |
| 4 | 11h–12h |
| 5 | 14h–15h |
| 6 | 15h–16h |

Les jours sont numérotés de 1 (Lundi) à 5 (Vendredi).

---

## 11. Tests et Performances

### Cas de tests

| # | Cas de test | Condition | Résultat attendu |
|---|---|---|---|
| T1 | Génération sans conflit | 2 classes, 3 matières, 2 salles, préférences définies | EDT valide, 0 conflit, score > 0 |
| T2 | Ressources insuffisantes | 5 classes, 1 seule salle disponible | Conflits de salle signalés (badge rouge) |
| T3 | Respect des préférences | Enseignant score +5 sur lundi 8h | Cours placé lundi 8h si possible |
| T4 | Connexion admin | Email + mot de passe valides | Redirection vers `/admin/dashboard` |
| T5 | Connexion enseignant | Email + mot de passe valides | Redirection vers `/teacher/preferences` |
| T6 | Accès non autorisé | Enseignant tente `/admin/teachers` | Redirection vers `/login` |
| T7 | Sauvegarde préférences | Enseignant modifie les sliders | Confirmation + données persistées en BDD |
| T8 | Suppression génération | Admin supprime un EDT archivé | Suppressions en cascade dans `timetable` |

### Résultats de performance

| Scénario | Résultat observé |
|---|---|
| 1 classe, 3 matières, 2 salles | < 1 seconde |
| 5 classes, 8 matières, 4 salles | 2 à 4 secondes |
| Taux de succès sans conflit (100 simulations) | ~90 % |
| Taux avec préférences bien configurées | ~95 % |
| Temps de génération pour 5 jours d'EDT | < 5 secondes |

---

## 12. Résultats Attendus

- [x] **Génération automatique** d'emploi du temps en quelques secondes
- [ ] **Respect des contraintes** définies (0 conflit dans 90–95% des cas)
- [x] **Prise en compte des préférences** enseignants avec affichage visuel (couleurs)
- [x] **Interface simple** : tableau de bord admin, sliders de préférences, visualisation colorée par classe
- [x] **Comparaison automatique** des générations — activation du meilleur EDT
- [x] **Impression** de l'EDT directement depuis le navigateur

---

## 13. Limites du Système

| Limitation | Impact |
|---|---|
| Pas de gestion des changements d'urgence | Toute modification nécessite une régénération complète |
| Plages horaires fixes (8h–12h, 14h–16h) | Impossible de définir des créneaux personnalisés |
| Pas d'export PDF/Excel natif | Seule l'impression navigateur est disponible |
| Pas de reset de mot de passe par email | L'administrateur doit intervenir manuellement |
| Un seul établissement par instance | Pas de gestion multi-sites |
| Performance sur gros volumes | Au-delà de 20 classes, le temps peut dépasser 30 secondes |

---

## 14. Améliorations Possibles

- **Export PDF / Excel** — Téléchargement de l'EDT au format PDF ou Excel pour distribution hors ligne
- **Notifications par email** — Envoi automatique de l'EDT aux enseignants lors d'une nouvelle activation
- **Gestion des remplacements** — Interface pour signaler une absence et proposer un remplaçant disponible
- **Interface mobile responsive** — Adaptation de l'affichage pour smartphones et tablettes
- **Multi-établissements** — Support de plusieurs établissements avec isolation des données
- **Algorithme génétique en complément** — Comparaison avec le recuit simulé sur des volumes importants
- **Historique des préférences** — Conservation par semestre pour analyser les tendances
- **Authentification renforcée** — Reset de mot de passe par email, sessions sécurisées

---

## 15. Conclusion

Ce projet a permis de développer une **application web fonctionnelle** pour la génération automatique d'emplois du temps scolaires, en s'appuyant sur des technologies modernes et un algorithme d'optimisation efficace.

L'utilisation du **recuit simulé** avec 50 redémarrages indépendants et un scoring multicritère permet d'obtenir des emplois du temps satisfaisants en quelques secondes, même sur des configurations complexes avec plusieurs classes, enseignants et salles.

L'architecture **MVC** (contrôleurs PHP, modèles, vues séparées) et la conteneurisation via **Docker** garantissent la portabilité et la maintenabilité du projet.

> Ce projet démontre l'application pratique des concepts d'ingénierie web (MVC, Docker, BDD relationnelle) combinés à l'optimisation algorithmique dans un contexte éducatif concret.

---

*OUSMANOU NANA — 22B707FS | Ingénierie des Applications Web — 2025-2026*