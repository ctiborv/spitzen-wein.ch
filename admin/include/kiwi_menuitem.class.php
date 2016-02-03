<?php
require_once 'kiwi_datarow.class.php';

class Kiwi_MenuItem
{
	protected $menuitem;
	protected $modules;

	public function __construct($row)
	{
		$this->menuitem = new Kiwi_DataRow($row);
		$this->modules = null;
	}

	public function __get($name)
	{
		if ($name == 'Modules')
		{
			if ($this->modules == null)
			{
				$id = $this->menuitem->ID;
				$result = mysql_query("SELECT MB.ID, MB.ModID, M.Name, M.Type, MB.Priority, MB.Active, MB.LastChange FROM modbinds AS MB JOIN modules AS M WHERE MB.ModID=M.ID AND MB.MIID=$id ORDER BY MB.Priority");
				$this->modules = array();
				while ($row = mysql_fetch_object($result))
					$this->modules[] = new Kiwi_DataRow($row);
			}
			return $this->modules;
		}
		else return $this->menuitem->$name;
	}
}
?>