# imaginea oficiala php cu apache
FROM php:8.2-apache

# setare in apache pentru a permite rutare
RUN a2enmod rewrite

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
