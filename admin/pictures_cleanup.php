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

define ('ESHOP_DIR', '../documents/eshop/');

$html_header = new HTML_Header;
$html_header->title = "$project - Content Management System";
$html_header->addCss('none.css');

$msgs = array();
$delete = array_key_exists('delete', $_GET);
$rename = array_key_exists('rename', $_GET);
$filesonly = array_key_exists('filesonly', $_GET);
$dbonly = array_key_exists('dbonly', $_GET);

if ($delete && $rename)
{
	$rename = false;
	$msgs[] = 'WARNING: Query variable "rename" will be ignored if "delete" variable is present...';
}

if ($filesonly && $dbonly)
{
	$filesonly = $dbonly = false;
	$msgs[] = 'WARNING: Query variables "filesonly" and "dbonly" cannot be used together, ignoring both...';
}

$pic_count = array('M' => 0, 'E' => 0, 'I' => 0, 'P' => 0);

// Read the contents of database:

$pictures = array();
$result = mysql_query("SELECT ID, Photo FROM products WHERE Photo!=''");
while ($row = mysql_fetch_object($result))
{
	if (!array_key_exists($row->Photo, $pictures))
		$pictures[$row->Photo] = array();
	$pictures[$row->Photo][] = array('M', $row->ID); // M means "Main"
	$pic_count['M']++;
}
$msgs[] = $pic_count['M'] . ' main product photos retrieved from database...';
mysql_free_result($result);

$result = mysql_query("SELECT ID, FileName FROM prodepics");
while ($row = mysql_fetch_object($result))
{
	if (!array_key_exists($row->FileName, $pictures))
		$pictures[$row->FileName] = array();
	$pictures[$row->FileName][] = array('E', $row->ID); // E means "Extra"
	$pic_count['E']++;
}
$msgs[] = $pic_count['E'] . ' extra product photos retrieved from database...';
mysql_free_result($result);

$result = mysql_query("SELECT ID, FileName FROM prodipics");
while ($row = mysql_fetch_object($result))
{
	if (!array_key_exists($row->FileName, $pictures))
		$pictures[$row->FileName] = array();
	$pictures[$row->FileName][] = array('I', $row->ID); // I means "Illustrative"
	$pic_count['I']++;
}
$msgs[] = $pic_count['I'] . ' illustrative product photos retrieved from database...';
mysql_free_result($result);

$result = mysql_query("SELECT PID, PPVID, Photo FROM prodpbinds WHERE Photo!=''");
while ($row = mysql_fetch_object($result))
{
	if (!array_key_exists($row->Photo, $pictures))
		$pictures[$row->Photo] = array();
	$pictures[$row->Photo][] = array('P', $row->PID, $row->PPVID); // P means "Property"
	$pic_count['P']++;
}
$msgs[] = $pic_count['P'] . ' photos bound to property value of some product retrieved from database...';
mysql_free_result($result);

// Read the contents of directories:

$dirnames = array('photo', 'catalog', 'catalog2', 'collection', 'detail', 'extra', 'illustrative');
$dirs = array();
foreach ($dirnames as $dirname)
{
	$dir = dir(ESHOP_DIR . $dirname);
	$dirs[$dirname] = array();
	while (false !== ($entry = $dir->read()))
	{
		if ($entry !== '.' && $entry !== '..')
			$dirs[$dirname][$entry] = true;
	}
	$dir->close();
}

