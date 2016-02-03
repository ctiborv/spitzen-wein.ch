<?php
require_once 'csv_export_file.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'utils.inc.php';

class Kiwi_Exporter
{
	protected $_config;
	protected $_destination;
	protected $_filename;
	protected $_rows_exported;

	protected $_log_level;
	protected $_log_started;

	protected $_property_values;

	protected $_rights;

	// column types
	const ID = 1;
	const TITLE = 2;
	const CODE = 3;
	const URL = 4;
	const PAGETITLE = 5;
	const SHORTDESC = 6;
	const LONGDESC = 7;
	const NEWCOST = 8;
	const ORIGINALCOST = 9;
	const DISCOUNT = 10;
	const ACTION = 11;
	const NOVELTY = 12;
	const ACTIVE = 13;
	const PROPERTY = 14;
	const PRODUCTLINES = 15;	

	// log levels
	const LOG_NORMAL = 0;
	const LOG_VERBOSE = 1;
	const LOG_ALL = 4;

	// log levels assignment
	const LOG_QUERY = 3;

	public function __construct($fname = null, $rights)
	{
		$this->_config = null;
		$this->_rows_exported = 0;
		$this->_log_level = self::LOG_NORMAL;
		$this->_log_started = false;
		$this->_property_values = array();
		$this->loadConfig();
		$this->_destination = null;
		$this->_filename = null;
		if ($fname !== null)
			$this->setDestination($fname);
		$this->_rights = $rights;
	}

	public function setDestination($fname)
	{
		if (!$this->isFileValid($fname))
		{
			$this->log('Unzulässigkeit der Export-Datei: ' . $fname);
			return;
		}

		$this->log('Festlegen des Ziels für den Export: ' . $fname);
		try
		{
			$this->initDestination($fname);
			$this->_filename = $fname;
		}
		catch (Exception $e)
		{
			$this->_destination = null;
		}
	}

	public function setLogLevel($log_level = self::LOG_NORMAL)
	{
		$this->_log_level = $log_level;
	}

	protected function initDestination($fname)
	{
		$ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
		switch ($ext)
		{
			case 'csv':
				$this->_destination = new CSV_Export_File($fname, $this->_config);
				break;
			case 'xls':
				throw new Exception('XLS importieren ist nicht implementiert');
			default:
				throw new Exception('Unbekannte Ausgabe-Dateiformat');
		}
	}

	public function export()
	{
		if ($this->_rights->EShop !== true && !$this->_rights->EShop['Read'])
		{
			$this->logX('Sie haben keine Rechte, aus der Datenbank des Shop zu lesen!');
			return false;
		}

		if ($this->_destination === null)
		{
			$this->logX('Konnte Ausgabedatei nicht öffnen!');
			return false;
		}

		try
		{
			$this->saveHeader();
			$this->exportData();
			$result = true;
		}
		catch (Exception $e)
		{
			$this->logX('Ist ein Fehler aufgetreten: ' . $e->getMessage());
			$result = false;
		}

		$this->logX('Insgesamt exportierten Produkte: ' . $this->_rows_exported);
		$this->logLink();
		$this->endLog();
		return $result;
	}

	protected function loadConfig()
	{
		if ($this->_config !== null) return;
		$config = false;
		require 'kiwi_exporter_config.inc.php';
		if (!is_array($config))
			throw new Exception('$config Feld wurde nicht korrekt definiert in ihrer Konfigurationsdatei: kiwi_exporter_config.inc.php');

		$req_fields = array('ppv_separators', 'columns');
		foreach ($req_fields as $rf)
			if (!array_key_exists($rf, $config))
				throw new Exception("Fehlende erforderliche Parameter \"$rf\" Konfigurationsdatei: kiwi_exporter_config.inc.php");

		if (!array_key_exists('default', $config['ppv_separators']))
			throw new Exception("Fehlende erforderliche Parameter \"ppv_separators['default']\" Konfigurationsdatei: kiwi_exporter_config.inc.php");

		if (!array_key_exists('pl_separator', $config))
			throw new Exception("Fehlende erforderliche Parameter \"pl_separators\" Konfigurationsdatei: kiwi_exporter_config.inc.php");

		$this->_config = $config;
	}

	protected function saveHeader()
	{
		$this->log("Exportieren der ersten Reihe: " . count($this->_config['columns']) . " Spalten");
		$this->_destination->write(array_keys($this->_config['columns']));
	}

	protected function exportData()
	{
		$this->lockTables();

		$this->loadPropertyValues();

		$result = $this->mysql_query("SELECT ID, Title, Code, ShortDesc, URL, PageTitle, LongDesc, Collection, OriginalCost, NewCost, Discount, Action, Novelty, Active FROM products ORDER BY ID");
		while ($row = mysql_fetch_assoc($result))
			$this->exportRow(new Kiwi_DataRow($row));

		$this->unlockTables();
	}

