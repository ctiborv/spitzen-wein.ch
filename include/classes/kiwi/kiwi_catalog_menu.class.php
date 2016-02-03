<?php
class Kiwi_Catalog_Menu extends Kiwi_Object
{
	protected $_root;
	protected $_menu;

	public function __construct($root = 1)
	{
		parent::__construct();
		$this->_root = $root;
		$this->_menu = null;
	}

	public function getArray()
	{
		$this->loadData();
		return $this->_menu;
	}

	protected function loadData()
	{
		if ($this->_menu !== null) return;

		$dbh = Project_DB::get();

		$query = "SELECT ID, Name, URL, Subgroup FROM eshop WHERE Active=1 AND ID=:ID";

		$stmt = $dbh->prepare($query);
		$stmt->bindValue(":ID", $this->_root, PDO::PARAM_INT);

		if ($stmt->execute())
		{
			$this->_menu = array();
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$ar = array
				(
					'ID' => $row['ID'],
					'Name' => $row['Name'],
					'URL' => $row['URL']
				);
				$ar['Contents'] = $row['Subgroup'] ? array() : false;
				$this->_menu[] = $ar;
				$this->populateMenu($this->_menu);
			}
		}
	}

	protected function populateMenu(&$menu)
	{
		$dbh = Project_DB::get();

		$query = "SELECT ID, Name, URL, Subgroup FROM eshop WHERE Active=1 AND Parent=:Parent ORDER BY Priority";
		$stmt = $dbh->prepare($query);

		foreach ($menu as &$mi)
		{
			if ($mi['Contents'] === false) continue;

			$stmt->bindValue(":Parent", $mi['ID'], PDO::PARAM_INT);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$ar = array
					(
						'ID' => $row['ID'],
						'Name' => $row['Name'],
						'URL' => $row['URL']
					);
					$ar['Contents'] = $row['Subgroup'] ? array() : false;
					$mi['Contents'][] = $ar;
				}

			$this->populateMenu($mi['Contents']);
		}		
	}
}
?>
