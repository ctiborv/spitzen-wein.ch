<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_html_header.class.php';
require_once 'include/kiwi_login_form.class.php';
require_once 'include/utils.inc.php';

$rights = new Kiwi_Rights;

$html_header = new Kiwi_HTML_Header;
$html_header->title = "$project - Content Management System";
$html_header->addJS('./js/kiwi_login_form.js');

// Jednotlivé bloky webové stránky
$page_items = array();
$page_items[] = $kiwi_login_form = new Kiwi_Login_Form($rights);

// Zpracování vstupů
foreach ($page_items as $item)
	$item->handleInput($_GET, $_POST);

// Eventuální přesměrování
foreach ($page_items as $item)
	if ($item->Redirection)
		redirectPage($item->Redirection); // přesměruje a ukončí zpracovávání tohoto skriptu

// Odeslání HTML hlavičky
$html_header->send();
?>
<body>
	<!--Hlavicka-->
	<div id="horni"></div>
	<!--Obsah-->
<?=$kiwi_login_form->getHTML("\t");?>
</body>
</html>