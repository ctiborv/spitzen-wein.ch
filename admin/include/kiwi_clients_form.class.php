<?php
//TODO: pagination
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';

class Kiwi_Clients_Form extends Page_Item
{
	protected $all_checked;
	protected $clients;
	protected $index;
	protected $checked;
	protected $checked_count;
	protected $lastchange;
	protected $letter;
	protected $fletters;
	protected $filter;

	const FILTER_GENERAL = 'g';

	public function __construct()
	{
		parent::__construct();
		$this->all_checked = false;
		$this->clients = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->letter = null; // 'A';
		$this->fletters = array();
		$this->filter = array();
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadClients();

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self" method="post">
	<h2>Katalog Klienten - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$tabs_html = '';
		$tqs = $this->consQS(true);
		$tqs .= $tqs == '' ? '?' : '&';

		foreach ($this->fletters as $fletter => $flcount)
		{
			$fletterqs = urlencode($fletter);
			if ($this->letter == $fletter)
				$tabs_html .= <<<EOT

			<span>$fletter</span>
EOT;
			elseif ($flcount == 0)
				$tabs_html .= <<<EOT

			<b>$fletter</b>
EOT;
			else
				$tabs_html .= <<<EOT

			<a href="$self${tqs}l=$fletterqs">$fletter</a>
EOT;
		}

		$html .= <<<EOT
		<div id="zalozky">$tabs_html

EOT;

		if ($this->letter === null)
			$html .= <<<EOT
			<span>Alles</span>

EOT;
		else
			$html .= <<<EOT
			<a href="$self${tqs}l=all">Alles</a>

EOT;

		$search = htmlspecialchars($this->getSearchBoxText());

		$html .= <<<EOT
		<div class="searchbox">
			<input type="text" id="kclfc_sbox" name="search" value="$search" class="searchinp" />
			<input type="submit" id="kclfc_sbtn" name="cmd" value="suchen" class="searchbtn" />
		</div>
		<br class="clear" />
		</div>

EOT;

		$disabled_str = sizeof($this->clients) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

    $html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Clients_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Name</th>
					<th>Email</th>
					<th></th>
					<th>geändert</th>
					<th>erstellt</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);

