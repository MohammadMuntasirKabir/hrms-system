FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_sqlite zip mbstring \
    && apt-get clean

# Install nginx
RUN apt-get update && apt-get install -y nginx && apt-get clean

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm install

COPY . .

# Create .env from .env.example and generate key
RUN cp .env.example .env
RUN php artisan key:generate --no-interaction

RUN npm run build
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Create SQLite database and run migrations
RUN touch database/database.sqlite
RUN php artisan migrate --force

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Fix permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database

EXPOSE 80

# Start both php-fpm and nginx
CMD php-fpm -D && nginx -g "daemon off;"
