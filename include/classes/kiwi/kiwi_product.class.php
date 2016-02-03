<?php
class Kiwi_Product extends Kiwi_Object
{
	protected $_id;
	protected $_pbid;
	protected $_prodbind;
	protected $_url;
	protected $_data;
	protected $_lines;
	protected $_paths;
	protected $_properties;
	protected $_property_values;
	protected $_extra_photos;
	protected $_illustrative_photos;
	protected $_collection;
	protected $_attachments;

	const PT_TEXT = 1;
	const PT_ICON = 2;
	const PT_COLOR = 3;

	public function __construct($id, $pbid = null)
	{
		parent::__construct();
		$this->_id = $id;
		$this->_pbid = $pbid;
		$this->_prodbind = null;
		$this->_url = null;
		$this->_data = null;
		$this->_lines = null;
		$this->_paths = null;
		$this->_properties = null;
		$this->_property_values = null;
		$this->_extra_photos = null;
		$this->_illustrative_photos = null;
		$this->_collection = null;
		$this->_attachments = null;
		if ($this->_id === null && $this->_pbid === null)
			throw new Data_Insufficient_Exception('Product ID or Product Bind ID');
		else
			$this->loadID();
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'prodbind':
				return $this->_prodbind;
			case 'PBID':
				return $this->_prodbind !== null ? $this->_prodbind->id : null;
			case 'lines':
				$this->loadLines();
				return $this->_lines;
			case 'paths':
				$this->loadPaths();
				return $this->_paths;
			case 'properties':
				$this->loadProperties();
				return $this->_properties;
			case 'selectablePropertiesCount':
				$this->loadProperties();
				return $this->selectablePropertiesCount();
			case 'extra_photos':
				$this->loadExtraPhotos();
				return $this->_extra_photos;
			case 'illustrative_photos':
				$this->loadIllustrativePhotos();
				return $this->_illustrative_photos;
			case 'collection':
				$this->loadCollection();
				return $this->_collection;
			case 'attachments':
				$this->loadAttachments();
				return $this->_attachments;
			default:
				try
				{
					$this->loadData();
					return $this->_data->$name;
				}
				catch (No_Such_Variable_Exception $e)
				{
					return parent::get($name);
				}
		};
	}

	public function getProperty($propid)
	{
		$this->loadProperties();
		if (!array_key_exists($propid, $this->_properties))
			throw new Kiwi_No_Such_Product_Property_Exception('ID = ' . $propid);
		return $this->_properties[$propid];
	}

	public function getPropertyByName($propname)
	{
		$this->loadProperties();
		foreach ($this->_properties as $property)
			if ($property['Name'] == $propname) return $property;
		throw new Kiwi_No_Such_Product_Property_Exception('Name = ' . $propname);
	}

	public function getPropertyValue($propvalue)
	{
		$this->loadProperties();
		$propval = array_key_exists($propvalue, $this->_property_values) ? $this->_property_values[$propvalue] : null;
		return $propval;
	}

	protected function loadID()
	{
		if ($this->_id === null)
		{		
			$this->_prodbind = new Kiwi_Product_Bind($this->_pbid);
			if (($this->_id = $this->_prodbind->pid) === null)
				throw new Kiwi_No_Such_Product_Exception('prodbinds.ID = ' . $this->_pbid);
		}
	}

	protected function loadData()
	{
		if ($this->_data === null)
		{
			$dbh = Project_DB::get();

			$query = "SELECT ID, Title, Code, ShortDesc, URL, PageTitle, LongDesc, Collection, OriginalCost, NewCost, Photo, Discount, Action, Novelty FROM products WHERE ID=:id AND Active=1";
			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":id", $this->_id, PDO::PARAM_INT);

			if ($stmt->execute())
				if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_data = new Var_Pool($row);
				else
					throw new Kiwi_No_Such_Product_Exception('ID = ' . $this->_id);
		}
	}

	protected function loadLines()
	{
		if ($this->_lines === null)
		{
			$this->_lines = array();

			$dbh = Project_DB::get();

			$query = "SELECT GID FROM prodbinds WHERE PID=:id AND Active=1 ORDER BY ID";
			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":id", $this->_id, PDO::PARAM_INT);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_lines[] = $row['GID'];
		}
	}

	protected function loadPaths()
	{
		if ($this->_paths === null)
		{
			$this->_paths = array();
			
			if ($this->_prodbind !== null)
			{
				$path = new Kiwi_Path($this->_prodbind->gid);
				$path_a = $path->data;
				if (!empty($path_a)) $this->_paths[] = $path_a;
			}
			else
			{
				$this->loadLines();

				foreach ($this->_lines as $line)
				{
					$path = new Kiwi_Path($line);
					$path_a = $path->data;
					if (!empty($path_a)) $this->_paths[] = $path_a;					
				}
			}
		}
	}

	protected function loadProperties()
	{
		if ($this->_properties === null)
		{
			$this->_properties = array();
			$this->_property_values = array();
			$dbh = Project_DB::get();

			$query = "SELECT ID, Name, Type, DataType FROM prodprops WHERE Active=1 ORDER BY Priority";
			$stmt = $dbh->prepare($query);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_properties[$row['ID']] = array
					(
						'Name' => $row['Name'],
						'Type' => $row['Type'],
						'DataType' => $row['DataType'],
						'Values' => array()
					);

			$query = "SELECT V.ID AS ValueID, V.PID AS PropertyID, V.Value AS Value, V.ExtraData AS ExtraData, V.Description AS ValueDesc, B.Photo, B.ToCost AS ToCost FROM prodpbinds AS B JOIN prodpvals AS V ON B.PPVID=V.ID WHERE B.PID=:pid AND V.Active=1 ORDER BY V.Priority";
			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":pid", $this->_id, PDO::PARAM_INT);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					if (array_key_exists($row['PropertyID'], $this->_properties))
					{
						$this->_property_values[$row['ValueID']] = array
						(
							'ID' => $row['ValueID'],
							'PropertyID' => $row['PropertyID'],
							'Value' => $row['Value'],
							'ExtraData' => $row['ExtraData'],
							'Description' => $row['ValueDesc'],
							'Photo' => $row['Photo'],
							'ToCost' => $row['ToCost']
						);
						$this->_properties[$row['PropertyID']]['Values'][] =& $this->_property_values[$row['ValueID']];
					}
		}
	}

	protected function selectablePropertiesCount()
	{
		$count = 0;
		foreach ($this->_properties as $prop)
			if ($prop['Type'] == 1 && !empty($prop['Values']))
				$count++;
		return $count;
	}

	protected function loadExtraPhotos()
	{
		if ($this->_extra_photos === null)
		{
			$this->_extra_photos = array();
			$dbh = Project_DB::get();

			$query = "SELECT ID, FileName FROM prodepics WHERE PID=:pid ORDER BY ID";
			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":pid", $this->_id, PDO::PARAM_INT);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_extra_photos[] = array
					(
						'ID' => $row['ID'],
						'FileName' => $row['FileName']
					);
		}
	}

	protected function loadIllustrativePhotos()
	{
		if ($this->_illustrative_photos === null)
		{
			$this->_illustrative_photos = array();
			$dbh = Project_DB::get();

			$query = "SELECT ID, FileName FROM prodipics WHERE PID=:pid ORDER BY ID";
			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":pid", $this->_id, PDO::PARAM_INT);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_illustrative_photos[] = array
					(
						'ID' => $row['ID'],
						'FileName' => $row['FileName']
					);
		}
	}

	protected function loadCollection()
	{
		if ($this->_collection === null)
		{
			$this->_collection = array();
			$dbh = Project_DB::get();

			if ($this->Collection != '')
			{
				$query = "SELECT ID, Title, URL, Photo FROM products WHERE ID!=:id AND Active=1 And Collection=:collection ORDER BY Title";
				$stmt = $dbh->prepare($query);
				$stmt->bindValue(":id", $this->_id, PDO::PARAM_INT);
				$stmt->bindValue(":collection", $this->Collection, PDO::PARAM_STR);

				if ($stmt->execute())
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
						$this->_collection[] = array
						(
							'ID' => $row['ID'],
							'Title' => $row['Title'],
							'URL' => $row['URL'],
							'FileName' => $row['Photo']
						);
			}
		}
	}

	protected function loadAttachments()
	{
		if ($this->_attachments === null)
		{
			$this->_attachments = array();
			$dbh = Project_DB::get();

			$query = "SELECT ID, FileName, Title FROM prodattach WHERE PID=:pid ORDER BY ID";
			$stmt = $dbh->prepare($query);
			$stmt->bindValue(":pid", $this->_id, PDO::PARAM_INT);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_attachments[] = array
					(
						'ID' => $row['ID'],
						'FileName' => $row['FileName'],
						'Title' => $row['Title']
					);
		}
	}
}
?>