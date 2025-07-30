<?php
// Railway PHP server configuration
$port = getenv('PORT') ?: 8080;
$host = '0.0.0.0';

echo "Starting PHP server on {$host}:{$port}\n";

// Start the built-in PHP server
$command = "php -S {$host}:{$port} -t . index.php";
exec($command);
?>
