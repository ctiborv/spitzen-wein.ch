<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_menuitem.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_modules.inc.php';

class Kiwi_MenuItem_Form extends Page_Item
{
	protected $read_only;
	protected $all_checked;
	protected $id;
	protected $parent;
	protected $record;
	protected $modules;
	protected $index;
	protected $module_types;
	protected $checked;
	protected $lastchange;

	public function __construct(&$rights)
	{
		parent::__construct();

		$mrights = $rights->WWW;
		if (is_array($mrights))
			$this->read_only = !$mrights['Write'];
		else $this->read_only = !$mrights;
		$this->all_checked = false;
		$this->id = 0;
		$this->parrent = null;
		$this->record = null;
		$this->modules = null;
		$this->index = array();
		$this->module_types = null;
		$this->checked = array();
		$this->lastchange = null;
	}

	public function _getHTML()
	{
		if ($this->id)
		{
			$qs = "?mi=$this->id";

			$this->loadRecord();

			$tname = $name = htmlspecialchars($this->record->Name);
			$webpage = htmlspecialchars($this->record->WebPage);
			$dt = parseDateTime($this->record->LastChange);
			$lastchange = date('j.n. Y - H:i', $dt['stamp']);
		}
		else
		{
			$qs = ($this->parent != null) ? "?sm=$this->parent" : '';

			$name = $webpage = '';
			$tname = 'neu';
			$lastchange = null;
		}

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
			$onchange_str = ' onchange="Kiwi_MenuItem_Form.onChange()" onkeydown="return Kiwi_MenuItem_Form.onKeyDown(event)"';
		}

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>[Posten] - $tname - [editieren]</h2>
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
							<td><input type="text" id="kmifc_name" name="Nazev_v_menu" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Website-Namen :</span></td>
							<td><input type="text" id="kmifc_webpage" name="Web_nazev" value="$webpage" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
					</table>
				</div>
				<input type="submit" id="kmifc_cmd1" name="cmd" value="speichern" class="but3D" disabled onclick="return Kiwi_MenuItem_Form.onSave()"/>
				<input type="submit" id="kmifc_cmd2" name="cmd" value="Náhled stránky" class="but3D" disabled />
			</fieldset>
		</div>
	</div>

EOT;
		if ($this->id) // todo: Přidat práva - má právo vidět seznam modulů?
		{
			$this->loadLastChange();
			$this->loadModules();

			$disabled_str = (sizeof($this->modules) == 0 || $this->read_only)? ' disabled' : '';

			$all_checked_str = $this->all_checked ? ' checked' : '';

			$html .= <<<EOT
	<h2>[Posten] - $tname - [angeschlossenen Module]</h2>
	<div class="levyV">
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_MenuItem_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Bezeichnung</th>
					<th>Modultyp</th>
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

			foreach ($this->modules as $module)
			{
				$i++;
				$checked_str = (isset($this->checked[$module->ID]) && $this->checked[$module->ID]) ? ' checked' : '';
				$disabled_str = $this->read_only ? ' disabled' : '';

				$name = htmlspecialchars($module->Name);

				$mlink = KIWI_EDIT_MODULE . "?m=$module->ModID&mi=$this->id";

	 			$mtype = htmlspecialchars($GLOBALS['module_types'][$module->Type][0]);

				$dt = parseDateTime($module->LastChange);
				$lastchange = date('j.n.Y H:i', $dt['stamp']);

				$active = $module->Active != 0 ? 'ja' : 'nein';

				$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$module->ID" onclick="Kiwi_MenuItem_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><a href="$mlink">$name</a></td>
					<td>$mtype</td>
					<td>$lastchange</td>

EOT;

				if (!$this->read_only)
					$html .= <<<EOT
					<td><a href="$self$qs&as=$module->ID">$active</a></td>

EOT;
				else
					$html .= <<<EOT
					<td>$active</td>

EOT;

				if (!$this->read_only)
				{
					$nullimg = "<img src=\"./image/null.gif\" alt=\"\" title=\"\" width=\"18\" height=\"18\" />";
					$html .=
			"\t\t\t\t\t<td>" . ($i < sizeof($this->record->Modules) - 1 ? "<a href=\"$self$qs&dd=$module->ID\"><img src=\"./image/alldown.gif\" alt=\"\" title=\"ganz unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i < sizeof($this->record->Modules) ? "<a href=\"$self$qs&d=$module->ID\"><img src=\"./image/down.gif\" alt=\"\" title=\"unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 1 ? "<a href=\"$self$qs&u=$module->ID\"><img src=\"./image/up.gif\" alt=\"\" title=\"oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 2 ? "<a href=\"$self$qs&uu=$module->ID\"><img src=\"./image/allup.gif\" alt=\"\" title=\"ganz oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . "</td>\n";
				}

				$html .= <<<EOT
				</tr>

EOT;

				$sw = $next_sw[$sw];
			}

			$html .= <<<EOT
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>
			<input type="submit" id="kmifc_cmd3" name="cmd" value="neu Modul hinzufügen:" class="but4$D_str"$ro_disabled_str />
			<select name="kmifc_modules" class="sel1"$ro_disabled_str>

EOT;

			foreach ($GLOBALS['module_types'] as $key => $value)
				$html .= <<<EOT
				<option value=$key>{$value[0]}</option>

EOT;

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

			$html .= <<<EOT
			</select>
			<input type="submit" id="kmifc_cmd4" name="cmd" value="vorhandene Modul hinzufügen" class="but4$D_str"$ro_disabled_str />
<br />
			<input type="submit" id="kmifc_cmd5" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_MenuItem_Form.onDelete()" />
			<input type="submit" id="kmifc_cmd6" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kmifc_cmd7" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
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
			if (isset($get['mi']))
			{
				if (($mi = (int)$get['mi']) < 1)
					throw new Exception("Neplatné ID záznamu: $mi");

				$this->id = $mi;
				$qs = "?mi=$this->id";
			}

			if (isset($get['sm']))
			{
				if (($this->parent = (int)$get['sm']) < 1)
					throw new Exception("Neplatné ID nadřazeného menu: $this->parent");
			}

			if (isset($get['as']) && !$this->read_only)
			{
				$this->loadRecord();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->modules[$this->index[$as]]->Active;

				mysql_query("UPDATE modbinds SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

//				$this->modules[$this->index[$as]]->Active = $nas;
//				$this->modules[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
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

				if (($cp = (int)$get[$qv]) < 1 || !isset($this->index[$cp]))
					throw new Exception("Neplatné ID záznamu: $cp");

				$this->moveModule($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;

				$this->redirection = $self . $qs;
			}
		}
		else throw new Exception("Chybějící ID nadřazeného menu");

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
					$web_nazev = mysql_real_escape_string(strip_gpc_slashes($post['Web_nazev']));
					if ($nazev_v_menu == '' || $web_nazev == '') throw new Exception('Některá z povinných položek nebyla vyplněna');
					if ($this->id)
						mysql_query("UPDATE menuitems SET Name='$nazev_v_menu', WebPage='$web_nazev', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
					else
					{
						if ($this->parent == null) throw new Exception("Chybějící ID nadřazeného menu");
						$result = mysql_query("SELECT Count(ID) FROM menuitems WHERE ID=$this->parent AND Submenu=1");
						$row = mysql_fetch_row($result);
						if ($row[0] != 1) throw new Exception("Neplatné ID nadřazeného menu");

						$result = mysql_query("SELECT MAX(Priority) FROM menuitems WHERE Parent=$this->parent");
						$row = mysql_fetch_row($result);
						$priority = (int)$row[0] + 1;

						mysql_query("INSERT INTO menuitems(Name, WebPage, Parent, Priority) VALUES ('$nazev_v_menu', '$web_nazev', $this->parent, $priority)");
						$this->id = mysql_insert_id();
						$qs = "?mi=$this->id";
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$this->redirection = $self . $qs;
					break;
				case 'Náhled stránky': throw new Exception('Funkce není implementována');
					break;
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					mysql_query("UPDATE modbinds SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = $self . $qs;
					break;
				case 'neu Modul hinzufügen:':
					$mtype = (int)$post['kmifc_modules'];
					$qs .= "&t=$mtype";
					$this->redirection = KIWI_ADD_MODULE . $qs;
					break;
				case 'vorhandene Modul hinzufügen':
					$this->redirection = KIWI_ADD_EXISTING_MODULE . $qs;
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						mysql_query("DELETE FROM modbinds WHERE ID IN ($id_list)");
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->redirection = $self . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadRecord()
	{
		if ($this->record == null && $this->id)
		{
			$result = mysql_query("SELECT ID, Name, WebPage, SubMenu, Parent, Active, LastChange FROM menuitems WHERE ID=$this->id");
			$row = mysql_fetch_object($result);
			$this->record = new Kiwi_MenuItem($row);
			$this->loadModules();
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->id)
		{
			if ($this->lastchange == null)
				$this->lastchange = new Kiwi_LastChange(array('menuitems', $this->id), 'j.n. Y - H:i');
			if ($acquire)
				$this->lastchange->acquire();
		}
	}

	protected function loadModules()
	{
		if ($this->modules == null)
		{
			$this->modules = array();
			$i = 0;
			foreach ($this->record->Modules as $module)
			{
				$this->modules[$i] = $module;
				$this->index[(int)$module->ID] = $i;
				$i++;
			}

			if ($this->module_types == null)
				$this->loadModuleTypes();
		}
	}

	protected function loadModuleTypes()
	{
		$this->module_types = array(1 => 'Text', 2 => 'Bild', 3 => 'News', 4 => 'Photogallery');
	}

	protected function moveModule($iid, $down = true, $totally = false)
	{
		$mvals = array
		(
			false => array('Min', '>', -1),
			true => array('Max', '<', +1)
		);

		mysql_query("START TRANSACTION");

		if ($totally)
		{
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM modbinds WHERE MIID=$this->id AND ID!=$iid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE modbinds SET Priority=$newpri WHERE ID=$iid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM modbinds WHERE ID=$iid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM modbinds WHERE MIID=$this->id AND Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE modbinds SET Priority=$priority WHERE MIID=$this->id AND Priority=$neigh");
				mysql_query("UPDATE modbinds SET Priority=$neigh WHERE ID=$iid");
			}
		}

		mysql_query("COMMIT");
	}
}
?>