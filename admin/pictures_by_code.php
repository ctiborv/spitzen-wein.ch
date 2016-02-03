<?php
require_once 'include/kiwi_exception.inc.php';
require_once 'include/utils.inc.php';
require_once 'include/project.inc.php';
require_once 'include/dbc.inc.php';
require_once 'include/kiwi_rights.class.php';
require_once 'include/html_header.class.php';
require_once 'include/page_names.inc.php';

define ('ESHOP_DIR', '../documents/eshop/');

function is_filename_by_code($filename, $code)
{
	return basename(strtolower($filename), '.jpg') === strtolower($code);
}

function is_filename_available($filename, $dirs)
{
	foreach ($dirs as $dir)
	{
		if (file_exists(ESHOP_DIR . $dir . '/' . $filename))
			return false;
	}
	return true;
}

function rename_main_photo($id, $old, $new, $dirs)
{
	$new_sql = mysql_real_escape_string($new);
	if ($result = mysql_query("UPDATE products SET Photo='$new_sql' WHERE ID=$id"))
	{
		foreach ($dirs as $dir)
		{
			if (file_exists(ESHOP_DIR . "$dir/$old"))
				rename(ESHOP_DIR . "$dir/$old", ESHOP_DIR . "$dir/$new");
		}
	}
}

$rights = new Kiwi_Rights;

if ($rights->EShop == false)
{
	if ($rights->UserID == DEFAULT_USERID)
		redirectPage(KIWI_LOGIN . '?page=' . urlencode($_SERVER['REQUEST_URI']));
}

$html_header = new HTML_Header;
$html_header->title = "$project - Content Management System";
$html_header->addCss('none.css');

$msgs = array();
$rename = array_key_exists('rename', $_GET);
$max = array_key_exists('max', $_GET) ? (int)$_GET['max'] : -1;

$pic_count = array('M' => 0, 'E' => 0, 'I' => 0, 'P' => 0);

// Read the contents of database:

$code_index = array();
$non_unique_codes = array();
$pictures = array();
$result = mysql_query("SELECT ID, Code, Photo FROM products WHERE Photo!=''");
while ($row = mysql_fetch_object($result))
{
	if ($row->Code !== '')
	{
		if (array_key_exists($row->Code, $code_index))
			$non_unique_codes[$row->Code] = true;
		$code_index[$row->Code][] = $row->ID;
	}

	$pictures[$row->ID] = array
	(
		'code' => $row->Code,
		'filename' => $row->Photo,
		'ephotos' => array(),
		'iphotos' => array(),
		'pvphotos' => array()
	);
	$pic_count['M']++;
/*
	$result2 = mysql_query("SELECT ID, FileName FROM prodepics WHERE PID=" . $row->ID);
	while ($row2 = mysql_fetch_object($result2))
	{
		$pictures[$row->ID]['ephotos'][] = array
		(
			'id' => $row2->ID,
			'filename' => $row2->FileName
		);
		$pic_count['E']++;
	}
	mysql_free_result($result2);

	$result2 = mysql_query("SELECT ID, FileName FROM prodipics WHERE PID=" . $row->ID);
	while ($row2 = mysql_fetch_object($result2))
	{
		$pictures[$row->ID]['iphotos'][] = array
		(
			'id' => $row2->ID,
			'filename' => $row2->FileName
		);
		$pic_count['I']++;
	}
	mysql_free_result($result2);

	$result2 = mysql_query("SELECT PPVID, Photo FROM prodpbinds WHERE Photo!='' AND PID=" . $row->ID);
	while ($row2 = mysql_fetch_object($result2))
	{
		$pictures[$row->ID]['pvphotos'][] = array
		(
			'ppvid' => $row2->PPVID,
			'filename' => $row2->Photo
		);
		$pic_count['P']++;
	}
	mysql_free_result($result2);
*/
}
mysql_free_result($result);

if (!$rename)
	$msgs[] = 'Diagnostic mode only...';
else
	$msgs[] = 'Preparing to rename pictures by appropriate product codes...';

$msgs[] = $pic_count['M'] . ' main product photos retrieved from database...';
$msgs[] = $pic_count['E'] . ' extra product photos retrieved from database...';
$msgs[] = $pic_count['I'] . ' illustrative product photos retrieved from database...';
$msgs[] = $pic_count['P'] . ' photos bound to property value of some product retrieved from database...';
if (!empty($non_unique_codes))
	$msgs[] = 'Non-unique codes: ' . count($non_unique_codes) . ' { ' . implode(', ', array_keys($non_unique_codes)) . ' }';
else
	$msgs[] = 'All codes are unique...';

//$dirnames = array('photo', 'catalog', 'catalog2', 'collection', 'detail', 'extra', 'illustrative');

foreach ($pictures as $id => $data)
{
	if (array_key_exists($data['code'], $non_unique_codes))
	{
		$msgs[] = 'Skipping ' . $data['filename'] . ' because the code ' . $data['code'] . ' is not unique...';
		continue;
	}
	if (!is_filename_by_code($data['filename'], $data['code']))
	{
		$filename = strtolower($data['code']) . '.jpg';
		if (is_filename_available($filename, array('photo', 'catalog', 'catalog2', 'collection', 'detail')))
		{
			$msgs[] = $data['filename'] . ' => ' . $filename;
			if ($rename)
			{
				rename_main_photo($id, $data['filename'], $filename, array('photo', 'catalog', 'catalog2', 'collection', 'detail'));
				if ($max >= 0) $max -= 1;
			}
		}
		else
			$msgs[] = $data['filename'] . ' :: ' . $filename . ' is not available, skipping...';
	}

	//TODO extra photos, illustrative photos and property value photos
	if ($max == 0)
		break;
}

$messages = implode("<br />\n", $msgs);
$html_header->send();
?>
<body>
	<!--Obsah-->
	<div id="stred">
		<div id="levy">
<?=$messages?><br /><br />
			<a href="?diagnose">Zopakovat diagnostiku</a><br />
			<a href="?rename">Přejmenovat obrázky podle kódů (u všech produktů)</a><br />
			<a href="?rename&max=1">Přejmenovat obrázky podle kódů (maximálně pro jeden produkt)</a><br />
			<a href="?rename&max=2">Přejmenovat obrázky podle kódů (maximálně pro 2 produkty)</a><br />
			<a href="?rename&max=5">Přejmenovat obrázky podle kódů (maximálně pro 5 produktů)</a><br />
			<a href="?rename&max=10">Přejmenovat obrázky podle kódů (maximálně pro 10 produktů)</a><br />
		</div>
		<br class="clear" />
	</div>
</body>
</html>