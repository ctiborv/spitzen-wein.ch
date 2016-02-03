<?php
//TODO: finish rights
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';

class Kiwi_ActionGroups_Form extends Page_Item
{
	protected $rights;

	protected $all_checked;
	protected $actiongroups;
	protected $index;
	protected $checked;
	protected $lastchange;

	protected $read_only;

	public function __construct(&$rights)
	{
		parent::__construct();

		$this->rights = $rights;
		if (is_array($rights))
			$this->read_only = !$rights['Write'];

		$this->all_checked = false;
		$this->actiongroups = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadActionGroups();

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self" method="post">
	<h2>Gruppe Aktionen - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$disabled_str = sizeof($this->actiongroups) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_ActionGroups_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Name</th>
					<th>Aktionen</th>
					<th>geändert</th>
					<th>aktiv</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);
		$i = 0;

		foreach ($this->actiongroups as $actiongroup)
		{
			$i++;
			$checked_str = (isset($this->checked[$actiongroup->ID]) && $this->checked[$actiongroup->ID]) ? ' checked' : '';

			$title = htmlspecialchars($actiongroup->Title);

			$plink = KIWI_ACTIONS . "?ag=$actiongroup->ID";

			if ($actiongroup->Usage)
			{
				$usage = $actiongroup->Usage;
				if ($actiongroup->Usage > $actiongroup->ActiveUsage)
				{
					$aktivni = 'aktivní';
					if ($actiongroup->ActiveUsage > 4) $aktivni .= 'ch';
					$usage .= " ($actiongroup->ActiveUsage $aktivni)";
				}
			}
			else
				$usage = '0';

			$dt = parseDateTime($actiongroup->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$active = $actiongroup->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$actiongroup->ID" onclick="Kiwi_ActionGroups_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$plink">$title</a></td>
					<td>$usage</td>
					<td>$lastchange</td>
					<td><a href="$self?as=$actiongroup->ID">$active</a></td>
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
/* Prozatím není možno přidávat skupiny akcí
		$html .= <<<EOT
			<input type="submit" id="kagfc_cmd1" name="cmd" value="Gruppe hinzufügen" class="but3" />
			<input type="submit" id="kagfc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_ActionGroups_Form.onDelete()" />

EOT;
*/
		$html .= <<<EOT
			<input type="submit" id="kagfc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kagfc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		if (!empty($get))
		{
			if (array_key_exists('as', $get))
			{
				if (!$this->read_only)
				{
					$this->loadLastChange();
					$this->loadActionGroups();

					if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
						throw new Exception("Neplatné ID záznamu: $as");

					$nas = !$this->actiongroups[$this->index[$as]]->Active;

					mysql_query("UPDATE actiongroups SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

					/*
					$this->actiongroups[$this->index[$as]]->Active = $nas;
					$this->actiongroups[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
					*/
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_ACTIONGROUPS;
				}
				else
				{
					$this->redirection = KIWI_ACTIONGROUPS;
				}
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
					 mysql_query("UPDATE actiongroups SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_ACTIONGROUPS;
					break;
				//case 'Přidat skupinu':
				//	$this->redirection = KIWI_ADD_ACTION_GROUP;
				//	break;
				//case 'entfernen':
				//	$id_list = implode(',', $post['check']);
				//	if ($id_list)
				//	{
				//		mysql_query("DELETE FROM actiongroups WHERE ID IN ($id_list)");
				//		mysql_query("DELETE FROM eshopactions WHERE AGID IN ($id_list)");
				//		$this->checked = array();
				//		$this->loadLastChange(false);
				//		$this->lastchange->register();
				//		$this->lastchange = null;
				//		$this->redirection = KIWI_ACTIONGROUPS;
				//	}
				//	break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('actiongroups', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadActionGroups()
	{
		if (!is_array($this->actiongroups))
		{
			$this->actiongroups = array();
			if ($result = mysql_query("SELECT ID, Title, Active, 0 AS `ActiveUsage`, 0 AS `Usage`, LastChange FROM actiongroups ORDER BY Title"))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->actiongroups[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
				}

				$result = mysql_query("SELECT AGID, Count(NullIf(Active,0)) AS `ActiveUsage`, Count(*) AS `Usage` FROM eshopactions GROUP BY AGID");

				while ($row = mysql_fetch_object($result))
				{
					$i = $this->index[(int)$row->AGID];
					if (array_key_exists($i, $this->actiongroups))
					{
						$this->actiongroups[$i]->ActiveUsage = $row->ActiveUsage;
						$this->actiongroups[$i]->Usage = $row->Usage;
					}
					// jinak nekonzistence v databázi
				}
			}
		}
	}
}
?>