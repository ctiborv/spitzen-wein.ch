<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_html_print_header.class.php';
require_once 'include/kiwi_menubar.class.php';
require_once 'include/kiwi_order_print.class.php';
require_once 'include/kiwi_footer.class.php';
require_once 'include/page_names.inc.php';

$rights = new Kiwi_Rights;

if ($rights->EShop == false)
{
	if ($rights->UserID == DEFAULT_USERID)
		redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
}

$html_header = new Kiwi_HTML_Print_Header;
$html_header->title = "$project - Content Management System";

// Jednotlivé bloky webové stránky
$page_items = array();
$page_items[] = $kiwi_order_print = new Kiwi_Order_Print();

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
<?=$kiwi_order_print->getHTML("\t");?>
</body>
</html>