// Fix the DB records, where DB associates with non-existent file:
if (!$filesonly)
{
	foreach ($pictures as $filename => $records)
	{
		foreach ($records as $record)
		{
			switch ($record[0])
			{
				case 'M':
					$id = $record[1];
					if (!array_key_exists($filename, $dirs['photo']))
					{
						if ($delete)
						{
							$msgs[] = "Removing product ($id) association with non-existent file: $filename";
							mysql_query("UPDATE products SET Photo='' WHERE ID=$id");
						}
						else
							$msgs[] = "Product ($id) associates with non-existent file: $filename";
					}
					break;
				case 'E':
					$id = $record[1];
					if (!array_key_exists($filename, $dirs['extra']))
					{
						if ($delete)
						{
							$msgs[] = "Removing extra photo association ($id) with non-existent file: $filename";
							mysql_query("DELETE FROM prodepics WHERE ID=$id");
						}
						else
							$msgs[] = "Extra photo association ($id) with non-existent file: $filename";
					}					
					break;
				case 'I':
					$id = $record[1];
					if (!array_key_exists($filename, $dirs['illustrative']))
					{
						if ($delete)
						{
							$msgs[] = "Removing illustrative photo association ($id) with non-existent file: $filename";
							mysql_query("DELETE FROM prodipics WHERE ID=$id");
						}
						else
							$msgs[] = "Illustrative photo association ($id) with non-existent file: $filename";
					}					
					break;
				case 'P':
					$pid = $record[1];
					$ppvid = $record[2];
					if (!array_key_exists($filename, $dirs['photo']))
					{
						if ($delete)
						{
							$msgs[] = "Removing property vale ($ppvid) product ($pid) bind association with non-existent file: $filename";
							mysql_query("DELETE FROM prodpbinds WHERE PID=$pid AND PPVID=$ppvid");
						}
						else
							$msgs[] = "Property value ($ppvid) product ($pid) bind associates with non-existent file: $filename";
					}
					break;
			}
		}
	}
}

