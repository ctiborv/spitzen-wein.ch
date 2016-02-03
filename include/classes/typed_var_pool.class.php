<?php
// modification of var pool which uses less memory
// the behavior is changed in following way:
// 1. var pool is parametrized by a type (string)
// 2. registration applies for whole type
// 3. variables containing null are not physically stored
// 4. this class also measures usage of individual variables of the type
class Typed_Var_Pool
{
	protected $_type;
	protected $_data;
	protected $_lock;

	static protected $_register = array();

	public function __construct($type, $data = null)
	{
		if ($type === null)
			throw new Invalid_Argument_Type_Exception('type', 'null', __CLASS__);
		$this->_type = $type;
		if (!array_key_exists($type, self::$_register))
			self::$_register[$type] = array();
		$this->_data = array();
		$this->_lock = false;
		if (is_array($data))
			foreach ($data as $key => $value)
				$this->register($key, $value);
	}

	public function clear()
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		foreach ($this->_data as $name => $value)
			$this->decreaseUsageRate($name);

		$this->_data = array();
	}

	public function get($name)
	{
		if (!$this->isRegistered($name))
			throw new No_Such_Variable_Exception($name, __CLASS__);

		if (array_key_exists($name, $this->_data))
			return $this->_data[$name];
		else
			return null;
	}

	public function set($name, $value)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		if (!$this->isRegistered($name))
			throw new No_Such_Variable_Exception($name, __CLASS__);

		if ($value === null)
		{
			if (array_key_exists($name, $this->_data))
			{
				$this->decreaseUsageRate($name);
				unset($this->_data[$name]);
			}
		}
		else
		{
			if (!array_key_exists($name, $this->_data))
				$this->increaseUsageRate($name);
			$this->_data[$name] = $value;
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

	protected function increaseUsageRate($name, $by = 1)
	{
		self::$_register[$this->_type][$name] += $by;
	}

	protected function decreaseUsageRate($name, $by = 1)
	{
		self::$_register[$this->_type][$name] -= $by;
	}

	public function getUsage($name)
	{
		if (!$this->isRegistered($name))
			throw new No_Such_Variable_Exception($name, __CLASS__);
		return self::$_register[$this->_type][$name];
	}

	public function register($name, $value = null, $check = false)
	{
		if ($this->isRegistered($name))
		{
			if ($check)
				throw new Variable_Already_Registered_Exception($name, __CLASS__);

			$this->set($name, $value);
		}
		else
		{
			self::$_register[$this->_type][$name] = 0;
			if ($value !== null)
				$this->set($name, $value);
		}
	}

	public function unregister($name, $check = true)
	{
		if (!$this->isRegistered($name))
		{
			if ($check)
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
		else
		{
			if (array_key_exists($name, $this->_data))
			{
				if ($this->_lock)
					throw new Locked_Exception(__CLASS__);
				$this->decreaseUsageRate($name);
				unset($this->_data[$name]);
			}

			if (self::$_register[$this->_type][$name] == 0)
				unset(self::$_register[$this->_type][$name]);
			else
				throw new Cannot_Unregister_Variable_Exception($name, __CLASS__ . "[$this->_type]");
		}
	}

	public function isRegistered($name)
	{
		return array_key_exists($name, self::$_register[$this->_type]);
	}

	public function toArray()
	{
		return $this->_data;
	}

	public function search($needle, $limit = 0)
	{
		$result = array();
		if ($needle === null)
			foreach (self::$_register[$this->_type] as $name => $usage)
			{
				if (!array_key_exists($name, $this->_data))
				{
					$result[] = $name;
					if (--$limit == 0) break;
				}
			}
		else
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