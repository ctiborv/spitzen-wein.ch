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
		'default' => ',',
		'Jarhgang' => '@',
		'Herkunft' => '@',
		'Region' => '@',
		'Traubensorte' => ',',
		'Farbe' => '@',
		'Trinktemperatur' => '@',
		'Passt gut zu' => ',',
		'Empfehlung' => '@',
		'Alkohol' => '@',
		'Raritäten' => ',',
		'Inhalt' => '@',
		'Charakter' => '@',
		'Serie' => '@'
	),
	'pl_separator' => '@',
	'header' => null, // možno nastavit array se sloupci (analogie k prvnímu řádku CSV souboru)
	'skip_header' => false, // vynechat první řádek souboru? (jen v případě že je definován header v konfiguračním souboru)
	'new_products_active' => true,
	'new_product_title_prefix' => '',
	'old_product_skip_title' => false,
	'columns' => array
	(
		'ID' => self::ID,
		'Art. Nr.' => self::CODE,
		'Artikel' => self::TITLE,
		'Jarhgang' => self::PROPERTY,
		'Herkunft' => self::PROPERTY,
		'Region' => self::PROPERTY,
		'Traubensorte' => self::PROPERTY,
		'Farbe' => self::PROPERTY,
		'Trinktemperatur' => self::PROPERTY,
		'Passt gut zu' => self::PROPERTY,
		'Empfehlung' => self::PROPERTY,
		'Alkohol' => self::PROPERTY,
		'Raritäten' => self::PROPERTY,
		'Inhalt' => self::PROPERTY,
		'Charakter' => self::PROPERTY,
		'URL' => self::URL,
		'HTML Title' => self::PAGETITLE,
		'Kurzbeschreibung' => self::SHORTDESC,
		'Beschreibung' => self::LONGDESC,
		'Unsere Preis' => self::NEWCOST,
		'Original Preis' => self::ORIGINALCOST,
		'Serie' => self::PRODUCTLINES
	),
	'transformations' => array
	(
		'default' => 'mb_trim', // mb_trim je funkce která odstraní mezery ze začátku a konce vstupu
		'Unsere Preis' => array('mb_trim', 'nahrad_desetinnou_carku_teckou'),
		'Original Preis' => array('mb_trim', 'nahrad_desetinnou_carku_teckou')
	),
	'encoding' => 'UTF-8'
);
?>