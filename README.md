# api-campus-event

## Description

`api-campus-event` est une API REST Laravel 13 qui gère les événements organisés sur un campus : ateliers, conférences, projections ou rencontres sportives. Chaque événement est créé par un utilisateur, se déroule dans un lieu du campus et porte un ou plusieurs thèmes. L'API est en lecture publique et réserve l'écriture au propriétaire de l'événement, authentifié par un jeton Sanctum. Le dépôt est livré avec le tag `v1.0`.

## Les quatre rôles

| Rôle | Modèle | Relation |
|---|---|---|
| Propriétaire | `User` | `hasMany(CampusEvent)` : un organisateur crée ses événements |
| Ressource principale | `CampusEvent` | `belongsTo(User)`, `belongsTo(Venue)`, `belongsToMany(Topic)` |
| Ressource parente | `Venue` | `hasMany(CampusEvent)` ; lecture seule, avec un slug unique |
| Étiquette | `Topic` | `belongsToMany(CampusEvent)` via la table pivot `campus_event_topic` |

La table `campus_events` contient sept colonnes métier : `title`, `slug`, `description`, `starts_at`, `ends_at`, `capacity` et `is_public`. `capacity` est validée avec `min:1` ; `starts_at`, `ends_at` et `is_public` sont déclarées dans `casts()`. Les clés étrangères utilisent `foreignId()->constrained()` et la table pivot possède un index unique composé avec timestamps.

## Installation

Le projet utilise SQLite : aucune configuration de base de données n'est à faire.

### Bash

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

### PowerShell

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

Si Artisan demande de créer `database/database.sqlite`, répondez `yes`. Le serveur écoute sur `http://localhost:8000`. Aucun npm n'est nécessaire : le projet n'a pas de pages.

Le seeder crée 2 utilisateurs, 3 lieux, 5 thèmes et 20 événements, chacun avec au moins un thème.

## Comptes de test

Les deux comptes créés par le seeder ont le mot de passe `password` :

| Nom | E-mail | Usage |
|---|---|---|
| Claire Fontaine | `organisateur1@campus.test` | propriétaire des événements impairs et des événements qu'elle crée dans Postman |
| Roland Marchand | `organisateur2@campus.test` | autre propriétaire : reçoit 403 sur les événements de Claire |

## Endpoints

Toutes les réponses sont JSON et les requêtes doivent envoyer `Accept: application/json`.

| Méthode | URI | Accès | Description |
|---|---|---|---|
| POST | `/api/login` | public | Renvoie un jeton Sanctum ; 422 si les identifiants sont invalides |
| POST | `/api/logout` | jeton | Révoque le jeton courant ; 401 sans jeton |
| GET | `/api/campus-events` | public | Liste paginée, filtrable avec `?venue=<slug>` |
| GET | `/api/campus-events/{event}` | public | Un événement ; 404 si inexistant |
| POST | `/api/campus-events` | jeton | Crée un événement appartenant à l'utilisateur connecté |
| PUT/PATCH | `/api/campus-events/{event}` | jeton + Policy | Modifie un événement ; propriétaire uniquement |
| DELETE | `/api/campus-events/{event}` | jeton + Policy | Supprime un événement ; propriétaire uniquement |
| GET | `/api/venues` | public | Lieux avec `campus_events_count` |
| GET | `/api/venues/{venue}` | public | Un lieu avec `campus_events_count` ; 404 si inexistant |
| GET | `/api/user` | jeton | Utilisateur associé au jeton |

Exemple : `GET /api/campus-events?venue=amphi-a&per_page=5`. Le paramètre `per_page` est accepté de 1 à 50 (5 par défaut).

## Authentification par jeton

`POST /api/login` accepte `email` et `password` et renvoie :

```json
{
  "token": "...",
  "token_type": "Bearer"
}
```

Envoyez ensuite l'en-tête `Authorization: Bearer <jeton>` sur `/api/user`, `/api/logout` et les écritures. La déconnexion supprime uniquement le jeton utilisé pour la requête.

## Codes d'erreur

| Code | Situation |
|---|---|
| 401 | Jeton absent, invalide ou révoqué sur une route protégée |
| 403 | Un organisateur tente de modifier ou supprimer l'événement d'un autre |
| 404 | Événement ou lieu introuvable |
| 422 | Validation échouée, y compris un slug de lieu inconnu ou des identifiants invalides |

Les messages de validation sont en français, par exemple « La capacité doit être au moins 1. ».

## Exemple curl.exe

```powershell
curl.exe -X POST -H "Accept: application/json" -H "Authorization: Bearer VOTRE_JETON" http://localhost:8000/api/logout
```

Remplacez `VOTRE_JETON` par la valeur renvoyée par `/api/login`. La réponse 204 n'a pas de corps.

## Collection Postman

La collection exportée se trouve dans `docs/postman/api-campus-event.postman_collection.json`. Elle utilise les variables `{{base_url}}` et `{{token}}`. La requête « Connexion » enregistre le jeton dans la variable de collection ; « Connexion compte 2 (pour le 403) » enregistre `{{token2}}` et la requête « Modification refusee 403 » vérifie que l'autre organisateur reçoit bien 403 sur l'événement n°1. L'export ne contient aucun jeton. Une capture de cette preuve est fournie dans `screenshots/api-campus-event-403.png`.

## Choix d'architecture

### Pourquoi la ressource principale appartient-elle à l'utilisateur, et que protège la Policy ?

`CampusEvent` est la ressource que l'utilisateur crée et dont il est responsable : c'est donc elle qui porte `user_id`. La `CampusEventPolicy` n'autorise que le propriétaire à modifier ou supprimer un événement. Concrètement, un autre organisateur ne peut ni changer l'horaire, la capacité ou le lieu d'un événement, ni le supprimer. La Form Request de modification appelle cette Policy dans `authorize()`, avant la validation, pour renvoyer 403 plutôt que 422. `destroy()` appelle directement `Gate::authorize('delete', $event)`.

### À quel besoin réel d'un utilisateur de l'API répond le filtre ?

Le filtre `?venue=amphi-a` répond à la question « quels événements se déroulent dans l'amphithéâtre A ? ». Un étudiant qui choisit un lieu sur le plan du campus peut ainsi afficher uniquement les événements qui l'y attendent, sans parcourir toutes les pages. La comparaison porte sur le slug exact du lieu, et un slug inconnu est refusé avec un 422 en français.

### Quelle requête produirait un problème N+1 sans with(), et comment l'avez-vous évité ?

`GET /api/campus-events` affiche pour chaque événement son organisateur, son lieu et ses thèmes. Sans chargement anticipé, chaque élément de la page déclencherait une requête supplémentaire pour `user`, une pour `venue` et une pour `topics`. Le contrôleur utilise `with(['user', 'venue', 'topics'])` et `withCount('topics')` ; les Resources utilisent `whenLoaded()` et `whenCounted()`. En développement, `Model::preventLazyLoading(! app()->isProduction())` transforme toute relation oubliée en erreur explicite au lieu d'une lenteur silencieuse.
