<?php
require_once 'csv_import_file.class.php';
require_once 'kiwi_importer_transformators.inc.php';
require_once 'kiwi_url_generator.class.php';
require_once 'utils.inc.php';

class Kiwi_Importer
{
	protected $_config;
	protected $_source;
	protected $_total_cols;
	protected $_columns;
	protected $_rows_imported;

	protected $_log_level;
	protected $_log_started;
	protected $_simulation; // when true, simulated import is performed

	protected $_property_ids;
	protected $_property_names;
	protected $_property_values;
	protected $_property_value_new_priorities;

	protected $_max_id;
	protected $_indexes;

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
	const PROPERTY = 10;
	const PRODUCTLINES = 11;

	// log levels
	const LOG_NORMAL = 0;
	const LOG_PROPERTIES = 1;
	const LOG_VERBOSE = 2;
	const LOG_ALL = 4;

	// log levels assignment
	const LOG_QUERY = 3;

	// default product title
	const DEFAULT_TITLE = '__unbenannt__';

	public function __construct($fname = null, $rights, $config_id = null)
	{
		$this->_config = null;
		$this->_total_cols = null;
		$this->_columns = null;
		$this->_rows_imported = 0;
		$this->_log_level = self::LOG_NORMAL;
		$this->_log_started = false;
		$this->_simulation = false;
		$this->_property_ids = null;
		$this->_property_names = null;
		$this->_property_values = array();
		$this->_property_value_new_priorities = null;
		$this->loadConfig($config_id);
		$this->_source = null;
		if ($fname !== null)
			$this->setSource($fname);
		$this->_max_id = null;
		$this->_indexes = null;
		$this->_rights = $rights;
	}

