FROM php:8.4.27-fpm

ARG uid
ARG gid
# A Debian mirror to install from instead of deb.debian.org, e.g.
# http://mirror.arvancloud.ir on the production server in Iran, where large
# international downloads from containers stall past apt's 30 s timeout and the
# build never finishes. Empty (the default) keeps the image's own sources.
ARG DEBIAN_MIRROR=""

# Patient on slow links: retry, and wait up to 2 minutes for a stalled download.
RUN printf 'Acquire::Retries "5";\nAcquire::http::Timeout "120";\nAcquire::https::Timeout "120";\n' \
        > /etc/apt/apt.conf.d/80-slow-network \
    && if [ -n "$DEBIAN_MIRROR" ]; then \
        sed -i -E "s#https?://deb\.debian\.org/debian(-security)?#${DEBIAN_MIRROR%/}/debian\1#" /etc/apt/sources.list.d/debian.sources \
        && grep '^URIs:' /etc/apt/sources.list.d/debian.sources; \
    fi

RUN apt-get update -y
RUN apt-get install -y git
RUN apt-get install -y curl
RUN apt-get install -y libpng-dev
RUN apt-get install -y libpq-dev
RUN apt-get install -y libonig-dev
RUN apt-get install -y libxml2-dev
RUN apt-get install -y zip
RUN apt-get install -y unzip
RUN apt-get install -y libzip-dev
RUN apt-get install -y libwebp-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libicu-dev
RUN apt-get install -y libxpm-dev
RUN apt-get install -y libfreetype6-dev

RUN docker-php-ext-configure gd \
    --enable-gd \
    --with-webp=/usr/include/ \
    --with-jpeg=/usr/include/ \
    --with-xpm=/usr/include/ \
    --with-freetype=/usr/include

RUN docker-php-ext-install pgsql
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install gd
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl
RUN pecl install redis && docker-php-ext-enable redis

RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs

# PM2 supervises php-fpm, the queue worker and the scheduler inside this
# container. pm2-runtime becomes PID 1 via the entrypoint, so no cron daemon and
# no separate process manager is needed. Pinned so an image rebuild cannot pick up
# a breaking major release on its own.
RUN npm install -g pm2@6.0.13 && pm2 --version

RUN  apt-get install -y fish
RUN  chsh -s 'which fish'

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN usermod -u $uid www-data
RUN groupmod -g $gid www-data

RUN mkdir -p "/var/www/.npm"
RUN chown -R $uid:$gid "/var/www/.npm"

# php-fpm cannot write to /proc/self/fd/2 when supervised by PM2, so redirect its
# error log to a file. Copied before the app so a source change does not rebuild it.
COPY ./docker/php/zzz-logs.conf /usr/local/etc/php-fpm.d/zzz-logs.conf

COPY . /var/www/html

WORKDIR /var/www/html
# Copy entrypoint file
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Make it executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the entrypoint
ENTRYPOINT ["entrypoint.sh"]
