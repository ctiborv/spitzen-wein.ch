<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_anchor.class.php';

class Kiwi_ProductProperties_Form extends Page_Item
{
	protected $all_checked;
	protected $properties;
	protected $index;
	protected $checked;
	protected $checked_count;
	protected $lastchange;
	protected $anchor;

	public function __construct()
	{
		parent::__construct();
		$this->all_checked = false;
		$this->properties = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->anchor = new CurrentKiwiAnchor();
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadProperties();

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form action="$self" method="post">
	<h2>KATALOG - Produkt-Eigenschaften - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$disabled_str = sizeof($this->properties) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

    $html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_ProductProperties_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Eigenschaft</th>
					<th>geändert</th>
					<th>aktiv</th>
					<th>Priorität</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);
		$i = 0;

		foreach ($this->properties as $property)
		{
			$i++;
			$checked_str = (isset($this->checked[$property->ID]) && $this->checked[$property->ID]) ? ' checked' : '';

 			$name = htmlspecialchars($property->Name);

			$plink = KIWI_EDIT_PRODUCT_PROPERTY . "?pp=$property->ID";
			$anchor_str = ($this->anchor->ID == $property->ID) ? ' name="zmena"' : '';

		  $dt = parseDateTime($property->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$active = $property->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$property->ID" onclick="Kiwi_ProductProperties_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$plink"$anchor_str>$name</a></td>
					<td>$lastchange</td>
					<td><a href="$self?as=$property->ID">$active</a></td>

EOT;

				$nullimg = <<<EOT
<img src="./image/null.gif" alt="" title="" width="18" height="18" />
EOT;
				$html .= "\t\t\t\t\t<td>" . ($i < sizeof($this->properties) - 1 ? "<a href=\"$self?dd=$property->ID\"><img src=\"./image/alldown.gif\" alt=\"\" title=\"ganz unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i < sizeof($this->properties) ? "<a href=\"$self?d=$property->ID\"><img src=\"./image/down.gif\" alt=\"\" title=\"unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 1 ? "<a href=\"$self?u=$property->ID\"><img src=\"./image/up.gif\" alt=\"\" title=\"oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 2 ? "<a href=\"$self?uu=$property->ID\"><img src=\"./image/allup.gif\" alt=\"\" title=\"ganz oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . "</td>\n\t\t\t\t</tr>\n";

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

  	$html .= <<<EOT
			<input type="submit" id="kppfc_cmd1" name="cmd" value="Eigenschaft hinzufügen" class="but3" />

EOT;
		$html .= <<<EOT
			<input type="submit" id="kppfc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_ProductProperties_Form.onDelete()" />
			<input type="submit" id="kppfc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kppfc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
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
			if (isset($get['as']))
			{
				$this->loadLastChange();
				$this->loadProperties();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->properties[$this->index[$as]]->Active;

				mysql_query("UPDATE prodprops SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

				$this->properties[$this->index[$as]]->Active = $nas;
				$this->properties[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $as;
				$this->redirection = KIWI_PRODUCT_PROPERTIES . '#zmena';
			}

			if (isset($get['d']) || isset($get['dd']) || isset($get['u']) || isset($get['uu']))
			{
				if ((int)isset($get['d']) + (int)isset($get['dd']) + (int)isset($get['u']) + (int)isset($get['uu']) != 1)
					throw new Exception("Neplatný vstup - více než jeden příkaz pro přesun položky");

				$dow = isset($get['d']) || isset($get['dd']);
				$tot = isset($get['dd']) || isset($get['uu']);

				$qv = $dow ? 'd' : 'u';
				if ($tot) $qv .= $qv;

				$this->loadProperties();

				if (($cp = (int)$get[$qv]) < 1 || !isset($this->index[$cp]))
					throw new Exception("Neplatné ID záznamu: $cp");

				$this->moveProduct($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;

				$this->anchor->ID = $cp;
				$this->redirection = KIWI_PRODUCT_PROPERTIES . '#zmena';
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
						mysql_query("UPDATE prodprops SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_PRODUCT_PROPERTIES;
					break;
				case 'Eigenschaft hinzufügen':
					$this->redirection = KIWI_ADD_PRODUCT_PROPERTY;
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						$this->deletePictureFiles($id_list);
						$this->deleteIcons($id_list);
						mysql_query("UPDATE products SET LastChange=CURRENT_TIMESTAMP WHERE ID IN (SELECT PID FROM prodpbinds WHERE PPVID IN (SELECT ID FROM prodpvals WHERE PID IN ($id_list)))");
						mysql_query("DELETE FROM prodpbinds WHERE PPVID IN (SELECT ID FROM prodpvals WHERE PID IN ($id_list))");
						mysql_query("DELETE FROM prodpvals WHERE PID IN ($id_list)");
						mysql_query("DELETE FROM prodprops WHERE ID IN ($id_list)");

						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$this->redirection = KIWI_PRODUCT_PROPERTIES;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function deleteIcons($pids)
	{
		$result = mysql_query("SELECT ExtraData FROM prodpvals WHERE PID IN ($pids)");
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

		$result = mysql_query("SELECT Photo FROM prodpbinds WHERE PPVID IN (SELECT ID FROM prodpvals WHERE PID IN ($id_list))");
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

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('prodprops', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadProperties()
	{
		if ($this->properties == null)
		{
			$this->properties = array();
			if ($result = mysql_query("SELECT ID, Name, Active, LastChange FROM prodprops ORDER BY Priority"))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->properties[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
				}
			}
		}
	}

	protected function moveProduct($ppid, $down = true, $totally = false)
	{
		$mvals = array
		(
			false => array('Min', '>', -1),
			true => array('Max', '<', +1)
		);

		mysql_query("LOCK TABLES prodprops WRITE");

		if ($totally)
		{
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM prodprops WHERE ID!=$ppid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE prodprops SET Priority=$newpri WHERE ID=$ppid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM prodprops WHERE ID=$ppid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM prodprops WHERE Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE prodprops SET Priority=$priority WHERE Priority=$neigh");
				mysql_query("UPDATE prodprops SET Priority=$neigh WHERE ID=$ppid");
			}
		}

		mysql_query("UNLOCK TABLES");
	}
}
?>