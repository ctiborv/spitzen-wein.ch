<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_invoice_pdf.class.php';
require_once 'include/page_names.inc.php';

$rights = new Kiwi_Rights;

if ($rights->EShop == false)
{
	if ($rights->UserID == DEFAULT_USERID)
		redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
}

// Jednotlivé bloky webové stránky
$page_items = array();
$page_items[] = $kiwi_invoice_pdf = new Kiwi_Invoice_pdf();

// Zpracování vstupů
foreach ($page_items as $item)
	$item->handleInput($_GET, $_POST);

// Eventuální přesměrování
foreach ($page_items as $item)
	if ($item->Redirection)
		redirectPage($item->Redirection); // přesměruje a ukončí zpracovávání tohoto skriptu

$kiwi_invoice_pdf->send();
