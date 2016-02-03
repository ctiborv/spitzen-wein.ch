<?php
class Var_Pool implements Lockable
{
	protected $_data;
	protected $_lock;

	public function __construct($data = null)
	{
		$this->_data = array();
		$this->_lock = false;
		if (is_array($data))
			foreach ($data as $key => $value)
				$this->_data[$key] = $value;
	}

	public function clear()
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		$this->_data = array();
	}

	public function get($name)
	{
		if (!$this->isRegistered($name))
			throw new No_Such_Variable_Exception($name, __CLASS__);

		return $this->_data[$name];
	}

	public function set($name, $value)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		if (!$this->isRegistered($name))
			throw new No_Such_Variable_Exception($name, __CLASS__);

		$this->_data[$name] = $value;
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	final public function __isset($name)
	{
		return $this->isRegistered($name) && $this->_data[$name] !== NULL;
	}

	final public function __unset($name)
	{
		$this->set($name, NULL);
	}

	public function register($name, $value = null, $check = true)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		if ($check && $this->isRegistered($name))
			throw new Variable_Already_Registered_Exception($name, __CLASS__);

		$this->_data[$name] = $value;
	}

	public function unregister($name, $check = true)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		if ($check && !$this->isRegistered($name))
			throw new No_Such_Variable_Exception($name, __CLASS__);

		unset($this->_data[$name]);
	}

	public function isRegistered($name)
	{
		return array_key_exists($name, $this->_data);
	}

	public function isEmpty()
	{
		return empty($this->_data);
	}

	public function toArray()
	{
		return $this->_data;
	}

	public function search($needle, $limit = 0)
	{
		$result = array();
		foreach ($this->_data as $key => $value)
			if ($value === $needle)
			{
				$result[] = $key;
				if (--$limit == 0) break;
			}
		return $result;
	}

	public function lock()
	{
		$this->_lock = true;
	}

	public function unlock()
	{
		$this->_lock = false;
	}
}
?>