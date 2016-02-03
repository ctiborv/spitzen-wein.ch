<?php
require_once 'kiwi_datarow.class.php';

class Kiwi_Menu
{
	protected $menudata;
	protected $menuitems;

	public function __construct($row)
	{
		$this->menudata = new Kiwi_DataRow($row);
		$this->menuitems = null;
	}

	public function __get($name)
	{
		if ($name == 'MenuItems')
		{
			if ($this->menuitems == null)
			{
				$id = $this->menudata->ID;
				$result = mysql_query("SELECT ID, Name, Submenu, Active, LastChange FROM menuitems WHERE Parent=$id ORDER BY Priority");
				$this->menuitems = array();
				while ($row = mysql_fetch_object($result))
					$this->menuitems[] = new Kiwi_DataRow($row);
			}
			return $this->menuitems;
		}
		else return $this->menudata->$name;
	}
}
?>