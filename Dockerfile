FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    wget \
    --no-install-recommends

RUN locale-gen ru_RU.UTF-8
ENV LANG=ru_RU.UTF-8
ENV LANGUAGE=ru_RU:ru
ENV LC_ALL=ru_RU.UTF-8

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        gd \
        zip \
        mysqli \
        pdo_mysql

RUN a2enmod rewrite

COPY bitrixsetup.php /var/www/html/
COPY apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default.conf

RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer

RUN wget https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz && \
    tar xzf ioncube_loaders_lin_x86-64.tar.gz && \
    rm ioncube_loaders_lin_x86-64.tar.gz && \
    mkdir -p /usr/lib/php/20210902/ && \
    cp ioncube/ioncube_loader_lin_8.1.so /usr/lib/php/20210902/

RUN echo "zend_extension = /usr/lib/php/20210902/ioncube_loader_lin_8.1.so" > /usr/local/etc/php/conf.d/00-ioncube.ini && \
    echo "date.timezone = Europe/Moscow" >> /usr/local/etc/php/conf.d/00-ioncube.ini && \
    echo "opcache.revalidate_freq = 0" >> /usr/local/etc/php/conf.d/00-ioncube.ini

RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /ioncube