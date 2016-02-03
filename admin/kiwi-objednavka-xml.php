<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_order_xml.class.php';
require_once 'include/page_names.inc.php';

$rights = new Kiwi_Rights;

if ($rights->EShop == false)
{
	if ($rights->UserID == DEFAULT_USERID)
		redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
}

if (array_key_exists('o', $_GET))
{
	$kiwi_order_xml = new Kiwi_Order_XML($_GET['o']);
	// Odeslání HTML hlavičky
	header("Content-type: text/xml");
	echo $kiwi_order_xml;
}
?>
