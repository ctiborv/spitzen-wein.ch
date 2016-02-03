<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';

class Kiwi_News_Form extends Page_Item
{
	protected $read_only;
	protected $all_checked;
	protected $ngid;
	protected $ngtitle;
	protected $news;
	protected $index;
	protected $checked;
	protected $checked_count;
	protected $lastchange;

	public function __construct(&$rights)
	{
		parent::__construct();

		$nrights = $rights->WWW;
		if (is_array($nrights))
			$this->read_only = !$nrights['Write'] && !$nrights['WriteNews'];
		else $this->read_only = !$nrights;
		$this->all_checked = false;
		$this->ngid = 1;
		$this->ngtitle = '';
		$this->news = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadNews();

		$self = basename($_SERVER['PHP_SELF']);


		if ($this->ngtitle == '')
			$htitle = '';
		else
			$htitle = ' - ' . $this->ngtitle;


		$html = <<<EOT
<form action="$self?ng=$this->ngid" method="post">
	<h2>WWW News / Artikel $htitle - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$disabled_str = (sizeof($this->news) == 0 || $this->read_only) ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

    $html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_News_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Bezeichnung</th>
					<th>geändert</th>
					<th>aktiv</th>
					<th>Datum</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);
		$i = 0;

		foreach ($this->news as $newsitem)
		{
			$i++;
			$checked_str = (isset($this->checked[$newsitem->ID]) && $this->checked[$newsitem->ID]) ? ' checked' : '';
			$disabled_str = $this->read_only ? ' disabled' : '';

 			$name = htmlspecialchars($newsitem->Name);

			$nlink = KIWI_EDIT_NEWSITEM . "?ng=$this->ngid&ni=$newsitem->ID";

			$dt = parseDateTime($newsitem->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$when = $newsitem->WhenF;

			$active = $newsitem->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$newsitem->ID" onclick="Kiwi_News_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><a href="$nlink">$name</a></td>
					<td>$lastchange</td>

EOT;

			if (!$this->read_only)
				$html .= <<<EOT
					<td><a href="$self?ng=$this->ngid&as=$newsitem->ID">$active</a></td>

EOT;
			else
				$html .= <<<EOT
					<td>$active</td>

EOT;

			$html .= <<<EOT
					<td>$when</td>
				</tr>

EOT;

			$sw = $next_sw[$sw];
		}

		if (sizeof($this->checked) == 0 || $this->read_only)
		{
			$disabled_str = ' disabled';
			$but_class = 'but3D';
		}
		else
		{
			$disabled_str = '';
			$but_class = 'but3';
		}

		if ($this->read_only)
		{
			$disabled_str2 = ' disabled';
			$but_class2 = 'but3D';
		}
		else
		{
			$disabled_str2 = '';
			$but_class2 = 'but3';
		}

		$html .= <<<EOT
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>

EOT;

  	$html .= <<<EOT
			<input type="submit" id="knefc_cmd1" name="cmd" value="Hinzufügen" class="$but_class2"$disabled_str2 />
			<input type="submit" id="knefc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_News_Form.onDelete()" />
			<input type="submit" id="knefc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="knefc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
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
			if (isset($get['ng']))
			{
				if (($ng = (int)$get['ng']) < 1)
					throw new Exception("Neplatné ID skupiny novinek: $ng");

				$this->ngid = $ng;
			}

			if (isset($get['as']) && !$this->read_only)
			{
				$this->loadLastChange();
				$this->loadNews();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->news[$this->index[$as]]->Active;

				mysql_query("UPDATE news SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE NGID=$this->ngid AND ID=$as");

//				$this->news[$this->index[$as]]->Active = $nas;
//				$this->news[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->redirection = KIWI_NEWS . "?ng=$this->ngid";
			}
		}

		if (!empty($post) && !$this->read_only)
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
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					 mysql_query("UPDATE news SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE NGID=$this->ngid AND ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_NEWS . "?ng=$this->ngid";
					break;
				case 'Hinzufügen':
					$this->redirection = KIWI_ADD_NEWSITEM . "?ng=$this->ngid";
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						mysql_query("DELETE FROM news WHERE NGID=$this->ngid AND ID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$this->redirection = KIWI_NEWS . "?ng=$this->ngid";
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('news', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadNews()
	{
		if (!is_array($this->news))
		{
			$this->news = array();
			$df = '%e.%c.%Y';
			$result = mysql_query("SELECT ID, Name, DATE_FORMAT(`When`, '$df') AS `WhenF`, Active, LastChange FROM news WHERE NGID=$this->ngid ORDER BY `When` DESC, LastChange DESC");
			$i = 0;
			while ($row = mysql_fetch_object($result))
			{
				$this->news[$i] = new Kiwi_DataRow($row);
				$this->index[(int)$row->ID] = $i;
				$i++;
			}
		}

		if ($this->ngtitle == '')
		{
			$result = mysql_query("SELECT Title FROM newsgroups WHERE ID=$this->ngid");
			if ($row = mysql_fetch_row($result))
				$this->ngtitle = $row[0];
			else
				throw new Exception("Neplatný identifikátor skupiny novinek!");
		}
	}
}
?>