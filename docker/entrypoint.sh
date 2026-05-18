#!/bin/sh
set -e

echo "========================================"
echo " SmartCleanAI - Container Entrypoint"
echo "========================================"

# Wait for MySQL
echo "[1/5] Waiting for MySQL..."
until php -r "try { new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD'); echo 'connected'; } catch (Exception $e) { exit(1); }" > /dev/null 2>&1; do
    echo "  MySQL not ready - retrying in 3s..."
    sleep 3
done
echo "  MySQL is ready."

# Wait for Redis
echo "[2/5] Waiting for Redis..."
until php -r "try { $redis = new Redis(); $redis->connect('$REDIS_HOST', 6379); $redis->auth('$REDIS_PASSWORD'); echo 'connected'; } catch (Exception $e) { exit(1); }" > /dev/null 2>&1; do
    echo "  Redis not ready - retrying in 3s..."
    sleep 3
done
echo "  Redis is ready."

# Install composer dependencies (if vendor missing)
if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "[3/5] Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "[3/5] Vendor directory exists - skipping composer install."
fi

# Laravel setup
echo "[4/5] Running Laravel setup..."
php artisan storage:link --force 2>/dev/null || true
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "[5/5] Running database migrations..."
    php artisan migrate --force --seed
else
    echo "[5/5] Skipping migrations (RUN_MIGRATIONS not set)."
fi

echo "========================================"
echo " Setup complete. Starting services..."
echo "========================================"

exec "$@"
