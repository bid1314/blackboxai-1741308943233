#!/usr/bin/env bash

# Exit if any command fails
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

# Version from package.json
VERSION=$(node -p "require('./package.json').version")
PLUGIN_SLUG="dallas-embroidery-designer"
RELEASE_DIR="releases"
BUILD_DIR="$RELEASE_DIR/build"
DIST_DIR="$RELEASE_DIR/dist"

# Ensure we're in the project root
if [ ! -f "package.json" ] || [ ! -f "composer.json" ]; then
    print_error "Please run this script from the project root"
    exit 1
fi

# Create release directories
print_status "Creating release directories..."
rm -rf $RELEASE_DIR
mkdir -p $BUILD_DIR $DIST_DIR

# Install dependencies
print_status "Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm ci

# Build assets
print_status "Building assets..."
npm run build

# Copy files to build directory
print_status "Copying files to build directory..."
cp -r \
    admin \
    includes \
    languages \
    public \
    templates \
    vendor \
    dallas-embroidery-designer.php \
    index.php \
    readme.txt \
    LICENSE \
    $BUILD_DIR/

# Remove development files from build
print_status "Removing development files..."
cd $BUILD_DIR
find . -type f -name "*.map" -delete
find . -type f -name "*.ts" -delete
find . -type d -name "tests" -exec rm -rf {} +
find . -type d -name "docs" -exec rm -rf {} +
find . -type d -name ".git" -exec rm -rf {} +
find . -type f -name ".git*" -delete
find . -type f -name "*.md" -delete
find . -type f -name "composer.*" -delete
find . -type f -name "package*.json" -delete
find . -type f -name "phpunit.xml*" -delete
find . -type f -name "phpcs.xml*" -delete
find . -type f -name ".env*" -delete
find . -type f -name ".eslint*" -delete
find . -type f -name ".prettier*" -delete
find . -type f -name ".editorconfig" -delete
find . -type f -name "webpack.config.js" -delete
find . -type f -name "jest.config.js" -delete
find . -type f -name ".travis.yml" -delete
find . -type f -name ".babelrc" -delete

# Create zip archive
print_status "Creating zip archive..."
cd ..
zip -r "dist/${PLUGIN_SLUG}-${VERSION}.zip" build/

# Create distribution files
print_status "Creating distribution files..."
cp "dist/${PLUGIN_SLUG}-${VERSION}.zip" "dist/${PLUGIN_SLUG}.zip"

# Clean up
print_status "Cleaning up..."
rm -rf $BUILD_DIR

print_status "Release created successfully!"
echo "Release files:"
echo "- dist/${PLUGIN_SLUG}-${VERSION}.zip"
echo "- dist/${PLUGIN_SLUG}.zip"

# Make script executable
chmod +x bin/create-release.sh
