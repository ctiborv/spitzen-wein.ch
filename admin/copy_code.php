<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/utils.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/html_header.class.php';
require_once 'include/page_names.inc.php';

$rights = new Kiwi_Rights;

if ($rights->EShop == false)
{
	if ($rights->UserID == DEFAULT_USERID)
		redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
}

$html_header = new HTML_Header;
$html_header->title = "$project - Content Management System";
$html_header->addCss('none.css');

if (!array_key_exists('cp', $_GET))
	throw new Exception("Chybí query string parametr \"cp\" s ID vlastnosti kódu výrobku!");

$prop_id = (int)$_GET['cp'];

if ($prop_id < 1)
	throw new Exception("Query string parametr \"cp\" s ID vlastnosti kódu výrobku obsahuje nepřípustnou hodnotu!");

$result = mysql_query("SELECT B.PID AS ProductID, V.Value AS Code FROM prodpbinds AS B JOIN prodpvals AS V ON B.PPVID=V.ID WHERE V.PID=$prop_id AND V.Value!=''");
$modifications = 0;
$text = '';
while ($row = mysql_fetch_object($result))
{
	$id = $row->ProductID;
	$code = mysql_real_escape_string($row->Code);
	$text .= 'Kopíruji kód ' . htmlspecialchars($code) . ' výrobku ' . $id . "<br />\n";
	mysql_query("UPDATE products SET Code='$code' WHERE ID=$id");
	$modifications += 1;
}

$text .= "$modifications kódů produktu překopírováno z vlastností do tabulky products.<br />";

$html_header->send();
?>
<body>
	<!--Obsah-->
	<div id="stred">
		<div id="levy">
<?=$text?><br /><br />
		</div>
		<br class="clear" />
	</div>
</body>
</html>