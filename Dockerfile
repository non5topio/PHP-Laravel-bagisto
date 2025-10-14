FROM php:8.2-cli AS test

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip intl calendar

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Set Composer environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_DISABLE_XDEBUG_WARN=1

# Copy composer files first for better layer caching
COPY composer.lock composer.json ./

# Install PHP dependencies without dev dependencies first
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy package.json for Node.js dependencies
COPY package*.json ./

# Install Node.js dependencies
RUN npm install

# Copy the rest of the application code
COPY . .

# Copy and set permissions for test runner script
COPY run-tests.sh /app/run-tests.sh
RUN chmod +x /app/run-tests.sh

# Create .env file from example for testing
RUN cp .env.example .env

# Set proper permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Install development dependencies for testing
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Generate application key and run post-install scripts
RUN php artisan key:generate --force

# Create necessary directories for coverage reports
RUN mkdir -p /app/coverage /app/database

# Set environment variables for testing
ENV APP_ENV=testing
ENV APP_KEY=base64:dummy-key-for-testing
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=:memory:

# Install PCOV for code coverage (faster than Xdebug)
RUN pecl install pcov && docker-php-ext-enable pcov

# Build frontend assets
RUN npm run build

# Default command runs the test runner script
CMD ["/app/run-tests.sh"]
