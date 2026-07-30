<?php

/**
 * Generate browser icons from the circular mark in the canonical NIVICO logo.
 *
 * Run from the project root:
 * php scripts/generate_favicon.php
 */

$root = dirname(__DIR__);
$sourcePath = $root.'/public/images/nivico-email-logo.png';

if (! extension_loaded('gd')) {
    throw new RuntimeException('PHP GD extension is required.');
}

$source = imagecreatefrompng($sourcePath);
if (! $source) {
    throw new RuntimeException('Unable to open the NIVICO logo.');
}

imagesavealpha($source, true);

// The canonical circular mark occupies the first 120 × 120 px of the logo.
$mark = imagecrop($source, [
    'x' => 0,
    'y' => 0,
    'width' => 120,
    'height' => 120,
]);
imagedestroy($source);

if (! $mark) {
    throw new RuntimeException('Unable to crop the NIVICO mark.');
}

$renderPng = static function ($sourceImage, int $size, string $target): void {
    $canvas = imagecreatetruecolor($size, $size);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
    imagefill($canvas, 0, 0, $transparent);
    imagealphablending($canvas, true);
    imagecopyresampled(
        $canvas,
        $sourceImage,
        0,
        0,
        0,
        0,
        $size,
        $size,
        imagesx($sourceImage),
        imagesy($sourceImage)
    );
    imagepng($canvas, $target, 9);
    imagedestroy($canvas);
};

$renderPng($mark, 256, $root.'/public/favicon.png');
$renderPng($mark, 32, $root.'/public/favicon-32x32.png');
$renderPng($mark, 16, $root.'/public/favicon-16x16.png');
$renderPng($mark, 180, $root.'/public/apple-touch-icon.png');
imagedestroy($mark);

// A modern ICO may contain a PNG-compressed 256 × 256 image.
$png = file_get_contents($root.'/public/favicon.png');
$ico = pack('vvv', 0, 1, 1)
    .pack('CCCCvvVV', 0, 0, 0, 0, 1, 32, strlen($png), 22)
    .$png;
file_put_contents($root.'/public/favicon.ico', $ico);

echo "NIVICO favicon assets generated.\n";
