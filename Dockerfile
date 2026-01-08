# Gunakan base image PHP 8.3 dengan Swoole
FROM phpswoole/swoole:5.1.3-php8.3-alpine

LABEL author="Alfarozy"

# Copy composer.lock dan composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install runtime dependencies
RUN apk add --no-cache \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    jpegoptim optipng pngquant gifsicle \
    imagemagick \
    imagemagick-dev \
    imagemagick-libs \
    ghostscript \
    openjpeg-dev \
    dcron \
    unzip \
    git \
    nano \
    supervisor \
    postgresql-dev \
    librsvg \
    tzdata \
    file \
    pkgconfig \
    icu-dev \
    bash \
    ttf-dejavu fontconfig

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install bcmath gd pdo_pgsql pcntl intl exif

# Install & enable Imagick (gunakan versi terbaru yang kompatibel dengan PHP 8.3)
RUN apk add --no-cache --virtual .build-deps autoconf make gcc g++ libtool \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .build-deps

# Alternatif jika pecl install imagick masih error, gunakan ini:
# RUN apk add --no-cache imagemagick imagemagick-dev \
#     && pecl install imagick \
#     && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies dengan Composer (hanya metadata dulu biar caching optimal)
RUN composer install --ignore-platform-reqs --no-scripts --no-autoloader

# Copy seluruh file aplikasi
COPY . /var/www

# Install dependency full setelah source code masuk
RUN composer install

# Konfigurasi CRON JOB
RUN echo "* * * * * php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1" | crontab - \
    && touch /var/log/cron.log

# Supervisor configuration files
COPY .docker/app/supervisor.conf /etc/supervisor/supervisord.conf

# Expose port 8000
EXPOSE 8000

# Jalankan aplikasi
CMD php artisan optimize:clear \
    && php artisan optimize \
    && supervisord -c /etc/supervisor/supervisord.conf \
    && supervisorctl reread \
    && supervisorctl update \
    && php artisan octane:start --host=0.0.0.0 --port=8000 --verbose
