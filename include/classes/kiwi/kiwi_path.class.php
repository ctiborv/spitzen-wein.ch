<?php
class Kiwi_Path extends Kiwi_Object
{
	protected $_data;

	public function __construct($gid)
	{
		parent::__construct();
		$this->_data = array();
		$this->populatePath($gid);
	}

	public function get($name)
	{
		if ($name == 'data') return $this->_data;
		else return parent::get($name);
	}

	protected function populatePath($gid)
	{
		$rpath = array();
		$dbh = Project_DB::get();
		$query = "SELECT Name, URL, Subgroup, Parent FROM eshop WHERE ID=:gid AND Active=1";
		$stmt = $dbh->prepare($query);

		$max_path_length = 32;
		while ($gid > 1 && $max_path_length-- > 0)
		{
			$stmt->bindValue(":gid", $gid, PDO::PARAM_INT);

			if ($stmt->execute())
				if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$rpath[] = array
					(
						'ID' => $gid,
						'Name' => $row['Name'],
						'URL' => $row['URL'],
						'Subgroup' => $row['Subgroup']
					);
				}
				else
				{
					$rpath = array();
					return;
				}

			$stmt->closeCursor();
			$gid = $row['Parent'];
		}

		$this->_data = array_reverse($rpath);
	}
}
?>