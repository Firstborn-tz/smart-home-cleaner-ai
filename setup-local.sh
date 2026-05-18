#!/bin/bash

echo "============================================"
echo " Smart Home Cleaner AI - Local Setup"
echo "============================================"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}1. Checking PHP version...${NC}"
php -v | head -n 1
if [ $? -ne 0 ]; then
    echo "❌ PHP not found. Please install PHP 8.2+"
    exit 1
fi

echo -e "${BLUE}2. Installing PHP dependencies...${NC}"
composer install
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

echo -e "${GREEN}✅ PHP dependencies installed${NC}"

echo -e "${BLUE}3. Setting up environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
    echo -e "${GREEN}✅ .env file created and key generated${NC}"
else
    echo -e "${YELLOW}⚠️  .env file already exists${NC}"
fi

echo -e "${BLUE}4. Configure your database in .env file:${NC}"
echo "   DB_DATABASE=smart_home_cleaner"
echo "   DB_USERNAME=root"
echo "   DB_PASSWORD="
echo ""
echo -e "${YELLOW}   Edit .env file now? (y/n)${NC}"
read -r edit_env
if [ "$edit_env" = "y" ]; then
    nano .env
fi

echo -e "${BLUE}5. Creating database...${NC}"
php artisan db:create 2>/dev/null || echo -e "${YELLOW}⚠️  Please create the database manually: CREATE DATABASE smart_home_cleaner;${NC}"

echo -e "${BLUE}6. Running migrations and seeders...${NC}"
php artisan migrate:fresh --seed
if [ $? -ne 0 ]; then
    echo "❌ Migration failed. Check your database configuration."
    exit 1
fi
echo -e "${GREEN}✅ Database migrated and seeded${NC}"

echo -e "${BLUE}7. Setting up storage link...${NC}"
php artisan storage:link
echo -e "${GREEN}✅ Storage linked${NC}"

echo -e "${BLUE}8. Setting up Python AI Microservice...${NC}"
cd ai-microservice
if [ ! -d "venv" ]; then
    python3 -m venv venv
    echo -e "${GREEN}✅ Python virtual environment created${NC}"
fi
source venv/bin/activate
pip install -r requirements.txt
deactivate
cd ..
echo -e "${GREEN}✅ AI service dependencies installed${NC}"

echo ""
echo "============================================"
echo -e "${GREEN}✅ SETUP COMPLETE!${NC}"
echo "============================================"
echo ""
echo -e "${BLUE}To start the application, run these commands in separate terminals:${NC}"
echo ""
echo -e "${GREEN}Terminal 1 - Laravel Server:${NC}"
echo "  php artisan serve"
echo ""
echo -e "${GREEN}Terminal 2 - AI Microservice:${NC}"
echo "  cd ai-microservice && source venv/bin/activate && python main.py"
echo ""
echo -e "${GREEN}Terminal 3 - Queue Worker:${NC}"
echo "  php artisan queue:work"
echo ""
echo "============================================"
echo -e "${YELLOW}Test Accounts:${NC}"
echo "  Super Admin: superadmin@smartcleaner.co.tz / password"
echo "  Admin: admin@smartcleaner.co.tz / password"
echo "  Cleaner: cleaner.DAR1@smartcleaner.co.tz / password"
echo "  Homeowner: homeowner1@smartcleaner.co.tz / password"
echo "============================================"