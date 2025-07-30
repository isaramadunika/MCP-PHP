<?php
/**
 * Railway Router for PHP MCP Server
 * This file handles routing for the built-in PHP server on Railway
 */

// Get the requested URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Log the request for debugging
error_log("Railway Router: Request for URI: " . $uri);

// Handle health check
if ($uri === '/health') {
    error_log("Railway Router: Routing to health check");
    require_once __DIR__ . '/health.php';
    return true;
}

// Handle MCP endpoint
if ($uri === '/mcp' || strpos($uri, '/mcp') === 0) {
    error_log("Railway Router: Routing to MCP server");
    require_once __DIR__ . '/http_mcp_server.php';
    return true;
}

// Handle root and other paths
if ($uri === '/' || $uri === '/index.php' || $uri === '') {
    error_log("Railway Router: Routing to index page");
    require_once __DIR__ . '/index.php';
    return true;
}

// For static files, let the server handle them
if (file_exists(__DIR__ . $uri)) {
    error_log("Railway Router: Serving static file: " . $uri);
    return false; // Let PHP serve the file
}

// Fallback to index
error_log("Railway Router: Fallback to index for: " . $uri);
require_once __DIR__ . '/index.php';
return true;
?>
