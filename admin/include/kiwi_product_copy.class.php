<?php
require_once 'project.inc.php';
require_once 'page_names.inc.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_Product_Copy
{
	protected $_s_pid;
	protected $_d_pid;
	protected $_data;
	protected $_files_to_copy;

	public function __construct($pid)
	{
		$this->_s_pid = $pid;
		$this->_d_pid = null;
		$this->_data = null;
		$this->_files_to_copy = array();
		if ($pid > 0)
			$this->copy();
	}

	public function getCopyPID()
	{
		return $this->_d_pid;
	}

	protected function loadData()
	{
		if ($this->_data === null && $this->_s_pid)
		{
			$result = mysql_query("SELECT ID, Title, Code, ShortDesc, URL, PageTitle, LongDesc, Collection, OriginalCost, NewCost, WSCost, Photo, Discount, Sellout, Action, Novelty, Exposed, LastChange FROM products WHERE ID=$this->_s_pid");
			if ($row = mysql_fetch_assoc($result))
				$this->_data = new Kiwi_DataRow($row);
		}
	}

	protected function copy()
	{
		$this->loadData();
		$this->copyProductRow(); // including photo
		$this->flushFileCopy();
		$this->copyExtraPhotos();
		$this->flushFileCopy();
		$this->copyIllustrativePhotos();
		$this->flushFileCopy();
		$this->copyProductProperties(); // including eventual photos
		$this->flushFileCopy();
	}

	protected function copyProductRow()
	{
		$vals = array();
		$vals['Title'] = $this->_data->Title . ' - kopie';
		$vals['Code'] = $this->_data->Code . '-kopie';
		$vals['ShortDesc'] = $this->_data->ShortDesc;
		$vals['URL'] = '';
		$vals['PageTitle'] = '';
		$vals['LongDesc'] = $this->_data->LongDesc;
		$vals['Collection'] = $this->_data->Collection;
		$vals['OriginalCost'] = $this->_data->OriginalCost;
		$vals['NewCost'] = $this->_data->NewCost;
		$vals['WSCost'] = $this->_data->WSCost;
		$vals['Photo'] = $photo = $this->copyMainPhoto();
		$vals['Discount'] = $this->_data->Discount;
		$vals['Sellout'] = $this->_data->Sellout;
		$vals['Action'] = $this->_data->Action;
		$vals['Novelty'] = $this->_data->Novelty;
		$vals['Exposed'] = $this->_data->Exposed;

		foreach ($vals as &$val)
			$val = "'" . mysql_real_escape_string($val) . "'";

		$values = implode(', ', $vals);

		if (!mysql_query("INSERT INTO products(Title, Code, ShortDesc, URL, PageTitle, LongDesc, Collection, OriginalCost, NewCost, WSCost, Photo, Discount, Sellout, Action, Novelty, Exposed) VALUES ($values)"))
			throw new Exception('Pokus uložit kopii produktu do databáze selhal');

		$this->_d_pid = mysql_insert_id();
	}

	private function copyMainPhoto()
	{
		return $this->copyProductPhoto($this->_data->Photo, array('detail', 'catalog', 'catalog2', 'collection'));
	}

	private function copyProductPhoto($photo, $dirs)
	{
		global $eshop_picture_config;
		$pdir = KIWI_DIR_PRODUCTS;

		if ($photo !== '')
		{
			$photo_pi = pathinfo($photo);
			$suffix = '';
			do
			{
				$suffix .= '_';
				$new_photo = $photo_pi['filename'] . $suffix . '.' . $photo_pi['extension'];
			}
			while (file_exists($pdir . 'photo/' . $new_photo));

			$this->copyFile($pdir . 'photo/' . $photo, $pdir . 'photo/' . $new_photo);
			foreach ($dirs as $dir)
				$this->copyFile($pdir . $dir . '/' . $photo, $pdir . $dir . '/' . $new_photo);
		}
		else
			$new_photo = '';

		return $new_photo;
	}

	private function copyExtraPhotos()
	{
		if ($this->_d_pid === null)
			throw new Exception('ID kopie produktu není známo');

		$result = mysql_query("SELECT FileName FROM prodepics WHERE PID=$this->_s_pid ORDER BY ID");
		$extra_photos = array();
		while ($row = mysql_fetch_row($result))
			$extra_photos[] = $this->copyProductPhoto($row[0], array('extra'));

		if (!empty($extra_photos))
		{
			$pairs = array();
			foreach ($extra_photos as $photo)
				$pairs[] = $this->_d_pid . ', "' . mysql_real_escape_string($photo) . '"';

			$pairs_list = implode('),(', $pairs);
			if (!mysql_query("INSERT INTO prodepics(PID, FileName) VALUES ($pairs_list)"))
				throw new Exception('Pokus uložit další fotografie produktu do databáze selhal');
		}
	}

	private function copyIllustrativePhotos()
	{
		if ($this->_d_pid === null)
			throw new Exception('ID kopie produktu není známo');

		$result = mysql_query("SELECT FileName FROM prodipics WHERE PID=$this->_s_pid ORDER BY ID");
		$illustrative_photos = array();
		while ($row = mysql_fetch_row($result))
			$illustrative_photos[] = $this->copyProductPhoto($row[0], array('illustrative'));

		if (!empty($illustrative_photos))
		{
			$pairs = array();
			foreach ($illustrative_photos as $photo)
				$pairs[] = $this->_d_pid . ', "' . mysql_real_escape_string($photo) . '"';

			$pairs_list = implode('),(', $pairs);
			if (!mysql_query("INSERT INTO prodipics(PID, FileName) VALUES ($pairs_list)"))
				throw new Exception('Pokus uložit ilustrativní fotografie produktu do databáze selhal');
		}
	}

	private function copyProductProperties()
	{
		if ($this->_d_pid === null)
			throw new Exception('ID kopie produktu není známo');

		$result = mysql_query("SELECT PPVID, Photo FROM prodpbinds WHERE PID=$this->_s_pid");
		$properties = array();
		while ($row = mysql_fetch_row($result))
		{
			$properties[] = array
			(
				'Value' => $row[0],
				'Photo' => $this->copyProductPhoto($row[1], array('detail', 'catalog', 'catalog2', 'collection'))
			);
		}

		if (!empty($properties))
		{
			$triplets = array();
			foreach ($properties as $property)
				$triplets[] = $this->_d_pid . ', ' . $property['Value'] . ', "' . mysql_real_escape_string($property['Photo']) . '"';
		
			$triplets_list = implode('),(', $triplets);
			if (!mysql_query("INSERT INTO prodpbinds(PID, PPVID, Photo) VALUES ($triplets_list)"))
				throw new Exception('Pokus uložit vlastnosti produktu do databáze selhal');
		}
	}

	private function copyFile($from, $to)
	{
		$this->_files_to_copy[] = array
		(
			'from' => $from,
			'to' => $to
		);
	}

	private function flushFileCopy()
	{
		$errors = array();
		foreach ($this->_files_to_copy as $record)
		{
			if (!@copy($record['from'], $record['to']))
				$errors[] = '"' . $record['from'] . '" => "' . $record['to'] . '"';
		}
		$this->_files_to_copy = array();
		if (!empty($errors))
			throw new Exception("Nepodařilo se zkopírovat: " . implode(", ", $errors));
	}
}
?>