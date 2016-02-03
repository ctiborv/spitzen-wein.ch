<?php
$config['db'] = array
(
	'main' => array
	(
		'host' => 'localhost',
		//'port' => ???,
		'user' => 'brunner2_spitzen',
		'password' => 's24bruNNer621',
		'dbname' => 'brunner2_spitzenwein'
	)
);
if ($_SERVER["SERVER_NAME"]=="localhost") {
    $config['db']['main']["user"]="root";
    $config['db']['main']["password"]="";
}
?>