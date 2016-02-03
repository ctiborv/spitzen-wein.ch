<?php
// modification of var pool which uses less memory
// the behavior is changed in following way:
// 1. variables containing null are not stored at all
// 2. reading a non-existent variable always returns null
// 3. registration checks do not work for null variables
// 4. registration is equal to setting a value
class Simple_Var_Pool implements Lockable
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
			return null;
		return $this->_data[$name];
	}

	public function set($name, $value)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);
		if ($value !== null)
			$this->_data[$name] = $value;
		elseif ($this->isRegistered($name))
			unset($this->_data[$name]);
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	public function register($name, $value = null)
	{
		$this->set($name, $value);
	}

	public function unregister($name)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		if ($this->isRegistered($name))
			unset($this->_data[$name]);
	}

	public function isRegistered($name)
	{
		return array_key_exists($name, $this->_data);
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