	public function setSource($fname)
	{
		if (!$this->isFileValid($fname))
		{
			$this->log('Unzulässige Datei für Import: ' . $fname);
			return;
		}

		$this->log('Einstellen der Quelldatei für Import: ' . $fname);
		try
		{
			$this->initSource($fname);
		}
		catch (Exception $e)
		{
			$this->_source = null;
		}
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

	protected function initSource($fname)
	{
		if (file_exists($fname))
		{
			$ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
			switch ($ext)
			{
				case 'csv':
					$this->_source = new CSV_Import_File(/*$tmp*/$fname, $this->_config);
					break;
				case 'xls':
					throw new Exception('XLS importieren ist nicht implementiert');
				default:
					throw new Exception('Unbekannte Format der Eingabedatei');
			}
		}
		else
			throw new Exception('Datei mit diesem Namen existiert nicht');
	}

	public function import()
	{
		if ($this->_rights->EShop !== true && !$this->_rights->EShop['Write'])
		{
			$this->logX('Sie haben keine Rechte für die Eintragung im Shop!');
			return false;
		}

		if ($this->_source === null)
		{
			$this->logX('Konnte nicht die Eingabedatei zu öffnen!');
			return false;
		}

		try
		{
			$this->loadHeader();
			$this->importData();
			$result = true;
		}
		catch (Exception $e)
		{
			$this->logX('Ist ein Fehler aufgetreten: ' . $e->getMessage());
			$result = false;
		}

		$this->logX('Insgesamt importierte Produkte: ' . $this->_rows_imported);
		$this->endLog();
		return $result;
	}

	protected function loadConfig($config_id = null)
	{
		if ($this->_config !== null) return;
		$config = false;
		if ($config_id === null)
			$config_id = '';

		if ($config_id !== '')
			$config_id = '.' . $config_id;

		require "kiwi_importer_config$config_id.inc.php";
		if (!is_array($config))
			throw new Exception("\$config wurde nicht korrekt definiert in ihrer Konfigurationsdatei: kiwi_importer_config$config_id.inc.php");

		$req_fields = array('ppv_separators', 'pl_separator', 'columns', 'transformations');
		foreach ($req_fields as $rf)
			if (!array_key_exists($rf, $config))
				throw new Exception("Fehlende erforderliche Parameter \"$rf\" Konfigurationsdatei: kiwi_importer_config$config_id.inc.php");

		if (!array_key_exists('default', $config['ppv_separators']))
			throw new Exception("Fehlende erforderliche Parameter \"ppv_separators['default']\" Konfigurationsdatei: kiwi_importer_config$config_id.inc.php");

		if (!array_key_exists('default', $config['transformations']))
			throw new Exception("Fehlende erforderliche Parameter \"transformations['default']\" Konfigurationsdatei: kiwi_importer_config$config_id.inc.php");

		$this->_config = $config;
	}

	protected function loadHeader()
	{
		if ($this->_columns !== null) return;
		$this->_columns = array
		(
			self::ID => null,
			self::TITLE => null,
			self::CODE => null,
			self::URL => null,
			self::PAGETITLE => null,
			self::SHORTDESC => null,
			self::LONGDESC => null,
			self::ORIGINALCOST => null,
			self::NEWCOST => null,
			self::PROPERTY => array(),
			self::PRODUCTLINES => null
		);

		if (array_key_exists('header', $this->_config) && is_array($this->_config['header']))
		{
			$this->log("CSV Header Quelle bestimmt Konfigurationsdatei");
			$row = $this->_config['header'];
			if (array_key_exists('skip_header', $this->_config) && $this->_config['skip_header'])
			{
				$this->log("Header überspringen in CSV-Datei angegeben");
				$this->_source->read();
			}
		}
		else
		{
			$this->log("CSV Header herkunftsgesichert der ersten Zeile der Datei");
			$row = $this->_source->read();
		}

		$this->_total_cols = count($row);
		$this->log("Laut einer CSV-Datei-Header Spalten $this->_total_cols", self::LOG_VERBOSE);
		foreach ($row as $i => $ritem)
		{
			$item = mb_trim($ritem);
			if (array_key_exists($item, $this->_config['columns']))
			{
				if ($this->_config['columns'][$item] == self::PRODUCTLINES)
				{
					if ($this->_columns[self::PRODUCTLINES] === null)
					{
						$this->log("Gefunden Verknüpfen von Informationen mit einer Reihe von Produkten \"$item\" in der Spalte $i", self::LOG_VERBOSE);
						$this->_columns[self::PRODUCTLINES] = array('col' => $i, 'name' => $item);
					}
					else
					{
						$this->logX("Gefunden Verknüpfen von Duplizität Informationen mit einer Reihe von Produkten \"$item\" in $i Spalte wird die Spalte ignoriert...", self::LOG_VERBOSE);
					}
				}
				elseif ($this->_config['columns'][$item] == self::PROPERTY)
				{
					$this->log("Gefunden Eigenschaft \"$item\ in der Spalte $i", self::LOG_VERBOSE);
					$this->_columns[self::PROPERTY][] = array('col' => $i, 'name' => $item);
				}
				else
				{
					$this->log("Gefunden elementar Eigenschaft \"$item\" in der Spalte $i", self::LOG_VERBOSE);
					$this->_columns[$this->_config['columns'][$item]] = array('col' => $i, 'name' => $item);
				}
			}
		}
	}

	protected function importData()
	{
		if ($this->_columns === null)
			throw new Exception('Unbekannte Datei-Header');

		$this->lockTables();

		$this->loadIndexes();

		$row = $this->_source->read();
		while ($row !== false)
		{
			$this->importRow($row);
			$row = $this->_source->read();
		}

		$this->unlockTables();
	}

	protected function importRow($row)
	{
		if (count($row) !== $this->_total_cols)
			throw new Exception("CSV-Datei hat eine Reihe von " . count($row) . "-Spalten (die erwartete Anzahl der Spalten ist $this->_total_cols, die Inhalte der defekten Reihe: >>>" . implode('#', $row) . "<<<)");

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
			self::ORIGINALCOST => 'OriginalCost'
		);

		$sqla = array();
		$props = array();
		$binds = null;
		$id = null;
		$code = null;
		$title = null;
		foreach ($this->_columns as $key => $val)
		{
			if ($val === null) continue;

			if ($key !== self::PROPERTY) // vlastnosti jsou transformovány jinde, až po rozdělení vstupu na jednotlivé položky
			{
				$transformations = $this->_config['transformations'][array_key_exists($val['name'], $this->_config['transformations']) ? $val['name'] : 'default'];
				if (!is_array($transformations))
					$transformations = array($transformations);
				foreach ($transformations as $transformator)
					if ($transformator && function_exists($transformator))
						$row[$val['col']] = $transformator($row[$val['col']]);
			}

			switch ($key)
			{
				case self::ID:
					$id = (int)$row[$val['col']];
					if ($id < 0)
						throw new Exception('Inakzeptable Product ID');
					break;
				case self::TITLE:
					$title = $row[$val['col']];
					break;
				case self::CODE:
					$code = $row[$val['col']]; // intentionally no break
				case self::URL:
				case self::PAGETITLE:
				case self::SHORTDESC:
				case self::LONGDESC:
				case self::NEWCOST:
				case self::ORIGINALCOST:
					$sqla[$sqlka[$key]] = mysql_real_escape_string($row[$val['col']]);
					break;
				case self::PROPERTY:
					foreach ($val as $propval)
						$props[$propval['name']] = $row[$propval['col']];
					break;
				case self::PRODUCTLINES:
					$binds = $row[$val['col']];
					break;
				default:
					throw new Exception('Unerwarteter Datentyp');
					break;
			}
		}

		if (!$id && ($code === null || $code === ''))
			throw new Exception('Fehlende-ID und Produkt-Code');

		if (!$id)
		{
			if ($id = $this->Code2ID($code)) // intentionally =
				$new = false;
			else
			{
				$id = $this->getNewID();
				$new = true;
			}
		}
		else
			$new = !$this->doesIDExist($id);

		if ($title === null && $new || $title === '')
			$title = self::DEFAULT_TITLE;
		
		$oldtitle = $this->getOldTitle($id);
		$newprefix = array_key_exists('new_product_title_prefix', $this->_config) ? $this->_config['new_product_title_prefix'] : '';
		$old_product_skip_title = array_key_exists('old_product_skip_title', $this->_config) ? $this->_config['old_product_skip_title'] : false;

		if ($title !== null && ($new || !$old_product_skip_title) && $title !== $oldtitle && $newprefix . $title !== $oldtitle)
		{
			// v případě, že nejsou v csv sloupce s URL a PageTitle, vygeneruj je
			if ($this->_columns[self::URL] === null)
			{
				//$url = Kiwi_URL_Generator::generate($title);
				$url = $this->generateURL($title);
				$sqla[$sqlka[self::URL]] = mysql_real_escape_string($url);
			}
			if ($this->_columns[self::PAGETITLE] === null)
			{
				$htitle = $this->generateTitle($title);
				$sqla[$sqlka[self::PAGETITLE]] = mysql_real_escape_string($htitle);
			}

			if ($new)
				$title = $newprefix . $title;
			
			$sqla[$sqlka[self::TITLE]] = mysql_real_escape_string($title);
		}

		$sqla[$sqlka[self::ID]] = $id;
		$this->insertProductRow($sqla);
		$this->updateProductProperties($props, $id);
		$this->updateProductBinds($binds, $id);
		$this->_rows_imported++;
	}

