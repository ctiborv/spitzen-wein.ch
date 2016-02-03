<?php
require_once 'thumbnail_watermark/Thumbnail.class.php';

class Kiwi_Picture_Importer
{
	protected $_dir;
	protected $_pi;
	protected $_pics_imported;
	protected $_pics_imported_total; // persistent
	protected $_pics_remaining; // persistent
	protected $_pics_max;

	protected $_log_level;
	protected $_simulation; // when true, simulated import is performed

	protected $_rights;

	protected $_unique_name;

	protected $_code_index;

	// log levels
	const LOG_NORMAL = 0;
	const LOG_ALL = 4;

	// log levels assignment
	const LOG_DELETIONS = 1;
	const LOG_THUMBNAILS = 2;
	const LOG_QUERY = 3;

	public function __construct($dir, $rights, $max, $restart = false)
	{
		$this->_dir = $dir;
		if (substr($dir, -1) != '/') $this->_dir += '/';
		if (session_id() == '') session_start();
		$this->_pi = 0;
		$this->_pics_imported = 0;
		$this->_pics_max = $max;
		$this->initializeContinousImport($restart);
		$this->_log_level = self::LOG_NORMAL;
		$this->_simulation = false;
		$this->_rights = $rights;
		$this->_unique_name = (int)strtotime("now");
		$this->_code_index = null;
		$this->loadCodeIndex();
	}

	protected function initializeContinousImport($restart)
	{
		if (!$restart && isset($_SESSION['kiwi']['_pics_remaining']))
		{
			$this->_pics_imported_total = $_SESSION['kiwi']['_pics_imported_total'];
			$this->_pics_remaining = $_SESSION['kiwi']['_pics_remaining'];
			$this->logX('Erkannt ein anschließenden Import-Schritt...');
		}
		else
		{
			$this->_pics_imported_total = 0;
			$this->_pics_remaining = null;
			$this->saveContinousImportStatus();
		}
	}

	protected function saveContinousImportStatus()
	{
		$_SESSION['kiwi']['_pics_imported_total'] = $this->_pics_imported_total;
		$_SESSION['kiwi']['_pics_remaining'] = $this->_pics_remaining;
	}

	public function isComplete()
	{
		return $this->_pics_remaining == 0; // intentionally ==
	}

	public function setLogLevel($log_level = self::LOG_NORMAL)
	{
		$this->_log_level = $log_level;
	}

	public function simulate($simulation = true)
	{
		if ($simulation)
			$this->log('Einrichten einer Simulation System');
		else
			$this->log('Einrichten einen scharfen Modus');
		$this->_simulation = $simulation;
	}

	public function import()
	{
		if ($this->_rights->EShop !== true && !$this->_rights->EShop['Write'])
		{
			$this->logX('Sie haben keine Rechte für die Eintragung im Shop!');
			return false;
		}

		try
		{
			$this->importPictures();
			$this->_pics_imported_total += $this->_pics_imported;
			$this->saveContinousImportStatus();
			$result = true;
		}
		catch (Exception $e)
		{
			$this->_pics_imported_total += $this->_pics_imported;
			$this->_pics_remaining = 0;
			saveContinousImportStatus();
			$this->logX('Ist ein Fehler aufgetreten: ' . $e->getMessage());
			$result = false;
		}

		$this->logX('Importierte Dateien: ' . $this->_pics_imported_total . ' (in Summe: ' . ($this->_pics_imported_total + $this->_pics_remaining) . ')');
		if ($this->_pics_remaining > 0)
			$this->logContinueLink();
		else
			$this->logX('Import ist abgeschlossen.');

		return $result;
	}

	protected function importPictures()
	{
		if (!$this->isDirValid($this->_dir))
			throw new Exception('Unzulässige Verzeichnis');

		$this->lockTables();

		$files = scandir($this->_dir);
		$this->_pics_remaining = 0;
		foreach ($files as $file)
		{
			$basename = basename($file);
			if ($this->validFileName($basename))
			{
				$this->_pi += 1;
				if ($this->_pi <= $this->_pics_imported_total) continue;
				if ($this->_pics_imported >= $this->_pics_max)
				{
					$this->_pics_remaining++;
					continue;
				}
				$this->importPicture($file);
			}
		}

		$this->unlockTables();
	}

