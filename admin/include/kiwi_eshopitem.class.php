<?php
require_once 'kiwi_datarow.class.php';

class Kiwi_EShopItem
{
	protected $eshopitem;
	protected $products;

	function __construct($row)
	{
		$this->eshopitem = new Kiwi_DataRow($row);
		$this->products = null;
	}

	function __get($name)
	{
		if ($name == 'Products')
		{
			if ($this->products == null)
			{
				$id = $this->eshopitem->ID;
				$result = mysql_query("SELECT PB.ID, PB.PID, P.Title, P.ShortDesc, P.LongDesc, P.OriginalCost, P.NewCost, P.Photo, P.Discount, P.Action, P.Novelty, P.Sellout, PB.Priority, PB.Active, PB.LastChange FROM prodbinds AS PB JOIN products AS P ON PB.PID=P.ID WHERE PB.GID=$id ORDER BY PB.Priority");
				$this->products = array();
				while ($row = mysql_fetch_object($result))
					$this->products[] = new Kiwi_DataRow($row);
			}
			return $this->products;
		}
		else return $this->eshopitem->$name;
	}
}
?>