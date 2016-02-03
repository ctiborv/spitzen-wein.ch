<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_NewsGroups_Form extends Page_Item
{
	protected $all_checked;
	protected $newsgroups;
	protected $index;
	protected $checked;
	protected $lastchange;

	function __construct()
	{
		parent::__construct();
		$this->all_checked = false;
		$this->newsgroups = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
	}

	function _getHTML()
	{
		$this->loadLastChange();
		$this->loadNewsGroups();

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self" method="post">
	<h2>WWW Gruppen News / Artikel - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$disabled_str = sizeof($this->newsgroups) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_NewsGroups_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Bezeichnung</th>
					<th>News</th>
					<th>geändert</th>
					<th>aktiv</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);
		$i = 0;

		foreach ($this->newsgroups as $newsgroup)
		{
			$i++;
			$checked_str = (isset($this->checked[$newsgroup->ID]) && $this->checked[$newsgroup->ID]) ? ' checked' : '';

			$title = htmlspecialchars($newsgroup->Title);

			$plink = KIWI_NEWS . "?ng=$newsgroup->ID";

			if ($newsgroup->Usage)
			{
				$usage = $newsgroup->Usage;
				if ($newsgroup->Usage > $newsgroup->ActiveUsage)
				{
					$aktivni = 'aktiv';
					//if ($newsgroup->ActiveUsage > 4) $aktivni .= 'ch'; // only for czech lang
					$usage .= " ($newsgroup->ActiveUsage $aktivni)";
				}
			}
			else
				$usage = '0';

			$dt = parseDateTime($newsgroup->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$active = $newsgroup->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$newsgroup->ID" onclick="Kiwi_NewsGroups_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$plink">$title</a></td>
					<td>$usage</td>
					<td>$lastchange</td>
					<td><a href="$self?as=$newsgroup->ID">$active</a></td>
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
/* prozatim nelze pridavat ani mazat
		$html .= <<<EOT
			<input type="submit" id="kngfc_cmd1" name="cmd" value="Gruppe hinzufügen" class="but3" />
			<input type="submit" id="kngfc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_NewsGroups_Form.onDelete()" />

EOT;
*/
		$html .= <<<EOT
			<input type="submit" id="kngfc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kngfc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	function handleInput($get, $post)
	{
		// todo: dořešit práva
		if (!empty($get))
		{
			if (isset($get['as']))
			{
				$this->loadLastChange();
				$this->loadNewsGroups();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->newsgroups[$this->index[$as]]->Active;

				mysql_query("UPDATE newsgroups SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

				/*
				$this->newsgroups[$this->index[$as]]->Active = $nas;
				$this->newsgroups[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				*/
				$this->lastchange->register();
				$this->lastchange = null;
				$this->redirection = KIWI_NEWSGROUPS;
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
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					 mysql_query("UPDATE newsgroups SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_NEWSGROUPS;
					break;
/* prozatim nelze pridavat skupiny novinek
				case 'Gruppe hinzufügen':
					$this->redirection = KIWI_ADD_NEWSGROUP;
					break;
*/
/* prozatim nelze odstranovat skupiny novinek
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						$this->deletePictureFiles($id_list);
						mysql_query("DELETE FROM newsgroups WHERE ID IN ($id_list)");
						$this->registerEItemsChanges($id_list);
						mysql_query("DELETE FROM news WHERE NGID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$this->redirection = KIWI_NEWSGROUPS;
					}
					break;
*/
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('newsgroups', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadNewsGroups()
	{
		if (!is_array($this->newsgroups))
		{
			$this->newsgroups = array();
			if ($result = mysql_query("SELECT ID, Title, Active, 0 AS `ActiveUsage`, 0 AS `Usage`, LastChange FROM newsgroups ORDER BY Title"))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->newsgroups[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
				}

				$result = mysql_query("SELECT NGID, Count(NullIf(Active,0)) AS `ActiveUsage`, Count(*) AS `Usage` FROM news GROUP BY NGID");

				while ($row = mysql_fetch_object($result))
				{
					$i = $this->index[(int)$row->NGID];
					if (array_key_exists($i, $this->newsgroups))
					{
						$this->newsgroups[$i]->ActiveUsage = $row->ActiveUsage;
						$this->newsgroups[$i]->Usage = $row->Usage;
					}
					// jinak nekonzistence v databázi
				}
			}
		}
	}
}
?>