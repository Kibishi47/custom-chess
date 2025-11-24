
# Custom Chess

## Objectifs d'apprentissage

Ce projet permet d'explorer plusieurs concepts avancés de Symfony :

- Utilisation de Mercure pour les mises à jour en temps réel
- Création de Validator et Normalizer personnalisés
- Authentification via lexik/jwt-authentication-bundle
- Utilisation des DTO
- Conception d’un moteur d’échecs extensible en PHP
- Gestion d’un moteur métier complexe avec une architecture POO claire

---

## Description du projet

Ce projet implémente une API permettant de gérer :

- la création, la participation et l’abandon de parties
- les coups d’échecs en respectant les règles officielles
- des règles personnalisables (nouvelles pièces, mouvements alternatifs, variantes)
- la synchronisation des parties en temps réel via Mercure

L’objectif est d’apprendre à construire un moteur d’échecs modulaire, extensible et maintenable.

---

## Architecture

### GameEngine
- Gère toutes les règles du jeu (déplacements, roque, en passant, promotion)
- Génère les coups pseudo-légaux puis filtre les coups légaux
- Applique les coups sur le plateau
- Vérifie les états (échec, mat, pat)

### Board / Pieces
- Board minimaliste : uniquement stockage des cases et pièces
- Pièces responsables de leurs déplacements bruts
- Gestion de hasMoved et canBeChecked au niveau des pièces

### Entities
- Game : état global d’une partie
- Move : historique des coups
- GamePlayer : association joueur/couleur

### Mercure
Un topic par partie :  
`/api/game/{id}`

### Authentification
LexikJWTAuthenticationBundle pour la génération et vérification des tokens JWT.

---

## Endpoints API

### Authentification

#### POST /api/register
Créer un compte utilisateur.

**Body JSON :**
```json
{
  "email": "example@test.com",
  "password": "mypassword"
}
```

#### POST /api/login
Retourne un JWT.

**Body JSON :**
```json
{
  "email": "example@test.com",
  "password": "mypassword"
}
```

#### GET /api/me
Retourne l'utilisateur courant (token obligatoire).

---

### Jeux d'échecs

#### GET /api/game/types
Retourne les types de plateau disponibles.

#### POST /api/game/join
Rejoint une partie en attente ou crée une nouvelle partie.

**Body JSON :**
```json
{
  "boardType": "standard"
}
```

#### POST /api/game/quit
Quitte la partie en cours.

#### POST /api/{game}/moves
Jouer un coup.

**Body JSON :**
```json
{
  "fromSq": "e2",
  "toSq": "e4",
  "color": "white",
  "piece": "pawn"
}
```

---

## Roadmap

- Conception du schéma de database
- Mise en place de l’authentification
- Installation et tests de Mercure
- Création du front minimal
- Implémentation des règles de base
- Ajout du moteur d’échecs complet
- Génération des coups légaux côté serveur
- Gestion de fin de partie
- Rejouabilité et robustesse de l’engine

---

## Tester le projet en local

```
git clone https://github.com/Kibishi47/custom-chess.git
composer install
docker compose build --pull --no-cache
docker compose up --wait

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

L’API est disponible sur :  
`https://localhost`

Pour le front : suivre les instructions du repository correspondant.  
👉 https://github.com/Kibishi47/custom-chess-front

---

## Pour aller plus loin

Le déploiement complet incluant Mercure est en cours de finalisation.  
Le moteur permet déjà d’ajouter des variantes et des pièces personnalisées.  
Il reste possible d'étendre :

- les règles du moteur
- les types de plateaux
- l’IA adverse
- les variantes complètes (960, horde, etc.)

