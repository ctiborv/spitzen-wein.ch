<?php
class Kiwi_Catalog extends Kiwi_Object
{
	protected $_products;
	protected $_total;
	protected $_limit;
	protected $_page;
	protected $_search;
	protected $_sort;
	protected $_line;
	protected $_skip;

	public function __construct($ppp = 0, $page = 1, Kiwi_Product_Search $search, Kiwi_Product_Sort $sort = null)
	{
		parent::__construct();
		$this->_products = null;
		$this->_total = null;
		$this->_limit = $ppp;
		$this->_page = $page;
		$this->_search = $search;
		$this->_sort = $sort;
		$this->_line = null;
		$this->_skip = 0;
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'products':
				$this->loadData();
				return $this->_products;
			case 'total':
				$this->loadData();
				return $this->_total;
			case 'limit':
				return $this->_limit;
			case 'page':
				return $this->_page;
			case 'search':
				return $this->_search;
			case 'sort':
				return $this->_sort;
			case 'line':
				$this->loadLineInfo();
				return $this->_line;
			default:
				return parent::get($name);
		};
	}

	public function set($name, $value)
	{
		switch ($name)
		{
			case 'skip':
				$this->_skip = max((int) $value, 0);
				break;
			default:
				return parent::set($name, $value);
		}
	}

	protected function loadData()
	{
		if ($this->_products === null)
		{
			$this->_products = array();
			$dbh = Project_DB::get();

			$search_sql = $this->_search->search_sql;
			$sql_params = $this->_search->sql_params;
			//$pmi = $this->_search->pmi;

			$sort_sql = $this->_sort !== null ? $this->_sort->sort_sql : '';

			if ($this->_limit > 0)
			{
				$from = ($this->_page - 1) * $this->_limit + $this->_skip;
				$limit_sql = " LIMIT :from, :limit";
				$sql_params['from'] = $from;
				$sql_params['limit'] = $this->_limit;
			}
			else
				$limit_sql = '';

			$query = "SELECT SQL_CALC_FOUND_ROWS PB.ID AS PBID, P.ID AS PID, E.ID AS EID, P.Title, P.ShortDesc, P.Code, E.URL AS LURL, P.URL, P.OriginalCost, P.NewCost, P.Photo, P.Discount, P.Action, P.Novelty FROM prodbinds AS PB JOIN products AS P ON PB.PID=P.ID JOIN eshop AS E ON PB.GID=E.ID WHERE PB.Active=1 AND P.Active=1 AND P.Exposed=1 AND E.Active=1$search_sql GROUP BY P.ID$sort_sql$limit_sql";
			$stmt = $dbh->prepare($query);

			foreach ($sql_params as $spk => $spv)
				$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

			if ($stmt->execute())
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$this->_products[] = new Var_Pool($row);

			$stmt->closeCursor();

			$query = "SELECT FOUND_ROWS()";
			$stmt = $dbh->prepare($query);

			if ($stmt->execute())
				if ($row = $stmt->fetch(PDO::FETCH_NUM))
					$this->_total = $row[0] - $this->_skip;

			$stmt->closeCursor();
		}
	}

	public function loadLineInfo()
	{
		if ($this->_line === null)
		{
			$this->_line = 0;
			if ($this->_search->pmi)
			{
				$dbh = Project_DB::get();

				$query = "SELECT ID, Name, URL, PageTitle, Description, Icon, Subgroup, Parent FROM eshop WHERE ID=:id";
				$stmt = $dbh->prepare($query);
				$stmt->bindValue(':id', $this->_search->pmi , PDO::PARAM_INT);

				if ($stmt->execute())
					if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
						$this->_line = new Var_Pool($row);
			}
		}
	}

	public static function getLines($filters, $columns = null)
	{
		if ($columns === null)
			$columns = array('ID');
		elseif (!is_array($columns))
			$columns = array($columns);

		$dbh = Project_DB::get();
		$sql_filters = array();
		$sql_params = array();
		foreach ($filters as $fkey => $fval)
		{
			switch ($fkey)
			{
				case 'name':
					$sql_filters[] = "Name=:name";
					$sql_params['name'] = $fval;
					break;
				case 'name_like':
					$sql_filters[] = "Name LIKE :name_like";
					$sql_params['name_like'] = $fval;
					break;
				case 'parent':
					$sql_filters[] = "Parent=:parent";
					$sql_params['parent'] = $fval;
					break;
				default:
					throw new Invalid_Argument_Value_Exception("filters/$fkey", $fkey);
			}
		}

		if (!empty($sql_filters))
			$fsql = implode(' AND ', $sql_filters);
		else
			$fsql = 1;

		$query = "SELECT ID FROM eshop WHERE " . $fsql;

		$stmt = $dbh->prepare($query);
		foreach ($sql_params as $spk => $spv)
			$stmt->bindValue(":$spk", $spv, is_int($spv) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$lines = array();
		if ($stmt->execute())
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				$lines[] = new Var_Pool($row);

		return $lines;
	}
}
