FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite module for Slim routing
RUN a2enmod rewrite

# Change Apache Document Root to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock ./

# Install dependencies (no dev, optimized)
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Copy the rest of the application files
COPY . .

# Generate final optimized autoloader
RUN composer dump-autoload --no-dev --optimize-all

# Create uploads directory and set permissions to www-data (Apache user)
RUN mkdir -p uploads && chown -R www-data:www-data /var/www/html

# Expose default HTTP port
EXPOSE 80

# Run Apache
CMD ["apache2-foreground"]
