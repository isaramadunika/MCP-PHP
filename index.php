<?php

// Entry point for Railway deployment
// This file routes all MCP requests to the HTTP server

// Enable error reporting for debugging (disable in production)
if (getenv('MCP_DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Get request details
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Check if this is an MCP request
$isMcpRequest = (
    $requestMethod === 'POST' || 
    $requestUri === '/mcp' || 
    strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false ||
    strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
);

// Route MCP requests to the HTTP server
if ($isMcpRequest) {
    require_once __DIR__ . '/http_mcp_server.php';
    exit;
}

// For non-MCP requests, show a simple status page
?>
<!DOCTYPE html>
<html>
<head>
    <title>MCP PHP Server - Railway</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .status { color: green; font-weight: bold; }
        .endpoint { background: #f5f5f5; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .tools { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .tool { margin: 10px 0; padding: 8px; background: white; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🚀 MCP PHP Server</h1>
    <p class="status">✅ Server is running on Railway!</p>
    
    <h2>📡 MCP Endpoint</h2>
    <div class="endpoint">
        <strong>URL:</strong> <?php echo 'https://' . $_SERVER['HTTP_HOST']; ?><br>
        <strong>Protocol:</strong> HTTP/HTTPS<br>
        <strong>Status:</strong> <span class="status">Active</span>
    </div>
    
    <h2>🛠️ Available Tools</h2>
    <div class="tools">
        <div class="tool">
            <strong>🧮 Calculator</strong> - Perform arithmetic operations (add, subtract, multiply, divide)
        </div>
        <div class="tool">
            <strong>🕐 DateTime</strong> - Get current date/time with timezone support
        </div>
        <div class="tool">
            <strong>🔍 Web Search</strong> - Search the web with customizable results
        </div>
    </div>
    
    <h2>📝 Available Prompts</h2>
    <div class="tools">
        <div class="tool">
            <strong>👋 Greeting</strong> - Generate personalized greetings in multiple languages
        </div>
        <div class="tool">
            <strong>📖 Story</strong> - Generate custom stories with themes and lengths
        </div>
    </div>
    
    <h2>📚 Configuration</h2>
    <p>Add this to your VS Code MCP configuration:</p>
    <div class="endpoint">
        <pre>{
  "servers": {
    "hosted-mcp-php": {
      "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>",
      "type": "http"
    }
  }
}</pre>
    </div>
    
    <hr>
    <p><small>Powered by PHP <?php echo PHP_VERSION; ?> • Model Context Protocol SDK</small></p>
</body>
</html>