	protected function updateProductBinds($binds, $id)
	{
		if ($this->_columns[self::PRODUCTLINES] !== null)
		{
			// přidej či odeber vazby na produkt:
			if ($binds === null || $binds === '')
				$binds_a = array();
			else
				$binds_a = array_flip(explode($this->_config['pl_separator'], $binds));
			$delete = array();
			$insert = array();
			$present = array();

			$query = "SELECT ID, GID FROM prodbinds WHERE PID=$id";
			$result = $this->mysql_noninvasive_query($query);
			while ($row = mysql_fetch_row($result))
				$present[$row[1]] = $row[0];

			foreach ($present as $bind => $bind_id)
				if (!array_key_exists($bind, $binds_a))
					$delete[$bind] = $bind_id;

			foreach ($binds_a as $bind => $dummy)
				if (!array_key_exists($bind, $present))
					$insert[] = $bind;

			if (!empty($delete))
			{
				$bind_ids_str = implode(',', $delete);
				$this->log('Ich nehmen das Produkt aus der Produktlinie ab: ' . implode(', ', array_keys($delete)), self::LOG_VERBOSE);
				$query = "DELETE FROM prodbinds WHERE ID IN ($bind_ids_str)";
				$this->mysql_invasive_query($query);
			}

			if (!empty($insert))
			{
				$insert_csl = implode(',', $insert);
				$query = "SELECT ID FROM eshop WHERE ID IN ($insert_csl)";
				$result = $this->mysql_noninvasive_query($query);
				$invalid_lines = array_flip($insert);
				$valid_lines = array();
				while ($row = mysql_fetch_row($result))
				{
					unset($invalid_lines[$row[0]]);
					$valid_lines[] = $row[0];
				}

				if (!empty($invalid_lines))
				{
					$nel_str = count($invalid_lines) > 1 ? 'nicht-existenten Serien' : 'nicht-existenten Serie';
					$this->logX('Versuch zum Einfügen eines Produkts in ' . $nel_str . ': ' . implode(', ', array_keys($invalid_lines)));
				}

				if (!empty($valid_lines))
				{
					$priorities = array();
					foreach ($valid_lines as $new_gid)
						$priorities[$new_gid] = 0;
					$valid_lines_csl = implode(',', $valid_lines);
					$query = "SELECT GID, Max(Priority) FROM prodbinds WHERE PID=$id GROUP BY GID HAVING GID IN ($valid_lines_csl)";
					$result = $this->mysql_noninvasive_query($query);
					while ($row = mysql_fetch_row($result))
						$priorities[$row[0]] = $row[1];

					$this->log('Folgende Produkte in Produkt-Serien: ' . implode(', ', $valid_lines), self::LOG_VERBOSE);
					$query_base = "INSERT INTO prodbinds(PID, GID, Priority, Active) VALUES ";
					foreach ($valid_lines as $new_gid)
					{
						$priority = ++$priorities[$new_gid];
						$query = $query_base . "($id, $new_gid, $priority, 1)";
						$this->mysql_invasive_query($query);
					}
				}
			}
		}

		$query = "UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$id";
		$this->mysql_invasive_query($query);
	}

