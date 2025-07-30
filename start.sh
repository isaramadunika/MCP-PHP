#!/bin/bash

# Railway deployment start script for PHP MCP Server

echo "🚀 Starting PHP MCP Server on Railway..."

# Get the port from Railway environment
PORT=${PORT:-8080}
echo "📡 Using port: $PORT"

# Create sessions directory if it doesn't exist
mkdir -p mcp_sessions
chmod 755 mcp_sessions

echo "✅ Session directory ready"

# Display environment info
echo "🔍 Environment info:"
echo "   PHP Version: $(php --version | head -n 1)"
echo "   Working Directory: $(pwd)"
echo "   Files: $(ls -la | wc -l) files"

# Start PHP built-in server with better error handling
echo "🌐 Starting PHP server on 0.0.0.0:$PORT"
echo "🔗 Server will be available at: https://mcp-php-php-mcp.up.railway.app"
echo "🔌 MCP Endpoint: https://mcp-php-php-mcp.up.railway.app/mcp"

# Use a more robust PHP server command with router
exec php -S 0.0.0.0:$PORT -t . -d display_errors=1 -d log_errors=1 router.php
