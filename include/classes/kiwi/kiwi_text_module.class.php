<?php
class Kiwi_Text_Module extends Kiwi_Module
{
	public function __construct($id = null, $name = null)
	{
		parent::__construct($id, self::KIWI_MODULE_TEXT);

		$this->_attributes->register('name', $name);
		$this->_attributes->register('content');
		$this->_attributes->register('lastchange');

		if ($this->id !== null || $this->name !== null) $this->loadData();
	}

	protected function loadData()
	{
		$dbh = Project_DB::get();

		$filter = array();
		$values = array();

		if ($this->id !== null)
		{
			$filter[] = ' M.ID = :id';
			$values['id'] = (int)$this->id;
		}

		if ($this->name !== null)
		{
			$filter[] = ' M.Name = :name';
			$values['name'] = $this->name;
		}

		$where = implode(' AND', $filter);

		$query = "SELECT M.ID, M.Type, M.Name, X.Content, M.LastChange, M.Active FROM modules AS M LEFT OUTER JOIN mod_text AS X ON M.ID=X.ID WHERE$where";

		$stmt = $dbh->prepare($query);

		foreach ($values as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		if ($stmt->execute())
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				if ($row['Type'] != $this->type)
					throw new Kiwi_Bad_Module_Type_Exception($row['Type'], $this->type);
				$this->id = $row['ID'];
				$this->name = $row['Name'];
				$this->content = $row['Content'];
				$this->lastchange = $row['LastChange'];
				$this->active = $row['Active'];
			}
			else
			{
				$idstr = $this->id !== null ? "id = $this->id" : "name = $this->name";
				throw new Kiwi_No_Such_Module_Exception($idstr);
			}
	}
}
?>