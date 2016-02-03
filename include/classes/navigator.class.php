<?php
/**
 * Navigator
 *
 * @package Skeleton
 * @author Tomáš Windsor
 * @copyright 2008
 * @version 1.0
 * @access public
 * @abstract Manages locations of files and directories on the local web.
 */
class Navigator implements Lockable
{
	protected $_base;
	protected $_locations;
	protected $_lock;

	public function __construct()
	{
		$this->_base = '/';
		$this->_locations = new Var_Pool;
		$this->_lock = false;
	}

	public function get($name, $nobase = false)
	{
		switch ($name)
		{
			case 'base':
				return $this->_base;
			default:
				try
				{
					$result = $this->_locations->$name;
					return (($nobase || $result !== '' && $result[0] === '/' || preg_match('#^[a-z]+://.*#i', $result)) ? '' : $this->_base) . $result;
				}
				catch (No_Such_Variable_Exception $e)
				{
					throw new Navigator_No_Such_Location_Exception($name);
				}
		}
	}

	public function set($name, $value)
	{
		if ($this->_lock)
			throw new Locked_Exception(get_class($this));

		switch ($name)
		{
			case 'base':
				$this->_base = (string)$value;
				break;
			default:
				try
				{
					$this->_locations->$name = $value;
				}
				catch (No_Such_Variable_Exception $e)
				{
					throw new Navigator_No_Such_Location_Exception($name);
				}
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

	public function getRelative($name, $path = null)
	{
		if ($path === null)
			$path = dirname(Server::get('PHP_SELF'));

		$base = $this->getRelativePath($path == '\\' ? '/' : $path, $this->_base);
		if ($base === null) $base = $this->_base;
		try
		{
			$result = $this->_locations->$name;
			return $base . $result;
		}
		catch (No_Such_Variable_Exception $e)
		{
			throw new Navigator_No_Such_Location_Exception($name);
		}
	}

	public function getRelativePath($from, $to)
	{
		if (substr($from, 0, 1) != '/')
			throw new Navigator_Exception("1st path is not absolute: '$from'");
		if (substr($to, 0, 1) != '/')
			throw new Navigator_Exception("2nd path is not absolute: '$to'");

		if (substr($from, -1) != '/') $from .= '/';
		if (substr($to, -1) != '/') $to .= '/';

		$from_a = explode('/', $from);
		$to_a = explode('/', $to);

		array_pop($from_a);
		array_pop($to_a);

		$from_s = sizeof($from_a);
		$to_s = sizeof($to_a);

		$i = 1;
		while ($i < $from_s && $i < $to_s && $from_a[$i] == $to_a[$i])
			$i++;

		$j = $i;
		$dif_a = array();
		while ($j++ < $from_s)
			$dif_a[] = '..';

		$k = $i;
		while ($k < $to_s)
			$dif_a[] = $to_a[$k++];

		$dif = './';
		if (!empty($dif_a))
			$dif .= implode('/', $dif_a) . '/';

		return $dif;
	}


	public function register($name, $value = null)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		try
		{
			$this->_locations->register($name, $value);
		}
		catch (Variable_Already_Registered_Exception $e)
		{
			throw new Navigator_Location_Already_Registered_Exception($name);
		}
	}

	public function unregister($name)
	{
		if ($this->_lock)
			throw new Locked_Exception(__CLASS__);

		try
		{
			$this->_locations->unregister($name);
		}
		catch (No_Such_Variable_Exception $e)
		{
			throw new Navigator_No_Such_Location_Exception($name);
		}
	}

	public function getNavPoint($search)
	{
		$blen = strlen($this->_base);
		if (substr($search, 0, $blen) === $this->_base)
		{
			$needle = (string)substr($search, $blen);
			$result = $this->_locations->search($needle, 1);
		}
		else
			$result = array();
		if (empty($result))
			throw new Navigator_Unregistered_Location_Exception($search);
		return $result[0];
	}

	public function lock()
	{
		$this->_lock = true;
		$this->_locations->lock();
	}

	public function unlock()
	{
		$this->_lock = false;
		$this->_locations->unlock();
	}
}
?>
