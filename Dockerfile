# Utiliser l'image officielle PHP avec Apache
FROM php:8.2-apache

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libonig-dev libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip

# Active le module Apache "rewrite" (nécessaire à CodeIgniter)
RUN a2enmod rewrite

# Copier les fichiers du projet dans le conteneur
COPY . /var/www/html

# Définir le dossier de travail
WORKDIR /var/www/html

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/html

# Exposer le port Apache (déjà fait dans docker-compose, mais OK de le redire)
EXPOSE 80
