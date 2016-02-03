<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_modules.inc.php';

class Kiwi_Modules_Form extends Page_Item
{
	protected $read_only;
	protected $all_checked;
	protected $modules;
	protected $index;
	protected $checked;
	protected $checked_count;
	protected $lastchange;
	protected $menu_item; // pro připojování modulů k dané stránce

	public function __construct(&$rights)
	{
		parent::__construct();

		$mrights = $rights->WWW;
		if (is_array($mrights))
			$this->read_only = !$mrights['Write'];
		else $this->read_only = !$mrights;
		$this->all_checked = false;
		$this->modules = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->menu_item = false;
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadModules();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = ($this->menu_item) ? "?mi=$this->menu_item" : '';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>WWW module - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$disabled_str = (sizeof($this->modules) == 0 || $this->read_only) ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Modules_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Bezeichnung</th>
					<th>Modultyp</th>
					<th>Nutzung</th>
					<th>geändert</th>
					<th>aktiv</th>
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

			$mlink = KIWI_EDIT_MODULE . "?m=$module->ID";
			if ($this->menu_item) $mlink .= "&smi=$this->menu_item";
			// kvůli korektnímu návratu po editaci modulu ze seznamu modulů

 			$mtype = htmlspecialchars($GLOBALS['module_types'][$module->Type][0]);

 			if ($module->Usage)
 			{
				$usage = $module->Usage;
 				if ($module->Usage > $module->ActiveUsage)
 				{
 					$aktivni = 'aktivní';
 					if ($module->ActiveUsage > 4) $aktivni .= 'ch';
 					$usage .= " ($module->ActiveUsage $aktivni)";
 				}
 			}
 			else
 				$usage = '0';

		  $dt = parseDateTime($module->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$active = $module->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$module->ID" onclick="Kiwi_Modules_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><a href="$mlink">$name</a></td>
					<td>$mtype</td>
					<td>$usage</td>
					<td>$lastchange</td>

EOT;

			if (!$this->read_only)
				$html .= <<<EOT
					<td><a href="$self?as=$module->ID">$active</a></td>

EOT;
			else
				$html .= <<<EOT
					<td>$active</td>

EOT;
			$html .= <<<EOT
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

		if ($this->menu_item)
	  	$html .= <<<EOT
			<input type="submit" id="kmofc_cmd5" name="cmd" value="zufügen" class="$but_class"$disabled_str />

EOT;
  	else
  	{
	  	$html .= <<<EOT
			<input type="submit" id="kmofc_cmd1" name="cmd" value="Modul hinzufügen:" class="$but_class2"$disabled_str2 />
			<select name="kmofc_modules" class="sel1"$disabled_str2>

EOT;
			foreach ($GLOBALS['module_types'] as $mk => $mt)
			{
				$html .= <<<EOT
				<option value="$mk">{$mt[0]}</option>

EOT;
			}
			$html .= <<<EOT
			</select>

EOT;
  	}

		$html .= <<<EOT
			<input type="submit" id="kmofc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Modules_Form.onDelete()" />
			<input type="submit" id="kmofc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kmofc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
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
			if (isset($get['mi']))
			{
				if (($mi = (int)$get['mi']) < 1)
					throw new Exception("Neplatná hodnota parametru \"mi\": $mi");

				$this->menu_item = $mi;
			}

			if (isset($get['as']) && !$this->read_only)
			{
				$this->loadLastChange();
				$this->loadModules();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->modules[$this->index[$as]]->Active;

				mysql_query("UPDATE modules SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

//				$this->modules[$this->index[$as]]->Active = $nas;
//				$this->modules[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->redirection = KIWI_MODULES . ($this->menu_item ? "?mi=$this->menu_item" : '');
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
					 mysql_query("UPDATE modules SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_MODULES . ($this->menu_item ? "?mi=$this->menu_item" : '');
					break;
				case 'Modul hinzufügen:':
					$mtype = (int)$post['kmofc_modules'];
					$qs = "?t=$mtype";
					$this->redirection = KIWI_ADD_MODULE . $qs;
					break;
				case 'zufügen':
					if (!$this->menu_item)
						throw new Exception("Pokus o připojení modulu/ů k neznámé položce menu.");
						$result = mysql_query("SELECT Count(ID) FROM menuitems WHERE ID=$this->menu_item");
						if ($row = mysql_fetch_row($result))
							if ($row[0] != 0)
							{
								$result = mysql_query("SELECT Max(Priority) FROM modbinds WHERE MIID=$this->menu_item");
								if ($row = mysql_fetch_row($result))
									$priority = (int)$row[0] + 1;
								else
									throw new Exception("Chyba při načítání priority modulů položky menu");

								$values = array();
								foreach ($post['check'] as $item)
								{
									$values[] = "($item, $this->menu_item, $priority)";
									$priority++;
								}

								if (sizeof($values))
									mysql_query('INSERT INTO modbinds(ModID, MIID, Priority) VALUES ' . implode(', ', $values));
								$this->redirection =  KIWI_EDIT_MENUITEM . "?mi=$this->menu_item";
								break;
							}
					throw new Exception("Chyba při pokusu připojit modul k dané položce menu");
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						mysql_query("DELETE FROM modules WHERE ID IN ($id_list)");
						mysql_query("DELETE FROM modbinds WHERE ModID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$this->redirection = KIWI_MODULES . ($this->menu_item ? "?mi=$this->menu_item" : '');
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('modules', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadModules()
	{
		if ($this->modules == null)
		{
			$this->modules = array();
			$result = mysql_query("SELECT ID, Name, Type, Active, 0 AS `ActiveUsage`, 0 AS `Usage`, LastChange FROM modules ORDER BY Type, Name");

			$i = 0;
			while ($row = mysql_fetch_object($result))
			{
				$this->modules[$i] = new Kiwi_DataRow($row);
				$this->index[(int)$row->ID] = $i;
				$i++;
			}

			$result = mysql_query("SELECT ModID, Count(NullIf(Active,0)) AS `ActiveUsage`, Count(*) AS `Usage` FROM modbinds GROUP BY ModID");

			while ($row = mysql_fetch_object($result))
			{
				$i = $this->index[(int)$row->ModID];
				if (array_key_exists($i, $this->modules[$i]))
				{
					$this->modules[$i]->ActiveUsage = $row->ActiveUsage;
					$this->modules[$i]->Usage = $row->Usage;
				}
				// jinak nekonzistence v databázi
			}
		}
	}
}
?>