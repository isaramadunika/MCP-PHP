<?php
/**
 * Health Check Endpoint for Railway
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => 'PHP MCP Server',
    'version' => '1.0.0',
    'endpoints' => [
        'web' => '/',
        'mcp' => '/mcp',
        'health' => '/health'
    ],
    'environment' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '/',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
    ]
];

echo json_encode($health, JSON_PRETTY_PRINT);
?>
