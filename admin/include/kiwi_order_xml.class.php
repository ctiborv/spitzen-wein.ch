<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_Order_XML
{
	protected $id;
	protected $data;
	protected $ordrows;
	protected $lastchange;

	protected static $fields = array
	(
		'FirstName' => array('Vorname', 1),
		'SurName' => array('Nachname', 1),
		'BirthDate' => array('Geburtsdatum', 1),
		'Salutation' => array('Anrede', 1),
		'Title' => array('Titel', 1),
		'Email' => array('E-Mail', 1),
		'Phone' => array('Telefon', 1),
		'MobilePhone' => array('Mobil', 1),
		'Fax' => array('Fax', 1),
		'BusinessName' => array('Firma', 2),
//		'IC' => array('IČ', 2),
//		'DIC' => array('DIČ', 2),
//		'BankAccount' => array('Číslo účtu', 2),
//		'BankCode' => array('Kód banky', 2),
		'FirmEmail' => array('E-Mail', 2),
		'FirmPhone' => array('Telefon', 2),
		'FirmFax' => array('Fax', 2),
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

	public function __construct($id)
	{
		$this->id = $id;
		$this->data = null;
		$this->ordrows = null;
		$this->formdata = array();
		$this->lastchange = null;
	}

	public function __toString()
	{
		$this->loadData();

		$o_id = sprintf("%03d", $this->data->YID) . "–{$this->data->Year}";

		$lastchange = $this->lastchange != null ? $this->lastchange : '';

		$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<xml>
<order>
	<id>$o_id</id>
	<lastchange>$lastchange</lastchange>
	<customer>

EOT;

		$mode = $this->data->BusinessName ? 2 : 1;
		foreach (self::$fields as $key => $value)
		{
			if (is_array($value))
			{
				$fval = htmlspecialchars($this->data->$key);
				if ($value[1] == 0 || $value[1] == $mode)
				{
					$tag = strtolower($key);
					$xml .= <<<EOT
		<$tag>$fval</$tag>

EOT;
				}
			}
		}

		$xml .= <<<EOT
	</customer>

EOT;

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


		$status_array = array
		(
			0 => 'in Bearbeitung',
			1 => 'gesendet',
			2 => 'fertig',
			3 => 'annulliert',
			4 => 'verlaufen',
			5 => 'vom System annulliert',
			6 => 'Nachlieferung'
		);
		$status = $status_array[$this->data->Status];

		$xml .= <<<EOT
	<details>
		<created>$created</created>
		<delivery>$delivery</delivery>
		<payment>$payment</payment>
		<productscost>$productscost_f</productscost>
		<totalcost>$totalcost_f</totalcost>
		<note>$note</note>
		<status>$status</status>
		<package>$packagecode</package>
	</details>
	<items>

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

			$xml .= <<<EOT
		<item>
			<title>{$fval['Title']}</title>
			<code>{$fval['Code']}</code>
			<properties>{$fval['Properties']}</properties>
			<amount>{$fval['Amount']}</amount>
			<cost>$cost_f</cost>
		</item>

EOT;
			$i++;
			$sw = $next_sw[$sw];
		}


		$xml .= <<<EOT
	</items>
</order>
</xml>

EOT;

		return $xml;
	}

	protected function loadData()
	{
		if ($this->data === null && $this->id != 0)
		{
			$sqlfields = array();
			foreach (self::$fields as $key => $value)
				if (is_array($value))
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
		$result = number_format($cost, 2, ',', '.');
		if ($subst_zeros) $result = str_replace(',00', ',-', $result);
		if ($currency != null) $result .= " $currency";
		return $result;
	}
}
?>