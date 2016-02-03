<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_Invoice extends Page_Item
{
	protected $id;
	protected $data;
	protected $ordrows;

	public function __construct()
	{
		parent::__construct();
		$this->id = 0;
		$this->data = null;
		$this->ordrows = null;
	}

	public function _getHTML()
	{
		$this->loadData();

		$o_id = sprintf("%03d", $this->data->YID) . "–{$this->data->Year}";
		$dt = parseDateTime($this->data->Created);
		$created = date('j.n.Y', $dt['stamp']);

		$html = <<<EOT
<table class="faktura" cellspacing="1" cellpadding="1">
	<tr class="adresa1">
		<td class="adresa1L"></td>
		<td class="adresa1P">
			<span>Bestellung Nr.: </span>$o_id<br />
			<span>Bestelldatum: </span>$created
		</td>
	</tr>
	<tr class="adresa2">
		<td class="adresa2L">
			<h3 class="adresa2Lh3">Lieferadresse:</h3>{$this->renderDeliveryAddress()}
		</td>
		<td class="adresa2P">
			<h3 class="adresa2Lh3">Rechnungsadresse:</h3>{$this->renderInvoiceAddress()}
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td class="objinfo">
			<strong>Zürich, $created</strong>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="objinfo1">
			<strong>Rechnung Nr. $o_id</strong>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table>
               	<tr class="polozkynadpis">
					<td class="nfks">Stk.</td>
					<td class="nfnm">Artikelbezeichnung</td>
					<td class="nfcn">Preis pro Stk.</td>
					<td class="nfck">Gesamt CHF</td>
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

			$fval['CostTotal'] = htmlspecialchars($ordrow['rowdata']->Cost * $ordrow['rowdata']->Amount);

			$props_a = array();
			foreach ($ordrow['properties'] as $property)
				$props_a[] = htmlspecialchars($property['name']) . ': ' . htmlspecialchars($property['value']);

			$fval['Properties'] = empty($props_a) ? '' : (', ' . implode(', ', $props_a));

			$cost1_f = $this->formatCost($fval['Cost'], 'CHF');
			$costT_f = $this->formatCost($fval['CostTotal'], 'CHF');

			$html .= <<<EOT
				<tr class="polozky">
					<td class="fks">{$fval['Amount']}</td>
					<td class="fnm">
						<strong>{$fval['Title']}</strong><br />
						Art.Nr. {$fval['Code']}{$fval['Properties']}
					</td>
					<td class="fcn">$cost1_f</td>
					<td class="fck"><strong>$costT_f</strong></td>
				</tr>

EOT;
			$i++;
			$sw = $next_sw[$sw];
		}


		$html .= <<<EOT
			</table>
		</td>
	</tr>

EOT;

		$delivery_specs = array
		(
			'CHP' => 'per Post',
			'DPD' => 'DPD',
			'PSA' => 'Persönliche Sammlung'
		);

		$delivery = array_key_exists($this->data->Delivery, $delivery_specs) ? $delivery_specs[$this->data->Delivery] : $this->data->Delivery;
		//if ($dcost = $this->data->DeliveryCost) $delivery .= " ({$this->formatCost($dcost, 'CHF')})";
		$dcost = $this->data->DeliveryCost;

		// způsob platby je spojen s doručením
		$payment_specs = array
		(
			'VPB' => 'Vorauskasse per Banküberweisung',
			'PNN' => 'auf Rechnung',
			'BAR' => 'Barzahlung'
		);

		$payment = array_key_exists($this->data->Payment, $payment_specs) ? $payment_specs[$this->data->Payment] : $this->data->Payment;
		//if ($pcost = $this->data->PaymentCost) $payment .= " ({$this->formatCost($pcost, 'CHF')})";
		$pcost = $this->data->PaymentCost;

		$productscost = $this->data->ProductsTotalCost;

		$products_and_delivery_cost = $productscost + $dcost;
		$products_and_delivery_cost_f = $this->formatCost($products_and_delivery_cost);

		if ($this->data->Payment === 'VPB' || $this->data->Payment === 'BAR')
		{
			$paymentcost_str = <<<EOT
$payment Skonto 3% von $products_and_delivery_cost_f CHF
EOT;
		}
		else
		{
			$paymentcost_str = $payment;
		}

		$totalcost = $this->roundCost($productscost + $dcost + $pcost);

		$productscost_f = $this->formatCost($productscost, 'CHF');
		$totalcost_f = $this->formatCost($totalcost, 'CHF');

		//$note = str_replace("\r\n", "\r", htmlspecialchars($this->data->Note));
		//$packagecode = htmlspecialchars($this->data->PackageCode);

		//$astate = array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '');
		//$astate[$this->data->Status] = ' checked';

		$html .= <<<EOT
	<tr class="infodoprava">
		<td colspan="2">
			<strong>Lieferung: </strong>$delivery
		</td>
	</tr>
	<tr class="infoplatba">
		<td colspan="2">
			<strong>Zahlungsart: </strong>$payment
		</td>
	</tr>
</table>
<table class="faktura">
	<tr>
		<td class="hodnota">Warenwert: </td>
		<td class="hodnota-cena">$productscost_f</td>
	</tr>

EOT;

		if ($dcost)
		{
			$deliverycost_f = $this->formatCost($dcost);
			$html .= <<<EOT
	<tr>
		<td class="doprava">Versandkosten: </td>
		<td class="doprava-cena">$deliverycost_f CHF</td>
	</tr>

EOT;
		}

		$MwSt = $this->formatCost($products_and_delivery_cost * 0.08);
		$html .= <<<EOT
		<!--<tr>
			<td class="dph">MwSt 8% von $products_and_delivery_cost_f CHF: </td>
			<td colspan="2" class="dph-cena">$MwSt CHF</td>
		</tr>-->

EOT;
		if ($pcost)
		{
			$paymentcost_f = $this->formatCost($pcost);
			$html .= <<<EOT
	<tr>
		<td class="sleva">$paymentcost_str:</td>
		<td class="sleva-cena">$paymentcost_f CHF</td>
	</tr>

EOT;
		}

		$html .= <<<EOT
	<tr>
		<td class="celkem">Gesamtsumme (CHF):</td>
		<td class="celkem-cena">$totalcost_f</td>
	</tr>
	<tr>
		<td colspan="2" class="celkem">Inkl. MwSt. 8% und Versand- Servicekosten</td>
	</tr>
</table>
<table class="faktura">
	<tr class="platba-info">
		<td><strong>Bitte zahlen Sie innert  10 Tagen auf unser Konto:</strong> 40-630173-3</td>
	</tr>
	<tr class="platba-info">
		<td><strong>Besten Dank für Ihren geschätzten Auftrag. Wir verbleiben mit freundlichen Grüssen und höflicher Empfehlung</strong></td>
	</tr>
</table>
EOT;
		return $html;
	}

	protected function renderInvoiceAddress()
	{
		$business_name = $this->data->BusinessName;
		if ($business_name === '')
		{
			$title = $this->data->Title;
			$firstname = $this->data->FirstName;
			$surname = $this->data->SurName;

			$name_items = array();
			if ($title !== '') $name_items[] = $title;
			if ($firstname !== '') $name_items[] = $firstname;
			if ($surname !== '') $name_items[] = $surname;

			$name_html = implode('&nbsp;', array_map('htmlspecialchars', $name_items));
		}
		else
		{
			$name_html = str_replace(' ', '&nbsp;', htmlspecialchars($business_name));
		}

		$street = $this->data->FStreet;
		$addressnumber = $this->data->FAddressNumber;
		$address_html = htmlspecialchars($street) . "&nbsp;" . htmlspecialchars($addressnumber);

		$postalcode = $this->data->FPostalCode;
		$city = $this->data->FCity;
		if ($postalcode !== '')
			$city_html = htmlspecialchars($postalcode) . "&nbsp;" . htmlspecialchars($city);
		else
			$city_html = htmlspecialchars($city);

		$country = $this->data->FCountry;
		if ($country !== '')
			$country_html = htmlspecialchars($country);
		else
			$country_html = '';

		$html = <<<EOT

			$name_html<br />
			$address_html<br />
			$city_html<br />
EOT;
		if ($country_html !== '')
			$html .= <<<EOT

			$country_html<br />
EOT;

		return $html;
	}

	protected function renderDeliveryAddress()
	{
		$name = $this->data->DName;
		if ($name === '') return $this->renderInvoiceAddress();
		$name_html = htmlspecialchars($name);

		$street = $this->data->DStreet;
		$addressnumber = $this->data->DAddressNumber;
		$address_html = htmlspecialchars($street) . "&nbsp;" . htmlspecialchars($addressnumber);

		$postalcode = $this->data->DPostalCode;
		$city = $this->data->DCity;
		if ($postalcode !== '')
			$city_html = htmlspecialchars($postalcode) . "&nbsp;" . htmlspecialchars($city);
		else
			$city_html = htmlspecialchars($city);

		$country = $this->data->DCountry;
		if ($country !== '')
			$country_html = htmlspecialchars($country);
		else
			$country_html = '';

		$html = <<<EOT

			$name_html<br />
			$address_html<br />
			$city_html<br />
EOT;
		if ($country_html !== '')
			$html .= <<<EOT

			$country_html<br />
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

		if (!$this->id)
			$this->redirection = KIWI_ORDERS;
	}

	protected function loadData()
	{
		if ($this->data === null && $this->id != 0)
		{
			$sql = "SELECT YID, Year(Created) AS Year, FirstName, SurName, Title, BusinessName, FStreet, FAddressNumber, FCity, FPostalCode, FCountry, DName, DStreet, DAddressNumber, DCity, DPostalCode, DCountry, ProductsTotalCost, Delivery, DeliveryCost, Payment, PaymentCost, Note, Status, PackageCode, Created, LastChange FROM eshoporders WHERE ID=$this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
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
					'value' => $row2['Value'],
					//'tocost' => $row2['ToCost']
				);
			mysql_free_result($result2);
		}
	}

	protected function roundCost($value)
	{
		return $value;
		//return round($value * 20) / 20;
	}

	protected function formatCost($cost, $currency = null, $subst_zeros = false)
	{
		$result = number_format($cost, 2, ',', '');
		if ($subst_zeros) $result = str_replace(',00', ',-', $result);
		if ($currency != null) $result .= " $currency";
		return $result;
	}
}
?>
