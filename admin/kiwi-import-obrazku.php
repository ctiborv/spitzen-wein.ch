<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_html_header.class.php';
require_once 'include/kiwi_picture_importer.class.php';
require_once 'include/page_names.inc.php';
require_once 'include/utils.inc.php';

$rights = new Kiwi_Rights;

if ($rights->EShop == false)
{
	if ($rights->UserID == DEFAULT_USERID)
		redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
}

$html_header = new Kiwi_HTML_Header;
$html_header->title = "$project - Content Management System";
$html_header->addJS(array('./js/utils.js', './js/kiwi_import.js'));

// Odeslání HTML hlavičky
$html_header->send();
?>
<body class="white">
	<div id="import-div" class="import-text">
		<ul id="import-log">
		</ul>
		<div id="konec"></div>
	</div>
<?php
if (array_key_exists('dir', $_GET))
{
	$restart = !array_key_exists('continue', $_GET);
	$kiwi_picture_importer = new Kiwi_Picture_Importer($_GET['dir'], $rights, 50, $restart);
	$kiwi_picture_importer->setLogLevel(/*Kiwi_Picture_Importer::LOG_ALL*/Kiwi_Picture_Importer::LOG_NORMAL);
//	$kiwi_picture_importer->simulate();
	$kiwi_picture_importer->import();
}
?>
</body>
</html>