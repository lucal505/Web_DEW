# imaginea oficiala php cu apache
FROM php:8.2-apache

# setare in apache pentru a permite rutare
RUN a2enmod rewrite

# setez directorul de lucru in interiorul containerului
WORKDIR /var/www/html
