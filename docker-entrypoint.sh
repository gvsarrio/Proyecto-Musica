#!/bin/bash
set -e

php bin/console importmap:install --env=prod
php bin/console cache:warmup --env=prod
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

exec "$@"
