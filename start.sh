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

# Start PHP built-in server
echo "🌐 Starting PHP server on 0.0.0.0:$PORT"
exec php -S 0.0.0.0:$PORT -t . index.php
