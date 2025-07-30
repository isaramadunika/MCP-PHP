<?php
// Simple test endpoint for Railway
header('Content-Type: text/plain');
http_response_code(200);
echo "Railway PHP Server Test\n";
echo "Status: RUNNING\n"; 
echo "Time: " . date('c') . "\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "Request: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . " " . ($_SERVER['REQUEST_URI'] ?? '/') . "\n";
?>
