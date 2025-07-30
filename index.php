<?php

/**
 * Main Index File for Hosted MCP Server
 * 
 * This serves as the entry point for the hosted MCP server.
 * Provides both a web interface and MCP protocol endpoints.
 */

// Basic routing
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Handle MCP server endpoint
if ($requestUri === '/mcp' || $requestUri === '/mcp/' || strpos($requestUri, '/mcp') === 0) {
    require_once 'http_mcp_server.php';
    exit;
}

// Handle root path - show information page
if ($requestUri === '/' || $requestUri === '/index.php') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PHP MCP Server - Hosted</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #333;
                min-height: 100vh;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            h1 {
                color: #2c3e50;
                text-align: center;
                margin-bottom: 30px;
                font-size: 2.5em;
            }
            .status {
                background: #2ecc71;
                color: white;
                padding: 15px;
                border-radius: 5px;
                text-align: center;
                margin: 20px 0;
                font-weight: bold;
            }
            .endpoint {
                background: #ecf0f1;
                padding: 15px;
                border-radius: 5px;
                margin: 10px 0;
                font-family: monospace;
                border-left: 4px solid #3498db;
            }
            .feature {
                background: #f8f9fa;
                padding: 15px;
                margin: 10px 0;
                border-radius: 5px;
                border-left: 4px solid #e74c3c;
            }
            .tools-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .tool-card {
                background: #ffffff;
                border: 1px solid #ddd;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .tool-card h4 {
                color: #2980b9;
                margin-top: 0;
            }
            code {
                background: #f1f2f6;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                color: #7f8c8d;
                font-size: 0.9em;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 PHP MCP Server</h1>
            
            <div class="status">
                ✅ Server is Online and Ready!
            </div>

            <h2>📡 MCP Endpoint</h2>
            <div class="endpoint">
                <strong>MCP Protocol URL:</strong> <?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>/mcp
            </div>

            <h2>🛠️ Available Tools</h2>
            <div class="tools-grid">
                <div class="tool-card">
                    <h4>🧮 Calculator</h4>
                    <p>Perform arithmetic operations: add, subtract, multiply, divide</p>
                    <code>calculator</code>
                </div>
                <div class="tool-card">
                    <h4>🕐 DateTime</h4>
                    <p>Get current date/time with timezone and format support</p>
                    <code>datetime</code>
                </div>
                <div class="tool-card">
                    <h4>🔍 Web Search</h4>
                    <p>Search the web with configurable result limits</p>
                    <code>web_search</code>
                </div>
            </div>

            <h2>📝 Available Prompts</h2>
            <div class="tools-grid">
                <div class="tool-card">
                    <h4>👋 Greeting</h4>
                    <p>Generate personalized greetings in multiple languages (en, es, fr)</p>
                    <code>greeting</code>
                </div>
                <div class="tool-card">
                    <h4>📚 Story</h4>
                    <p>Generate short stories with customizable themes and lengths</p>
                    <code>story</code>
                </div>
            </div>

            <h2>⚙️ Configuration</h2>
            <div class="feature">
                <h4>VS Code MCP Configuration:</h4>
                <pre><code>{
  "servers": {
    "hosted-mcp-server": {
      "type": "http",
      "url": "<?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>/mcp"
    }
  }
}</code></pre>
            </div>

            <h2>🎯 Features</h2>
            <div class="feature">
                <strong>✅ HTTP Transport:</strong> Full MCP protocol over HTTP/HTTPS
            </div>
            <div class="feature">
                <strong>✅ Multi-language Support:</strong> Greeting prompts in English, Spanish, and French
            </div>
            <div class="feature">
                <strong>✅ Real-time Calculations:</strong> Instant arithmetic operations
            </div>
            <div class="feature">
                <strong>✅ Timezone Support:</strong> DateTime operations with global timezone support
            </div>
            <div class="feature">
                <strong>✅ Web Search Ready:</strong> Extensible web search functionality
            </div>

            <div class="footer">
                <p>🔧 Powered by PHP MCP SDK | 🌐 Model Context Protocol</p>
                <p>Server Time: <?= date('Y-m-d H:i:s T') ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'error' => 'Not Found',
    'message' => 'The requested endpoint was not found.',
    'available_endpoints' => [
        '/' => 'Information page',
        '/mcp' => 'MCP protocol endpoint'
    ]
]);
?>
