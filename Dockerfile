FROM php:8.3-apache

# Installer les dépendances système et extensions PHP nécessaires pour Laravel + MySQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    dos2unix \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Node.js (nécessaire pour builder les assets Vite/Tailwind)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Activer mod_rewrite pour les routes Laravel
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier le code de l'application
COPY . .

# Installer les dépendances PHP (sans les paquets de dev, optimisé pour la prod)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Installer les dépendances front-end et builder les assets (CSS/JS via Vite)
RUN npm install
RUN npm run build

# Configurer Apache pour pointer vers le dossier public/ de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Donner les permissions nécessaires à Laravel (storage, cache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port utilisé par Render
EXPOSE 80

# Script de démarrage : convertit les fins de ligne (au cas où le fichier vient de Windows),
# le rend exécutable, puis l'utilise comme commande de démarrage.
RUN dos2unix /var/www/html/docker/start.sh && chmod +x /var/www/html/docker/start.sh

CMD ["/var/www/html/docker/start.sh"]