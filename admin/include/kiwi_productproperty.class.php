<?php
require_once 'kiwi_datarow.class.php';

class Kiwi_ProductProperty
{
	protected $property;
	protected $values;

	function __construct($row)
	{
		$this->property = new Kiwi_DataRow($row);
		$this->values = null;
	}

	function __get($name)
	{
		if ($name == 'Values')
		{
			if ($this->values == null)
			{
				$id = $this->property->ID;
				$result = mysql_query("SELECT PV.ID, PV.PID, PV.Value, PV.Priority, PV.Active, PV.LastChange FROM prodpvals AS PV JOIN prodprops AS PP ON PV.PID=PP.ID WHERE PV.PID=$id ORDER BY PV.Priority");
				$this->values = array();
				while ($row = mysql_fetch_object($result))
					$this->values[] = new Kiwi_DataRow($row);
			}
			return $this->values;
		}
		else return $this->property->$name;
	}
}
?>