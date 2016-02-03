<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_Client_Form extends Page_Item
{
	protected $id;
	protected $data;
	protected $lastchange;
	protected $read_only;

	protected static $fields = array
	(
		'Password' => array('Passwort', 0),
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
		'IC' => array('IČ', 2),
		'DIC' => array('DIČ', 2),
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

	function __construct()
	{
		parent::__construct();
		$this->id = 0;
		$this->data = null;
		$this->lastchange = null;
		$this->read_only = true; // docasne
	}

	function _getHTML()
	{
		$this->loadData();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->constructQueryString();

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>Klient - $this->id - [editieren]</h2>
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
			$onchange_str = ' onchange="Kiwi_Client_Form.onChange()" onkeydown="Kiwi_Client_Form.onKeyDown(event)"';
		}

    $html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">

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
							<td><input type="text" id="kclifc_$key" name="$key" value="$fval" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
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

		$html .= <<<EOT
					</table>
				</div>
				<input type="submit" id="kclifc_cmd1" name="cmd" value="speichern" class="but3D" onclick="return Kiwi_Client_Form.onSave()" disabled />
			</fieldset>
		</div>
	</div>
</form>

EOT;

		return $html;
	}

	function handleInput($get, $post)
	{
		// todo: přidat práva
		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (isset($get['c']))
			{
				if (($c = (int)$get['c']) < 1)
					throw new Exception("Neplatná hodnota parametru \"c\": $c");

				$this->id = $c;
			}
			else $this->redirection = KIWI_CLIENTS;
		}

		if (!empty($post))
		{
			$xpost = strip_gpc_slashes($post);
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->handleFormData();
					$this->saveData();
					$this->redirection = KIWI_CLIENTS;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadData()
	{
		if ($this->data == null && $this->id != 0)
		{
			$sqlfields = array();
			foreach (self::$fields as $key => $value)
				if (is_array($value))
					$sqlfields[] = $key;

			$sql = 'SELECT ' . implode(', ', $sqlfields) . " FROM eshopclients WHERE ID=$this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
			}
		}
		else throw new Exception("Neplatný identifikátor klienta");
	}

	protected function saveData()
	{
		return; // prozatim neni implementovano
		if ($this->id) // úprava obsahu
		{
			mysql_query("UPDATE eshopclients SET ... LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
		}
	}

	protected function handleFormData()
	{
		return; // prozatim neni implementovano
	}

	protected function constructQueryString()
	{
		return $this->id ? "?c=$this->id" : '';
	}
}
?>