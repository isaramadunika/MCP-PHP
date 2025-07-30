# PHP MCP Server - Hosting Deployment Guide

## 📋 Prerequisites
- PHP 8.1 or higher
- Composer installed on hosting server
- cURL extension enabled
- JSON extension enabled

## 🚀 Deployment Steps

### 1. Upload Files
Upload all files to your hosting root directory (usually `public_html` or `www`)

### 2. Install Dependencies
Run in your hosting terminal or via hosting control panel:
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Set Permissions
Ensure these directories are writable:
```bash
chmod 755 .
chmod 777 mcp_sessions
chmod 644 *.php
```

### 4. Configure Domain
Point your domain to the hosting directory containing these files.

## 🔧 Server Configuration

### Apache .htaccess (included)
The .htaccess file handles routing and security

### PHP Configuration
Ensure your hosting supports:
- PHP 8.1+
- cURL extension
- JSON extension
- Allow URL fopen (for web search)

## 🌟 Access URLs

After deployment, your MCP server will be available at:
- **Web Interface**: `https://yourdomain.com/`
- **MCP Endpoint**: `https://yourdomain.com/mcp`

## 🔒 Security Notes

1. The `.htaccess` file prevents access to sensitive files
2. Session data is stored in `/mcp_sessions` directory
3. All errors are logged, not displayed in production

## 📞 Support

If you encounter issues:
1. Check PHP error logs
2. Verify Composer dependencies are installed
3. Ensure file permissions are correct
4. Contact your hosting provider for PHP configuration

## 🎯 Testing

Test your deployment:
1. Visit `https://yourdomain.com/` - should show the web interface
2. Visit `https://yourdomain.com/mcp` - should return MCP protocol response
3. Update your VS Code MCP configuration with the new URL
