<?php
require_once __DIR__ . '/tab-session.php';
goraStartTabSession();

// Generar código aleatorio de 5 a 7 caracteres
$captcha_length = rand(5, 7);
$captcha_text = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, $captcha_length);
$_SESSION["captcha"] = $captcha_text;

// Configurar imagen
$width = 150;
$height = 50;
$image = imagecreatetruecolor($width, $height);

// Colores
$bg_color = imagecolorallocate($image, 255, 255, 255); // Fondo blanco
$text_color = imagecolorallocate($image, 0, 0, 0); // Texto negro
$noise_color = imagecolorallocate($image, 150, 150, 150); // Color del ruido

// Rellenar fondo
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Agregar líneas de ruido
for ($i = 0; $i < 10; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noise_color);
}

// Agregar puntos de ruido
for ($i = 0; $i < 200; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Ruta a la fuente TTF
$font = __DIR__ . "/arial.ttf";

// Dibujar texto con variación de posición y rotación
for ($i = 0; $i < strlen($captcha_text); $i++) {
    $angle = rand(-15, 15); // Rotación aleatoria
    $x = 20 + ($i * 20);
    $y = rand(30, 40); // Variación en altura
    imagettftext($image, 20, $angle, $x, $y, $text_color, $font, $captcha_text[$i]);
}

// Enviar imagen al navegador
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