	protected function insertProductRow($sqla)
	{
		$icols_a = $ivals_a = $updates_a = array();
		foreach ($sqla as $key => $val)
		{
			$icols_a[] = $key;
			$ivals_a[] = "'$val'";
			$updates_a[] = "$key=VALUES($key)";
		}

		$icols_a[] = 'LastChange';
		$ivals_a[] = 'CURRENT_TIMESTAMP';
		$updates_a[] = 'LastChange=CURRENT_TIMESTAMP';

		$icols_a[] = 'Active';
		$ivals_a[] = (array_key_exists('new_products_active', $this->_config) && $this->_config['new_products_active']) ? 1 : 0;
		// $updates_a[] = 'Active=1'; // no need to activate deactivated product

		$icols = implode(', ', $icols_a);
		$ivals = implode(', ', $ivals_a);
		$updates = implode(', ', $updates_a);

		if (array_key_exists('ID', $sqla))
			$this->log('Importieren eines Produkts ' . $sqla['ID']);
		else
			$this->log('Importieren eines Produkts mit einem unbekannten ID');

		$query = "INSERT INTO products($icols) VALUES ($ivals) ON DUPLICATE KEY UPDATE $updates";

		$this->mysql_invasive_query($query);
	}

	protected function updateProductProperties($props, $id)
	{
		// $props is array: name => list separated by configured separator
		
		// retrieve property values of the product, cathegorized
		$ppvals = $this->retrieveProductProperties($id);
		
		// identify new property values and binds and the ones to be deleted
		$props_a = array();
		$new_pvals = array();
		$del_pvals = array();
		foreach ($props as $pname => $pvals)
		{
			$propid = $this->getPropertyId($pname);
			if ($propid !== null)
			{							
				$sep = $this->_config['ppv_separators'][array_key_exists($pname, $this->_config['ppv_separators']) ? $pname : 'default'];
				$pvals_a = explode($sep, $pvals);

				$transformations = $this->_config['transformations'][array_key_exists($pname, $this->_config['transformations']) ? $pname : 'default'];
				if (!is_array($transformations))
					$transformations = array($transformations);
				foreach ($transformations as $transformator)
					if ($transformator && function_exists($transformator))
						$pvals_a = array_map($transformator, $pvals_a);

				$props_a[$propid] = array();
				$new_pvals[$propid] = array();
				// look for new values
				foreach ($pvals_a as $pval)
				{
					if ($pval === '') continue;
					if (array_key_exists($propid, $ppvals) && array_key_exists($pval, $ppvals[$propid]['values']))
					{
						$pvid = $ppvals[$propid]['values'][$pval][0]; // more keys can have same value, we take the first one
						$props_a[$propid][] = $pvid;
					}
					else
						$new_pvals[$propid][] = $pval;
				}
				// look for values that should be deleted
				if (array_key_exists($propid, $ppvals))
					foreach ($ppvals[$propid]['values'] as $pval => $pvids)
					{
						if (array_search($pval, $pvals_a) === false)
							$del_pvals = array_merge($del_pvals, $pvids);
					}
			}
			else
				throw new Exception('Eigenschaft für das Produkt existiert nicht: ' . $pname);
		}
		
		// delete the removed property value binds
		if (!empty($del_pvals))
		{
			$sql_vals = implode(',', $del_pvals);		
			$this->deletePropertyBindPictureFiles($id, $sql_vals);
			$this->log("Entfernen der überschüssigen Eigenschaften des Produktes $id: ($sql_vals)", self::LOG_PROPERTIES);
			$query = "DELETE FROM prodpbinds WHERE PID=$id AND PPVID IN ($sql_vals)";
			$this->mysql_invasive_query($query);
		}
		
		// create new property values (if needed) and create new binds with the product
		foreach ($new_pvals as $propid => $pvals)
		{
			$this->loadPropertyValues($propid);
			foreach ($pvals as $pval)
			{
				if (!array_key_exists($pval, $this->_property_values[$propid]['values']))
				{
					// register new property value
					$prior = $this->getNewPropertyValuePriority($propid);
					$epval = mysql_real_escape_string($pval);
					$pname = $this->getPropertyName($propid);
					$query = "INSERT INTO prodpvals(PID, Value, Priority, Active) VALUES ($propid, '$epval', $prior, 1)";
					$this->mysql_invasive_query($query);
					$ppvid = $this->mysql_insert_id();
					$this->log("Die neue Wertbildung der Eigenschaft \"$pname\" mit der ID $ppvid: \"$pval\"", self::LOG_PROPERTIES);
					// register in the cache as well
					$this->_property_values[$propid]['ids'][$ppvid] = $pval;
					$this->_property_values[$propid]['values'][$pval] = array($ppvid);
				}
				else
					$ppvid = $this->_property_values[$propid]['values'][$pval][0]; // more keys can have same value, we take the first one

				// create new product property value bind
				$this->log("Erstellen einer neuen Verbindung zwischen dem Produkt $id und den Wert der Eigenschaften $ppvid", self::LOG_PROPERTIES);
				$query = "INSERT INTO prodpbinds(PID, PPVID) VALUES ($id, $ppvid)";
				$this->mysql_invasive_query($query);
			}
		}
	}

