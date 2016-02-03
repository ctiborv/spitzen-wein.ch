<?php
if (!isset($_GET['image']))
{
	header("Content-Type: text/html; charset=UTF-8");
	echo 'ERROR: image query string variable not set!';
	die();
}

require_once 'include/essentials.inc.php';

$image = $_GET['image'];
$size = $quality = '';

if (isset($_GET['size']))
	$size = $_GET['size'];

if (isset($_GET['quality']))
	$quality = $_GET['quality'];

echo Image_Handler::renderImage($image, $size, $quality);
?>
