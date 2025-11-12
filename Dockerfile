FROM php:8.3-apache

# Activer mod_rewrite et définir le bon DocumentRoot
RUN a2enmod rewrite && \
    sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Paramètres PHP recommandés pour upload
RUN { \
  echo "file_uploads = On"; \
  echo "post_max_size = 16M"; \
  echo "upload_max_filesize = 16M"; \
} > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
