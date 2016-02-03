<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_Order_Form extends Page_Item
{
	protected $id;
	protected $data;
	protected $ordrows;
	protected $formdata;
	protected $lastchange;
	protected $read_only;

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
		'FirmEmail' => array('Email', 2),
		'FirmPhone' => array('Telefon', 2),
		'FirmFax' => array('Fax', 2),
		'#1' => 'Rechnungsadresse:',
		'FStreet' => array('Strasse', 0),
		'FAddressNumber' => array('Hausnummer', 0),
		'FCity' => array('Ort', 0),
		'FPostalCode' => array('Postleitzahl', 0),
		'FCountry' => array('Land', 0),
		'#2' => 'Lieferadresse:',
		'DName' => array('Name der Firma', 0),
		'DSalutation' => array('Anrede', 0),
		'DStreet' => array('Strasse', 0),
		'DAddressNumber' => array('Hausnummer', 0),
		'DCity' => array('Ort', 0),
		'DPostalCode' => array('Postleitzahl', 0),
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
		$this->read_only = false; // docasne
	}

	public function _getHTML()
	{
		$this->loadData();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->constructQueryString();

		$o_id = sprintf("%03d", $this->data->YID) . "–{$this->data->Year}";

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>Bestellung - $o_id - [Detail]</h2>
	<div class="levyV">
 		<div class="form3">
			<fieldset>

EOT;
		if ($this->lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange}</div>

EOT;
		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$onchange_str = '';
		}
		else
		{
			$readonly_str = '';
			$onchange_str = ' onchange="Kiwi_Order_Form.onChange()" onkeydown="Kiwi_Order_Form.onKeyDown(event)"';
		}

    $html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td colspan=2><span>Besteller</span></td>
						</tr>

