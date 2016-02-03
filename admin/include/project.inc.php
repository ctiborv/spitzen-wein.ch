<?php
$kiwi_config = array
(
	'project' => 'www.spitzen-wein.ch',
	'db_server' => 'localhost',
	'db_user' => 'brunner2_spitzen',
	'db_password' => 's24bruNNer621',
	'db_name' => 'brunner2_spitzenwein',
	'eshop' => array
	(
		'protocol' => 'http',
		'www' => 'www.spitzen-wein.ch',
		'contact_email' => 'info@spitzen-wein.ch',
		'collections' => true,
		'grouped_products_group' => 3,
		'collections_group' => 4,
		'newsletters_products' => true,
		'pictures' => array
		(
			'catalog' => array(200, 250),
			'catalog2' => array(136, 170),
			'detail' => array(320, 400),
			'extra' => array(90, 108),
			'illustrative' => array(150, 180),
			'collection' => array(140, 168),
			'action' => array(700, 350)
		)
	)
);
if ($_SERVER["SERVER_NAME"]=="localhost") {
    $kiwi_config["db_user"]="root";
    $kiwi_config["db_password"]="";
}
$project = &$kiwi_config['project'];
$eshop_picture_config = &$kiwi_config['eshop']['pictures'];
?>
