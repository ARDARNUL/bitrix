FROM php:8.1-apache

# Обновляем пакеты и устанавливаем необходимые расширения PHP
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    --no-install-recommends

# Настраиваем локаль
RUN locale-gen ru_RU.UTF-8
ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:ru
ENV LC_ALL ru_RU.UTF-8

# Устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j $(nproc) gd
RUN docker-php-ext-install -j $(nproc) zip
RUN docker-php-ext-install -j $(nproc) mysqli
RUN docker-php-ext-enable mysqli

# Включаем mod_rewrite
RUN a2enmod rewrite

# Копируем bitrixsetup.php в корень веб-сервера
COPY bitrixsetup.php /var/www/html/

# Устанавливаем права на папку /var/www/html/
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# Устанавливаем composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем права на composer
RUN chmod +x /usr/local/bin/composer

# Устанавливаем ionCube Loader
RUN apt-get update && apt-get install -y wget
RUN wget https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz
RUN tar xzf ioncube_loaders_lin_x86-64.tar.gz
RUN cp ioncube/ioncube_loader_lin_8.1.so /usr/lib/php/20210902/
RUN echo "zend_extension = /usr/lib/php/20210902/ioncube_loader_lin_8.1.so" > /usr/local/etc/php/conf.d/00-ioncube.ini

# Очистка apt кеша
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*