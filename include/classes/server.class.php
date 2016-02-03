<?php
class Server
{
	public function __construct()
	{
	}

	public static function get($name)
	{
		switch ($name)
		{
			case 'ABSOLUTE_URL':
				return self::getAbsoluteURL();
			case 'URL_BASE':
				return self::getURLBase();
			case 'REQUEST_QUERY_STRING':
				return self::getRequestQueryString();
			case 'REQUEST_URL':
				return self::getRequestURL();
			default:
				if (array_key_exists($name, $_SERVER))
					return $_SERVER[$name];
				else
					throw new Server_Exception("Undefined server variable: $name");
		}
	}

	final public function __get($name)
	{
		return self::get($name);
	}

	public static function getRequestQueryString()
	{
		if (!array_key_exists('REQUEST_URI', $_SERVER))
			throw new Server_Exception('Undefined server variable: REQUEST_URI');
		$qmp = strpos($_SERVER['REQUEST_URI'], '?');
		return $qmp === false ? '' : substr($_SERVER['REQUEST_URI'], $qmp + 1);
	}

	public static function getRequestURL()
	{
		if (!array_key_exists('REQUEST_URI', $_SERVER))
			throw new Server_Exception('Undefined server variable: REQUEST_URI');
		$qmp = strpos($_SERVER['REQUEST_URI'], '?');
		return $qmp === false ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $qmp);
	}

	public static function getURLBase()
	{
		// inspired by Nette Framework
		// support for user and password skipped
		$scheme = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') ? 'https' : 'http';

		if (isset($_SERVER['HTTP_HOST'])) {
			$pair = explode(':', $_SERVER['HTTP_HOST']);
		} elseif (isset($_SERVER['SERVER_NAME'])) {
			$pair = explode(':', $_SERVER['SERVER_NAME']);
		} else {
			$pair = array('');
		}
		
		$host = preg_match('#^[-._a-z0-9]+$#', $pair[0]) ? $pair[0] : '';

		if (isset($pair[1])) {
			$port = (int) $pair[1];
		} elseif (isset($_SERVER['SERVER_PORT'])) {
			$port = (int) $_SERVER['SERVER_PORT'];
		}
		
		static $defaultPorts = array(
			'http' => 80,
			'https' => 443,
			'ftp' => 21,
			'news' => 119,
			'nntp' => 119,
		);

		if (isset($defaultPorts[$scheme]) && $port === $defaultPorts[$scheme]) {
			unset($port);
		}
		
		return $scheme . '://' . $host . (isset($port) ? ":$port" : '');
	}

	public static function getAbsoluteURL()
	{
		$requestUrl = $_SERVER['REQUEST_URI'];
		return self::getURLBase() . $requestUrl;
	}
}
