<?php

$width = 500;
$height = 120;
$navy = [24, 42, 130];
$muted = [100, 116, 139];

$image = imagecreatetruecolor($width, $height);
imagesavealpha($image, true);
$transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
imagefill($image, 0, 0, $transparent);

$navyColor = imagecolorallocate($image, ...$navy);
$mutedColor = imagecolorallocate($image, ...$muted);
$white = imagecolorallocate($image, 255, 255, 255);

imagefilledellipse($image, 58, 60, 104, 104, $navyColor);

$fontRegular = 'C:\Windows\Fonts\arial.ttf';
$fontBold = 'C:\Windows\Fonts\arialbd.ttf';
$fontSerif = 'C:\Windows\Fonts\georgiab.ttf';

$drawCentered = function (string $text, float $size, int $centerX, int $baseline, int $color, string $font) use ($image): void {
    $box = imagettfbbox($size, 0, $font, $text);
    $textWidth = $box[2] - $box[0];
    imagettftext($image, $size, 0, (int) ($centerX - ($textWidth / 2)), $baseline, $color, $font, $text);
};

$drawCentered('NIVICO', 17, 58, 60, $white, $fontBold);
$drawCentered('Electronic Mart', 6.5, 58, 76, $white, $fontRegular);

imagettftext($image, 42, 0, 128, 65, $navyColor, $fontSerif, 'NIVICO');
imagettftext($image, 15, 0, 132, 91, $mutedColor, $fontRegular, 'Electronic Mart');

$target = dirname(__DIR__).'/public/images/nivico-email-logo.png';
imagepng($image, $target, 9);
imagedestroy($image);

echo $target.PHP_EOL;
