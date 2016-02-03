<?php
abstract class Kiwi_Module extends Kiwi_Object
{
	protected $_attributes;

	const KIWI_MODULE_TEXT = 1;
	const KIWI_MODULE_PICTURE = 2;
	const KIWI_MODULE_NEWS = 3;
	const KIWI_MODULE_GALLERY = 4;

	public function __construct($id = null, $type = null, $active = true)
	{
		parent::__construct();
		$this->_attributes = new Var_Pool;

		$this->_attributes->register('id', $id);
		$this->_attributes->register('type', $type);
		$this->_attributes->register('active', $active);
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

	public static function load($mid)
	{
		// neni jasne, zda neni tato funkce nadbytecna, ci zda nema byt v jine tride
		$dbh = Project_DB::get();

		$query = "SELECT ID, Type, Active FROM modules WHERE ID=?";

		$stmt = $dbh->prepare($query);
		if ($stmt->execute(array($mid)))
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$this->type = $row['Type'];
				$this->active = $row['Active'];
				switch ($row['Type'])
				{
					case KIWI_MODULE_TEXT:
						throw new Unsupported_Feature_Exception();
						// return new Kiwi_Text_Module($this->id);
					case KIWI_MODULE_PICTURE:
						throw new Unsupported_Feature_Exception();
					case KIWI_MODULE_NEWS:
						return new Kiwi_News_Module($this->id);
					case KIWI_MODULE_GALLERY:
						throw new Unsupported_Feature_Exception();
				}
			}

		throw new Kiwi_No_Such_Module_Exception($this->id);
	}

	public static function getModuleActiveStatus($mid)
	{
		$dbh = Project_DB::get();

		$query = "SELECT Active FROM modules WHERE ID=?";

		$stmt = $dbh->prepare($query);
		if ($stmt->execute(array($mid)))
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				return $row['Active'];

		return false;
	}
}
?>