	protected function importPicture($picture)
	{
		$bname = basename($picture);
		$pid = $this->getPIDFromPictureName($picture);
		if ($pid === false)
		{
			$this->log('Ignorieren von Dateien: ' . basename($picture));
			return;		
		}

		if (!$this->removeProductPhoto($pid))
		{
			$this->log('Ignorieren von Dateien: ' . basename($picture));
			return;
		}

		$this->log('das Bild importieren: ' . $bname);
		$target_dir = KIWI_DIR_PRODUCTS . 'photo/';
		$photo = $this->getUniqueFileName($target_dir, $bname);
		if (!copy($this->_dir . $picture, $target_dir . $photo))
			throw new Exception("Fehler beim Kopieren der Datei $picture bis $target_dir$photo");
		$this->createThumbs($photo, array('detail', 'catalog', 'catalog2', 'collection'));
		$query = "UPDATE products SET Photo='$photo', LastChange=CURRENT_TIMESTAMP WHERE ID=$pid";
		$this->mysql_invasive_query($query);
		$query = "UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$pid";
		$this->mysql_invasive_query($query);
		if ($this->_simulation) unlink($target_dir . $photo); // delete the copy if we are running simulation only
		$this->_pics_imported++;
	}

	protected function createThumbs($file, $targets)
	{
		global $eshop_picture_config;
		$dir_photo = KIWI_DIR_PRODUCTS;
		foreach ($targets as $target)
		{
			if (!array_key_exists($target, $eshop_picture_config))
				throw new Exception('Für das Vorschaubild Ziel unbekannt');
			if (is_array($eshop_picture_config[$target]))
			{
				$t = new Thumbnail("{$dir_photo}photo/$file");
				$t->size($eshop_picture_config[$target][0], $eshop_picture_config[$target][1]);
				$t->quality = 80;
				$t->output_format='JPG';
				$this->log("Erstellen einer Miniaturansicht zu das Bild $file in das Verzeichnis $target");
				$t->process();
				if (!$this->_simulation)
				{
					$status = $t->save("{$dir_photo}$target/$file");
					if (!$status)
						throw new Exception('Fehler beim Speichern einer Miniaturansicht des Bildes');
				}
			}
		}
	}

	protected function removeProductPhoto($pid)
	{
		$query = "SELECT Photo FROM products WHERE ID=$pid";
		$result = $this->mysql_noninvasive_query($query);
		if ($row = mysql_fetch_row($result))
		{
			$photo = $row[0];
			if ($photo !== '')
			{
				$this->log("Das Löschen des Original-Foto des Produktes $pid", self::LOG_DELETIONS);
				$this->deleteProductFile($photo, array('detail', 'catalog', 'catalog2', 'collection'));		
				$this->mysql_invasive_query("UPDATE products SET Photo='', LastChange=CURRENT_TIMESTAMP WHERE ID=$pid");
			}
			return true;
		}
		else
			return false;
	}

	protected function getUniqueFileName($dir, $suggestion)
	{
		if (substr($dir, -1) != '/')
			throw new Exception('Das Verzeichnis ist nicht durch einen Schrägstrich beendet.');

		$path_parts = pathinfo($suggestion);
		$ext = $path_parts['extension'];
		$handle = @fopen($dir . $suggestion, 'x');
		if ($handle !== false)
		{
			$fname = $suggestion;
			fclose($handle);
		}
		else
		{
			$fname = $this->_unique_name++ . 'i.' . $ext;
		}
		//chmod($dir . $fname, 0666);
		return $fname;
	}

	protected function updateProductBind($id)
	{
		$query = "UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$id";
		$this->mysql_invasive_query($query);
	}

