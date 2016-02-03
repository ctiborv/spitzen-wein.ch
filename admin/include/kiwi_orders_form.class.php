<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';

class Kiwi_Orders_Form extends Page_Item
{
	protected $all_checked;
	protected $orders;
	protected $index;
	protected $checked;
	protected $checked_count;
	protected $lastchange;
	protected $year;
	protected $years;
	protected $filter;

	const FILTER_GENERAL = 'g',
		FILTER_STATUS = 's';

	public function __construct()
	{
		parent::__construct();
		$this->all_checked = false;
		$this->orders = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->year = date('Y');
		$this->years = array();
		$this->filter = array();
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadOrders();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->consQS();

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>KATALOG Bestellungen - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$tabs_html = '';
		foreach ($this->years as $year => $ycount)
		{
			if ($this->year == $year)
				$tabs_html .= <<<EOT

			<span>$year</span>
EOT;
			elseif ($ycount == 0)
				$tabs_html .= <<<EOT

			<b>$year</b>
EOT;
			else
			{
				$cqs = $this->consQS(array('y' => $year));
				$tabs_html .= <<<EOT

			<a href="$self$cqs">$year</a>
EOT;
			}
		}

		if ($this->year === null)
			$tabs_html .= <<<EOT
			<span>Alles</span>

EOT;
		else
		{
			$cqs = $this->consQS(array('y' => NULL));
			$tabs_html .= <<<EOT
			<a href="$self$cqs">Alles</a>

EOT;
		}

		$f = $this->filter;
		if (isset($this->filter[self::FILTER_STATUS]) && $this->filter[self::FILTER_STATUS] == 0)
			$tabs_html .= <<<EOT
			<span>in Bearbeiting</span>

EOT;
		else
		{
			$f[self::FILTER_STATUS] = 0; // in Bearbeitung
			$cqs = $this->consQS(array('f' => $f));
			$tabs_html .= <<<EOT
			<a href="$self$cqs">in Bearbeitung</a>

EOT;
		}

		if (isset($this->filter[self::FILTER_STATUS]) && $this->filter[self::FILTER_STATUS] == 1) // gesendet
			$tabs_html .= <<<EOT
			<span>gesendet</span>

EOT;
		else
		{
			$f[self::FILTER_STATUS] = 1; // gesendet
			$cqs = $this->consQS(array('f' => $f));

			$tabs_html .= <<<EOT
			<a href="$self$cqs">gesendet</a>

EOT;
		}

		if (isset($this->filter[self::FILTER_STATUS]) && $this->filter[self::FILTER_STATUS] == 2) // fertig
			$tabs_html .= <<<EOT
			<span>fertig</span>

EOT;
		else
		{
			$f[self::FILTER_STATUS] = 2; // fertig
			$cqs = $this->consQS(array('f' => $f));

			$tabs_html .= <<<EOT
			<a href="$self$cqs">fertig</a>

EOT;
		}

		if (!array_key_exists(self::FILTER_STATUS, $this->filter))
			$tabs_html .= <<<EOT
			<span>alle Bestellungen</span>

EOT;
		else
		{
			unset($f[self::FILTER_STATUS]);
			$cqs = $this->consQS(array('f' => $f));
			$tabs_html .= <<<EOT
			<a href="$self$cqs">alle Bestellungen</a>

EOT;
		}


		$html .= <<<EOT
		<div id="zalozky">$tabs_html
			<br class="clear" />
		</div>

EOT;

		$disabled_str = sizeof($this->orders) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

    $html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Orders_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Bestellnummer</th>
					<th>Name</th>
					<th>Datum und Zeit</th>
					<th>Zustand</th>
					<th>Zahlung</th>
					<th>Gesamtpreis</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);

		foreach ($this->orders as $order)
		{
			if ($this->year !== null)
			{
				$yr = (int)$order->Year;
				if ($yr != $this->year) continue;
			}

			$checked_str = (isset($this->checked[$order->ID]) && $this->checked[$order->ID]) ? ' checked' : '';

			$olink = KIWI_EDIT_ORDER . "?o=$order->ID";

			$o_id = sprintf("%03d", $order->YID) . "-$order->Year";

			$name = $this->formatOrdererName($order);

			$dt = parseDateTime($order->Created);
			$orderdate = date('j.n.Y H:i', $dt['stamp']);

			$states = array(0 => 'in Bearbeitung', 1 => 'gesendet', 2 => 'Fertig', 3 => 'annulliert', 4 => 'im Gange', 5 => 'vom System annulliert', 6 => 'Nachlieferung');
			$status = $states[$order->Status];

			$payment = $this->formatPayment($order);

			$totalsum_f = $this->formatCost($this->roundCost($order->TotalCost), 'CHF');

			$html .= <<<EOT
				<tr class="t-s-$sw order-status-{$order->Status}">
					<td><input type="checkbox" name="check[]" value="$order->ID" onclick="Kiwi_Orders_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$olink">$o_id</a></td>
					<td>$name</td>
					<td>$orderdate</td>
					<td class="order-status">$status</td>
					<td>$payment</td>
					<td>$totalsum_f</td>
				</tr>

EOT;

			$sw = $next_sw[$sw];
		}