	protected function loadPropertyValues($propid)
	{
		if (!array_key_exists($propid, $this->_property_values))
		{
			$this->_property_values[$propid] = array
			(
				'ids' => array(),
				'values' => array()
			);
			$query = "SELECT ID, Value FROM prodpvals WHERE PID=$propid";
			$result = $this->mysql_noninvasive_query($query);
			while ($row = mysql_fetch_row($result))
			{
				$this->_property_values[$propid]['ids'][$row[0]] = $row[1];
				if (!array_key_exists($row[1], $this->_property_values[$propid]['values']))
					$this->_property_values[$propid]['values'][$row[1]] = array();
				$this->_property_values[$propid]['values'][$row[1]][] = $row[0];
			}
		}	
	}

	protected function getNewPropertyValuePriority($propid)
	{
		if ($this->_property_value_new_priorities === null)
		{
			$this->_property_value_new_priorities = array();
			$query = "SELECT PID, MAX(Priority)+1 FROM `prodpvals` GROUP BY PID";
			$result = $this->mysql_noninvasive_query($query);
			while ($row = mysql_fetch_row($result))
				$this->_property_value_new_priorities[$row[0]] = (int)$row[1];
		}

		if (!array_key_exists($propid, $this->_property_value_new_priorities))
			$this->_property_value_new_priorities[$propid] = 1;
		return $this->_property_value_new_priorities[$propid]++;
	}

	protected function retrieveProductProperties($id)
	{
		$pprops = array();
		$query = "SELECT V.ID, V.PID, V.Value FROM prodpvals AS V JOIN prodpbinds AS B ON V.ID=B.PPVID WHERE B.PID=$id ORDER BY V.Priority";
		$result = $this->mysql_noninvasive_query($query);
		while ($row = mysql_fetch_row($result))
		{
			if (!array_key_exists($row[1], $pprops))
				$pprops[$row[1]] = array
				(
					'ids' => array(),
					'values' => array()
				);
			$pprops[$row[1]]['ids'][$row[0]] = $row[2];
			if (!array_key_exists($row[2], $pprops[$row[1]]['values']))
				$pprops[$row[1]]['values'][$row[2]] = array();
			$pprops[$row[1]]['values'][$row[2]][] = $row[0];
		}

		return $pprops;
	}