	protected function deleteProductFile($filename, $thumbsloc)
	{
		global $eshop_picture_config;
		$dir = KIWI_DIR_PRODUCTS;

		if (!($this->unlink("{$dir}photo/$filename")))
			throw new Exception("Fehler beim Löschen der Datei mit Foto");

		foreach ($thumbsloc as $loc)
		{
			if (!array_key_exists($loc, $eshop_picture_config))
				throw new Exception('Vorschaubild Foto von unbekannten Ort');
			if (is_array($eshop_picture_config[$loc]))
				if (!($this->unlink("{$dir}$loc/$filename")))
					throw new Exception("Konnte nicht eine Datei mit einem Vorschaubild Foto löschen");
		}
	}

	protected function js_escape($arg)
	{
		$earg = mb_ereg_replace("\\\\", "\\\\", $arg);
		$eearg = mb_ereg_replace("'", "\\'", $earg);
		return $eearg;
	}

	protected function log($msg, $level = self::LOG_NORMAL)
	{
		if ($level <= $this->_log_level)
		{
			$emsg = $this->js_escape($msg);
			if ($this->_simulation)
				$emsg = "S: $emsg";
			echo <<<EOT
<script type="text/javascript">
log('$emsg');
scrollToEnd();
</script>

EOT;
		}
	}

	protected function logX($msg, $level = self::LOG_NORMAL)
	{
		if ($level <= $this->_log_level)
		{
			$emsg = $this->js_escape($msg);
			if ($this->_simulation)
				$emsg = "S: $emsg";
			echo <<<EOT
<script type="text/javascript">
logX('$emsg');
scrollToEnd();
</script>

EOT;
		}
	}

	protected function logContinueLink()
	{
			echo <<<EOT
<script type="text/javascript">
logContinueLink();
scrollToEnd();
</script>

EOT;
	}

	protected function lockTables()
	{
		$this->log('Tabellensperren der Datenbank');
		$this->mysql_noninvasive_query("LOCK TABLES products WRITE, prodbinds WRITE");
	}

	protected function unlockTables()
	{
		$this->log('Öffnen der Datenbank-Tabellen');
		$this->mysql_noninvasive_query("UNLOCK TABLES");
	}

	protected function mysql_invasive_query($query)
	{
		$this->log($query, self::LOG_QUERY);
		if (!$this->_simulation) $result = mysql_query($query);
		else $result = null;
		return $result;
	}

	protected function mysql_noninvasive_query($query)
	{
		$this->log($query, self::LOG_QUERY);
		return mysql_query($query);
	}

	protected function unlink($file)
	{
		if (!$this->_simulation) $result = unlink($file);
		else $result = true;
		return $result;
	}

	protected function validFileName($fname)
	{
		return eregi(".+\\.jpg", $fname);
		//return eregi("[0-9]+\\.jpg", $fname);
	}

	protected function isDirValid($dir)
	{
		return strpos($dir, '../import/') === 0;
	}

	protected function getPIDFromPictureName($picture)
	{
		if ($picture[0] == '_')
			$pid = (int)basename(strtolower(substr($picture, 1)), '.jpg');
		else
		{
			$code = basename(strtolower($picture), '.jpg');
			if (array_key_exists($code, $this->_code_index))
				$pid = $this->_code_index[$code][0];
			else
				$pid = false;
		}
		return $pid;
	}

	protected function loadCodeIndex()
	{
		if ($this->_code_index === null)
		{
			$this->_code_index = array();
			$result = mysql_query("SELECT ID, Code FROM products");
			while ($row = mysql_fetch_object($result))
			{
				$code_low = strtolower($row->Code);
				if (array_key_exists($code_low, $this->_code_index) && count($this->_code_index[$code_low]) == 1)
					$this->logX("Warnung: Product Code '$row->Code' ist nicht einzigartig!");
				$this->_code_index[$code_low][] = $row->ID;
			}
		}
	}
}
?>
