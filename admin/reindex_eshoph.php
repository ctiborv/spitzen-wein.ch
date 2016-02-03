<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/utils.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_eshop_indexer.class.php';
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

Kiwi_EShop_Indexer::reindexAll();
$text = 'Kiwi_EShop_Indexer::reindexAll() called';

//Kiwi_EShop_Indexer::unindex(6);
//$text .= '<br />Kiwi_EShop_Indexer::unindex(6) called';

//Kiwi_EShop_Indexer::indexDeep(6);
//$text .= '<br />Kiwi_EShop_Indexer::deepIndex(6) called';

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