	protected function exportRow($datarow)
	{
		$sqlka = array
		(
			self::ID => 'ID',
			self::TITLE => 'Title',
			self::CODE => 'Code',
			self::URL => 'URL',
			self::PAGETITLE => 'PageTitle',
			self::SHORTDESC => 'ShortDesc',
			self::LONGDESC => 'LongDesc',
			self::NEWCOST => 'NewCost',
			self::ORIGINALCOST => 'OriginalCost',
			self::DISCOUNT => 'Discount',
			self::ACTION => 'Action',
			self::NOVELTY => 'Novelty',
			self::ACTIVE => 'Active'
		);

		$separators =& $this->_config['ppv_separators'];
		$row = array();
		foreach ($this->_config['columns'] as $column => $type)
		{
			$sqlk = array_key_exists($type, $sqlka) ? $sqlka[$type] : null;
			switch ($type)
			{
				case self::ID:
				case self::TITLE:
				case self::CODE:
				case self::URL:
				case self::PAGETITLE:
				case self::SHORTDESC:
				case self::LONGDESC:
				case self::NEWCOST:
				case self::ORIGINALCOST:
					$row[] = $datarow->$sqlk;
					break;
				case self::PROPERTY:
					$separator = $separators[array_key_exists($column, $separators) ? $column : 'default'];
					$row[] = $this->getProductPropertyValues($datarow->ID, $column, $separator);
					break;
				case self::PRODUCTLINES:
					$separator = $this->_config['pl_separator'];
					$row[] = $this->getProductBinds($datarow->ID, $separator);
					break;
				default:
					throw new Exception('Unerwarteter Datentyp');
					break;
			}
		}
		
		$this->log('Produkt exportieren: ID: ' . $datarow->ID . ($datarow->Code !== '' ? " (Code: $datarow->Code)" : ''), self::LOG_VERBOSE);
		$this->_destination->write($row);
		$this->_rows_exported++;
	}

	protected function loadPropertyValues()
	{
		$this->log('Lädt die Eigenschaftenwerte von Produkten...');
		$this->_property_values = array();
		$query = "SELECT PPV.ID, PP.Name, PPV.Value FROM prodpvals AS PPV JOIN prodprops AS PP ON PPV.PID=PP.ID";
		$result = $this->mysql_query($query);
		while ($row = mysql_fetch_object($result))
			$this->_property_values[$row->Name][$row->ID] = $row->Value;

		foreach ($this->_config['columns'] as $column => $type)
			if ($type == self::PROPERTY && !array_key_exists($column, $this->_property_values))
				$this->logX("Eigenschaft für das Produkt \"$column\" existiert nicht..");
	}

	protected function retrieveProductProperties($id)
	{
		$pprops = array();
		$query = "SELECT PPV.ID FROM prodpvals AS PPV JOIN prodpbinds AS PPB ON PPV.ID=PPB.PPVID WHERE PPB.PID=$id ORDER BY PPV.Priority";
		$result = $this->mysql_query($query);
		while ($row = mysql_fetch_object($result))
			$pprops[] = $row->ID;

		return $pprops;
	}

	protected function getProductPropertyValues($id, $column, $separator)
	{
		if (!array_key_exists($column, $this->_property_values))
			return '';
		$pprops = $this->retrieveProductProperties($id);
		$result_a = array();
		foreach ($pprops as $ppvid)
		{
			if (array_key_exists($ppvid, $this->_property_values[$column]))
			{
				$value = $this->_property_values[$column][$ppvid];
				if (mb_strstr($value, $separator) !== false)
					$this->logX("Wert der Eigenschaft \"$column\": \"$value\" (produkt $id) enthält das ausgewählte Trennzeichen \"$separator\" und es müssen manuelle Korrekturen dieses Eintrag durchführen...");
				$result_a[] = $value;
			}
		}
		return implode($separator, $result_a);
	}

	protected function getProductBinds($id, $separator)
	{
		$query = "SELECT GID FROM prodbinds WHERE PID=$id";
		$result = $this->mysql_query($query);
		$binds = array();
		while ($row = mysql_fetch_row($result))
			$binds[] = $row[0];
		return implode($separator, $binds);
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
			if (!$this->_log_started)
				$this->beginLog();
			echo <<<EOT
log('$emsg');

EOT;
		}
	}

	protected function logX($msg, $level = self::LOG_NORMAL)
	{
		if ($level <= $this->_log_level)
		{
			$emsg = $this->js_escape($msg);
			if (!$this->_log_started)
				$this->beginLog();
			echo <<<EOT
logX('$emsg');

EOT;
		}
	}

	protected function logLink()
	{
		$filename = $this->js_escape($this->_filename);
		if (!$this->_log_started)
			$this->beginLog();	
		echo <<<EOT
logLink('Exportierte Datei kann über diesen Link heruntergeladen werden:', '$filename');

EOT;
	}

	protected function beginLog()
	{
		echo <<<EOT
<script type="text/javascript">

EOT;
		$this->_log_started = true;
	}

	protected function endLog()
	{
		echo <<<EOT
scrollToEnd();
</script>

EOT;
		$this->_log_started = false;
	}

	protected function lockTables()
	{
		$this->log('Tabellensperren der Datenbank');
		$this->mysql_query("LOCK TABLES products READ, prodbinds READ, prodprops AS PP READ, prodpbinds AS PPB READ, prodpvals AS PPV READ");
	}

	protected function unlockTables()
	{
		$this->log('Öffnen der Datenbank-Tabellen');
		$this->mysql_query("UNLOCK TABLES");
	}

	protected function mysql_query($query)
	{
		$this->log($query, self::LOG_QUERY);
		return mysql_query($query);
	}

	protected function isFileValid($file)
	{
		return strpos($file, '../export/') === 0;
	}
}
?>