	protected function loadProperties()
	{
		if ($this->_property_ids === null)
		{
			$this->_property_ids = array();
			$this->_property_names = array();
			$query = "SELECT ID, Name FROM prodprops";
			$result = $this->mysql_noninvasive_query($query);
			while ($row = mysql_fetch_row($result))
			{
				$this->_property_ids[$row[0]] = $row[1];
				if (!array_key_exists($row[1], $this->_property_names))
					$this->_property_names[$row[1]] = array();
				$this->_property_names[$row[1]][] = $row[0];
			}
		}	
	}

	protected function getPropertyName($pid)
	{
		$this->loadProperties();
	
		if (array_key_exists($pid, $this->_property_ids))
			return $this->_property_ids[$pid];
		else
			return null;
	}

	protected function getPropertyId($pname)
	{
		$this->loadProperties();

		if (array_key_exists($pname, $this->_property_names))
			return $this->_property_names[$pname][0]; // return the first match
		else
			return null;
	}

	protected function loadIndexes()
	{
		if ($this->_indexes === null)
		{
			$this->log("Laden von Hilfsmittel Produkt-Index");
			$this->_indexes = array
			(
				'id' => array(),
				'code' => array()
			);
			$result = $this->mysql_noninvasive_query("SELECT ID, Title, Code FROM products");
			while ($row = mysql_fetch_object($result))
			{
				$code_low = strtolower($row->Code);
				$this->_indexes['id'][$row->ID] = array
				(
					'code' => $code_low,
					'title' => $row->Title
				);
				if (array_key_exists($code_low, $this->_indexes['code']) && count($this->_indexes['code'][$code_low]) == 1)
					$this->logX("Warnung: Product Code '$row->Code' ist nicht einzigartig!");
				$this->_indexes['code'][$code_low][] = $row->ID;
			}
		}
	}

	protected function Code2ID($code)
	{
		$code_low = strtolower($code);
		return array_key_exists($code_low, $this->_indexes['code']) ? $this->_indexes['code'][$code_low][0] : null;
	}

	protected function doesIDExist($id)
	{
		return array_key_exists($id, $this->_indexes['id']);
	}

	protected function getOldTitle($id)
	{
		return array_key_exists($id, $this->_indexes['id']) ? $this->_indexes['id'][$id]['title'] : null;
	}

	protected function getNewID()
	{
		if ($this->_max_id === null)
		{
			$result = $this->mysql_noninvasive_query("SELECT Max(ID) FROM products");
			if ($row = mysql_fetch_row($result))
				$this->_max_id = $row[0];
			else
				throw new Exception('Fehler beim Lesen von Produkten aus einer Tabelle');
		}
		return ++$this->_max_id;
	}

	protected function deletePropertyBindPictureFiles($pid, $ppvid_list)
	{
		if ($ppvid_list == '') return;

		$result = $this->mysql_noninvasive_query("SELECT Photo FROM prodpbinds WHERE PID=$pid AND PPVID IN ($ppvid_list)");
		while ($row = mysql_fetch_row($result))
		{
			if ($row[0] != '')
			{
				$this->log("Löschen eines Fotos aus der zugehörigebindung für das Produkt $pid: {$row[0]}", self::LOG_PROPERTIES);
				$this->deleteProductFile($row[0], array('detail', 'catalog', 'catalog2', 'collection'));
			}
		}
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
		$arg = str_replace(array("\r\n", "\n"), array("\\n", "\\n"), $arg);
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
			if ($this->_simulation)
				$emsg = "S: $emsg";
			if (!$this->_log_started)
				$this->beginLog();
			echo <<<EOT
logX('$emsg');

EOT;
		}
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
		$this->mysql_noninvasive_query("LOCK TABLES products WRITE, prodbinds WRITE, prodprops READ, prodpbinds WRITE, prodpvals WRITE, prodpvals as V READ, prodpbinds AS B READ, eshop READ");
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

	protected function mysql_insert_id()
	{
		if (!$this->_simulation) $result = mysql_insert_id();
		else $result = 777;
		return $result;
	}

	protected function unlink($file)
	{
		if (!$this->_simulation) $result = unlink($file);
		else $result = true;
		return $result;
	}

	protected function generateURL($str)
	{
		$result = Kiwi_URL_Generator::generate($str);
		$this->log("Generierte URL für \"$str\": $result", self::LOG_VERBOSE);
		return $result;
	}		

	protected function generateTitle($str)
	{
		return $str;
	}

	protected function isFileValid($file)
	{
		return strpos($file, '../import/') === 0;
	}
}
?>
