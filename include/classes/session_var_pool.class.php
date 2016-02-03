<?php
class Session_Var_Pool extends Var_Pool
{
	protected $_name;
	protected $_autosave; // auto-save after each change? (default is no)
	protected $_dirty; // when set, contents need to be saved

	public function __construct($name, $data = null, $autosave = false)
	{
		if (session_id() == '') session_start();
		$this->_name = $name;
		$this->_autosave = $autosave;
		$this->_dirty = false;

		if ($data === null && array_key_exists($name, $_SESSION))
			parent::__construct($_SESSION[$name]);
		else
		{
			parent::__construct($data);
			$this->modified();
		}
	}

	public function __destruct()
	{
		$this->save();
	}

	public function autosave($enable = true)
	{
		$this->_autosave = (bool)$enable;
		if ($this->_autosave) $this->save();
	}

	protected function modified()
	{
		$this->_dirty = true;
		if ($this->_autosave) $this->save();
	}

	public function save()
	{
		if ($this->_dirty)
		{		
			if (!empty($this->_data))
				$_SESSION[$this->_name] = $this->_data;
			else
				unset($_SESSION[$this->_name]);
			$this->_dirty = false;
		}
	}

	public function clear()
	{
		if (!empty($this->_data))
		{
			parent::clear();
			$this->modified();
		}
	}

	public function set($name, $value)
	{
		parent::set($name, $value);
		$this->modified();
	}

	public function register($name, $value = null, $check = true)
	{
		parent::register($name, $value, $check);
		$this->modified();
	}

	public function unregister($name, $check = true)
	{
		parent::unregister($name, $check);
		$this->modified();
	}
}
?>