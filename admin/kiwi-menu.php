<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_html_header.class.php';
require_once 'include/kiwi_menubar.class.php';
require_once 'include/kiwi_menu_form.class.php';
require_once 'include/kiwi_footer.class.php';
require_once 'include/page_names.inc.php';

$rights = new Kiwi_Rights;

if ($rights->WWW == false)
{
	if ($rights->UserID == DEFAULT_USERID)
		redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
}

$html_header = new Kiwi_HTML_Header;
$html_header->title = "$project - Content Management System";
$html_header->addJS(array('./js/utils.js', './js/kiwi_menu_form.js'));

// Jednotlivé bloky webové stránky
$page_items = array();
$page_items[] = $kiwi_menubar = new Kiwi_MenuBar('WWW', $rights);
$page_items[] = $kiwi_menu_form = new Kiwi_Menu_Form($rights);
$page_items[] = $kiwi_footer = new Kiwi_Footer();

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
<?=$kiwi_menubar->getHTML("\t");?>
	<!--Obsah-->
	<div id="stred">
		<div id="levy">
<?=$kiwi_menu_form->getHTML("\t\t\t");?>
		</div>
		<br class="clear" />
	</div>
	<!--Pata-->
<?=$kiwi_footer->getHTML("\t");?>
</body>
</html>