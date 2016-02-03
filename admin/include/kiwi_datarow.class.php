<?php
class Kiwi_DataRow
{
	protected $data;

	function __construct($row)
	{
		$this->data = array();
		foreach ($row as $key => $value)
		{
			$this->data[$key] = $value;
		}
	}

	function __get($name)
	{
		if (!array_key_exists($name, $this->data))
			throw new Exception('Došlo k pokusu o čtení neexistující vlastnosti objektu třídy ' . __CLASS__ . " ($name)");
		return $this->data[$name];
	}

	function __set($name, $value)
	{
		if (!array_key_exists($name, $this->data))
			throw new Exception('Došlo k pokusu o nastavení neexistující vlastnosti objektu třídy ' . __CLASS__ . " ($name)");
		$this->data[$name] = $value;
	}
}
?>