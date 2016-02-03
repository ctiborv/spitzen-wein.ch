<?php
define ('MODT_TEXT', 1);
define ('MODT_PICTURE', 2);
define ('MODT_NEWS', 3);
define ('MODT_PHOTOGALLERY', 4);

$module_types = array
(
	MODT_TEXT => array('Text', 'kiwi-modul-text'),
//	MODT_PICTURE => array('Obrázek', 'kiwi-modul-obrazek'),
	MODT_NEWS => array('News', 'kiwi-modul-novinky'),
//	MODT_PHOTOGALLERY => array('Fotogalerie', 'kiwi-modul-fotogalerie')
);

function isValidModuleType($type)
{
	return isset($GLOBALS['module_types'][$type]);
}

function getModulePage($type)
{
	if (!isValidModuleType($type))
		throw new Exception("Neplatný typ modulu: $type");

	return $GLOBALS['module_types'][$type][1] . '.php';
}
?>