		foreach ($this->clients as $client)
		{
			if ($this->letter !== null)
			{
				$clientname = $client->BusinessName ? $client->BusinessName : $client->SurName;
				$ltr = mb_strtoupper(mb_substr($clientname, 0, 2));
				if ($ltr != 'CH')
					$ltr = $this->removeDiaT1(mb_substr($ltr, 0, 1));
				if ($ltr != $this->letter) continue;
			}

			$checked_str = (isset($this->checked[$client->ID]) && $this->checked[$client->ID]) ? ' checked' : '';

			$clink = KIWI_EDIT_CLIENT . "?c=$client->ID";
			$mlink = KIWI_ESHOPMAIL_FORM . "?c=$client->ID";

			if ($client->BusinessName)
			{
	 			$name = htmlspecialchars($client->BusinessName);
	 			$email = htmlspecialchars($client->FirmEmail);
				$clientstatus_icon = 'clientF';
				$clientstatus_text = 'Firma';
			}
			else
			{
				$name = htmlspecialchars(($client->Title ? "$client->Title " : '') . "$client->FirstName $client->SurName");
				$email = htmlspecialchars($client->Email);
				$clientstatus_icon = 'clientP';
				$clientstatus_text = 'Privatperson';
			}
			$clientstatus_str = <<<EOT
<img src="./image/$clientstatus_icon.gif" alt="" title="$clientstatus_text" />
EOT;

		  $dt = parseDateTime($client->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$dt = parseDateTime($client->Created);
			$created = date('j.n.Y H:i', $dt['stamp']);

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$client->ID" onclick="Kiwi_Clients_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$clink">$name</a></td>
					<td><a href="$mlink">$email</a></td>
					<td>$clientstatus_str</td>
					<td>$lastchange</td>
					<td>$created</td>
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
			<input type="submit" id="kclfc_cmd1" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Clients_Form.onDelete()" />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		// todo: dořešit práva
		if (!empty($get))
		{
			if (array_key_exists('l', $get))
			{
				if ($get['l'] == 'all')
					$this->letter = null;
				else
				{
					$ltr = strtoupper(substr($get['l'], 0, 2));
					if ($ltr != 'CH')
						$ltr = substr($ltr, 0, 1);
					$this->letter = $ltr;
				}
			}

			if (array_key_exists('f', $get) && is_array($get['f']))
				$this->filter = $get['f'];
		}

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
				case 'suchen':
					$this->filter[self::FILTER_GENERAL] = $post['search'];
					$this->letter = null; // nez bude paginace
					$qs = $this->consQS(false, true, false); // az bude paginace, pak bude prvni parametr true
					$this->redirection = KIWI_CLIENTS . $qs;
					break;
				case 'entfernen':
					$id_list = implode(', ', $post['check']);
					if ($id_list)
					{
						mysql_query("DELETE FROM eshopclients WHERE ID IN ($id_list)");
						mysql_query("UPDATE nlemails SET ClientID=NULL WHERE ClientID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$qs = $this->consQS();
						$this->redirection = KIWI_CLIENTS . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('eshopclients', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadClients()
	{
		if ($this->clients == null)
		{
			$this->clients = array();
			mb_internal_encoding("UTF-8");

			for ($c = ord('A'); $c <= ord('H'); $c++)
				$this->fletters[chr($c)] = 0;

			$this->fletters['CH'] = 0;

			for ($c = ord('I'); $c <= ord('Z'); $c++)
				$this->fletters[chr($c)] = 0;

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
					default:
						throw new Exception('Unknown filter type');
				}
			}

			$filters_sql = implode(' AND ', $filters);
			if ($filters_sql !== '')
				$filters_sql = ' WHERE ' . $filters_sql;

			$sql = "SELECT ID, FirstName, SurName, Title, Email, BusinessName, FirmEmail, Created, LastChange FROM eshopclients$filters_sql ORDER BY ID";

			if ($result = mysql_query($sql))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->clients[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
					$clientname = $row->BusinessName ? $row->BusinessName : $row->SurName;
					$ltr = mb_strtoupper(mb_substr($clientname, 0, 2));
					if ($ltr != 'CH')
						$ltr = $this->removeDiaT1(mb_substr($ltr, 0, 1));
					if (!array_key_exists($ltr, $this->fletters))
						$this->fletters[$ltr] = 0;
					$this->fletters[$ltr]++;
				}
			}
		}
	}

	protected function removeDiaT1($char)
	{
		$replacements = array
		(
			'Á' => 'A',
			'Ä' => 'A',
			'Č' => 'C',
			'Ď' => 'D',
			'É' => 'E',
			'Ě' => 'E',
			'Ë' => 'E',
			'Í' => 'I',
			'Ľ' => 'L',
			'Ö' => 'O',
			'Ň' => 'N',
			'Ó' => 'O',
			'Ř' => 'R',
			'Š' => 'S',
			'Ť' => 'T',
			'Ú' => 'U',
			'Ů' => 'U',
			'Ü' => 'U',
			'Ý' => 'Y',
			'Ž' => 'Z'
		);

		if (array_key_exists($char, $replacements))
			return $replacements[$char];
		else
			return $char;
	}

	protected function getSearchBoxText()
	{
		if (array_key_exists(self::FILTER_GENERAL, $this->filter))
			return $this->filter[self::FILTER_GENERAL];
		else
			return '';
	}

	protected function consQS($skipl = false, $skipp = false, $skipf = false)
	{
		$qsa = array();

		if (!$skipf)
			foreach ($this->filter as $type => $value)
				$qsa[] = 'f[' . $type .']=' . urlencode($value);

		if (!$skipl)
		{
			if ($this->letter === null)
				$qsa[] = 'l=all';
			elseif ($this->letter != 'A')
				$qsa[] = 'l=' . urlencode($this->letter);
		}

		if (empty($qsa))
			$qs = '';
		else
			$qs = '?' . implode('&', $qsa);

		return $qs;
	}
}
?>