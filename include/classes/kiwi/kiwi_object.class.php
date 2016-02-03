<?php
class Kiwi_Object
{
	protected $_koi; // kiwi object id
	protected static $_next_koi = 1;

	public function __construct()
	{
		$this->_koi = self::$_next_koi++;
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'koi':
				return $this->_koi;
			default:
				throw new No_Such_Variable_Exception($name, get_class($this));
		};
	}

	public function set($name, $value)
	{
		switch ($name)
		{
			case 'koi':
				throw new Readonly_Variable_Exception($name, get_class($this));
			default:
				throw new No_Such_Variable_Exception($name, get_class($this));
		}
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}
}
?>