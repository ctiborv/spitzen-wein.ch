<?php
class Kiwi_Product_Search
{
	protected $_pmi;
	protected $_novelty;
	protected $_action;
	protected $_discount;
	protected $_title;
	protected $_cost_min;
	protected $_cost_max;
	protected $_properties;
	protected $_sql_params;

	const DEFAULT_PMI = 1;

	public function __construct()
	{
		$this->_pmi = self::DEFAULT_PMI;
		$this->_novelty = null;
		$this->_action = null;
		$this->_discount = null;
		$this->_title = null;
		$this->_cost_min = null;
		$this->_cost_max = null;
		$this->_properties = null;
		$this->_sql_params = null;
	}

	public function __toString()
	{
		$str = <<<EOT
search:$this->_pmi#$this->_novelty#$this->_action#$this->_discount#$this->_title#$this->_cost_min#$this->_cost_max#$this->_properties#$this->_sql_params
EOT;
		return $str;
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'pmi':
				return $this->_pmi;
			case 'novelty':
				return $this->_novelty;
			case 'action':
				return $this->_action;
			case 'discount':
				return $this->_discount;
			case 'title':
				return $this->_title;
			case 'cost_min':
				return $this->_cost_min;
			case 'cost_max':
				return $this->_cost_max;
			case 'properties':
				return $this->_properties;
			case 'search_sql':
				return $this->getSearchSQL();
			case 'sql_params':
				return $this->_sql_params;
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
			case 'pmi':
				$this->_pmi = $value;
				break;
			case 'novelty':
				$this->_novelty = $value;
				break;
			case 'action':
				$this->_action = $value;
				break;
			case 'discount':
				$this->_discount = $value;
				break;
			case 'title':
				$this->_title = $value;
				break;
			case 'cost_min':
				$this->_cost_min = $value;
				break;
			case 'cost_max':
				$this->_cost_max = $value;
				break;
			case 'properties':
				$this->_properties = $value;
				break;
			case 'search_sql':
			case 'sql_params':
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
		// product line id is not extracted from query string as it can be invalid
		// therefore, the product line id has to be set explicitly

		$this->_novelty = array_key_exists("{$ns}novinky", $_GET);
		$this->_action = array_key_exists("{$ns}akce", $_GET);
		$this->_discount = array_key_exists("{$ns}slevy", $_GET);

		if (array_key_exists("{$ns}s", $_GET))
			$this->_title = $_GET["{$ns}s"];

		if (array_key_exists("{$ns}cmin", $_GET))
			$this->_cost_min = (int)$_GET["{$ns}cmin"];

		if (array_key_exists("{$ns}cmax", $_GET))
			$this->_cost_max = (int)$_GET["{$ns}cmax"];

		if (array_key_exists("{$ns}pp", $_GET))
			$this->_properties = $_GET["{$ns}pp"];
	}

