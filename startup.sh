#!/bin/bash

# 1. Copiar configuración de Nginx (Desde tu carpeta persistente al sistema)
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default
service nginx reload

# 2. Base de datos y Caché de Laravel
php /home/site/wwwroot/artisan migrate --force
php /home/site/wwwroot/artisan config:clear
php /home/site/wwwroot/artisan route:clear
php /home/site/wwwroot/artisan view:clear
php /home/site/wwwroot/artisan cache:clear

# 3. Enlace simbólico para imágenes
php /home/site/wwwroot/artisan storage:link