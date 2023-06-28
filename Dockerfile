FROM composer:2 as composer

COPY . /app

RUN composer install --no-ansi --no-progress --no-interaction --no-dev -o -a --no-scripts --ignore-platform-reqs

FROM ghcr.io/medleybox/php-cli:master as websocket

WORKDIR /app

USER 82

ENTRYPOINT ["php", "/app/server.php"]

HEALTHCHECK --interval=20s --timeout=1s --start-period=5s \
  CMD php /app/healthcheck.php

COPY --from=composer /app /app