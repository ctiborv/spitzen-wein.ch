<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_Order_Print extends Page_Item
{
	protected $id;
	protected $data;
	protected $ordrows;
	protected $formdata;
	protected $lastchange;

	// -1 ... jen pro nacteni z db
	// 1 = bit osoba, 2 = bit spolecnost, 4 = bit zobrazit vzdy, 8 = bit pro vynechat db

	protected static $fields = array
	(
		'FirstName' => array('', -1),
		'SurName' => array('', -1),
		'Title' => array('Titel', 5),
		'Name' => array('Vorname und Nachname / Firma', 9),
		'BusinessName' => array('Vorname und Nachname / Firma', 2),
		'BirthDate' => array('Geburtsdatum', 1),
		'Salutation' => array('Anrede', 1),
//		'IC' => array('IČ', 6),
//		'DIC' => array('DIČ', 6),
		'Email' => array('E-Mail', 1),
		'FirmEmail' => array('E-Mail', 2),
		'Phone' => array('Telefon', 1),
		'FirmPhone' => array('Telefon', 2),
		'MobilePhone' => array('Mobil', 5),
		'Fax' => array('Fax', 1),
		'FirmFax' => array('Fax', 2),
//		'BankAccount' => array('Číslo účtu', 2),
//		'BankCode' => array('Kód banky', 2),
		'#1' => 'Rechnungsadresse:',
		'FStreet' => array('Strasse', 0),
		'FAddressNumber' => array('Hausnummer', 0),
		'FCity' => array('Ort', 0),
		'FPostalCode' => array('PLZ', 0),
		'FCountry' => array('Land', 0),
		'#2' => 'Lieferadresse:',
		'DName' => array('Name der Firma', 0),
		'DSalutation' => array('Anrede', 0),
		'DStreet' => array('Strasse', 0),
		'DAddressNumber' => array('Hausnummer', 0),
		'DCity' => array('Ort', 0),
		'DPostalCode' => array('PLZ', 0),
		'DCountry' => array('Land', 0)
	);

	public function __construct()
	{
		parent::__construct();
		$this->id = 0;
		$this->data = null;
		$this->ordrows = null;
		$this->formdata = array();
		$this->lastchange = null;
	}

	public function _getHTML()
	{
		$this->loadData();

		$o_id = sprintf("%03d", $this->data->YID) . "–{$this->data->Year}";

		$html = <<<EOT
<table class="tab-form" cellspacing="1" cellpadding="1">
	<tr>
		<td>Bestellung :</td>
		<td>$o_id</td>
	</tr>
	<tr>
		<td colspan="2">Besteller</td>
	</tr>
	
EOT;

		$mode = $this->data->BusinessName ? 2 : 1;
		foreach (self::$fields as $key => $value)
		{
			if (is_array($value))
			{
				if ($value[1] < 0) continue;
				elseif ($value[1] == 0 || ($value[1] & 3) == $mode)
				{
					$fval = htmlspecialchars($this->getDataValue($key));
					$html .= <<<EOT
	<tr>
		<td>{$value[0]} :</td>
		<td>$fval</td>
	</tr>

EOT;
				}
				elseif ($value[1] & 4)
				{
					$html .= <<<EOT
	<tr>
		<td>{$value[0]} :</td>
		<td></td>
	</tr>

EOT;
				}
			}
			else
				$html .= <<<EOT
	<tr>
		<td colspan="2">$value</td>
	</tr>

EOT;
		}

		$dt = parseDateTime($this->data->Created);
		$created = date('j.n.Y H:i', $dt['stamp']);

		$delivery_specs = array
		(
			'CHP' => 'per Post',
			'DPD' => 'DPD',
			'PSA' => 'Persönliche Sammlung'
		);

		$delivery = array_key_exists($this->data->Delivery, $delivery_specs) ? $delivery_specs[$this->data->Delivery] : $this->data->Delivery;
		if ($dcost = $this->data->DeliveryCost) $delivery .= " ({$this->formatCost($dcost, 'CHF')})";

		// způsob platby je u zavazadel spojen s doručením
		$payment_specs = array
		(
			'VPB' => 'Vorauskasse per Banküberweisung',
			'PNN' => 'per Nachnahme',
			'BAR' => 'Barzahlung'
		);

		$payment = array_key_exists($this->data->Payment, $payment_specs) ? $payment_specs[$this->data->Payment] : $this->data->Payment;
		if ($pcost = $this->data->PaymentCost) $payment .= " ({$this->formatCost($pcost, 'CHF')})";

		$productscost = $this->data->ProductsTotalCost;
		$totalcost = $productscost + $dcost + $pcost;

		$productscost_f = $this->formatCost($productscost, 'CHF');
		$totalcost_f = $this->formatCost($totalcost, 'CHF');

		$note = str_replace("\r\n", "\r", htmlspecialchars($this->data->Note));
		$packagecode = htmlspecialchars($this->data->PackageCode);

		$astate = array_fill(0, 7, '');
		$astate[$this->data->Status] = ' checked';

		$html .= <<<EOT
	<tr>
		<td colspan="2">Bestellung</td>
	</tr>
	<tr>
		<td>erstellt :</td>
		<td>$created</td>
	</tr>
	<tr>
		<td>Lieferung :</td>
		<td>$delivery</td>
	</tr>
	<tr>
		<td>Zahlungsart :</td>
		<td>$payment</td>
	</tr>
	<tr>
		<td>Warenwert gesamt :</td>
		<td>$productscost_f</td>
	</tr>
	<tr>
		<td>Gesamtsumme :</td>
		<td>$totalcost_f</td>
	</tr>
	<tr>
		<td>Hinweis :</td>
		<td>$note</td>
	</tr>
</table>
<table class="tab-seznam" cellspacing="0" cellpadding="0">
	<tr>
		<th></th>
		<th>Produkt</th>
		<th>Code</th>
		<th>Spezifikation</th>
		<th>Anzahl</th>
		<th>Preis</th>
	</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);
		$flds = array('Title', 'Code', 'Amount', 'Cost');

		$i = 1;
		foreach ($this->ordrows as $ordrow)
		{
			$fval = array();
			foreach ($flds as $fld)
				$fval[$fld] = htmlspecialchars($ordrow['rowdata']->$fld);

			$props_a = array();
			foreach ($ordrow['properties'] as $property)
				$props_a[] = htmlspecialchars($property['name']) . ': ' . htmlspecialchars($property['value']);

			$fval['Properties'] = implode(', ', $props_a);

			$cost_f = $this->formatCost($fval['Cost'], 'CHF');

			$html .= <<<EOT
	<tr class="t-s-$sw">
		<td>&nbsp;$i.</td>
		<td>{$fval['Title']}</td>
		<td>{$fval['Code']}</td>
		<td>{$fval['Properties']}</td>
		<td>{$fval['Amount']}</td>
		<td>$cost_f</td>
	</tr>

EOT;
			$i++;
			$sw = $next_sw[$sw];
		}


		$html .= <<<EOT
</table>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		// todo: přidat práva
		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (isset($get['o']))
			{
				if (($o = (int)$get['o']) < 1)
					throw new Exception("Neplatná hodnota parametru \"o\": $o");

				$this->id = $o;
			}
			else $this->redirection = KIWI_ORDERS;
		}
	}

	protected function loadData()
	{
		if ($this->data === null && $this->id != 0)
		{
			$sqlfields = array();
			foreach (self::$fields as $key => $value)
				if (is_array($value) && ($value[1] < 0 || ($value[1] & 8) == 0))
					$sqlfields[] = $key;

			$sql = 'SELECT YID, Year(Created) AS Year, ProductsTotalCost, Delivery, DeliveryCost, Payment, PaymentCost, Note, Status, PackageCode, Created, LastChange, ' . implode(', ', $sqlfields) . " FROM eshoporders WHERE ID=$this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
			  $dt = parseDateTime($this->data->LastChange);
				$this->lastchange = date('j.n.Y H:i', $dt['stamp']);
				$this->loadOrderRows();
			}
			else throw new Exception('Neplatný identifikátor objednávky');
		}
	}

	protected function loadOrderRows()
	{
		$this->ordrows = array();
		$result = mysql_query("SELECT ID, Title, Code, Amount, Cost FROM eshopordrows WHERE OID=$this->id");

		while ($row = mysql_fetch_array($result))
		{
			$rid = (int)$row['ID'];
			$this->ordrows[$rid] = array
			(
				'rowdata' => new Kiwi_DataRow($row),
				'properties' => array()
			);
			$result2 = mysql_query("SELECT Property, Value FROM eshopordprops WHERE RID=$rid");
			while ($row2 = mysql_fetch_array($result2))
				$this->ordrows[$rid]['properties'][] = array
				(
					'name' => $row2['Property'],
					'value' => $row2['Value']
				);
			mysql_free_result($result2);
		}
	}

	protected function formatCost($cost, $currency = null, $subst_zeros = false)
	{
		$result = number_format($cost, 2, ',', '');
		if ($subst_zeros) $result = str_replace(',00', ',-', $result);
		if ($currency != null) $result .= " $currency";
		return $result;
	}

	protected function getDataValue($key)
	{
		if ($key == 'Name')
		{
			$fname = $this->data->FirstName;
			$sname = $this->data->SurName;
			$name_a = array();
			if ($fname !== '')
				$name_a[] = $fname;
			if ($sname !== '')
				$name_a[] = $sname;
			$name = implode(' ', $name_a);
			return $name;
		}
		else
			return $this->data->$key;
	}
}
?>