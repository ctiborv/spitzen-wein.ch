<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_html_print_header.class.php';
require_once 'include/kiwi_invoice.class.php';
require_once 'include/page_names.inc.php';

if (!isset($_GET['bauth'])) // bypass authentication
{
	$rights = new Kiwi_Rights;

	if ($rights->EShop == false)
	{
		if ($rights->UserID == DEFAULT_USERID)
			redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
	}
}

$html_header = new Kiwi_HTML_Print_Header;
$html_header->title = ''; //"$project - Content Management System";
$html_header->addCSS('./styles/faktura.css');

// Jednotlivé bloky webové stránky
$page_items = array();
$page_items[] = $kiwi_invoice = new Kiwi_Invoice();

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
<?=$kiwi_invoice->getHTML("\t");?>
</body>
</html>
