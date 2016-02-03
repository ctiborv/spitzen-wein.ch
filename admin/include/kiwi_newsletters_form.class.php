<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_anchor.class.php';

class Kiwi_Newsletters_Form extends Page_Item
{
	protected $all_checked;
	protected $newsletters;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $anchor;
	protected $page;
	protected $total;
	protected $sort;
	protected $filter;

	protected static $records_per_page = 50;

	const MAX_TITLE_LEN = 70;

	const SORT_BY_ALPHABET = 'a';
	const SORT_BY_TIME = 't';

	const ASCENDING_ORDER = 'a';
	const DESCENDING_ORDER = 'd';

	const FILTER_TITLE = 't';

	const STATUS_WAITING = 0,
		STATUS_STARTED = 1,
		STATUS_DONE = 2,
		STATUS_CANCELLED = 3;

	public function __construct()
	{
		parent::__construct();
		$this->all_checked = false;
		$this->newsletters = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->anchor = new CurrentKiwiAnchor();
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
		$this->loadNewsletters();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->consQS();
		$qsep = $qs ? '&' : '?';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>E-SHOP Newsletters - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange !== null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$tabs_html = '';
		$tqs = $this->consQS(true);
		$tqsep = $tqs == '' ? '?' : '&';

		$pages = (int)($this->total / self::$records_per_page + 1);
		$pages_html = '';
		if ($pages > 1)
		{
			$pqs = $this->consQS(true);
			$pqs .= $pqs == '' ? '?' : '&';

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

		$html .= <<<EOT
		<div id="zalozky">$tabs_html

EOT;

		$search = htmlspecialchars($this->getSearchBoxText());

		$html .= <<<EOT
		<div class="searchbox">
			<input type="text" id="knlfc_sbox" name="search" value="$search" class="searchinp" />
			<!--[if IE]><input type="text" style="display: none;" disabled="disabled" size="1" /><![endif]-->
			<input type="submit" id="knlfc_sbtn" name="cmd" value="suchen" class="searchbtn" />
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

		$disabled_str = count($this->newsletters) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Newsletters_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Title</th>
					<th>Start</th>
					<th>Status</th>
					<th>Sent</th>
					<th>Failures</th>
					<th>geändert</th>
					<th>aktiv</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);

		$statuses = array
		(
			self::STATUS_WAITING => 'waiting',
			self::STATUS_STARTED => 'started',
			self::STATUS_DONE => 'done',
			self::STATUS_CANCELLED => 'cancelled',
		);

		mb_internal_encoding('UTF-8');
		foreach ($this->newsletters as $newsletter)
		{
			$checked_str = (isset($this->checked[$newsletter->ID]) && $this->checked[$newsletter->ID]) ? ' checked' : '';

			if (mb_strlen($newsletter->Title) > self::MAX_TITLE_LEN)
			{
				$title = htmlspecialchars(mb_substr($newsletter->Title, 0, self::MAX_TITLE_LEN) . '...');
			}
			else
				$title = htmlspecialchars($newsletter->Title);

			$plink = KIWI_EDIT_NEWSLETTER . "?nl=$newsletter->ID";

			$sqs = $this->saveQS();
			if ($sqs) $plink .= "&sqs=$sqs";

			$anchor_str = ($this->anchor->ID == $newsletter->ID) ? ' name="zmena"' : '';

			// $startTime = new DateTime($newsletter->Start);
			// $start = $startTime->format('j.n.Y H:i');
			$dt = parseDateTime($newsletter->Start);
			$start = date('j.n.Y H:i', $dt['stamp']);
			$status = isset($statuses[$newsletter->Status]) ? $statuses[$newsletter->Status] : $statuses[$newsletter->Status];
			$sent = $newsletter->Sent;
			$failures = $newsletter->Failures;

			$dt = parseDateTime($newsletter->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$active = $newsletter->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$newsletter->ID" onclick="Kiwi_Newsletters_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$plink"$anchor_str>$title</a></td>
					<td>$start</td>
					<td>$status</td>
					<td>$sent</td>
					<td>$failures</td>
					<td>$lastchange</td>
					<td><a href="$self$qs{$qsep}as=$newsletter->ID">$active</a></td>
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

		$html .= <<<EOT
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>
			<input type="submit" id="knlfc_cmd1" name="cmd" value="Newsletter hinzufügen" class="but4" />
			<input type="submit" id="knlfc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Newsletters_Form.onDelete()" />
			<input type="submit" id="knlfc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="knlfc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
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

			if (array_key_exists('as', $get))
			{
				$this->loadLastChange();
				$this->loadNewsletters();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->newsletters[$this->index[$as]]->Active;

				mysql_query("UPDATE newsletters SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

				$this->newsletters[$this->index[$as]]->Active = $nas;
				$this->newsletters[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $as;
				$qs = $this->consQS();
				$this->redirection = KIWI_NEWSLETTERS . $qs . '#zmena';
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

			$act = 0;

			switch ($post['cmd'])
			{
				case 'suchen':
					$this->filter[self::FILTER_TITLE] = $post['search'];
					$qs = $this->consQS(true, false);
					$this->redirection = KIWI_NEWSLETTERS . $qs;
					break;
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$id_list = implode(',', $post['check']);
					if ($id_list)
						mysql_query("UPDATE newsletters SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$qs = $this->consQS();
					$this->redirection = KIWI_NEWSLETTERS . $qs;
					break;
				case 'Newsletter hinzufügen':
					$this->redirection = KIWI_ADD_NEWSLETTER;
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						mysql_query("DELETE FROM newsletters WHERE ID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$qs = $this->consQS();
						$this->redirection = KIWI_NEWSLETTERS . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('newsletters', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadNewsletters()
	{
		if ($this->newsletters === null)
		{
			$this->newsletters = array();
			mb_internal_encoding("UTF-8");

			$sort_sql_by = array(self::SORT_BY_ALPHABET => 'Title', self::SORT_BY_TIME => 'Start');
			$sort_sql_order = array(self::ASCENDING_ORDER => 'ASC', self::DESCENDING_ORDER => 'DESC');

			$sort_sql = ' ORDER BY ' . $sort_sql_by[$this->sort['by']] . ' ' . $sort_sql_order[$this->sort['order']];

			$filters = array();
			foreach ($this->filter as $ftype => $fvalue)
			{
				if ($fvalue === '') continue;
				$efvalue = mysql_real_escape_string($fvalue);
				switch ($ftype)
				{
					case self::FILTER_TITLE:
						$filters[] = "(Title LIKE '%$efvalue%')";
						break;
					default:
						throw new Exception('Unknown filter type');
				}
			}

			$filters_sql = implode(' AND ', $filters);
			if ($filters_sql !== '')
				$filters_sql = ' WHERE ' . $filters_sql;

			$offset = self::$records_per_page * ($this->page - 1);
			$limit_sql = " LIMIT $offset, " . self::$records_per_page;

			$sql = "SELECT SQL_CALC_FOUND_ROWS ID, Title, Start, Status, Sent, Failures, Active, LastChange FROM newsletters" . $filters_sql . $sort_sql . $limit_sql;

			if ($result = mysql_query($sql))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->newsletters[$i] = new Kiwi_DataRow($row);
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


	protected function getSearchBoxText()
	{
		if (array_key_exists(self::FILTER_TITLE, $this->filter))
			return $this->filter[self::FILTER_TITLE];
		else
			return '';
	}

	protected function consQS($skipp = false, $skipf = false)
	{
		$qsa = array();

		$qsa[] = 's=' . $this->sort['by'] . $this->sort['order'];

		if (!$skipf)
			foreach ($this->filter as $type => $value)
				$qsa[] = 'f[' . $type .']=' . urlencode($value);

		if (!$skipp && $this->page > 1)
			$qsa[] = "pg=$this->page";

		if (empty($qsa))
			$qs = '';
		else
			$qs = '?' . implode('&', $qsa);

		return $qs;
	}

	protected function saveQS()
	{
		$qs = $this->consQS();
		if ($qs !== '')
			$qs = substr($qs, 1);
		return urlencode($qs);
	}
}
?>