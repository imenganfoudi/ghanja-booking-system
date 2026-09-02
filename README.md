# Ghanja v2 — Backend API

API REST développée avec **Laravel 13** et **Laravel Sanctum**, pour le système de réservation en ligne Ghanja.

Cette version constitue une réécriture backend orientée API, consommée par un frontend séparé développé avec Vue.js 3 (SPA).

🔗 **API live :** [ghanja-v2-api.onrender.com](https://ghanja-v2-api.onrender.com)
🔗 **Frontend associé :** [ghanja-frontend.onrender.com](https://ghanja-frontend.onrender.com) — [code source](https://github.com/imenganfoudi/ghanja-frontend)

## Stack technique

- **Laravel 13** (PHP 8.3)
- **Laravel Sanctum** — authentification par tokens
- **MySQL** (hébergé sur Aiven)
- **Docker** (image basée sur `php:8.3-apache`)

## Fonctionnalités

- Authentification API (login / logout / utilisateur courant) via tokens Sanctum
- Gestion des rendez-vous (CRUD complet) :
  - Vérification des créneaux disponibles en temps réel
  - Protection contre les doubles réservations via transaction SQL + verrouillage de lignes (`lockForUpdate`)
  - Notifications par e-mail (client + administrateur)
- Gestion des services (CRUD complet)
- Gestion des employés (CRUD complet)

## Sécurité

- CSRF / IDOR : accès protégés par tokens Sanctum et validation stricte des entrées
- Gestion des accès concurrents via transactions SQL pour éviter les conflits de réservation
- Variables sensibles gérées via variables d'environnement (non versionnées)

## Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

L'API tourne par défaut sur `http://localhost:8000`.

## Déploiement

Déployé sur [Render](https://render.com) via Docker, avec une base de données MySQL managée sur [Aiven](https://aiven.io).

## Projet lié

Ce backend est consommé par le frontend Vue.js disponible sur [github.com/imenganfoudi/ghanja-frontend](https://github.com/imenganfoudi/ghanja-frontend).

Il s'agit d'une évolution du projet original [Ghanja](https://github.com/imenganfoudi/ghanja-booking-system) (version Blade/Laravel monolithique), disponible sur la branche `main` du même dépôt.