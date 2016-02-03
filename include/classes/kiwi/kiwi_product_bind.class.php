<?php
class Kiwi_Product_Bind extends Kiwi_Object
{
	protected $_attributes;

	public function __construct($id)
	{
		parent::__construct();
		$this->_attributes = new Var_Pool;

		$this->_attributes->register('id');
		$this->_attributes->register('pid');
		$this->_attributes->register('gid');

		if ($this->id !== null) $this->loadData();
	}

	public function get($name)
	{
		try
		{
			return $this->_attributes->get($name);
		}
		catch (No_Such_Variable_Exception $e)
		{
			return parent::get($name);
		}
	}
	
	public function set($name, $value)
	{
		$this->_attributes->set($name, $value);
	}

	protected function loadData()
	{
		$dbh = Project_DB::get();

		$query = "SELECT PID, GID FROM prodbinds WHERE ID=:ID AND Active=1";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(":ID", $this->id, PDO::PARAM_INT);
		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$this->pid = $row['PID'];
				$this->gid = $row['GID'];
			}
	}
}
?>
