FROM webdevops/php-nginx:8.4

# First, let's find all nginx configs and remove them
RUN find /etc/nginx -name "*.conf" -type f 2>/dev/null | head -20

FROM webdevops/php-nginx:8.4

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean

# Find ALL nginx configs to understand the structure
RUN find /etc/nginx -name "*.conf" -o -name "*.vhost" 2>/dev/null | sort

# Then we know what to delete
