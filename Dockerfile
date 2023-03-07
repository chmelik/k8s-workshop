# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.1
ARG NGINX_VERSION=1.22

FROM php:${PHP_VERSION}-fpm-alpine AS app_php

ENV APP_ENV=prod

RUN set -eux; \
    apk add --no-cache --update \
        bash \
        acl \
        openssh-client \
        unzip \
        zip \
        file \
        fcgi \
        gnu-libiconv

# install gnu-libiconv and set LD_PRELOAD env to make iconv work fully on Alpine image.
# see https://github.com/docker-library/php/issues/240#issuecomment-763112749
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

RUN set -eux; \
    apk add --no-cache --update --virtual .build-deps \
        freetype-dev \
        icu-dev \
        libxml2-dev \
        libzip-dev \
        zlib-dev \
        postgresql-dev \
    ; \
    docker-php-ext-install -j "$(nproc)" \
        zip pdo_pgsql bcmath opcache \
    ; \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
        )"; \
    apk add --no-cache --virtual .composer-phpext-rundeps $runDeps; \
    apk del .build-deps

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/

COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/php/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

RUN mkdir /app
WORKDIR /app

VOLUME /app/var

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.* symfony.* ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress; \
	composer clear-cache

COPY .env ./
COPY bin bin/
COPY config config/
COPY public public/
COPY src src/

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chmod +x bin/console; sync

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


FROM nginx:${NGINX_VERSION}-alpine as nginx
WORKDIR /app
COPY --from=app_php /app/public public/
COPY docker/nginx/templates /etc/nginx/templates


FROM nginx:${NGINX_VERSION}-alpine as api_gateway
COPY docker/gateway/ /etc/nginx/
