<?php

$configFile = dirname(__DIR__, 2) . '/private/config.php';


if (!is_file($configFile) || !is_readable($configFile)) {
    error_log('Private database configuration file is missing or unreadable.');
    http_response_code(503);
    exit('La aplicación no está disponible.');
}

require_once $configFile;
echo "por aca entro";
$db_host = getenv('GORA_DB_HOST');
$db_user = getenv('GORA_DB_USER');
$db_pass = getenv('GORA_DB_PASSWORD');
$db_name = getenv('GORA_DB_NAME');

echo "Vars $db_host, $db_user, $db_pass, $db_name";


if (!$db_host || !$db_user || !$db_pass || !$db_name) {
    error_log('Database configuration is incomplete.');
    http_response_code(503);
    exit('La aplicación no está disponible.');
}

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    exit('La aplicación no está disponible.');
}

$conn->set_charset('utf8mb4');
?>