		if (sizeof($this->checked) == 0)
		{
			$disabled_str = ' disabled';
			$but_class = 'but3D';
		}
		else
		{
			$disabled_str = '';
			$but_class = 'but3';
		}

		$html .= <<<EOT
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>

EOT;

		$html .= <<<EOT
			<input type="submit" id="korfc_cmd1" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Orders_Form.onDelete()" />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		if (!empty($get))
		{
			if (array_key_exists('y', $get))
			{
				if ($get['y'] == 'all')
					$this->year = null;
				else
					$this->year = (int)$get['y'];
			}

			if (array_key_exists('f', $get) && is_array($get['f']))
				$this->filter = $get['f'];
		}

		// todo: dořešit práva
		if (!empty($post))
		{
			$this->all_checked = isset($post['checkall']);
			if (isset($post['check']) && is_array($post['check']))
				foreach ($post['check'] as $value)
				{
					if (!is_numeric($value)) throw new Exception("Nepovolený vstup: check[]");
					$this->checked[$value] = true;
				}

			$act = 0;

			switch ($post['cmd'])
			{
				case 'entfernen':
					$id_list = implode(', ', $post['check']);
					if ($id_list)
					{
						mysql_query("START TRANSACTION");
						mysql_query("DELETE FROM eshopordprops WHERE RID IN (SELECT ID FROM eshopordrows WHERE OID IN ($id_list))");
						mysql_query("DELETE FROM eshopordrows WHERE OID IN ($id_list)");
						mysql_query("DELETE FROM eshoporders WHERE ID IN ($id_list)");
						mysql_query("COMMIT");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$qs = $this->consQS();
						$this->redirection = KIWI_ORDERS . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('eshoporders', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadOrders()
	{
		if ($this->orders == null)
		{
			$this->orders = array();

			$ys = 1; // lišta bude obsahovat minimálně jeden rok (ten aktuální)
			$y = (int)date('Y');
			do
			{
				$this->years[$y] = 0;
				$y--;
				$ys--;
			}
			while ($ys > 0);

			$filters = array();
			foreach ($this->filter as $ftype => $fvalue)
			{
				if ($fvalue === '') continue;
				$efvalue = mysql_real_escape_string($fvalue);
				switch ($ftype)
				{
					case self::FILTER_GENERAL:
						$filters[] = "(FirstName LIKE '%$efvalue%' OR SurName LIKE '%$efvalue%' OR Title LIKE '%$efvalue%' OR Email LIKE '%$efvalue%' OR BusinessName LIKE '%$efvalue%' OR FirmEmail LIKE '%$efvalue%')";
						break;
					case self::FILTER_STATUS:
						$filters[] = "Status='$efvalue'";
						break;
					default:
						throw new Exception('Unknown filter type');
				}
			}

			$filters_sql = implode(' AND ', $filters);
			if ($filters_sql !== '')
				$filters_sql = ' WHERE ' . $filters_sql;

			$sql = "SELECT ID, YID, FirstName, SurName, Title, BusinessName, Delivery, Payment, Year(Created) AS Year, Created, Status, ProductsTotalCost + DeliveryCost + PaymentCost AS TotalCost FROM eshoporders$filters_sql ORDER BY Created DESC";

			if ($result = mysql_query($sql))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->orders[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
					$yr = (int)$row->Year;
					if (!array_key_exists($yr, $this->years))
						$this->years[$yr] = 0;
					$this->years[$yr]++;
				}
			}
		}
	}

	protected function formatOrdererName($order)
	{
		if ($order->BusinessName) return $order->BusinessName;
		$title = $order->Title;
		$fname = $order->FirstName;
		$sname = $order->SurName;
		return $title ? "$title $fname $sname" : "$fname $sname";
	}

	protected function formatPayment($order)
	{
		static $payments = array(
			'VPB' => 'Vorauskasse',
			'PNN' => 'auf Rechnung',
			'BAR' => 'Direkt Abholung',
		);

		if (!isset($payments[$order->Payment]))
			return '';

		return $payments[$order->Payment];
	}

	protected function roundCost($value)
	{
		return round($value * 20) / 20;
	}

	protected function formatCost($cost, $currency = null, $subst_zeros = false)
	{
		$result = number_format($cost, 2, ',', '.');
		if ($subst_zeros) $result = str_replace(',00', ',-', $result);
		if ($currency != null) $result .= " $currency";
		return $result;
	}

	protected function consQS($data = array())
	{
		$qsa = array();

		$filter = array_key_exists('f', $data) ? $data['f'] : $this->filter;
		if ($filter === NULL)
			$filter = array();
		foreach ($filter as $type => $value)
			$qsa[] = 'f[' . $type .']=' . urlencode($value);

		$year = array_key_exists('y', $data) ? $data['y'] : $this->year;
		if ($year === null)
			$qsa[] = 'y=all';
		elseif ($year != date('Y'))
			$qsa[] = "y=$year";

		if (empty($qsa))
			$qs = '';
		else
			$qs = '?' . implode('&', $qsa);

		return $qs;
	}
}
?>