// Remove the files, for which no association exists:
if (!$dbonly)
{
	// main photos
	foreach ($dirs['photo'] as $filename => $true)
	{
		if (!array_key_exists($filename, $pictures))
		{
			if ($delete)
			{
				$msgs[] = "Removing non-associated file: photo/$filename";
				unlink(ESHOP_DIR . "photo/$filename");
			}
			elseif ($rename)
			{
				if (substr($filename, -5) != '.dead')
				{
					$msgs[] = "Renaming non-associated file: photo/$filename to photo/$filename.dead";
					rename(ESHOP_DIR . "photo/$filename", ESHOP_DIR . "photo/$filename.dead");
				}
			}
			else
				$msgs[] = "No association exists for file: photo/$filename";
		}
	}

	// catalog photos
	foreach ($dirs['catalog'] as $filename => $true)
	{
		$found = false;
		if (array_key_exists($filename, $pictures))
		{
			foreach ($pictures[$filename] as $record)
				if ($record[0] == 'M' || $record[0] == 'P')
				{
					$found = true;
					break;
				}
		}

		if (!$found)
		{
			if ($delete)
			{
				$msgs[] = "Removing non-associated file: catalog/$filename";
				unlink(ESHOP_DIR . "catalog/$filename");
			}
			elseif ($rename)
			{
				if (substr($filename, -5) != '.dead')
				{
					$msgs[] = "Renaming non-associated file: catalog/$filename to catalog/$filename.dead";
					rename(ESHOP_DIR . "catalog/$filename", ESHOP_DIR . "catalog/$filename.dead");
				}
			}
			else
				$msgs[] = "No association exists for file: catalog/$filename";
		}
	}

	// catalog2 photos
	foreach ($dirs['catalog2'] as $filename => $true)
	{
		$found = false;
		if (array_key_exists($filename, $pictures))
		{
			foreach ($pictures[$filename] as $record)
				if ($record[0] == 'M' || $record[0] == 'P')
				{
					$found = true;
					break;
				}
		}

		if (!$found)
		{
			if ($delete)
			{
				$msgs[] = "Removing non-associated file: catalog2/$filename";
				unlink(ESHOP_DIR . "catalog2/$filename");
			}
			elseif ($rename)
			{
				if (substr($filename, -5) != '.dead')
				{
					$msgs[] = "Renaming non-associated file: catalog2/$filename to catalog2/$filename.dead";
					rename(ESHOP_DIR . "catalog2/$filename", ESHOP_DIR . "catalog2/$filename.dead");
				}
			}
			else
				$msgs[] = "No association exists for file: catalog2/$filename";
		}
	}

	// collection photos
	foreach ($dirs['collection'] as $filename => $true)
	{
		$found = false;
		if (array_key_exists($filename, $pictures))
		{
			foreach ($pictures[$filename] as $record)
				if ($record[0] == 'M' || $record[0] == 'P')
				{
					$found = true;
					break;
				}
		}

		if (!$found)
		{
			if ($delete)
			{
				$msgs[] = "Removing non-associated file: collection/$filename";
				unlink(ESHOP_DIR . "collection/$filename");
			}
			elseif ($rename)
			{
				if (substr($filename, -5) != '.dead')
				{
					$msgs[] = "Renaming non-associated file: collection/$filename to collection/$filename.dead";
					rename(ESHOP_DIR . "collection/$filename", ESHOP_DIR . "collection/$filename.dead");
				}
			}
			else
				$msgs[] = "No association exists for file: collection/$filename";
		}
	}

	// detail photos
	foreach ($dirs['detail'] as $filename => $true)
	{
		$found = false;
		if (array_key_exists($filename, $pictures))
		{
			foreach ($pictures[$filename] as $record)
				if ($record[0] == 'M' || $record[0] == 'P')
				{
					$found = true;
					break;
				}
		}

		if (!$found)
		{
			if ($delete)
			{
				$msgs[] = "Removing non-associated file: detail/$filename";
				unlink(ESHOP_DIR . "detail/$filename");
			}
			elseif ($rename)
			{
				if (substr($filename, -5) != '.dead')
				{
					$msgs[] = "Renaming non-associated file: detail/$filename to detail/$filename.dead";
					rename(ESHOP_DIR . "detail/$filename", ESHOP_DIR . "detail/$filename.dead");
				}
			}
			else
				$msgs[] = "No association exists for file: detail/$filename";
		}
	}

	// extra photos
	foreach ($dirs['extra'] as $filename => $true)
	{
		$found = false;
		if (array_key_exists($filename, $pictures))
		{
			foreach ($pictures[$filename] as $record)
				if ($record[0] == 'E')
				{
					$found = true;
					break;
				}
		}

		if (!$found)
		{
			if ($delete)
			{
				$msgs[] = "Removing non-associated file: extra/$filename";
				unlink(ESHOP_DIR . "extra/$filename");
			}
			elseif ($rename)
			{
				if (substr($filename, -5) != '.dead')
				{
					$msgs[] = "Renaming non-associated file: extra/$filename to extra/$filename.dead";
					rename(ESHOP_DIR . "extra/$filename", ESHOP_DIR . "extra/$filename.dead");
				}
			}
			else
				$msgs[] = "No association exists for file: extra/$filename";
		}
	}

	// illustrative photos
	foreach ($dirs['illustrative'] as $filename => $true)
	{
		$found = false;
		if (array_key_exists($filename, $pictures))
		{
			foreach ($pictures[$filename] as $record)
				if ($record[0] == 'I')
				{
					$found = true;
					break;
				}
		}

		if (!$found)
		{
			if ($delete)
			{
				$msgs[] = "Removing non-associated file: illustrative/$filename";
				unlink(ESHOP_DIR . "illustrative/$filename");
			}
			elseif ($rename)
			{
				if (substr($filename, -5) != '.dead')
				{
					$msgs[] = "Renaming non-associated file: illustrative/$filename to illustrative/$filename.dead";
					rename(ESHOP_DIR . "illustrative/$filename", ESHOP_DIR . "illustrative/$filename.dead");
				}
			}
			else
				$msgs[] = "No association exists for file: illustrative/$filename";
		}
	}
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
			<a href="?delete">Opravit záznamy v databázi a smazat přebytečné soubory</a><br />
			<a href="?delete&dbonly">Opravit záznamy v databázi</a><br />
			<a href="?delete&filesonly">Smazat přebytečné soubory</a><br />
			<a href="?rename">Přejmenovat přebytečné soubory (přidat příponu .dead)</a><br />
		</div>
		<br class="clear" />
	</div>
</body>
</html>