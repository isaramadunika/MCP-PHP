<?php

// Public entry point for Railway deployment
// This file is served by Apache and routes MCP requests

// Set up autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Get request details
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// Log request for debugging
error_log("Railway MCP Request: {$requestMethod} {$requestUri} Content-Type: {$contentType}");

// Handle CORS preflight
if ($requestMethod === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

// Health check endpoints
if ($requestUri === '/health' || $requestUri === '/ping') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('c'),
        'php_version' => PHP_VERSION,
        'mcp_ready' => class_exists('Mcp\Server\Server'),
        'request_method' => $requestMethod,
        'request_uri' => $requestUri
    ]);
    exit;
}

// Check if this is an MCP request
$rawInput = file_get_contents('php://input');
$isMcpRequest = (
    $requestMethod === 'POST' || 
    strpos($contentType, 'application/json') !== false ||
    strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
    !empty($rawInput)
);

// Route MCP requests to the HTTP server
if ($isMcpRequest) {
    error_log("Routing to MCP server... Input length: " . strlen($rawInput));
    
    try {
        require_once __DIR__ . '/../mcp_server.php';
    } catch (Exception $e) {
        error_log("MCP Server Error: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'MCP Server Error',
            'message' => $e->getMessage(),
            'timestamp' => date('c')
        ]);
    }
    exit;
}

// For non-MCP requests, show status page
?>
<!DOCTYPE html>
<html>
<head>
    <title>MCP PHP Server - Railway</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 800px; margin: 50px auto; padding: 20px; 
            background: #f8f9fa; color: #212529;
        }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status { color: #28a745; font-weight: bold; font-size: 1.1em; }
        .endpoint { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff; }
        .tools { background: #e3f2fd; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .tool { margin: 12px 0; padding: 12px; background: white; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .config { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 14px; }
        h1 { color: #007bff; margin-bottom: 10px; }
        h2 { color: #495057; margin-top: 30px; margin-bottom: 15px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 MCP PHP Server</h1>
        <p class="status">✅ Server is running on Railway!</p>
        <p><strong>Deployment Time:</strong> <?php echo date('Y-m-d H:i:s T'); ?></p>
        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
        <p><strong>MCP SDK:</strong> <?php echo class_exists('Mcp\Server\Server') ? '✅ Loaded' : '❌ Not Found'; ?></p>
        
        <h2>📡 MCP Endpoint</h2>
        <div class="endpoint">
            <strong>URL:</strong> <?php echo 'https://' . $_SERVER['HTTP_HOST']; ?><br>
            <strong>Protocol:</strong> HTTP/HTTPS<br>
            <strong>Transport:</strong> HTTP JSON-RPC<br>
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
                <strong>🔍 Web Search</strong> - Search the web with customizable results (simulated)
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
        
        <h2>📚 VS Code Configuration</h2>
        <p>Add this to your <code>mcp.json</code> file:</p>
        <div class="config">
{
  "servers": {
    "hosted-mcp-php": {
      "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>",
      "type": "http"
    }
  }
}
        </div>
        
        <h2>🔍 Quick Tests</h2>
        <div class="endpoint">
            <strong>Health Check:</strong> <a href="/health" target="_blank"><?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>/health</a><br>
            <strong>Ping:</strong> <a href="/ping" target="_blank"><?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>/ping</a>
        </div>
        
        <div class="footer">
            <p>Powered by PHP <?php echo PHP_VERSION; ?> • Model Context Protocol SDK • Railway Platform</p>
            <p><small>Request ID: <?php echo uniqid(); ?> • <?php echo date('c'); ?></small></p>
        </div>
    </div>
</body>
</html>
