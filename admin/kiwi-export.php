<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_html_header.class.php';
require_once 'include/kiwi_exporter.class.php';
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
$html_header->addJS(array('./js/utils.js', './js/kiwi_export.js'));

// Odeslání HTML hlavičky
$html_header->send();
?>
<body class="white">
	<div id="export-div" class="export-text">
		<ul id="export-log">
		</ul>
		<div id="konec"></div>
	</div>
<?php
if (array_key_exists('file', $_GET))
{
	$kiwi_exporter = new Kiwi_Exporter($_GET['file'], $rights);
	//$kiwi_exporter->setLogLevel(Kiwi_Exporter::LOG_ALL);
	$kiwi_exporter->export();
}
else
	echo <<<EOT
<script type="text/javascript">
logX('Erhielt nicht den Namen des Ziels für den Export!');
</script>
EOT;
?>
</body>
</html>