<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';

class Kiwi_Actions_Form extends Page_Item
{
	protected $all_checked;
	protected $agid;
	protected $agtitle;
	protected $actions;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $read_only;

	public function __construct(&$rights)
	{
		parent::__construct();

		if (is_array($rights))
			$this->read_only = !$rights['Write'];

		$this->all_checked = false;
		$this->agid = 1;
		$this->agtitle = '';
		$this->actions = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadActions();

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self?ag=$this->agid" method="post">
	<h2>KATALOG Aktionen - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$disabled_str = sizeof($this->actions) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Actions_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Name</th>
					<th></th>
					<th>geändert</th>
					<th>aktiv</th>

EOT;

			if (!$this->read_only)
				$html .= <<<EOT
					<th>Priorität</th>

EOT;

			$html .= <<<EOT
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);
		$i = 0;

		foreach ($this->actions as $action)
		{
			$i++;
			$checked_str = (isset($this->checked[$action->ID]) && $this->checked[$action->ID]) ? ' checked' : '';

			$title = htmlspecialchars($action->Title);

			$plink = KIWI_EDIT_ACTION . "?ag=$this->agid&a=$action->ID";

			if ($action->Picture)
			{
				$photo = 'photoOK';
				$phototitle = 'Aktion hat Bild';
			}
			else
			{
				$photo = 'photoKO';
				$phototitle = 'Kein Bild vorhanden';
			}
			$photostr = <<<EOT
<img src="./image/$photo.gif" alt="" title="$phototitle" />
EOT;

			$dt = parseDateTime($action->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$active = $action->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$action->ID" onclick="Kiwi_Actions_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$plink">$title</a></td>
					<td>$photostr</td>
					<td>$lastchange</td>

EOT;

			if (!$this->read_only)
				$html .= <<<EOT
					<td><a href="$self?ag=$this->agid&as=$action->ID">$active</a></td>

EOT;
			else
				$html .= <<<EOT
					<td>$active</td>

EOT;
			if (!$this->read_only)
			{
				$nullimg = "<img src=\"./image/null.gif\" alt=\"\" title=\"\" width=\"18\" height=\"18\" />";
				$html .=
					"\t\t\t\t\t<td>" . ($i < sizeof($this->actions) - 1 ? "<a href=\"$self?dd=$action->ID\"><img src=\"./image/alldown.gif\" alt=\"\" title=\"ganz unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i < sizeof($this->actions) ? "<a href=\"$self?d=$action->ID\"><img src=\"./image/down.gif\" alt=\"\" title=\"unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 1 ? "<a href=\"$self?u=$action->ID\"><img src=\"./image/up.gif\" alt=\"\" title=\"oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 2 ? "<a href=\"$self?uu=$action->ID\"><img src=\"./image/allup.gif\" alt=\"\" title=\"ganz oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . "</td>\n";
			}

			$html .= <<<EOT
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
			<input type="submit" id="kacfc_cmd1" name="cmd" value="Aktion hinzufügen" class="but3" />
			<input type="submit" id="kacfc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Actions_Form.onDelete()" />
			<input type="submit" id="kacfc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kacfc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		// todo: dořešit práva

		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (isset($get['ag']))
			{
				if (($ag = (int)$get['ag']) < 1)
					throw new Exception("Neplatné ID skupiny akcí: $ag");

				$this->agid = $ag;
			}

			if (isset($get['as']))
			{
				$this->loadLastChange();
				$this->loadActions();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->actions[$this->index[$as]]->Active;

				mysql_query("UPDATE eshopactions SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE AGID=$this->agid AND ID=$as");

				$this->actions[$this->index[$as]]->Active = $nas;
				$this->actions[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->redirection = $self . "?ag=$this->agid";
			}

			if (isset($get['d']) || isset($get['dd']) || isset($get['u']) || isset($get['uu']))
			{
				if ((int)isset($get['d']) + (int)isset($get['dd']) + (int)isset($get['u']) + (int)isset($get['uu']) != 1)
					throw new Exception("Neplatný vstup - více než jeden příkaz pro přesun položky");

				$dow = isset($get['d']) || isset($get['dd']);
				$tot = isset($get['dd']) || isset($get['uu']);

				$qv = $dow ? 'd' : 'u';
				if ($tot) $qv .= $qv;

				$this->loadActions();

				if (($cp = (int)$get[$qv]) < 1 || !isset($this->index[$cp]))
					throw new Exception("Neplatné ID záznamu: $cp");

				$this->moveItem($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;

				$this->redirection = $self . $qs;
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
						mysql_query("UPDATE eshopactions SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE AGID=$this->agid AND ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = $self . "?ag=$this->agid";
					break;
				case 'Aktion hinzufügen':
					$this->redirection = KIWI_ADD_ACTION . "?ag=$this->agid";
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						$this->deletePictureFiles($id_list);
						mysql_query("DELETE FROM eshopactions WHERE AGID=$this->agid AND ID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$this->redirection = $self . "?ag=$this->agid";
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('actions', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadActions()
	{
		if ($this->actions == null)
		{
			$this->actions = array();
			if ($result = mysql_query("SELECT ID, Title, Description, Picture, Link, Active, LastChange FROM eshopactions WHERE AGID=$this->agid ORDER BY Priority"))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->actions[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
				}
			}
		}
	}

	protected function deletePictureFiles($id_list)
	{
		if ($id_list == '') return;

		$result = mysql_query("SELECT picture FROM eshopactions WHERE AGID=$this->agid AND ID IN ($id_list)");
		while ($row = mysql_fetch_row($result))
		{
			if ($row[0] == '') continue;
				$this->deleteActionFile($row[0]);
		}
	}

	protected function deleteActionFile($filename)
	{
		if (!(unlink(KIWI_DIR_ACTIONS . $filename)))
			throw new Exception("Nepodařilo se smazat soubor s obrázkem akce");
	}

	protected function moveItem($iid, $down = true, $totally = false)
	{
		$mvals = array
		(
			false => array('Min', '>', -1),
			true => array('Max', '<', +1)
		);

		mysql_query("LOCK TABLES eshopactions WRITE");

		if ($totally)
		{
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM eshopactions WHERE AGID=$this->agid AND ID!=$iid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE eshopactions SET Priority=$newpri WHERE ID=$iid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM eshopactions WHERE ID=$iid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM eshopactions WHERE AGID=$this->agid AND Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE eshopactions SET Priority=$priority WHERE AGID=$this->agid AND Priority=$neigh");
				mysql_query("UPDATE eshopactions SET Priority=$neigh WHERE ID=$iid");
			}
		}

		mysql_query("UNLOCK TABLES");
	}
}
?>