EOT;

		$mode = $this->data->BusinessName ? 2 : 1;
		foreach (self::$fields as $key => $value)
		{
			if (is_array($value))
			{
				$fval = htmlspecialchars($this->data->$key);
				if ($value[1] == 0 || $value[1] == $mode)
					$html .= <<<EOT
						<tr>
							<td><span class="span-form2">{$value[0]} :</span></td>
							<td><input type="text" id="kordfc_$key" name="$key" value="$fval" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>

EOT;
			}
			else
				$html .= <<<EOT
						<tr>
							<td colspan=2><span>$value</span></td>
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

		// způsob platby je spojen s doručením
		$payment_specs = array
		(
			'VPB' => 'Vorauskasse per Banküberweisung',
			'PNN' => 'auf Rechnung',
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
							<td colspan=2><span>Bestellung</span></td>
						</tr>
						<tr>
							<td><span class="span-form2">Erstellt am :</span></td>
							<td><input type="text" id="kordfc_Created" name="Created" value="$created" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Lieferung :</span></td>
							<td><input type="text" id="kordfc_Delivery" name="Delivery" value="$delivery" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Zahlungsart :</span></td>
							<td><input type="text" id="kordfc_Payment" name="Payment" value="$payment" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Warenwert gesamt :</span></td>
							<td><input type="text" id="kordfc_ProductsCost" name="ProductsCost" value="$productscost_f" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Gesamtsumme :</span></td>
							<td><input type="text" id="kordfc_ProductsTotalCostD" name="TotalCost" value="$totalcost_f" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Hinweis :</span></td>
							<td><textarea id="kordfc_Note" name="Note" class="texarOUT" onfocus="this.className='texarON'" onblur="this.className='texarOFF'"$onchange_str$readonly_str />$note</textarea></td>
						</tr>
						<tr>
							<td><span class="span-form2">Status :</span></td>
							<td>
								<input type="radio" id="kordfc_Status0" name="Status" value="0"{$astate[0]}$onchange_str$readonly_str />in Bearbeitung
								<input type="radio" id="kordfc_Status1" name="Status" value="1"{$astate[1]}$onchange_str$readonly_str />gesendet
								<input type="radio" id="kordfc_Status6" name="Status" value="6"{$astate[6]}$onchange_str$readonly_str />Nachlieferung
								<input type="radio" id="kordfc_Status2" name="Status" value="2"{$astate[2]}$onchange_str$readonly_str />fertig
								<input type="radio" id="kordfc_Status3" name="Status" value="3"{$astate[3]}$onchange_str$readonly_str />annulliert
								<input type="radio" id="kordfc_Status4" name="Status" value="4"{$astate[4]}$onchange_str$readonly_str />verlaufen
								<input type="radio" id="kordfc_Status5" name="Status" value="5"{$astate[5]}$onchange_str$readonly_str />vom System annulliert
							</td>
						</tr>
						<tr>
							<td><span class="span-form2">Paketnummer :</span></td>
							<td><input type="text" id="kordfc_PackageCode" name="PackageCode" value="$packagecode" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>

EOT;

		if ($this->id)
		{
			$export_script = KIWI_ORDER_XML . "?o=" . $this->id;
			$export_html = <<<EOT

				<input type="button" id="kordfc_cmd2" name="cmd" value="Export nach XML" class="but4" onclick="Kiwi_Order_Form.Export('$export_script')" />
EOT;
			$print_script = KIWI_ORDER_PRINT . "?o=" . $this->id;
			$print_html = <<<EOT

				<input type="button" id="kordfc_cmd3" name="cmd" value="drucken" class="but3" onclick="Kiwi_Order_Form.Print('$print_script')" />
EOT;
			$pdf_script = KIWI_INVOICE_PDF . "?o=" . $this->id;
			$pdf_html = <<<EOT

				<input type="button" id="kordfc_cmd4" name="cmd" value="PDF Rechnung" class="but3" onclick="Kiwi_Order_Form.PDF('$pdf_script')" />
EOT;
		}

		$html .= <<<EOT
					</table>
				</div>
				<input type="submit" id="kordfc_cmd1" name="cmd" value="speichern" class="but3D" onclick="return Kiwi_Order_Form.onSave()" disabled />$export_html$print_html$pdf_html
			</fieldset>
		</div>
	</div>

EOT;

		$html .= <<<EOT
	<h2>[Posten] - $o_id - [Liste]</h2>
	<div class="levyV">
		<div id="frame">
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
		</div>
	</div>
</form>

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

		if (!empty($post))
		{
			$xpost = strip_gpc_slashes($post);
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->loadData();
					$this->handleFormData($xpost);
					$this->saveData();
					if ($this->data->Status != 1 && $this->formdata['Status'] == 1)
						$this->redirection = KIWI_ESHOPMAIL_FORM . "?mt=odeslano&o=" . $this->id;
					else
						$this->redirection = KIWI_ORDERS;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
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

	protected function saveData()
	{
		if ($this->id)
		{
			$assigns = array();
			foreach ($this->formdata as $key => $value)
			{
				if ($key == 'Status') $sqlvalue = (int)$value[0];
				else $sqlvalue = "'" . mysql_real_escape_string($value) . "'";
				$assigns[] = "$key=$sqlvalue";
			}

			if (sizeof($assigns))
			{
				$sassigns = implode(', ', $assigns);
				mysql_query("UPDATE eshoporders SET $sassigns, LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			}
		}
		else throw new Exception('Pokus uložit neidentifikovanou objednávku');
	}

	protected function handleFormData($post)
	{
		$status = $post['Status'];
		if (!is_numeric($status) || $status < 0 || $status > 6) throw new Exception('Nekorektní vstup formuláře - radio button Status: ' . __CLASS__);

		$this->formdata['Status'] = $status;
		$this->formdata['Note'] = $post['Note'];
		$this->formdata['PackageCode'] = $post['PackageCode'];
	}

	protected function constructQueryString()
	{
		return $this->id ? "?o=$this->id" : '';
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