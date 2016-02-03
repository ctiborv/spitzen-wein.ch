<?php
class Remote_HTML_Parser
{
	protected $_parser_url;
	protected $_dtd;

	protected $_filename;
	protected $_filedata;

	public function __construct($remote_parser, $builder = null)
	{
		$this->_parser_url = $remote_parser;
		// builder is ignored
		$this->_dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';	
		$this->_filedata = null;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'dtd':
				return $this->_dtd;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'dtd':
				$this->_dtd = $value;
				break;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}		
	}

	public function parse($file)
	{
		if (($this->_filedata = @file_get_contents($file)) === false)
			throw new HTML_Parser_Exception('Failed to load template file: ' . $file);
		$this->_filename = $file;
	}
	
	public function build()
	{
		throw new Feature_Not_Available_Exception('cannot build directly with remote parser, use compile');
	}

	public function compile()
	{
		if ($this->_filedata === null)
			throw new HTML_Parser_Exception('No parsed content to process');

		$pconf = new Project_Config;
		$password = isset($pconf->templates['remote_parser_password']) ? $pconf->templates['remote_parser_password'] : '';

		$postdata = array
		(
			'source' => $this->_filedata,
			'filename' => $this->_filename,
			'dtd' => $this->_dtd,
			'password' => $password
		);
		return $this->doPostRequest($this->_parser_url, http_build_query($postdata), 'Content-type: application/x-www-form-urlencoded');
	}

	protected function doPostRequest($url, $data, $optional_headers = null)
	{
		$params = array
		(
			'http' => array
			(
				'method' => 'POST',
				'content' => $data
			)
		);

		if ($optional_headers !== null)
			$params['http']['header'] = $optional_headers;

		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp)
		{
			if (isset($php_errormsg))
				throw new Exception("Problem with $url, $php_errormsg");
			else
				throw new Exception("Unknown problem with $url");
		}

		$response = @stream_get_contents($fp);
		if ($response === false)
			throw new Exception("Problem reading data from $url, $php_errormsg");

		return $response;
	}
}
?>