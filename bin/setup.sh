#!/bin/bash

# Exit on error
set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Print with color
print_status() {
    echo -e "${GREEN}==>${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}WARNING:${NC} $1"
}

print_error() {
    echo -e "${RED}ERROR:${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create necessary directories
print_status "Creating necessary directories..."
mkdir -p dist
mkdir -p tests/js/__mocks__
mkdir -p tests/php/Unit
mkdir -p tests/php/Integration

# Install PHP dependencies
print_status "Installing PHP dependencies..."
if command -v composer &> /dev/null; then
    composer install
else
    print_warning "Composer not found locally, using Docker..."
    docker run --rm -v "$PWD":/app composer install
fi

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
if command -v npm &> /dev/null; then
    npm install
else
    print_warning "npm not found locally, using Docker..."
    docker run --rm -v "$PWD":/app -w /app node:16 npm install
fi

# Build assets
print_status "Building assets..."
if command -v npm &> /dev/null; then
    npm run build
else
    docker run --rm -v "$PWD":/app -w /app node:16 npm run build
fi

# Start Docker containers
print_status "Starting Docker containers..."
docker-compose up -d

# Wait for MySQL to be ready
print_status "Waiting for MySQL to be ready..."
until docker-compose exec -T db mysqladmin ping -h"localhost" --silent; do
    echo -n "."
    sleep 1
done
echo ""

# Install WordPress
print_status "Installing WordPress..."
docker-compose exec -T wordpress wp core install \
    --url=http://localhost:8000 \
    --title="Dallas Embroidery Designer Development" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=admin@example.com \
    --skip-email

# Activate plugin
print_status "Activating plugin..."
docker-compose exec -T wordpress wp plugin activate dallas-embroidery-designer

# Install and activate WooCommerce
print_status "Installing and activating WooCommerce..."
docker-compose exec -T wordpress wp plugin install woocommerce --activate

# Create test products
print_status "Creating test products..."
docker-compose exec -T wordpress wp wc product create \
    --name="Test T-Shirt" \
    --type="simple" \
    --regular_price="19.99" \
    --description="A customizable t-shirt for testing the design tool."

# Set up test environment
print_status "Setting up test environment..."
cp phpunit.xml.dist phpunit.xml
cp .env.example .env

# Initialize Git hooks
print_status "Initializing Git hooks..."
if [ -d ".git" ]; then
    npm run prepare
else
    print_warning "Not a Git repository, skipping Git hooks setup"
fi

print_status "Setup complete! Your development environment is ready."
echo -e "\nAccess the following services:"
echo -e "- WordPress: ${GREEN}http://localhost:8000${NC}"
echo -e "- PhpMyAdmin: ${GREEN}http://localhost:8080${NC}"
echo -e "- MailHog: ${GREEN}http://localhost:8025${NC}"
echo -e "\nWordPress admin credentials:"
echo -e "- Username: ${GREEN}admin${NC}"
echo -e "- Password: ${GREEN}admin${NC}"

# Make the script executable
chmod +x bin/setup.sh