	public function getSearchSQL()
	{
		$this->_sql_params = array();

		$dbh = Project_DB::get();
		$query = "SELECT Contained FROM eshoph WHERE Container=:pmi";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(':pmi', $this->_pmi, PDO::PARAM_INT);
		$stmt->execute();
		$sub_groups = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			$sub_groups[] = $row['Contained'];
		$stmt->closeCursor();
		if (!empty($sub_groups))
		{
			$sub_groups_str = implode(',', $sub_groups);
			$search_pmi_sql = " AND PB.GID IN ($sub_groups_str)";
		}

		if (!isset($search_pmi_sql)) $search_pmi_sql = "";

		$search_flags_sql = "";
		if ($this->_novelty) $search_flags_sql .= " AND P.Novelty=1";
		if ($this->_action) $search_flags_sql .= " AND P.Action=1";
		if ($this->_discount) $search_flags_sql .= " AND P.Discount=1";

		if ($this->_title !== null && $this->_title !== '')
		{
			$search_title_sql = " AND P.Title LIKE :title";
			$this->_sql_params['title'] = '%' . $this->_title . '%';
		}
		else
			$search_title_sql = "";

		$search_cost_sql = '';
		if ($this->_cost_min !== null)
		{
			$search_cost_sql .= " AND P.NewCost>=:cmin";
			$this->_sql_params['cmin'] = (int)$this->_cost_min;
		}

		if ($this->_cost_max !== null)
		{
			$search_cost_sql .= " AND P.NewCost<=:cmax";
			$this->_sql_params['cmax'] = (int)$this->_cost_max;
		}

		$search_properties_sql = '';
		if ($this->_properties !== null)
		{
			$prop_filters = array
			(
				'==' => array(),
				'<=' => array(),
				'>=' => array(),
				'<>' => array(), // intervals
				'?s' => array() // substrings
			);

			$matches = array();

			// parse the property filter string:
			$properties = explode(',', $this->_properties);
			foreach ($properties as $prop_filter)
			{
				/*
				 * $prop_filter can be either:
				 * 1. number - id of property value
				 * 2. interval in format of number-number
				 * 2.1 0-number
				 * 2.2 number-0
				 * 2.3 non_zero_number-non_zero_number
				 * 3. number:string - id of property and string to search for in property values
				 */
				if (preg_match('/^[0-9]+$/', $prop_filter))
					$prop_filters['=='][] = (int)$prop_filter;
				elseif (preg_match('/^([0-9]+)-([0-9]+)$/', $prop_filter, $matches))
				{
					$lbound = (int)$matches[1];
					$rbound = (int)$matches[2];
					if ($lbound == 0 && $rbound > 0) $prop_filters['<='][] = $rbound;
					elseif ($lbound > 0 && $rbound == 0) $prop_filters['>='][] = $lbound;
					elseif ($lbound > 0 && $lbound == $rbound) $prop_filters['=='][] = $lbound;
					elseif ($lbound > 0 && $rbound > 0) $prop_filters['<>'][] = array('low' => $lbound, 'high' => $rbound);
				}
				elseif (preg_match('/^([0-9]+):(.+)$/', $prop_filter, $matches))
				{
					$prop_id = (int)$matches[1];
					$pv_str = $matches[2];
					$prop_filters['?s'][] = array('pid' => $prop_id, 'string' => $pv_str);
				}
			}

			$filter_selects = array();
			$si = 1;

			foreach ($prop_filters['=='] as $ppvid)
			{
				$filter_selects[$si] = "SELECT PID AS PID$si FROM prodpbinds WHERE PPVID=:ppvid$si";
				$this->_sql_params['ppvid' . $si] = $ppvid;
				$si++;
			}

			foreach ($prop_filters['<='] as $ppvid)
			{
				$filter_selects[$si] = "SELECT B$si.PID AS PID$si FROM prodpbinds AS B$si JOIN prodpvals AS V$si ON B$si.PPVID=V$si.ID AND V$si.Active=1 JOIN prodpvals AS V{$si}H ON V$si.PID=V{$si}H.PID WHERE V{$si}H.ID=:ppvid$si AND V$si.Priority<=V{$si}H.Priority";
				$this->_sql_params['ppvid' . $si] = $ppvid;
				$si++;
			}

			foreach ($prop_filters['>='] as $ppvid)
			{
				$filter_selects[$si] = "SELECT B$si.PID AS PID$si FROM prodpbinds AS B$si JOIN prodpvals AS V$si ON B$si.PPVID=V$si.ID AND V$si.Active=1 JOIN prodpvals AS V{$si}L ON V$si.PID=V{$si}L.PID WHERE V{$si}L.ID=:ppvid$si AND V$si.Priority>=V{$si}L.Priority";
				$this->_sql_params['ppvid' . $si] = $ppvid;
				$si++;
			}

			foreach ($prop_filters['<>'] as $ppvids)
			{
				$filter_selects[$si] = "SELECT B$si.PID AS PID$si FROM prodpbinds AS B$si JOIN prodpvals AS V$si ON B$si.PPVID=V$si.ID AND V$si.Active=1 JOIN prodpvals AS V{$si}L ON V$si.PID=V{$si}L.PID JOIN prodpvals AS V{$si}H ON V$si.PID=V{$si}H.PID WHERE V{$si}L.ID=:ppvidlow$si AND V{$si}H.ID=:ppvidhigh$si AND V$si.Priority BETWEEN V{$si}L.Priority AND V{$si}H.Priority";
				$this->_sql_params['ppvidlow' . $si] = $ppvids['low'];
				$this->_sql_params['ppvidhigh' . $si] = $ppvids['high'];
				$si++;
			}

			foreach ($prop_filters['?s'] as $pp_search)
			{
				$filter_selects[$si] = "SELECT B$si.PID AS PID$si FROM prodpbinds AS B$si JOIN prodpvals AS V$si ON B$si.PPVID=V$si.ID AND V$si.Active=1 AND V$si.PID=:ppsearch$si AND V$si.Value LIKE :substr$si";
				$this->_sql_params['ppsearch' . $si] = $pp_search['pid'];
				$this->_sql_params['substr' . $si] = '%' . $pp_search['string'] . '%';
				$si++;
			}

			if (!empty($filter_selects))
			{
				// combine the selects
				$filter_sql = 'SELECT F1.PID1';
				foreach ($filter_selects as $si => $select)
				{
					if ($si == 1)
					{
						$filter_sql .= " FROM ($select) AS F1";
						$si++;
					}
					else
					{
						$filter_sql .= " JOIN ($select) AS F$si ON F1.PID1=F$si.PID$si";
						$si++;
					}
				}
				$search_properties_sql = " AND P.ID IN ($filter_sql)";
			}
			else $search_properties_sql = '';
		}

		$search_sql = $search_pmi_sql . $search_flags_sql . $search_title_sql . $search_cost_sql . $search_properties_sql;
		return $search_sql;
	}
}
?>
