<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_menu.class.php';
require_once 'kiwi_menuitem.class.php';
require_once 'kiwi_lastchange.class.php';

class Kiwi_Menu_Form extends Page_Item
{
	protected $read_only;
	protected $all_checked;
	protected $id;
	protected $record;
	protected $records;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $new_submenu;

	public function __construct(&$rights)
	{
		parent::__construct();

		$mrights = $rights->WWW;
		if (is_array($mrights))
			$this->read_only = !$mrights['Write'];
		else $this->read_only = !$mrights;
		$this->all_checked = false;
		$this->id = 1;
		$this->record = null;
		$this->records = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->new_submenu = false;
	}

	public function _getHTML()
	{
		$qs = "?sm=$this->id";
		$tname = $lastchange = null;

		if (!$this->new_submenu)
		{
			$this->loadRecord();
			$this->loadMenuItems();

			$tname = $name = htmlspecialchars($this->record->Name);
			$dt = parseDateTime($this->record->LastChange);
			$lastchange = date('j.n. Y - H:i', $dt['stamp']);
		}
		else
		{
			$name = '';
			$tname = 'Neu';
			$qs .= '&n';
		}

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$onchange_str = '';
		}
		else
		{
			$readonly_str = '';
			$onchange_str = ' onchange="return Kiwi_Menu_Form.onChange()" onkeydown="return Kiwi_Menu_Form.onKeyDown(event)"';
		}

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self$qs" method="post">

EOT;

		if ($this->new_submenu || $this->id > 1)
		{
			$html .= <<<EOT
	<h2>[Untermenü] - $tname - [editieren]</h2>
	<div class="levyV">
		<div class="form3">
			<fieldset>

EOT;
			if ($lastchange != null)
				$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $lastchange</div>

EOT;

			$html .= <<<EOT
				<div id="frame2">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Menübezeichnung :</span></td>
							<td><input type="text" id="kmfc_name" name="Nazev_v_menu" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
					</table>
				</div>
				<input type="submit" id="kmfc_cmd1" name="cmd" value="speichern" class="but3D" disabled onclick="return Kiwi_Menu_Form.onSave()"/>
			</fieldset>
		</div>
	</div>

EOT;
		}

