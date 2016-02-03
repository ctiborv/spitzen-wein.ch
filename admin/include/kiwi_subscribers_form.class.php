<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_anchor.class.php';

class Kiwi_Subscribers_Form extends Page_Item
{
	protected $all_checked;
	protected $subscribers;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $anchor;
	protected $letter;
	protected $fletters;
	protected $page;
	protected $total;
	protected $sort;
	protected $filter;

	protected static $records_per_page = 50;

	const MAX_TITLE_LEN = 70;

	const SORT_BY_ALPHABET = 'a';

	const ASCENDING_ORDER = 'a';
	const DESCENDING_ORDER = 'd';

	const FILTER_EMAIL = 'e',
		FILTER_GENERAL = 'g',
		FILTER_STATUS = 's';

	const STATUS_NOTSUBSCRIBED = 0,
		STATUS_SUBSCRIBED = 1,
		STATUS_UNSUBSCRIBED = 2,
		STATUS_BLOCKED = -1;

	const KEY_TOTAL = '~TOTAL~';

	public function __construct()
	{
		parent::__construct();
		$this->all_checked = false;
		$this->subscribers = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->anchor = new CurrentKiwiAnchor();
		$this->letter = null;
		$this->fletters = null;
		$this->page = 1;
		$this->total = null;
		$this->sort = array
		(
			'by' => self::SORT_BY_ALPHABET,
			'order' => self::ASCENDING_ORDER
		);
		$this->filter = array();
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadSubscriberCounts();
		$this->loadSubscribers();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = htmlspecialchars($this->consQS());
		$qsep = $qs ? '&amp;' : '?';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>E-SHOP Newsletters subscribers - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange !== null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$tabs_html = '';
		$tqs = htmlspecialchars($this->consQS(array('l' => NULL, 'pg' => NULL)));
		$tqsep = $tqs == '' ? '?' : '&amp;';

		foreach ($this->fletters as $fletter => $flcount)
		{
			if ($fletter === self::KEY_TOTAL) continue;
			$fletterqs = urlencode($fletter);
			if ($this->letter == $fletter)
				$tabs_html .= <<<EOT

			<span title="$flcount">$fletter</span>
EOT;
			elseif ($flcount == 0)
				$tabs_html .= <<<EOT

			<b>$fletter</b>
EOT;
			else
				$tabs_html .= <<<EOT

			<a href="$self${tqs}${tqsep}l=$fletterqs" title="$flcount">$fletter</a>
EOT;
		}

		$pages = (int)($this->total / self::$records_per_page + 1);
		$pages_html = '';
		if ($pages > 1)
		{
			$pqs = $this->consQS(array('pg' => NULL));
			$pqs .= $pqs == '' ? '?' : '&amp;';

			for ($pg = 1; $pg <= $pages; $pg++)
			{
				if ($pg == $this->page)
					$pages_html .= <<<EOT

			<span>$pg</span>
EOT;
				else
					$pages_html .= <<<EOT

			<a href="$self{$pqs}pg=$pg">$pg</a>
EOT;
			}
		}

		$totalcount = $this->fletters[self::KEY_TOTAL];
		if ($this->letter === null)
			$tabs_html .= <<<EOT
			<span title="$totalcount">Alles</span>

EOT;
		else
			$tabs_html .= <<<EOT
			<a href="$self${tqs}" title="$totalcount">Alles</a>

EOT;

		$statuses = array
		(
			self::STATUS_NOTSUBSCRIBED => array('adj' => 'not subscribed', 'verb' => FALSE),
			self::STATUS_UNSUBSCRIBED => array('adj' => 'unsubscribed', 'verb' => 'unsubscribe'),
			self::STATUS_SUBSCRIBED => array('adj' => 'subscribed', 'verb' => 'subscribe'),
			self::STATUS_BLOCKED => array('adj' => 'blocked', 'verb' => 'block'),
		);

		foreach ($statuses as $status_code => $status_desc) {
			if ($status_code === self::STATUS_NOTSUBSCRIBED) continue;

			$status_adj = htmlspecialchars($status_desc['adj']);
			if (isset($this->filter[self::FILTER_STATUS]) && $this->filter[self::FILTER_STATUS] == $status_code) {
				$tabs_html .= <<<EOT
			<span>$status_adj</span>

EOT;
			} else {
				$cqs = $this->consQS(array('f' => array(self::FILTER_STATUS => $status_code)));
				$tabs_html .= <<<EOT
			<a href="$self$cqs">$status_adj</a>

EOT;
			}
		}

		if (!array_key_exists(self::FILTER_STATUS, $this->filter)) {
			$tabs_html .= <<<EOT
			<span>all Statuses</span>

EOT;
		} else {
			$cqs = $this->consQS(array('f' => array(self::FILTER_STATUS => NULL)));
			$tabs_html .= <<<EOT
			<a href="$self$cqs">all Statuses</a>

EOT;
		}


		$html .= <<<EOT
		<div id="zalozky">$tabs_html

EOT;

		$search = htmlspecialchars($this->getSearchBoxText());

		$html .= <<<EOT
			<div class="searchbox">
				<input type="text" id="knlsfc_sbox" name="search" value="$search" class="searchinp" />
				<!--[if IE]><input type="text" style="display: none;" disabled="disabled" size="1" /><![endif]-->
				<input type="submit" id="knlsfc_sbtn" name="cmd" value="suchen" class="searchbtn" />
			</div>
			<br class="clear" />
		</div>

EOT;

		if ($pages_html !== '')
			$html .= <<<EOT
		<div id="stranky">Seite: $pages_html
			<br class="clear" />
		</div>

EOT;

		$disabled_str = count($this->subscribers) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Subscribers_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Name</th>
					<th>E-mail</th>
					<th>Status</th>
					<th>geändert</th>
					<th></th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);

