<?php
$config = array
(
	'csv' => array
	(
		'separator' => ';',
		'line_limit' => 10000
	),
	'ppv_separators' => array
	(
		'default' => ','
	),
	'pl_separator' => '@',
	'header' => array
	(
		'sloupec1',
		'sloupec2',
		'sloupec3',
		'Kód',
		'sloupec5',
		'Erreichbarkeit'
	),
	'skip_header' => false, // vynechat první řádek souboru? (jen v případě že je definován header v konfiguračním souboru)
	'new_products_active' => false,
	'columns' => array
	(
		'ID' => self::ID,
		'Kód' => self::CODE,
		'Bezeichnung' => self::TITLE,
		'Erreichbarkeit' => self::PROPERTY,	
		'URL' => self::URL,
		'HTML Title' => self::PAGETITLE,
		'Krátký popis' => self::SHORTDESC,
		'Dlouhý popis' => self::LONGDESC,
		'Unsere Preis' => self::NEWCOST,
		'Běžná cena' => self::ORIGINALCOST,
		'Řady' => self::PRODUCTLINES
	),
	'transformations' => array
	(
		'default' => 'mb_trim', // mb_trim je funkce která odstraní mezery ze začátku a konce vstupu
		'Kód' => array('mb_trim', 'pridej_prefix_OL'),
		'Erreichbarkeit' => array('mb_trim', 'zkonvertuj_dostupnost')
	),
	'encoding' => 'Windows-1250'
);
?>