<?php
class KiwiAnchor
{
	protected $key;
	protected $anchor_ids;

	public function __construct($key)
	{
		$this->key = $key;
		$this->clear();
	}

	public function clear()
	{
		$this->anchor_ids = array($this->key => null);
	}

	public function __set($name, $val)
	{
		switch ($name)
		{
			case 'Key':
				$this->key = $val;
				break;
			case 'ID':
				$this->anchor_ids[$this->key] = $val;
				break;
			default: throw new Exception('Doљlo k pokusu o nastavenн neexistujнcн vlastnosti objektu tшнdy ' . __CLASS__);
		}
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'Key':
				return $this->key;
			case 'ID':
				return array_key_exists($this->key, $this->anchor_ids) ? $this->anchor_ids[$this->key] : null;
			default: throw new Exception('Doљlo k pokusu o иtenн neexistujнcн vlastnosti objektu tшнdy ' . __CLASS__);
		}
	}

	public function set_key_value($key, $value)
	{
		$this->anchor_ids[$key] = $value;
	}

	public function export()
	{
		return $this->anchor_ids;
	}

	public function import($data)
	{
		if (is_array($data))
			$this->anchor_ids = $data;
		else throw new Exception('Chybnэ vstup pшi importovбnн obsahu objektu tшнdy ' . __CLASS__);
	}
}

// singleton
class CurrentKiwiAnchor
{
	private static $anchor = null;

	public function __construct()
	{
		if (session_id() == '') session_start();
	}

	protected function getInstance()
	{
		if (self::$anchor == null)
		{
			self::$anchor = new KiwiAnchor(basename($_SERVER['PHP_SELF']));
			if (isset($_SESSION['KiwiAnchor'])) self::$anchor->import($_SESSION['KiwiAnchor']);
		}
		return self::$anchor;
	}

	public function __set($name, $val)
	{
		$anchor = self::getInstance();
		$anchor->$name = $val;
		$_SESSION['KiwiAnchor'] = $anchor->export();
	}

	public function __get($name)
	{
		$anchor = self::getInstance();
		return $anchor->$name;
	}

	public function set_key_value($key, $value)
	{
		$anchor = self::getInstance();
		$anchor->set_key_value($key, $value);
		$_SESSION['KiwiAnchor'] = $anchor->export();
	}

	public function clear()
	{
		$anchor = self::getInstance();
		$anchor->clear();
		$_SESSION['KiwiAnchor'] = $anchor->export();
	}

	public function export()
	{
		$anchor = self::getInstance();
		return $anchor->export();
	}
}
?>