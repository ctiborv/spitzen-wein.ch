<?php
require_once 'export_file.class.php';

class CSV_Export_File implements Export_File
{
	protected $_filename;
	protected $_separator;

	protected $_handle;

	const DEFAULT_SEPARATOR = ';';

	public function __construct($fname, $config)
	{
		if (array_key_exists('csv', $config) && array_key_exists('separator', $config['csv']))
			$this->_separator = $config['csv']['separator'];
		else
			$this->_separator = self::DEFAULT_SEPARATOR;
		$this->_filename = $fname;
		$this->_handle = null;
	}

	public function __destruct()
	{
		if (is_resource($this->_handle))
			fclose($this->_handle);
	}

	public function write($record)
	{
		if (!is_resource($this->_handle))
		{
			$this->_handle = @fopen($this->_filename, "wb");
			if (!$this->_handle)
				throw new Exception("Nepodaшilo se otevшнt soubor: \"$this->_filename\".");
		}

		foreach ($record as $key => $item)
			$record[$key] = $this->escape($item);

		fwrite($this->_handle, implode($this->_separator, $record) . "\n");
	}

	protected function escape($item)
	{
		$quote = (mb_strstr($item, $this->_separator) !== false || mb_strstr($item, "\n") !== false);
		if (mb_strstr($item, '"') !== false)
		{
			$item = mb_ereg_replace('"', "\"\"", $item, 'm');
			$quote = true;
		}

		if (mb_strstr($item, "\\") !== false)
		{
			$item = mb_ereg_replace("\\\\", "\\\\", $item, 'm');
			$quote = true;
		}

		if ($quote)
			$item = "\"$item\"";

		return $item;
	}
}
?>