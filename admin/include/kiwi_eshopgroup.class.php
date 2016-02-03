<?php
require_once 'kiwi_datarow.class.php';

class Kiwi_EShopGroup
{
	protected $groupdata;
	protected $groupitems;

	public function __construct($row)
	{
		$this->groupdata = new Kiwi_DataRow($row);
		$this->groupitems = null;
	}

	public function __get($name)
	{
		if ($name == 'EShopItems')
		{
			if ($this->groupitems == null)
			{
				$id = $this->groupdata->ID;
				$result = mysql_query("SELECT ID, Name, URL, PageTitle, Description, Subgroup, Flags, Active, LastChange FROM eshop WHERE Parent=$id ORDER BY Priority");
				$this->groupitems = array();
				while ($row = mysql_fetch_object($result))
					$this->groupitems[] = new Kiwi_DataRow($row);
			}
			return $this->groupitems;
		}
		else return $this->groupdata->$name;
	}
}
?>