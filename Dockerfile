FROM php:8.2-fpm-alpine

LABEL maintainer="Smart Home Cleaner AI Team"
LABEL description="Laravel 12 PHP-FPM with Supervisor for Queue Workers"

WORKDIR /var/www

# ----------------------------------------------
# System Dependencies
# ----------------------------------------------
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    supervisor \
    icu-dev \
    oniguruma-dev \
    fcgi

# ----------------------------------------------
# PHP Extensions
# ----------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# ----------------------------------------------
# Composer
# ----------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ----------------------------------------------
# Application Code
# ----------------------------------------------
COPY . .

# ----------------------------------------------
# PHP-FPM Health Check Script
# ----------------------------------------------
RUN echo '#!/bin/sh' > /usr/local/bin/healthcheck \
    && echo 'cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1' >> /usr/local/bin/healthcheck \
    && chmod +x /usr/local/bin/healthcheck

# ----------------------------------------------
# Production PHP Configuration
# ----------------------------------------------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" 2>/dev/null || true
COPY docker/php/php.ini "$PHP_INI_DIR/conf.d/99-custom.ini"

# ----------------------------------------------
# Supervisor Configuration
# ----------------------------------------------
RUN mkdir -p /var/log/supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ----------------------------------------------
# Entrypoint
# ----------------------------------------------
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# ----------------------------------------------
# Permissions
# ----------------------------------------------
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD /usr/local/bin/healthcheck

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]


