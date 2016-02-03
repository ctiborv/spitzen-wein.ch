<?php
require_once 'import_file.class.php';

class CSV_Import_File implements Import_File
{
	protected $_filename;
	protected $_separator;
	protected $_linelimit;
	protected $_temp_file;

	protected $_handle;
	protected $_old_auto_detect_line_endings;

	const DEFAULT_SEPARATOR = ';';
	const DEFAULT_LINELIMIT = 16000;

	public function __construct($fname, $config)
	{
		$this->_separator = self::DEFAULT_SEPARATOR;
		$this->_linelimit = self::DEFAULT_LINELIMIT;
		if (array_key_exists('csv', $config))
		{
			if (array_key_exists('separator', $config['csv']))
				$this->_separator = $config['csv']['separator'];
			if (array_key_exists('line_limit', $config['csv']))
				$this->_linelimit = $config['csv']['line_limit'];
		}

		$this->_handle = null;
		$this->_old_auto_detect_line_endings = null;

		if (array_key_exists('encoding', $config) && strtoupper($config['encoding']) !== 'UTF-8')
		{
			$this->_temp_file = tempnam(sys_get_temp_dir(), 'csv_import_');
			$this->convertFileEncoding($config['encoding'], 'UTF-8', $fname, $this->_temp_file);
			$this->_filename = $this->_temp_file;
		}
		else
		{
			$this->_filename = $fname;
			$this->_temp_file = null;
		}
	}

	public function __destruct()
	{
		if (is_resource($this->_handle))
		{
			fclose($this->_handle);
			if ($this->_temp_file)
				@unlink($this->_temp_file);
			ini_set('auto_detect_line_endings', $this->_old_auto_detect_line_endings);
		}
	}

	protected function convertFileEncoding($in_charset, $out_charset, $source, $destination)
	{
		$sh = @fopen($source, "r");
		if ($sh)
		{
			$dh = @fopen($destination, "w");
			if ($dh)
			{
				while (!feof($sh))
				{
					$buffer = fgets($sh, $this->_linelimit);
					$converted = iconv($in_charset, $out_charset, $buffer);
					fwrite($dh, $converted);
				}

				fclose($dh);
			}
			else
				throw new Exception("Nepodaшilo se otevшнt doиasnэ soubor: \"$destination\".");

			fclose($sh);
		}
		else
			throw new Exception("Nepodaшilo se otevшнt soubor: \"$source\".");
	}

	public function read()
	{
		if (!is_resource($this->_handle))
		{
			$this->_old_auto_detect_line_endings = ini_get('auto_detect_line_endings');
			ini_set('auto_detect_line_endings', true);
			$this->_handle = @fopen($this->_filename, "r");
			if (!$this->_handle)
				throw new Exception("Nepodaшilo se otevшнt soubor: \"$this->_filename\".");
		}

		while(empty($row))
		{
			$row = fgetcsv($this->_handle, $this->_linelimit, $this->_separator);
			if (!$row)
				return false;
		}

		return $row;
	}
}
?>