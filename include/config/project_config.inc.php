<?php
$config = array
(
	'project' => 'Spitzen-wein.ch',
	'html_prefix' => '',
	'timezone' => 'Europe/Prague',
	'locale' => 'cs_CZ.UTF-8',
	'locale_numeric' => 'en_US.UTF-8',
	'default_language' => 'cz',
	'monetary_format' => '',
	'costs_with_vat' => true, // jsou ceny v DB včetně DPH?
	'vat_coefs' => array
	(
		'top' => 0.1597, // koeficient pro výpočet DPH z ceny s DPH (metoda shora)
		'bottom' => 0.19 // koeficient pro výpočet DPH z ceny bez DPH (metoda zdola)
	)
);

require 'db_config.inc.php';
require 'contacts_config.inc.php';
require 'templates_config.inc.php';
require 'constants_config.inc.php';
require 'newsletters_config.inc.php';
?>