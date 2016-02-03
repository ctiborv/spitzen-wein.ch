<?php
$config['constants'] = array
(
	'languages_constants' => array('cz', 'en')
);

// language specific constants:
foreach ($config['constants']['languages_constants'] as $language)
{	
	include $language . '_constants_config.inc.php';
}
?>