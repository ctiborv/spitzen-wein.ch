<?php
class Kiwi_Product_Sort
{
	protected $_sort_by;
	protected $_sort_dir;

	public function __construct()
	{
		$this->_sort_by = null;
		$this->_sort_dir = null;
	}

	public function __toString()
	{
		$str = <<<EOT
sort:$this->_sort_by#$this->_sort_dir
EOT;
		return $str;
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'sort_by':
				return $this->_sort_by;
			case 'sort_dir':
				return $this->_sort_dir;
			case 'sort_sql':
				return $this->getSortSQL();
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	public function set($name, $value)
	{
		switch ($name)
		{
			case 'sort_by':
				$this->_sort_by = $value;
				break;
			case 'sort_dir':
				$this->_sort_dir = $value;
				break;
			case 'sort_sql':		
				throw new Readonly_Variable_Exception($name, __CLASS__);
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	public function handleQS($ns)
	{
		$possibilities = array
		(
			't' => 'P.Title',
			'c' => 'P.NewCost'
		);

		foreach ($possibilities as $spk => $spv)
		{
			$qsvb = $ns . 'sb' . $spk;
			if (array_key_exists($qsvb . 'a', $_GET))
			{
				$this->_sort_by = $spv;
				$this->_sort_dir = 'ASC';
				break;
			}
			elseif (array_key_exists($qsvb . 'd', $_GET))
			{
				$this->_sort_by = $spv;
				$this->_sort_dir = 'DESC';
				break;
			}
		}
	}

	public function getSortSQL()
	{
		if ($this->_sort_by === null)
			return '';

		$sort_sql = ' ORDER BY ' . $this->_sort_by;
		if ($this->_sort_dir !== null)
			$sort_sql .= $this->_sort_dir == 'DESC' ? ' DESC' : ' ASC';

		return $sort_sql;
	}
}
?>