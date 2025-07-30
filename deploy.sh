#!/bin/bash

# PHP MCP Server - Deployment Script
# Run this script on your hosting server after uploading files

echo "🚀 Starting PHP MCP Server Deployment..."

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP Version: $PHP_VERSION"

if ! php -r "exit(version_compare(PHP_VERSION, '8.1.0', '>=') ? 0 : 1);"; then
    echo "❌ Error: PHP 8.1 or higher is required"
    exit 1
fi

# Check required extensions
echo "🔍 Checking PHP extensions..."

REQUIRED_EXTENSIONS=("curl" "json")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        echo "✅ $ext extension found"
    else
        echo "❌ Error: $ext extension is required"
        exit 1
    fi
done

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
    echo "✅ Dependencies installed"
else
    echo "❌ Composer not found. Please install Composer and run:"
    echo "   composer install --no-dev --optimize-autoloader"
    exit 1
fi

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p mcp_sessions
chmod 777 mcp_sessions
echo "✅ Session directory created"

# Set file permissions
echo "🔒 Setting file permissions..."
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 644 .htaccess
echo "✅ Permissions set"

# Test the installation
echo "🧪 Testing installation..."
if php -l index.php > /dev/null 2>&1; then
    echo "✅ index.php syntax OK"
else
    echo "❌ index.php has syntax errors"
    exit 1
fi

if php -l http_mcp_server.php > /dev/null 2>&1; then
    echo "✅ http_mcp_server.php syntax OK"
else
    echo "❌ http_mcp_server.php has syntax errors"
    exit 1
fi

# Display success message
echo ""
echo "🎉 Deployment completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "1. Point your domain to this directory"
echo "2. Visit https://yourdomain.com/ to see the web interface"
echo "3. Use https://yourdomain.com/mcp as your MCP endpoint"
echo "4. Update your VS Code MCP configuration with the new URL"
echo ""
echo "🔧 Configuration example for VS Code:"
echo '{'
echo '  "servers": {'
echo '    "your-hosted-mcp-server": {'
echo '      "type": "http",'
echo '      "url": "https://yourdomain.com/mcp"'
echo '    }'
echo '  }'
echo '}'
echo ""
echo "📞 If you encounter issues, check the hosting logs and ensure all requirements are met."
