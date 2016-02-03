<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_productproperty.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_anchor.class.php';

class Kiwi_ProductProperty_Form extends Page_Item
{
	protected $read_only;
	protected $all_checked;
	protected $id;
	protected $record;
	protected $values;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $anchor;

	public function __construct()
	{
		parent::__construct();
		$this->read_only = false; // todo: Přidat práva - má právo upravovat?
		$this->all_checked = false;
		$this->id = 0;
		$this->record = null;
		$this->values = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->anchor = new CurrentKiwiAnchor();
	}

	public function _getHTML()
	{
		if ($this->id)
		{
			$qs = "?pp=$this->id";

			$this->loadRecord();

			$tname = $name = htmlspecialchars($this->record->Name);
			$type = $this->record->Type;
			$datatype = $this->record->DataType;
			$dt = parseDateTime($this->record->LastChange);
			$lastchange = date('j.n. Y - H:i', $dt['stamp']);
		}
		else
		{
			$qs = '';

			$name = '';
			$type = 1;
			$datatype = 1;
			$tname = 'neu';
			$lastchange = null;
		}

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$onchange_str = '';
			$onchange_str2 = '';
			$ro_disabled_str = ' disabled';
			$D_str = 'D';
		}
		else
		{
			$readonly_str = $ro_disabled_str = $D_str = '';
			$onchange_str = ' onchange="Kiwi_ProductProperty_Form.onChange()" onkeydown="return Kiwi_ProductProperty_Form.onKeyDown(event)"';
			$onchange_str2 = ' onchange="Kiwi_ProductProperty_Form.onChange()" onkeydown="return Kiwi_ProductProperty_Form.onChange()"';
		}

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>[Eigenschaft] - $tname - [editieren]</h2>
	<div class="levyV">
		<div class="form3">
			<fieldset>

EOT;

