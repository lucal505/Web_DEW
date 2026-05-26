# imaginea oficiala php cu apache
FROM php:8.2-apache

# setare in apache pentru a permite rutare
RUN a2enmod rewrite

# activez citirea fisierelor .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# instalare extensii necesare pentru zip si git
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# instalare composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer
    
WORKDIR /var/www/html
