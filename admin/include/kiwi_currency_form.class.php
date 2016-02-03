<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_Currency_Form extends Page_Item
{
	protected $read_only;
	protected $rates;
	protected $lastchange;

	public function __construct(&$rights)
	{
		parent::__construct();
		$mrights = $rights->EShop;
		if (is_array($mrights))
			$this->read_only = !$mrights['Write'];
		else $this->read_only = !$mrights;
		$this->rates = null;
		$this->lastchange = null;
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadRates();

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$onchange_str = '';
			$ro_disabled_str = ' disabled';
			$D_str = 'D';
		}
		else
		{
			$readonly_str = $ro_disabled_str = $D_str = '';
			$onchange_str = ' onchange="Kiwi_Currency_Form.onChange()" onkeydown="return Kiwi_Currency_Form.onKeyDown(event)"';
		}

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self" method="post">
	<h2>KATALOG - [Währungskurse]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
		{
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;
		}

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-meny" cellspacing="0" cellpadding="0">

EOT;

		$i = 1;
		$ccodes = array();
		foreach ($this->rates as $rate)
		{
			$htmlrate = htmlspecialchars($rate->CCode);
			$htmlvalue = htmlspecialchars($rate->Rate);
			$ccodes[] = $htmlrate;
			$html .= <<<EOT
				<tr>
					<td>$htmlrate</td>
					<td><input type="text" id="kcrfc_cc$i" name="cc$i" value="$htmlvalue" class="inpOUT5" onfocus="this.className='inpON5'" onblur="this.className='inpOUT5'"$onchange_str$readonly_str /></td>
				</tr>

EOT;
			$i++;
		}

		$ccodes_str = implode('#', $ccodes);

		$html .= <<<EOT
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>
			<input type="hidden" name="kiwi_currency_form" value="1" />
			<input type="hidden" name="ccodes" value="$ccodes_str" />
			<input type="submit" id="kcrfc_cmd1" name="cmd" value="speichern" class="but3D" disabled onclick="return Kiwi_Currency_Form.onSave()"/>
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		if (!$this->read_only && array_key_exists('kiwi_currency_form', $post))
		{
			switch ($post['cmd'])
			{
				case 'speichern':
					$ccodes = explode('#', $post['ccodes']);
					$i = 1;
					foreach ($ccodes as $ccode)
					{
						if (array_key_exists("cc$i", $post))
						{
							$rate = (double)str_replace(',', '.', $post["cc$i"]);
							$query = "UPDATE currency SET Rate='$rate', LastChange=CURRENT_TIMESTAMP WHERE CCode='$ccode'";
							mysql_query($query);
						}
						$i++;
					}
					$this->loadLastChange();
					$this->lastchange->register();
					$this->redirection = KIWI_CURRENCY_RATES;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('currency_rates', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadRates()
	{
		if ($this->rates == null)
		{
			$this->rates = array();
			$query = "SELECT CCode, Rate, LastChange FROM currency ORDER BY CCode ASC";
			$result = mysql_query($query);
			while ($row = mysql_fetch_assoc($result))
				$this->rates[] = new Kiwi_DataRow($row);
		}
	}
}
?>