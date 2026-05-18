#!/bin/bash
set -e

# =============================================
# SmartCleanAI - Production Deployment Script
# =============================================
# Usage: bash deploy.sh [production|staging]
# =============================================

ENV_MODE=${1:-production}
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "========================================"
echo " SmartCleanAI Deployment"
echo " Environment: $ENV_MODE"
echo "========================================"

# -----------------------------------------
# 1. Pre-flight Checks
# -----------------------------------------
echo "[1/8] Running pre-flight checks..."

if ! command -v docker &> /dev/null; then
    echo "ERROR: Docker is not installed. Install it first."
    exit 1
fi

if ! docker compose version &> /dev/null; then
    echo "ERROR: Docker Compose v2 is required."
    exit 1
fi

echo "  Docker: $(docker --version)"
echo "  Compose: $(docker compose version)"

# -----------------------------------------
# 2. Environment Setup
# -----------------------------------------
echo "[2/8] Setting up environment..."

if [ ! -f ".env" ]; then
    echo "  Creating .env from .env.docker..."
    cp .env.docker .env
    
    # Generate app key (will be replaced on first run)
    echo "  Generating APP_KEY..."
    APP_KEY=$(php artisan key:generate --show 2>/dev/null || echo "")
    if [ -n "$APP_KEY" ]; then
        sed -i "s/APP_KEY=.*/APP_KEY=$APP_KEY/" .env
    fi
else
    echo "  .env already exists - using existing."
fi

# Export variables from .env
set -a
source .env
set +a

# -----------------------------------------
# 3. Create Required Directories
# -----------------------------------------
echo "[3/8] Creating required directories..."
mkdir -p storage/logs/nginx
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/app/public
chmod -R 775 storage bootstrap/cache

# -----------------------------------------
# 4. Stop Running Containers (if any)
# -----------------------------------------
echo "[4/8] Stopping existing containers..."
docker compose down --remove-orphans 2>/dev/null || true

# -----------------------------------------
# 5. Build Images
# -----------------------------------------
echo "[5/8] Building Docker images..."
docker compose build --no-cache

# -----------------------------------------
# 6. Start Services
# -----------------------------------------
echo "[6/8] Starting services..."
docker compose up -d

# -----------------------------------------
# 7. Wait for Health Checks
# -----------------------------------------
echo "[7/8] Waiting for services to be healthy..."
echo "  Waiting for MySQL..."
until docker compose exec -T mysql mysqladmin ping -h localhost -u root -p"${DB_ROOT_PASSWORD}" --silent 2>/dev/null; do
    sleep 2
done
echo "  MySQL is healthy."

echo "  Waiting for Redis..."
until docker compose exec -T redis redis-cli -a "${REDIS_PASSWORD}" PING 2>/dev/null | grep -q PONG; do
    sleep 2
done
echo "  Redis is healthy."

echo "  Waiting for App..."
sleep 5
echo "  App is running."

# -----------------------------------------
# 8. Run Laravel Setup
# -----------------------------------------
echo "[8/8] Running Laravel setup inside container..."
docker compose exec -T app php artisan storage:link --force 2>/dev/null || true
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

if [ "${RUN_MIGRATIONS}" = "true" ] || [ "$ENV_MODE" = "staging" ]; then
    echo "  Running database migrations..."
    docker compose exec -T app php artisan migrate --force --seed
fi

# -----------------------------------------
# DONE
# -----------------------------------------
echo ""
echo "========================================"
echo " Deployment Complete!"
echo "========================================"
echo ""
echo "  URL:      http://localhost:${APP_PORT:-80}"
echo "  AI-API:   http://localhost:${AI_PORT:-8001}/health"
echo ""
echo "  Test Accounts:"
echo "    Superadmin: superadmin@smartcleaner.co.tz"
echo "    Admin:     admin@smartcleaner.co.tz"
echo "    Cleaner:   cleaner.DAR1@smartcleaner.co.tz"
echo "    Homeowner: homeowner1@smartcleaner.co.tz"
echo "    Password:  password"
echo ""
echo "  Useful Commands:"
echo "    docker compose ps               - Check status"
echo "    docker compose logs -f app      - App logs"
echo "    docker compose logs -f ai-service - AI logs"
echo "    docker compose exec app bash    - Shell in container"
echo "    docker compose down             - Stop everything"
echo ""
echo "========================================"
