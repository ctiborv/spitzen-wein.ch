<?php
class Redirection
{
	protected $schema;
	protected $host;
	protected $target;
	protected $code;

	private static $codes = array
	(
		301 => 'HTTP/1.1 301 Moved Permanently',
		302 => 'HTTP/1.1 302 Found',
		303 => 'HTTP/1.1 303 See Other', // Use when redirecting POST
		307 => 'HTTP/1.1 307',
		404 => 'HTTP/1.0 404 Not Found'
	);

	public function __construct($target = null, $code = 303, $schema = null, $host = null)
	{
		if (!array_key_exists($code, self::$codes))
			throw new Unsupported_Redirection_Code_Exception($code);

		if ($target === null)
			$this->target = Server::get('REQUEST_URI');
		else
			$this->target = $target;

		$this->code = $code;

		if ($schema === null)
			$this->schema = $_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http';
		else
			$this->schema = $schema;

		if ($host === null)
			$this->host = $_SERVER['HTTP_HOST'] != '' ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		else
			$this->host = $host;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'Target':
				return $this->target;
			case 'Code':
				return $this->code;
			case 'Protocol':
			case 'Schema':
				return $this->schema;
			case 'Host':
				return $this->host;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'NavPoint':
				$navigator = new Project_Navigator;
				$this->target = $navigator->$value;
				break;
			case 'Target':
				$this->target = $value;
				break;
			case 'Code':
				if (!array_key_exists($this->code, self::$codes))
					throw new Unsupported_Redirection_Code_Exception($value);
				$this->code = $value;
				break;
			case 'Protocol':
			case 'Schema':
				$this->schema = $value;
				break;
			case 'Host':
				$this->host = $value;
				break;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	public function redirect()
	{
		if (headers_sent())
			throw new Headers_Already_Sent_Exception();

		$to = $this->target;

		if ($to[0] != '/')
		{
			$dirname = dirname(Server::get('REQUEST_URL'));
			if ($dirname == '/') $to = "/$to";
			else $to = "$dirname/$to";
		}

		if (array_key_exists($this->code, self::$codes))
			$ht = self::$codes[$this->code];
		else
			throw new Unknown_HTTP_Header_Code_Exception($this->code);
		
		header(self::$codes[$this->code]);
		header("Location: $this->schema://$this->host$to");
		exit();
	}

	public static function redirectPage($to, $code = 303, $schema = null, $host = null)
	{
		$ro = new Redirection($to, $code, $schema, $host);
		$ro->redirect();
	}

	public static function redirectNavPoint($nav_point, $code = 303, $schema = null)
	{
		$navigator = new Project_Navigator;
		$to = $navigator->get($nav_point);
		self::redirectPage($to, $code, $schema);
	}
}
?>
