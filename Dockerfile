FROM php:8.3-apache

COPY ./docker/ssl/configure_ssl.sh /usr/local/bin/configure_ssl.sh
RUN chmod +x /usr/local/bin/configure_ssl.sh

COPY ./config.docker.php /var/www/html/config.php
COPY ./docker/ssl /docker/ssl

ARG SSL_ENABLED

RUN /usr/local/bin/configure_ssl.sh

EXPOSE 80
EXPOSE 443

RUN apt update && apt install -y \
    libpng-dev \
    cron \
    && docker-php-ext-install mysqli gd pdo_mysql

# Uncomment the next line when deploying to production
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Get Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# This is to prevent removing config.php from gitignore for now. Might be removed when non-docker install gets improved.
COPY . /var/www/html

# Install Composer dependencies
RUN cd /var/www/html && composer install

COPY ./docker/run_cron_job.sh /usr/local/bin/run_cron_job.sh
COPY ./docker/cron_jobs /etc/cron.d/cron_jobs

# Ensure the script and cron job files are executable
RUN chmod +x /usr/local/bin/run_cron_job.sh
RUN chmod 0644 /etc/cron.d/cron_jobs

RUN crontab /etc/cron.d/cron_jobs

RUN chmod +x ./docker/start_cron.sh
CMD ./docker/start_cron.sh
