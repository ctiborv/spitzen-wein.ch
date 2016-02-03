<?php
class Kiwi_Banners extends Kiwi_Object
{
	protected $_group;
	protected $_active;
	protected $_data;

	public function __construct($group = 1)
	{
		parent::__construct();
		$this->_group = (int)$group;
		$this->_active = null;
		$this->_data = null;
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'group':
				return $this->_group;
			case 'active':
				$this->checkActive();
				return $this->_active;
			case 'data':
				$this->loadData();
				return $this->_data;
			default:
				return parent::get($name);
		};
	}

	protected function checkActive()
	{
		if ($this->_active === null)
		{
			$dbh = Project_DB::get();

			$query = "SELECT Active FROM actiongroups WHERE ID=:id";
			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":id", $this->_group, PDO::PARAM_INT);

			if ($stmt->execute())
				if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_active = (bool)$row['Active'];
				else
					throw new Kiwi_No_Such_BannerGroup_Exception('ID = ' . $this->_group);
		}
	}

	protected function loadData()
	{
		if ($this->_data === null)
		{
			$this->_data = array();
			$this->checkActive();

			if ($this->_active)
			{
				$dbh = Project_DB::get();

				$query = "SELECT ID, Title, Description, Picture, Link FROM eshopactions WHERE AGID=:agid AND Active=1 ORDER BY Priority";
				$stmt = $dbh->prepare($query);
				$stmt->bindValue(":agid", $this->_group, PDO::PARAM_INT);

				if ($stmt->execute())
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
						$this->_data[] = new Var_Pool($row);
			}
		}
	}
}
?>