<?php

// Subes un nivel desde public_html para entrar a app_config
require_once __DIR__ . '/../private/config.php';

$db_host = getenv('GORA_DB_HOST');
$db_user = getenv('GORA_DB_USER');
$db_pass = getenv('GORA_DB_PASSWORD');
$db_name = getenv('GORA_DB_NAME');

echo " $db_host";
echo "paso por aca";

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