		if ($lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $lastchange</div>

EOT;

		$typy = array(1 => 'Auswahleigenschaft', 2 => 'informative Eigenschaft');
		$datatypy = array(1 => 'Text', 2 => 'Icon', 3 => 'Farbe');

		$seltyp_str = '';
		foreach ($typy as $idtyp => $typ)
		{
			$selected_str = $type == $idtyp ? ' selected' : '';
			$seltyp_str .= <<<EOT

									<option value="$idtyp"$selected_str>$typ</option>
EOT;
		}

		$seldtyp_str = '';
		foreach ($datatypy as $idtyp => $typ)
		{
			$selected_str = $datatype == $idtyp ? ' selected' : '';
			$seldtyp_str .= <<<EOT

									<option value="$idtyp"$selected_str>$typ</option>
EOT;
		}

		$html .= <<<EOT
				<div id="frame2">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Eigenschaftsbezeichnung :</span></td>
							<td><input type="text" id="kppyfc_name" name="Nazev_vlastnosti" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Charaktereigenschaften :</span></td>
							<td>
								<select id="kppyfc_type" name="Charakter_vlastnosti" class="sel1"$onchange_str2$readonly_str>$seltyp_str
								</select>
							</td>
						</tr>
						<tr>
							<td><span class="span-form2">Datatype Eigenschaften :</span></td>
							<td>
								<select id="kppyfc_type" name="DataTyp_vlastnosti" class="sel1"$onchange_str2$readonly_str>$seldtyp_str
								</select>
							</td>
						</tr>
					</table>
				</div>
				<input type="submit" id="kppyfc_cmd1" name="cmd" value="speichern" class="but3D" disabled onclick="return Kiwi_ProductProperty_Form.onSave()"/>
			</fieldset>
		</div>
	</div>

EOT;

		if ($this->id) // todo: Přidat práva - má právo vidět seznam produktů?
		{
			$this->loadLastChange();
			$this->loadValues();

			$disabled_str = (sizeof($this->values) == 0 || $this->read_only)? ' disabled' : '';

			$all_checked_str = $this->all_checked ? ' checked' : '';

			$html .= <<<EOT
	<h2>[Eigenschaft] - $tname - [definierte Werte]</h2>
	<div class="levyV">
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_ProductProperty_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Wert</th>
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

			foreach ($this->values as $value)
			{
				$i++;
				$checked_str = (isset($this->checked[$value->ID]) && $this->checked[$value->ID]) ? ' checked' : '';
				$disabled_str = $this->read_only ? ' disabled' : '';

				$shortdesc = htmlspecialchars($value->Value);

				$vlink = KIWI_EDIT_PRODUCT_PROPERTY_VALUE . "?pv=$value->ID";
				$anchor_str = ($this->anchor->ID == $value->ID) ? ' name="zmena"' : '';

				$dt = parseDateTime($value->LastChange);
				$lastchange = date('j.n.Y H:i', $dt['stamp']);

				$active = $value->Active != 0 ? 'ja' : 'nein';

				$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$value->ID" onclick="Kiwi_ProductProperty_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><a href="$vlink"$anchor_str>$shortdesc</a></td>
					<td>$lastchange</td>

EOT;

				if (!$this->read_only)
					$html .= <<<EOT
					<td><a href="$self$qs&as=$value->ID">$active</a></td>

EOT;
				else
					$html .= <<<EOT
					<td>$active</td>

EOT;

				if (!$this->read_only)
				{
					$nullimg = "<img src=\"./image/null.gif\" alt=\"\" title=\"\" width=\"18\" height=\"18\" />";
					$html .=
						"\t\t\t\t\t<td>" . ($i < sizeof($this->record->Values) - 1 ? "<a href=\"$self$qs&dd=$value->ID\"><img src=\"./image/alldown.gif\" alt=\"\" title=\"ganz unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i < sizeof($this->record->Values) ? "<a href=\"$self$qs&d=$value->ID\"><img src=\"./image/down.gif\" alt=\"\" title=\"unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 1 ? "<a href=\"$self$qs&u=$value->ID\"><img src=\"./image/up.gif\" alt=\"\" title=\"oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 2 ? "<a href=\"$self$qs&uu=$value->ID\"><img src=\"./image/allup.gif\" alt=\"\" title=\"ganz oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . "</td>\n";
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
			<input type="submit" id="kppyfc_cmd3" name="cmd" value="neuer Wert hinzufügen" class="but4$D_str"$ro_disabled_str />

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
			<input type="submit" id="kppyfc_cmd5" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_ProductProperty_Form.onDelete()" />
			<input type="submit" id="kppyfc_cmd6" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kppyfc_cmd7" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
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
		$qs = '';

		if (!empty($get))
		{
			if (isset($get['pp']))
			{
				if (($pp = (int)$get['pp']) < 1)
					throw new Exception("Neplatné ID záznamu: $pp");

				$this->id = $pp;
				$qs = "?pp=$this->id";
			}

			if (isset($get['as']))
			{
				$this->loadRecord();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->values[$this->index[$as]]->Active;

				mysql_query("UPDATE prodpvals SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

				$this->values[$this->index[$as]]->Active = $nas;
				$this->values[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $as;
				$this->redirection = KIWI_EDIT_PRODUCT_PROPERTY . $qs . '#zmena';
			}

			if (isset($get['d']) || isset($get['dd']) || isset($get['u']) || isset($get['uu']))
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

				$this->moveValue($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $cp;
				$this->redirection = KIWI_EDIT_PRODUCT_PROPERTY . $qs . '#zmena';
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
				case 'speichern':
					$nazev = mysql_real_escape_string(strip_gpc_slashes($post['Nazev_vlastnosti']));
					if ($nazev == '')
						throw new Exception('Některá z povinných položek nebyla vyplněna');
					$typ = mysql_real_escape_string($post['Charakter_vlastnosti']);
					if ($typ != 1 && $typ != 2)
						throw new Exception("Nekorektní vstup - charakter vlastnosti: $typ");

					$datatyp = mysql_real_escape_string($post['DataTyp_vlastnosti']);
					if ($datatyp != 1 && $datatyp != 2 && $datatyp != 3)
						throw new Exception("Nekorektní vstup - datový typ vlastnosti: $datatyp");

					if ($this->id)
						mysql_query("UPDATE prodprops SET Name='$nazev', Type=$typ, DataType=$datatyp, LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
					else
					{
						mysql_query("START TRANSACTION");
						$result = mysql_query("SELECT MAX(Priority) FROM prodprops");
						$row = mysql_fetch_row($result);
						$priority = (int)$row[0] + 1;

						mysql_query("INSERT INTO prodprops(Name, Type, DataType, Priority) VALUES ('$nazev', $typ, $datatyp, $priority)");
						$this->id = mysql_insert_id();
						mysql_query("COMMIT");
						$qs = "?pp=$this->id";
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$this->redirection = KIWI_EDIT_PRODUCT_PROPERTY . $qs;
					break;
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					mysql_query("UPDATE prodpvals SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_EDIT_PRODUCT_PROPERTY . $qs;
					break;
				case 'neuer Wert hinzufügen':
					$this->redirection = KIWI_ADD_PRODUCT_PROPERTY_VALUE . $qs;
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						$this->deletePictureFiles($id_list);
						$this->deleteIcons($id_list);
						mysql_query("DELETE FROM prodpbinds WHERE PPVID IN ($id_list)");
						mysql_query("DELETE FROM prodpvals WHERE ID IN ($id_list)");
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$this->redirection = KIWI_EDIT_PRODUCT_PROPERTY . $qs;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function deleteIcons($ids)
	{
		$result = mysql_query("SELECT ExtraData FROM prodpvals WHERE ID IN ($ids)");
		while ($row = mysql_fetch_row($result))
			if ($row[0] !== '' && $this->isValidIconFile($row[0]))
			{
				if (!(unlink(KIWI_DIR_PRODUCTS . 'properties/icons/' . $row[0])))
				{
					//throw new Exception("Nepodařilo se smazat soubor s ikonou");
				}				
			}
	}

	protected function isValidIconFile($filename)
	{
		return preg_match('/.+\.(jpg|gif|png)/i', $filename) != 0;
	}

	protected function deletePictureFiles($id_list)
	{
		if ($id_list == '') return;

		$result = mysql_query("SELECT Photo FROM prodpbinds WHERE PPVID IN ($id_list)");
		while ($row = mysql_fetch_row($result))
			if ($row[0] != '') $this->deleteProductFile($row[0], array('detail', 'catalog', 'catalog2', 'collection'));
	}

	protected function deleteProductFile($filename, $thumbsloc)
	{
		global $eshop_picture_config;
		$dir = KIWI_DIR_PRODUCTS;

		if (!(unlink("{$dir}photo/$filename")))
			throw new Exception("Nepodařilo se smazat soubor s fotografií");

		foreach ($thumbsloc as $loc)
		{
			if (!array_key_exists($loc, $eshop_picture_config))
				throw new Exception('Neznámá lokace miniatury fotografie');
			if (is_array($eshop_picture_config[$loc]))
				if (!(unlink("{$dir}$loc/$filename")))
					throw new Exception("Nepodařilo se smazat soubor s miniaturou fotografie");
		}
	}

	protected function loadRecord()
	{
		if ($this->record == null && $this->id)
		{
			$result = mysql_query("SELECT ID, Name, Type, DataType, Active, LastChange FROM prodprops WHERE ID=$this->id");
			$row = mysql_fetch_object($result);
			$this->record = new Kiwi_ProductProperty($row);
			$this->loadValues();
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->id)
		{
			if ($this->lastchange == null)
				$this->lastchange = new Kiwi_LastChange(array('prodprops', $this->id), 'j.n. Y - H:i');
			if ($acquire)
				$this->lastchange->acquire();
		}
	}

	protected function loadValues()
	{
		if ($this->values == null)
		{
			$this->values = array();
			$i = 0;
			foreach ($this->record->Values as $value)
			{
				$this->values[$i] = $value;
				$this->index[(int)$value->ID] = $i;
				$i++;
			}
		}
	}

	protected function moveValue($pvid, $down = true, $totally = false)
	{
		$mvals = array
		(
			false => array('Min', '>', -1),
			true => array('Max', '<', +1)
		);

		mysql_query("START TRANSACTION");

		if ($totally)
		{
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM prodpvals WHERE PID=$this->id AND ID!=$pvid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE prodpvals SET Priority=$newpri WHERE ID=$pvid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM prodpvals WHERE ID=$pvid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM prodpvals WHERE PID=$this->id AND Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE prodpvals SET Priority=$priority WHERE PID=$this->id AND Priority=$neigh");
				mysql_query("UPDATE prodpvals SET Priority=$neigh WHERE ID=$pvid");
			}
		}

		mysql_query("COMMIT");
	}
}
?>