		if (!$this->new_submenu) // todo: Přidat práva - má právo vidět seznam menuitems?
		{
			$this->loadLastChange();

			$disabled_str = (sizeof($this->records) == 0 || $this->read_only) ? ' disabled' : '';

			$all_checked_str = $this->all_checked ? ' checked' : '';

			$html .= <<<EOT
	<h2>[Posten] - $tname - [Liste]</h2>
	<div class="levyV">
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Menu_Form.enableBtns(false);"$all_checked_str /></th>
					<th><img src="./image/null.gif" width="16" height="0" />&nbsp;Seite / Submenu</th>
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

			foreach ($this->records as $record)
			{
				$i++;
				$checked_str = (isset($this->checked[$record->ID]) && $this->checked[$record->ID]) ? ' checked' : '';
				$disabled_str = $this->read_only ? ' disabled' : '';

				$name = htmlspecialchars($record->Name);

				if ($record->Submenu)
				{
					$icon = 'slozka.gif';
					$icontitle = 'submenu';
					$link = KIWI_MENU . "?sm=$record->ID";
				}
				else
				{
					$icon = 'stranka.gif';
					$icontitle = 'Seite';
					$link = KIWI_MENUITEM . "?mi=$record->ID";
				}

				$dt = parseDateTime($record->LastChange);
				$lastchange = date('j.n.Y H:i', $dt['stamp']);

				$active = $record->Active != 0 ? 'ja' : 'nein';

				$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$record->ID" onclick="Kiwi_Menu_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><img src="./image/$icon" alt="" title="$icontitle" />&nbsp;<a href="$link">$name</a></td>
					<td>$lastchange</td>

EOT;

				if (!$this->read_only)
					$html .= <<<EOT
					<td><a href="$self$qs&as=$record->ID">$active</a></td>

EOT;
				else
					$html .= <<<EOT
					<td>$active</td>

EOT;

				if (!$this->read_only)
				{
					$nullimg = "<img src=\"./image/null.gif\" alt=\"\" title=\"\" width=\"18\" height=\"18\" />";
					$html .=
						"\t\t\t\t\t<td>" . ($i < sizeof($this->records) - 1 ? "<a href=\"$self$qs&dd=$record->ID\"><img src=\"./image/alldown.gif\" alt=\"\" title=\"ganz unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i < sizeof($this->records) ? "<a href=\"$self$qs&d=$record->ID\"><img src=\"./image/down.gif\" alt=\"\" title=\"unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 1 ? "<a href=\"$self$qs&u=$record->ID\"><img src=\"./image/up.gif\" alt=\"\" title=\"oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 2 ? "<a href=\"$self$qs&uu=$record->ID\"><img src=\"./image/allup.gif\" alt=\"\" title=\"ganz oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . "</td>\n";
				}

				$html .= <<<EOT
				</tr>

EOT;

				$sw = $next_sw[$sw];
			}

			if ($this->read_only || sizeof($this->checked) == 0)
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
			<input type="submit" id="kmfc_cmd3" name="cmd" value="Untermenü zugeben" class="$but_class2"$disabled_str2 />
			<input type="submit" id="kmfc_cmd2" name="cmd" value="Seite zugeben" class="$but_class2"$disabled_str2 />
			<input type="submit" id="kmfc_cmd4" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Menu_Form.onDelete()" />
			<input type="submit" id="kmfc_cmd5" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kmfc_cmd6" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
		</fieldset>
	</div>

EOT;
		}

		$html .= <<<EOT
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		$self = basename($_SERVER['PHP_SELF']);
		$qs = '';

		if (!empty($get))
		{
			if (isset($get['sm']))
			{
				if (($sm = (int)$get['sm']) < 1)
					throw new Exception("Neplatné ID záznamu: $sm");

				$this->id = $sm;
				$qs = "?sm=$sm";
			}

			if (isset($get['n']))
			{
				$result = mysql_query("SELECT Count(ID) FROM menuitems WHERE ID=$this->id AND Submenu=1");
				if ($row = mysql_fetch_row($result))
					if ($row[0] == 1) $this->new_submenu = true;

				if (!$this->new_submenu) throw new Exception("Neplatné ID záznamu: $this->id");
			}

			if (isset($get['as']) && !$this->read_only)
			{
				$this->loadRecord();
				$this->loadMenuItems();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->records[$this->index[$as]]->Active;

				mysql_query("UPDATE menuitems SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

//				$this->records[$this->index[$as]]->Active = $nas;
//				$this->records[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;

				$this->redirection = $self . $qs;
			}

			if ((isset($get['d']) || isset($get['dd']) || isset($get['u']) || isset($get['uu']))  && !$this->read_only)
			{
				if ((int)isset($get['d']) + (int)isset($get['dd']) + (int)isset($get['u']) + (int)isset($get['uu']) != 1)
					throw new Exception("Neplatný vstup - více než jeden příkaz pro přesun položky");

				$dow = isset($get['d']) || isset($get['dd']);
				$tot = isset($get['dd']) || isset($get['uu']);

				$qv = $dow ? 'd' : 'u';
				if ($tot) $qv .= $qv;

				$this->loadRecord();
				$this->loadMenuItems();

				if (($cp = (int)$get[$qv]) < 1 || !isset($this->index[$cp]))
					throw new Exception("Neplatné ID záznamu: $cp");

				$this->moveItem($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;

				$this->redirection = $self . $qs;
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
				case 'speichern':
					$nazev_v_menu = mysql_real_escape_string(strip_gpc_slashes($post['Nazev_v_menu']));
					if (!$this->id) throw new Exception("Neplatné ID záznamu: $this->id");
					if (!$this->new_submenu)
					{
						if ($this->id == 1) throw new Exception('Neplatná operace: editace nastavení hlavního menu');
						else
						{
							mysql_query("UPDATE menuitems SET Name='$nazev_v_menu', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
						}
					}
					elseif ($nazev_v_menu != '')
					{
						$result = mysql_query("SELECT Max(Priority) FROM menuitems WHERE Parent=$this->id");
						if ($row = mysql_fetch_row($result))
							$priority = (int)$row[0] + 1;
						else
							throw new Exception("Chyba při načítání priority položek menu");

						mysql_query("INSERT INTO menuitems(Name, Submenu, Parent, Priority, Active) VALUES ('$nazev_v_menu', 1, $this->id, $priority, 1)");
						$this->id = mysql_insert_id();
						$qs = "?sm=$this->id";
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$this->redirection = $self . $qs;
					break;
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					mysql_query("UPDATE menuitems SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list) AND ID > 1");
					// ID 1 odpovídá hlavnímu menu, které nelze deaktivovat
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = $self . $qs;
					break;
				case 'Seite zugeben':
					$this->redirection = KIWI_ADD_MENUITEM . "?sm=$this->id";
					break;
				case 'Untermenü zugeben':
					$this->redirection = KIWI_ADD_SUBMENU . "?sm=$this->id&n";
					break;
				case 'entfernen':
					if (sizeof($post['check']) > 0)
					{
						$id_list_rec = implode(',', $this->getRecursiveIdList($post['check']));
						mysql_query("DELETE FROM menuitems WHERE ID IN ($id_list_rec) AND ID > 1");
						// ID 1 odpovídá hlavnímu menu, které nelze odstranit
						mysql_query("DELETE FROM modbinds WHERE MIID IN ($id_list_rec)");
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->redirection = $self . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange(array('menuitems', $this->id), 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadRecord()
	{
		$result = mysql_query("SELECT ID, Name, Submenu, Parent, Active, LastChange FROM menuitems WHERE ID=$this->id");
		if ($row = mysql_fetch_object($result))
		{
			$this->record = new Kiwi_Menu($row);
		}
		else throw new Exception("Neplatné ID záznamu: $this->id");
	}

	protected function loadMenuItems()
	{
		if ($this->records == null)
		{
			$this->records = array();
			$i = 0;
			foreach ($this->record->MenuItems as $item)
			{
				$this->records[$i] = $item;
				$this->index[(int)$item->ID] = $i;
				$i++;
			}
		}
	}

	protected function moveItem($iid, $down = true, $totally = false)
	{
		$mvals = array
		(
			false => array('Min', '>', -1),
			true => array('Max', '<', +1)
		);

		mysql_query("START TRANSACTION");

		if ($totally)
		{
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM menuitems WHERE Parent=$this->id AND ID!=$iid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE menuitems SET Priority=$newpri WHERE ID=$iid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM menuitems WHERE ID=$iid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM menuitems WHERE Parent=$this->id AND Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE menuitems SET Priority=$priority WHERE Parent=$this->id AND Priority=$neigh");
				mysql_query("UPDATE menuitems SET Priority=$neigh WHERE ID=$iid");
			}
		}

		mysql_query("COMMIT");
	}

	protected function getRecursiveIdList($id_list)
	{
		// doplni seznam ID o vsechna ID ze vsech obsazenych submenu
		if (!is_array($id_list)) throw Exception();
		if (sizeof($id_list) > 0)
		{
			$ids = implode(',', $id_list);
			$result = mysql_query("SELECT ID FROM menuitems WHERE ID IN ($ids) AND SubMenu=1");
			$newids = array();
			while ($row = mysql_fetch_row($result))
			{
				$res2 = mysql_query("SELECT ID FROM menuitems WHERE Parent={$row[0]}");
				while ($row2 = mysql_fetch_row($res2))
					$newids[] = $row2[0];
			}
			mysql_free_result($res2);
			mysql_free_result($result);
			$newids_rec = $this->getRecursiveIdList($newids);
//			$debug_str = "($ids) -> (";
			$id_list = array_merge($id_list, $newids_rec);
//			$debug_str .= implode(',', $id_list) . ')';
//			error_log($debug_str);
		}
		return $id_list;
	}
}
?>