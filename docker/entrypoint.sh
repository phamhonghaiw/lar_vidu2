#!/bin/bash
set -Eeuo pipefail

cd /var/www

# Allow maintenance commands with: docker run ... IMAGE php artisan ...
if (( $# > 0 )); then
    exec su-exec www-data "$@"
fi

: "${APP_KEY:?Set a persistent APP_KEY before starting the application}"
: "${APP_URL:?Set APP_URL to the public HTTPS address}"
export PORT="${PORT:-10000}"
if [[ ! "$PORT" =~ ^[0-9]{1,5}$ ]] || (( 10#$PORT < 1 || 10#$PORT > 65535 )); then
    echo "PORT must be an integer between 1 and 65535" >&2
    exit 1
fi
if [[ -n "${MYSQL_ATTR_SSL_CA:-}" && ! -r "$MYSQL_ATTR_SSL_CA" ]]; then
    echo "MYSQL_ATTR_SSL_CA must point to a readable CA certificate" >&2
    exit 1
fi

# Substitute PORT only; preserve Nginx variables such as $uri and $query_string.
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

su-exec www-data php artisan config:cache
case "${RUN_MIGRATIONS:-true}" in
    true) su-exec www-data php artisan migrate --force --no-interaction ;;
    false) ;;
    *) echo "RUN_MIGRATIONS must be true or false" >&2; exit 1 ;;
esac
su-exec www-data php artisan route:cache
su-exec www-data php artisan view:cache

nginx -t
php-fpm -t

# Stop the whole container if either server exits, and forward stop signals.
server_pids=()
cleanup() {
    trap - EXIT TERM INT
    if (( ${#server_pids[@]} )); then
        kill -QUIT "${server_pids[@]}" 2>/dev/null || true
        wait "${server_pids[@]}" 2>/dev/null || true
    fi
}
trap cleanup EXIT
trap 'exit 0' TERM INT

php-fpm -F &
server_pids+=("$!")
nginx -g 'daemon off;' &
server_pids+=("$!")

status=0
wait -n "${server_pids[@]}" || status=$?
echo "A web server exited (status $status); stopping container" >&2
exit 1