#!/bin/bash
set -e

# Génère la clé d'application si elle n'existe pas encore (sécurité, normalement déjà définie via env var)
php artisan config:clear

# Lance les migrations automatiquement au démarrage
php artisan migrate --force

# Cache la config pour de meilleures performances en production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Démarre Apache au premier plan
apache2-foreground