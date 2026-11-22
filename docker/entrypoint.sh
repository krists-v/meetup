#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi

if [ -n "${DATABASE_URL:-}" ]; then
    export DB_CONNECTION=pgsql
else
    export DB_CONNECTION="${DB_CONNECTION:-sqlite}"

    if [ "$DB_CONNECTION" = "sqlite" ]; then
        DB_DATABASE="${DB_DATABASE:-/data/database.sqlite}"
        export DB_DATABASE
        mkdir -p "$(dirname "$DB_DATABASE")"
        if [ ! -f "$DB_DATABASE" ]; then
            touch "$DB_DATABASE"
        fi
    fi
fi

export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export AVATAR_ROOT="${AVATAR_ROOT:-/data/avatars}"
export PHOTO_ROOT="${PHOTO_ROOT:-/data/photos}"
mkdir -p "$AVATAR_ROOT" "$PHOTO_ROOT"

php artisan migrate --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
