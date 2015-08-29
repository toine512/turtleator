<?php
if(strlen($_GET['id'] = trim($_GET['id'])) == 11)
{
	$im = imagecreatefromjpeg('http://img.youtube.com/vi/' . $_GET['id'] . '/mqdefault.jpg');
	imagesetinterpolation($im, IMG_SINC);
	$im = imagescale($im, 256, 144);
}
else
{
	$im = imagecreate(1, 1);
}

//Will cache for 1 month
header('Cache-Control: public, max-age=2592000');
header('Content-Type: image/jpeg');
imagejpeg($im, null, 81);
imagedestroy($im);
?>