		//mb_internal_encoding('UTF-8');
		foreach ($this->subscribers as $subscriber)
		{
			$checked_str = (isset($this->checked[$subscriber->ID]) && $this->checked[$subscriber->ID]) ? ' checked' : '';

			$email = htmlspecialchars($subscriber->Email);

			if ($subscriber->ClientID) {
				$clientLink = KIWI_EDIT_CLIENT . "?c=$subscriber->ClientID";
				if ($subscriber->BusinessName)
				{
		 			$name = htmlspecialchars($subscriber->BusinessName);
					$clientstatus_icon = 'clientF';
					$clientstatus_text = 'Firma';
				}
				else
				{
					$name = htmlspecialchars(($subscriber->Title ? "$subscriber->Title " : '') . "$subscriber->FirstName $subscriber->SurName");
					$clientstatus_icon = 'clientP';
					$clientstatus_text = 'Privatperson';
				}
				$clientstatus_str = <<<EOT
<a href="$clientLink"><img src="./image/$clientstatus_icon.gif" alt="$clientstatus_text" />&nbsp;$name</a>
EOT;
			} else {
				$clientstatus_str = '';
			}

			$sqs = $this->saveQS();
//			if ($sqs) $plink .= "&sqs=$sqs";

			$anchor_str = ($this->anchor->ID == $subscriber->ID) ? ' name="zmena"' : '';

			$status = isset($statuses[$subscriber->Status]) ? $statuses[$subscriber->Status]['adj'] : $subscriber->Status;

			$dt = parseDateTime($subscriber->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$changestatus_str = '';
			foreach ($statuses as $status_code => $status_desc) {
				if ($status_desc['verb'] === FALSE) continue;
				$status_name_str = htmlspecialchars($status_desc['verb']);
				if ($changestatus_str !== '') $changestatus_str .= ' ';
				if ($subscriber->Status == $status_code) {
					$current_str = " class='current'";
				} else {
					$current_str = '';
				}
				$changestatus_str .= <<<EOT
<a href="$self$qs{$qsep}sid=$subscriber->ID&amp;sc=$status_code"$current_str>$status_name_str</a>
EOT;
			}

			$html .= <<<EOT
				<tr class="t-s-$sw subscriber-status-{$subscriber->Status}">
					<td><input type="checkbox" name="check[]" value="$subscriber->ID" onclick="Kiwi_Subscribers_Form.enableBtns(this.checked)"$checked_str /></td>
					<td>$clientstatus_str</td>
					<td>$email</td>
					<td class="subscriber-status">$status</td>
					<td>$lastchange</td>
					<td>$changestatus_str</td>
				</tr>

EOT;

			$sw = $next_sw[$sw];
		}

		if (count($this->checked) == 0)
		{
			$disabled_str = ' disabled';
			$but_class = 'but3D';
		}
		else
		{
			$disabled_str = '';
			$but_class = 'but3';
		}

//			<input type="submit" id="knlsfc_cmd1" name="cmd" value="Newsletter hinzufügen" class="but4" />

		$html .= <<<EOT
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>
			<input type="submit" id="knlsfc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Subscribers_Form.onDelete()" />
			<input type="submit" id="knlsfc_cmd3" name="cmd" value="subscribe" class="$but_class"$disabled_str />
			<input type="submit" id="knlsfc_cmd4" name="cmd" value="unsubscribe" class="$but_class"$disabled_str />
			<input type="submit" id="knlsfc_cmd5" name="cmd" value="block" class="$but_class"$disabled_str />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		global $kiwi_config;
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
					// if ($ltr != 'CH')
					// 	$ltr = substr($ltr, 0, 1);
					$this->letter = $ltr;
				}
			}

			if (array_key_exists('pg', $get))
			{
				$pg = (int)$get['pg'];
				if ($pg > 0) $this->page = $pg;
			}

			if (array_key_exists('s', $get))
			{
				$sort = $get['s'];
				if (isset($sort[0]))
					switch ($sort[0])
					{
						case self::SORT_BY_ALPHABET:
						case self::SORT_BY_TIME:
							$this->sort['by'] = $sort[0];
							break;
					}

				if (isset($sort[1]))
					switch ($sort[1])
					{
						case self::ASCENDING_ORDER:
						case self::DESCENDING_ORDER:
							$this->sort['order'] = $sort[1];
							break;
					}
			}

			if (array_key_exists('f', $get) && is_array($get['f']))
				$this->filter = $get['f'];

			if (array_key_exists('sid', $get))
			{
				if (!isset($get['sc']))
					throw new Exception("Chybějící parametr: sc");

				$new_status = $get['sc'];

				$this->loadLastChange();
				$this->loadSubscribers();

				if (($sid = (int)$get['sid']) < 1 || !isset($this->index[$sid]))
					throw new Exception("Neplatné ID záznamu: $sid");

				if (!in_array($new_status, array(
					self::STATUS_NOTSUBSCRIBED,
					self::STATUS_SUBSCRIBED,
					self::STATUS_UNSUBSCRIBED,
					self::STATUS_BLOCKED)))
					throw new Exception("Neplatná hodnota parametru sc: $new_status");

				mysql_query("UPDATE nlemails SET Status='$new_status', LastChange=CURRENT_TIMESTAMP WHERE ID=$sid");

				if ($new_status == self::STATUS_SUBSCRIBED)
				{
					$this->generateNewCodes(array($sid));
				}
				else
				{
					$this->clearCodes(array($sid));
				}

				$this->subscribers[$this->index[$sid]]->Status = $new_status;
				$this->subscribers[$this->index[$sid]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $sid;
				$qs = $this->consQS();
				$this->redirection = KIWI_SUBSCRIBERS . $qs . '#zmena';
			}
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

			switch ($post['cmd'])
			{
				case 'suchen':
					$this->filter[self::FILTER_GENERAL] = $post['search'];
					$qs = $this->consQS(array('l' => NULL, 'pg' => NULL));
					$this->redirection = KIWI_SUBSCRIBERS . $qs;
					break;
				case 'subscribe':
				case 'unsubscribe':
				case 'block':
					$new_statuses = array(
						'subscribe' => self::STATUS_SUBSCRIBED,
						'unsubscribe' => self::STATUS_UNSUBSCRIBED,
						'block' => self::STATUS_BLOCKED,
					);
					$new_status = $new_statuses[$post['cmd']];
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						mysql_query("UPDATE nlemails SET Status=$new_status, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
						if ($new_status === self::STATUS_SUBSCRIBED)
						{
							$this->generateNewCodes($id_list);
						}
						else
						{
							$this->clearCodes($id_list);
						}
					}
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$qs = $this->consQS();
					$this->redirection = KIWI_SUBSCRIBERS . $qs;
					break;
				// case 'Subscriber hinzufügen':
				// 	$this->redirection = KIWI_ADD_SUBSCRIBER;
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						mysql_query("DELETE FROM nlemails WHERE ID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$qs = $this->consQS();
						$this->redirection = KIWI_SUBSCRIBERS . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('subscribers', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function getFilterSQL($skip_letter = false)
	{
		global $kiwi_config;
		$filters = array();
		if (!$skip_letter && $this->letter !== null)
		{
			switch ($this->letter)
			{
				// case 'C':
				// 	$filters[] = "(E.Email>='C' AND E.Email<'D') OR (C.SurName>='C' AND C.SurName<'D') OR (C.BusinessName>='C' AND C.BusinessName<'D')";
				// 	break;
				// case 'CH':
				// 	$filters[] = "(E.Email>='CH' AND E.Email<'I') OR (C.SurName>='CH' AND C.SurName<'I') OR (C.BusinessName>='CH' AND C.BusinessName<'I')";
				// 	break;
				default:
					$ltr = mysql_real_escape_string($this->letter);
					$filters[] = "(E.Email LIKE '$ltr%' OR C.SurName LIKE '$ltr%' OR C.BusinessName LIKE '$ltr%')";
					break;
			}
		}

		foreach ($this->filter as $ftype => $fvalue)
		{
			if ($fvalue === '') continue;
			$efvalue = mysql_real_escape_string($fvalue);
			switch ($ftype)
			{
				case self::FILTER_EMAIL:
					$filters[] = "(E.Email LIKE '%$efvalue%')";
					break;
				case self::FILTER_GENERAL:
					$filters[] = "(E.Email LIKE '%$efvalue%' OR C.FirstName LIKE '%$efvalue%' OR C.SurName LIKE '%$efvalue%' OR C.Title LIKE '%$efvalue%' OR C.BusinessName LIKE '%$efvalue%')";
					break;
				case self::FILTER_STATUS:
					$filters[] = "E.Status = $efvalue";
					break;
				default:
					throw new Exception('Unknown filter type');
			}
		}

		$filter_sql = implode(' AND ', $filters);
		return $filter_sql;
	}

	protected function loadSubscriberCounts()
	{
		if ($this->fletters === null)
		{
			$this->fletters = array();

			// add default letters
			for ($c = ord('A'); $c <= ord('Z'); $c++)
				$this->fletters[chr($c)] = 0;

			$filter_sql = $this->getFilterSQL(true);
			if ($filter_sql !== '')
			{
				$fw_sql = " WHERE $filter_sql";
				$fa_sql = " AND $filter_sql";
			}
			else
				$fw_sql = $fa_sql = '';

			// české CH se může řešit přes CASE ... WHEN 'CH' THEN ... ELSE ... END

			$result = mysql_query("SELECT Upper(SubStr(E.Email, 1, 1)) AS FL, Count(*) FROM nlemails AS E LEFT OUTER JOIN eshopclients AS C ON E.ClientID = C.ID$fw_sql GROUP BY FL");
			while ($row = mysql_fetch_row($result))
			{
				$ltr = $this->removeDiaT1($row[0]);
				if (!isset($this->fletters[$ltr]))
					$this->fletters[$ltr] = 0;
				$this->fletters[$ltr] += (int)$row[1];
			}

			$result = mysql_query("SELECT Upper(SubStr(C.SurName, 1, 1)) AS FL, Count(*) FROM nlemails AS E LEFT OUTER JOIN eshopclients AS C ON E.ClientID = C.ID WHERE BINARY Upper(SubStr(C.SurName, 1, 1)) != BINARY Upper(SubStr(E.Email, 1, 1))$fa_sql GROUP BY FL");
			while ($row = mysql_fetch_row($result))
			{
				$ltr = $this->removeDiaT1($row[0]);
				if (!isset($this->fletters[$ltr]))
					$this->fletters[$ltr] = 0;
				$this->fletters[$ltr] += (int)$row[1];
			}

			$result = mysql_query("SELECT Count(*) FROM nlemails AS E LEFT OUTER JOIN eshopclients AS C ON E.ClientID = C.ID$fw_sql");
			$row = mysql_fetch_row($result);
			$this->fletters[self::KEY_TOTAL] = $row[0];
		}
	}

	protected function loadSubscribers()
	{
		if ($this->subscribers === null)
		{
			$this->subscribers = array();
			//mb_internal_encoding("UTF-8");

			$sort_sql_by = array(self::SORT_BY_ALPHABET => 'Email');
			$sort_sql_order = array(self::ASCENDING_ORDER => 'ASC', self::DESCENDING_ORDER => 'DESC');

			$sort_sql = ' ORDER BY ' . $sort_sql_by[$this->sort['by']] . ' ' . $sort_sql_order[$this->sort['order']];

			$filter_sql = $this->getFilterSQL();
			if ($filter_sql !== '')
				$filter_sql = ' WHERE ' . $filter_sql;

			$offset = self::$records_per_page * ($this->page - 1);
			$limit_sql = " LIMIT $offset, " . self::$records_per_page;

			$sql = "SELECT SQL_CALC_FOUND_ROWS E.ID, E.Email, E.ClientID, E.Status, C.Title, C.FirstName, C.SurName, C.BusinessName, E.LastChange FROM nlemails AS E LEFT OUTER JOIN eshopclients AS C ON E.ClientID = C.ID" . $filter_sql . $sort_sql . $limit_sql;

			if ($result = mysql_query($sql))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->subscribers[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
				}

				mysql_free_result($result);

				$result = mysql_query("SELECT FOUND_ROWS()");
				if ($row = mysql_fetch_row($result))
					$this->total = $row[0];
			}
		}
	}

	protected function createNewCode($id)
	{
		$_id = (int) $id;
		for ($attempts = 0, $maxAttempts = 50; $attempts < $maxAttempts; ++$attempts) {
			$code = $this->generateCode();
			$_code = mysql_real_escape_string($code);
			$newCodeSql = "UPDATE nlemails SET Code='$_code' WHERE ID=$_id";
			if (mysql_query($newCodeSql)) return $code;
		}

		throw new Exception('Failed to create unique random code.');
	}

	protected function generateCode()
	{
		return self::randomString(16);
	}

	/**
	 * Generate random string.
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function randomString($length = 10, $charlist = '0-9a-z')
	{
		$charlist = str_shuffle(preg_replace_callback('#.-.#', array('Kiwi_Subscribers_Form', 'rangeHelperCallback'), $charlist));
		$chLen = strlen($charlist);

		$s = '';
		for ($i = 0; $i < $length; $i++) {
			if ($i % 5 === 0) {
				$rand = lcg_value();
				$rand2 = microtime(TRUE);
			}
			$rand *= $chLen;
			$s .= $charlist[($rand + $rand2) % $chLen];
			$rand -= (int) $rand;
		}
		return $s;
	}

	public static function rangeHelperCallback($m)
	{
		return implode('', range($m[0][0], $m[0][2]));
	}

	protected function generateNewCodes($id_list)
	{
		foreach ($id_list as $id)
		{
			$this->createNewCode($id);
		}
	}

	protected function clearCodes($id_list)
	{
		if (empty($id_list)) return;
		$id_list_str = implode(',', $id_list);
		$clearCodesSql = "UPDATE nlemails SET Code=NULL WHERE ID IN ($id_list_str)";
		mysql_query($clearCodesSql);
	}

	protected function removeDiaT1($char)
	{
		static $replacements = array
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

	protected function consQS(array $params = array())
	{
		$qsa = array();

		$sort = array_key_exists('s', $params)
			? $params['s']
			: ($this->sort['by'] . $this->sort['order']);

		if ($sort !== NULL)
			$qsa[] = 's=' . urlencode($sort);

		if (array_key_exists('f', $params)) {

			if (is_array($params['f'])) {
				$filter = array_merge($this->filter, $params['f']);

			} elseif ($params['f'] === NULL) {
				$filter = array();

			} else {
				throw new Exception('Incorrect syntax of $params.');

			}

		} else {
			$filter = $this->filter;

		}

		foreach ($filter as $type => $value) {
			if ($value !== NULL)
				$qsa[] = "f[$type]=" . urlencode($value);
		}

		$letter = array_key_exists('l', $params)
			? $params['l']
			: $this->letter;

		if ($letter !== NULL)
			$qsa[] = 'l=' . urlencode($letter);

		$page = array_key_exists('pg', $params)
			? $params['pg']
			:  $this->page;

		if ($page > 1)
			$qsa[] = "pg=$page";

		return empty($qsa) ? '' : ('?' . implode('&', $qsa));
	}

	protected function saveQS()
	{
		$qs = $this->consQS();
		if ($qs !== '')
			$qs = substr($qs, 1);
		return urlencode($qs);
	}
}
