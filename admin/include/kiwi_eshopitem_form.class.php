<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_eshopitem.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_anchor.class.php';
require_once 'kiwi_url_generator.class.php';
require_once 'kiwi_eshop_indexer.class.php';

require_once 'upload.class.php';

class Kiwi_EShopItem_Form extends Page_Item
{
	protected $rights;
	protected $read_only;
	protected $all_checked;
	protected $id;
	protected $title;
	protected $auto;
	protected $url;
	protected $htitle;
	protected $description;
	protected $icon;
	protected $flags;
	protected $parent;
	protected $record;
	protected $products;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $anchor;
	protected $grouped_product;

	const FLAG_FRONTMENU = 1;

	public function __construct(&$rights)
	{
		parent::__construct();

		$this->rights = $rights->EShop;
		if (is_array($this->rights))
			$this->read_only = !$this->rights['Write'];
		else $this->read_only = !$this->rights;

		$this->all_checked = false;
		$this->id = 0;
		$this->title = null;
		$this->auto = true;
		$this->url = null;
		$this->htitle = null;
		$this->description = null;
		$this->icon = null;
		$this->flags = null;
		$this->parent = null;
		$this->record = null;
		$this->products = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->anchor = new CurrentKiwiAnchor();
		$this->grouped_product = null;
	}

	public function _getHTML()
	{
		$qs = $this->consQS();
		if ($this->id)
		{
			$this->loadRecord();

			$tname = $name = htmlspecialchars($this->record->Name);
			$url = htmlspecialchars($this->url);
			$htitle = htmlspecialchars($this->htitle);
			$description = str_replace("\r\n", "\r", htmlspecialchars($this->description));
			$frontmenu_checked_str = $this->flags & self::FLAG_FRONTMENU ? ' checked' : '';
			$dt = parseDateTime($this->record->LastChange);
			$lastchange = date('j.n. Y - H:i', $dt['stamp']);
		}
		else
		{
			$name = '';
			$tname = 'neu';
			$url = '';
			$htitle = '';
			$description = '';
			$frontmenu_checked_str = '';
			$lastchange = null;
		}

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$readonly_str2 = ' disabled';
			$readonly_str3 = 'D';
			$onchange_str = '';
			$onchange_str3 = '';
			$ro_disabled_str = ' disabled';
			$D_str = 'D';
		}
		else
		{
			$readonly_str = $ro_disabled_str = $D_str = '';
			$readonly_str2 = '';
			$readonly_str3 = '';
			$onchange_str = ' onchange="Kiwi_EShopItem_Form.onChange()" onkeydown="return Kiwi_EShopItem_Form.onKeyDown(event)"';
			$onchange_str3 = ' onchange="Kiwi_EShopItem_Form.onChangeAuto()" onkeydown="Kiwi_EShopItem_Form.onKeyDownAuto(event)" onclick="Kiwi_EShopItem_Form.onChangeAuto()"';
		}

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form enctype="multipart/form-data" action="$self$qs" method="post">
	<h2>[Serie] - $tname - [editieren]</h2>
	<div class="levyV">
		<div class="form3">
			<fieldset>

EOT;

		if ($this->rights === true || $this->rights['EditURLs'])
		{
			if ($this->auto)
			{
				$checked_str = ' checked';
				if ($readonly_str == '')
					$disabled_str = ' disabled';
				else
					$disabled_str = '';
			}
			else
			{
				$checked_str = '';
				$disabled_str = '';
			}
		
			$ue_html = <<<EOT

					<tr>
						<td><span class="span-form2">Automatische URL und Titel :</span></td>
						<td colspan="2"><input type="checkbox" id="keifc_auto" name="Auto"$onchange_str3$readonly_str$checked_str /></td>
					</tr>
					<tr>
						<td><span class="span-form2">Serie URL :</span></td>
						<td><input type="text" id="keifc_url" name="URL_rady" value="$url" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str$disabled_str /></td>
					</tr>
					<tr>
						<td><span class="span-form2">HTML-Titel :</span></td>
						<td><input type="text" id="keifc_htitle" name="htitle_rady" value="$htitle" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str$disabled_str /></td>
					</tr>
EOT;
		}
		else
			$ue_html = '';

		if ($lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $lastchange</div>

EOT;

		if ($this->parent == 0)
		{
			$back_disabled_str = ' disabled';
			$back_disabled_D = 'D';
		}
		else
		{
			$back_disabled_str = '';
			$back_disabled_D = '';
		}		

		$html .= <<<EOT
				<div id="frame2">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Bezeichnung :</span></td>
							<td><input type="text" id="keifc_name" name="Nazev" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Icon der Serie :</span></td>

EOT;

			if ($this->icon)
			{
				$iconimg = KIWI_DIR_GROUPICONS . $this->icon;
				$html .= <<<EOT
							<td><img src="$iconimg" alt="" /><input type="button" id="keifc_cmd9" name="removeIcon" value="entfernen" class="but3$readonly_str3" onclick="Kiwi_EShopItem_Form.onRemoveIcon('$self$qs')" $readonly_str2/></td>

EOT;
			}
			elseif ($this->read_only)
				$html .= <<<EOT
							<td>nicht verfügbar</td>

EOT;
			else
				$html .= <<<EOT
							<td><input type="file" id="keifc_upload1" name="upload1" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str /></td>

EOT;

			$html .= <<<EOT
						</tr>
						<tr>
							<td><span class="span-form2">Beschreibung der Serie :</span></td>
							<td><textarea type="text" id="keifc_desc" name="Popis" class="texarOUT" onfocus="this.className='texarON'" onblur="this.className='texarOUT'"$onchange_str$readonly_str>$description</textarea></td>
						</tr>$ue_html
						<tr>
							<td><span class="span-form2">Einführen im Hauptmenü :</span></td>
							<td colspan="2"><input type="checkbox" id="keifc_mainmenu" name="mainmenu_flag"$onchange_str$readonly_str$frontmenu_checked_str /></td>
						</tr>
					</table>
				</div>
				<input type="submit" id="keifc_cmd1" name="cmd" value="speichern" class="but3D" disabled onclick="return Kiwi_EShopItem_Form.onSave()"/>
				<input type="submit" id="keifc_cmd2" name="cmd" value="Zurück" class="but3$back_disabled_D"$back_disabled_str />
			</fieldset>
		</div>
	</div>

EOT;
		if ($this->id) // todo: Přidat práva - má právo vidět seznam produktů?
		{
			$this->loadLastChange();
			$this->loadProducts();

			$disabled_str = (sizeof($this->products) == 0 || $this->read_only)? ' disabled' : '';

			$all_checked_str = $this->all_checked ? ' checked' : '';

			$html .= <<<EOT
	<h2>[Posten] - $tname - [anschliessende Artikel]</h2>
	<div class="levyV">
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_EShopItem_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Artikel-Name</th>
					<th>Preis</th>
					<th><span title="Neu">N</span></th>
					<th><span title="Aktion">Ak</span></th>
					<th><span title="Rabatt">R</span></th>
					<th><span title="Ausferkauf">Au</span></th>
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

			foreach ($this->products as $product)
			{
				$i++;
				$checked_str = (isset($this->checked[$product->ID]) && $this->checked[$product->ID]) ? ' checked' : '';
				$disabled_str = $this->read_only ? ' disabled' : '';

				$title = htmlspecialchars($product->Title);

				$plink = KIWI_EDIT_PRODUCT . "?p=$product->PID&ei=$this->id";
				$anchor_str = ($this->anchor->ID == $product->ID) ? ' name="zmena"' : '';

				$new_cost = htmlspecialchars(sprintf("%01.2f", $product->NewCost));

			if ($product->Photo)
			{
				$photo = 'photoOK';
				$phototitle = 'Produkt hat Foto';
			}
			else
			{
				$photo = 'photoKO';
				$phototitle = 'Foto nicht verfügbar';
			}
			$photostr = <<<EOT
<img src="./image/$photo.gif" alt="" title="$phototitle" />
EOT;

				$novelty = $product->Novelty != 0 ? 'ja' : 'nein';
				$action = $product->Action != 0 ? 'ja' : 'nein';
				$discount = $product->Discount != 0 ? 'ja' : 'nein';
				$sellout = $product->Sellout != 0 ? 'ja' : 'nein';

				$dt = parseDateTime($product->LastChange);
				$lastchange = date('j.n.Y H:i', $dt['stamp']);

				$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$product->ID" onclick="Kiwi_EShopItem_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><a href="$plink"$anchor_str>$title</a></td>
					<td>$new_cost</td>

EOT;

				if (!$this->read_only)
					$html .= <<<EOT
					<td><a href="$self$qs&tn=$product->ID">$novelty</a></td>
					<td><a href="$self$qs&ta=$product->ID">$action</a></td>
					<td><a href="$self$qs&td=$product->ID">$discount</a></td>
					<td><a href="$self$qs&ts=$product->ID">$sellout</a></td>

EOT;
				else
					$html .= <<<EOT
					<td>$novelty</td>
					<td>$action</td>
					<td>$discount</td>
					<td>$sellout</td>

EOT;
				$html .= <<<EOT
					<td>$photostr</td>
					<td>$lastchange</td>

EOT;

				$pac = $product->Active;
				$active = $pac ? 'ja' : 'nein';

				if (!$this->read_only && ($this->record->Active || $pac))
				{
					$asqvs = $pac ? 'd' : 'a';
					$html .= <<<EOT
					<td><a href="$self$qs&as$asqvs=$product->ID">$active</a></td>

EOT;
				}
				else
					$html .= <<<EOT
					<td>$active</td>

EOT;

				if (!$this->read_only)
				{
					$nullimg = "<img src=\"./image/null.gif\" alt=\"\" title=\"\" width=\"18\" height=\"18\" />";
					$html .=
						"\t\t\t\t\t<td>" . ($i < sizeof($this->record->Products) - 1 ? "<a href=\"$self$qs&dd=$product->ID\"><img src=\"./image/alldown.gif\" alt=\"\" title=\"ganz unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i < sizeof($this->record->Products) ? "<a href=\"$self$qs&d=$product->ID\"><img src=\"./image/down.gif\" alt=\"\" title=\"unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 1 ? "<a href=\"$self$qs&u=$product->ID\"><img src=\"./image/up.gif\" alt=\"\" title=\"oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 2 ? "<a href=\"$self$qs&uu=$product->ID\"><img src=\"./image/allup.gif\" alt=\"\" title=\"ganz oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . "</td>\n";
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
			<input type="submit" id="keifc_cmd3" name="cmd" value="neuer Artikel hinzufügen" class="but4$D_str"$ro_disabled_str />

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

			if (!$this->record->Active)
			{
				$disabled_str3 =' disabled';
				$cmd68 = 8;
				$cmd79 = 9;
			}
			else
			{
				$disabled_str3 = $disabled_str;
				$cmd68 = 6;
				$cmd79 = 7;
			}

			$html .= <<<EOT
			<input type="submit" id="keifc_cmd4" name="cmd" value="vorhandene Artikel hinzufügen" class="but4$D_str"$ro_disabled_str />
			<input type="submit" id="keifc_cmd5" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_EShopItem_Form.onDelete()" />
			<input type="submit" id="keifc_cmd$cmd68" name="cmd" value="aktivieren" class="$but_class"$disabled_str3 />
			<input type="submit" id="keifc_cmd$cmd79" name="cmd" value="deaktivieren" class="$but_class"$disabled_str3 />
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
			if (isset($get['ei']))
			{
				if (($ei = (int)$get['ei']) < 1)
					throw new Exception("Neplatné ID záznamu: $ei");

				$this->id = $ei;
				$qs = "?ei=$this->id";
			}

			if (isset($get['sg']))
			{
				if (($this->parent = (int)$get['sg']) < 1)
					throw new Exception("Neplatné ID nadřazené skupiny: $this->parent");
			}

			if (isset($get['gp']))
			{
				if (($this->grouped_product = (int)$get['gp']) < 1)
					throw new Exception("Neplatné ID sdruženého produktu: $this->grouped_product");
				if (!$this->id)
					throw new Exception("V případě použití parametru gp je povinný i parametr ei");
				$qs .= '&gp=' . $this->grouped_product;
			}

			if (isset($get['ri']) && $this->id != 1)
			{
				$this->removeGroupIcon();
				$this->redirection = $self . $qs;
				return;
			}

			if (isset($get['asa']) || isset($get['asd']))
			{
				$nas = isset($get['asa']) ? 1 : 0;
				if ($nas && isset($get['asd']))
					throw new Exception("Současná přítomnost parametrů asa a asd není přípustná");

				$qsv = 'as' . ($nas ? 'a' : 'd');

				$this->loadRecord();

				if (($as = (int)$get[$qsv]) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				if ($nas && !$this->record->Active)
					throw new Exception("Pokud o nepřípustnou aktivaci záznamu: $as");

				mysql_query("UPDATE prodbinds SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

/*
				$this->products[$this->index[$as]]->Active = $nas;
				$this->products[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
*/
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $as;
				$this->redirection = KIWI_EDIT_ESHOPITEM . $qs . '#zmena';
			}

			if (isset($get['tn']))
			{
				$this->loadRecord();

				if (($tn = (int)$get['tn']) < 1 || !isset($this->index[$tn]))
					throw new Exception("Neplatné ID záznamu: $tn");

				$prod = $this->products[$this->index[$tn]];
				$ntn = !$prod->Novelty;

				mysql_query("UPDATE products SET Novelty='$ntn', LastChange=CURRENT_TIMESTAMP WHERE ID=$prod->PID");
				mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE ID=$tn");

				$this->products[$this->index[$tn]]->Novelty = $ntn;
				$this->products[$this->index[$tn]]->LastChange = date('Y-m-d H:i', time());
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $tn;
				$this->redirection = KIWI_EDIT_ESHOPITEM . $qs . '#zmena';
			}

			if (isset($get['ta']))
			{
				$this->loadRecord();

				if (($ta = (int)$get['ta']) < 1 || !isset($this->index[$ta]))
					throw new Exception("Neplatné ID záznamu: $ta");

				$prod = $this->products[$this->index[$ta]];
				$nta = !$prod->Action;

				mysql_query("UPDATE products SET Action='$nta', LastChange=CURRENT_TIMESTAMP WHERE ID=$prod->PID");
				mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE ID=$ta");

				$this->products[$this->index[$ta]]->Action = $nta;
				$this->products[$this->index[$ta]]->LastChange = date('Y-m-d H:i', time());
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $ta;
				$this->redirection = KIWI_EDIT_ESHOPITEM . $qs . '#zmena';
			}

			if (isset($get['td']))
			{
				$this->loadRecord();

				if (($td = (int)$get['td']) < 1 || !isset($this->index[$td]))
					throw new Exception("Neplatné ID záznamu: $td");

				$prod = $this->products[$this->index[$td]];
				$ntd = !$prod->Discount;

				mysql_query("UPDATE products SET Discount='$ntd', LastChange=CURRENT_TIMESTAMP WHERE ID=$prod->PID");
				mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE ID=$td");

				$this->products[$this->index[$td]]->Discount = $ntd;
				$this->products[$this->index[$td]]->LastChange = date('Y-m-d H:i', time());
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $td;
				$this->redirection = KIWI_EDIT_ESHOPITEM . $qs . '#zmena';
			}

			if (isset($get['ts']))
			{
				$this->loadRecord();

				if (($ts = (int)$get['ts']) < 1 || !isset($this->index[$ts]))
					throw new Exception("Neplatné ID záznamu: $ts");

				$prod = $this->products[$this->index[$ts]];
				$nts = !$prod->Sellout;

				mysql_query("UPDATE products SET Sellout='$nts', LastChange=CURRENT_TIMESTAMP WHERE ID=$prod->PID");
				mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE ID=$ts");

				$this->products[$this->index[$ts]]->Sellout = $nts;
				$this->products[$this->index[$ts]]->LastChange = date('Y-m-d H:i', time());
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $ts;
				$this->redirection = KIWI_EDIT_ESHOPITEM . $qs . '#zmena';
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

				$this->moveProduct($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;

				$this->anchor->ID = $cp;
				$this->redirection = KIWI_EDIT_ESHOPITEM . $qs . '#zmena';
			}
		}
		else throw new Exception("Chybějící ID nadřazené skupiny");

		if (!empty($post))
		{
			$xpost = strip_gpc_slashes($post);

			$this->all_checked = isset($xpost['checkall']);
			if (isset($xpost['check']) && is_array($xpost['check']))
				foreach ($xpost['check'] as $value)
				{
					if (!is_numeric($value)) throw new Exception("Nepovolený vstup: check[]");
					$this->checked[$value] = true;
				}

			$act = 0;
			switch ($xpost['cmd'])
			{
				case 'speichern':
					$this->handleUploads();
					$this->title = $xpost['Nazev'];
					if ($this->title == '') throw new Exception('Název řady nebyl vyplněn');
					$this->description = $xpost['Popis'];
					$this->auto = array_key_exists('Auto', $xpost);
					if (array_key_exists('URL_rady', $xpost))
						$this->url = $xpost['URL_rady'];
					if (array_key_exists('htitle_rady', $xpost))
						$this->htitle = $xpost['htitle_rady'];

					$ue = $this->rights === true || $this->rights['EditURLs'];

					$flds = array('title', 'description', 'icon', 'flags');
					if ($ue || !$this->id)
					{
						$flds[] = 'url';
						$flds[] = 'htitle';		

						if ($this->auto || !$this->id && !$ue)
						{
							$this->generateURL();
							$this->generateTitle();
						}
					}

					$this->flags = (int)array_key_exists('mainmenu_flag', $xpost);

					foreach ($flds as $fld)
						$$fld = mysql_real_escape_string($this->$fld);

					if ($this->id)
					{
						$ue_sql = $ue ? ", URL='$url', PageTitle='$htitle'" : '';	
						$icon_sql = $icon !== '' ? ", Icon='$icon'" : '';
						mysql_query("UPDATE eshop SET Name='$title', Description='$description'$ue_sql$icon_sql, Flags=$flags, LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
					}
					else // vytvoření nové řady
					{
						if ($this->parent == null) throw new Exception("Chybějící ID nadřazené skupiny");
						$result = mysql_query("SELECT Count(ID) FROM eshop WHERE ID=$this->parent AND Subgroup=1");
						$row = mysql_fetch_row($result);
						if ($row[0] != 1) throw new Exception("Neplatné ID nadřazené skupiny");

						$result = mysql_query("SELECT MAX(Priority) FROM eshop WHERE Parent=$this->parent");
						$row = mysql_fetch_row($result);
						$priority = (int)$row[0] + 1;

						if ($ue)
						{
							$ue_sql1 = ', URL, PageTitle';
							$ue_sql2 = ",'$url', '$htitle'";
						}
						else
							$ue_sql1 = $ue_sql2 = '';

						if ($icon)
						{
							$icon_sql1 = ', Icon';
							$icon_sql2 = ",'$icon'";
						}
						else
							$icon_sql1 = $icon_sql2 = '';

						mysql_query("INSERT INTO eshop(Name, Description$ue_sql1$icon_sql1, Parent, Flags, Priority) VALUES ('$title', '$description'$ue_sql2$icon_sql2, $this->parent, $flags, $priority)");
						$this->id = mysql_insert_id();
						Kiwi_EShop_Indexer::index($this->id, $this->parent);
						$qs = "?ei=$this->id";
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$this->redirection = KIWI_EDIT_ESHOPITEM . $qs;
					break;
				case 'Zurück':
					$this->loadRecord();
					$this->redirection = $this->getBackLink();
					break;
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$this->loadRecord();
					if ($act && !$this->record->Active)
						throw new Exception("Pokud o nepřípustnou aktivaci záznamů");
					$id_list = implode(',', $post['check']);
					if ($id_list)
						mysql_query("UPDATE prodbinds SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = KIWI_EDIT_ESHOPITEM . $qs;
					break;
				case 'neuer Artikel hinzufügen':
					$this->redirection = KIWI_ADD_PRODUCT . $qs;
					break;
				case 'vorhandene Artikel hinzufügen':
					$this->redirection = KIWI_ADD_EXISTING_PRODUCT . $qs;
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						mysql_query("DELETE FROM prodbinds WHERE ID IN ($id_list)");
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$this->redirection = KIWI_EDIT_ESHOPITEM . $qs;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function handleUploads()
	{
		//$h = fopen('./logs/upload.log.html', 'a');

		if (!$_FILES['upload1']['error'])
		{
			$up1 = $this->handleUpload('upload1');
			if ($up1->upload())
				$this->icon = $up1->file_copy;
			//fwrite($h, $error_string);
		}

		//fclose($h);
	}

	protected function handleUpload($upload)
	{
		$fu = new file_upload;
		$fu->upload_dir = KIWI_DIR_GROUPICONS;
		$fu->extensions = array('.jpg', '.png', '.gif');
		$fu->language = 'de';
		$fu->max_length_filename = 32;
		$fu->rename_file = true;
		$fu->the_temp_file = $_FILES[$upload]['tmp_name'];
		$fu->the_file = $_FILES[$upload]['name'];
		$fu->http_error = $_FILES[$upload]['error'];
		$fu->replace = "n";
		$fu->do_filename_check = "y";
		return $fu;
	}

	protected function removeGroupIcon()
	{
		if (!$this->id) throw new Exception('Pokus odstranit ikonu nespecifikované produktové řady');
		$this->loadRecord();
		if ($this->icon)
		{
			if (!(unlink(KIWI_DIR_GROUPICONS . $this->icon)))
			{
				//throw new Exception("Nepodařilo se smazat soubor s ikonou");
			}
			else
				mysql_query("UPDATE eshop SET Icon='', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
		}
		else throw new Exception('Pokus odstranit ikonu neexistující produktuvé řady');
	}

	protected function generateURL()
	{
		$this->url = Kiwi_URL_Generator::generate($this->title);
	}

	protected function generateTitle()
	{
		$this->htitle = $this->title;
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->id)
		{
			if ($this->lastchange == null)
				$this->lastchange = new Kiwi_LastChange(array('eshopitems', $this->id), 'j.n. Y - H:i');
			if ($acquire)
				$this->lastchange->acquire();
		}
	}

	protected function loadRecord()
	{
		if ($this->record == null && $this->id)
		{
			$result = mysql_query("SELECT ID, Name, URL, PageTitle, Description, Icon, Subgroup, Parent, Flags, Active, LastChange FROM eshop WHERE ID=$this->id");
			$row = mysql_fetch_object($result);
			$this->record = new Kiwi_EShopItem($row);
			$this->title = $this->record->Name;
			$this->auto = $this->record->URL == '';
			$this->url = $this->record->URL;
			$this->htitle = $this->record->PageTitle;
			$this->description = $this->record->Description;
			$this->icon = $this->record->Icon;
			$this->parent = $this->record->Parent;
			$this->flags = $this->record->Flags;

			$this->loadProducts();
		}
	}

	protected function loadProducts()
	{
		if ($this->products == null)
		{
			$this->products = array();
			$i = 0;
			foreach ($this->record->Products as $product)
			{
				$this->products[$i] = $product;
				$this->index[(int)$product->ID] = $i;
				$i++;
			}
		}
	}

	protected function moveProduct($pbid, $down = true, $totally = false)
	{
		$mvals = array
		(
			false => array('Min', '>', -1),
			true => array('Max', '<', +1)
		);

		mysql_query("START TRANSACTION");

		if ($totally)
		{
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM prodbinds WHERE GID=$this->id AND ID!=$pbid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE prodbinds SET Priority=$newpri WHERE ID=$pbid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM prodbinds WHERE ID=$pbid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM prodbinds WHERE GID=$this->id AND Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE prodbinds SET Priority=$priority WHERE GID=$this->id AND Priority=$neigh");
				mysql_query("UPDATE prodbinds SET Priority=$neigh WHERE ID=$pbid");
			}
		}

		mysql_query("COMMIT");
	}

	protected function getBackLink()
	{
		if ($this->grouped_product !== null)
			$link =	KIWI_EDIT_PRODUCT . "?p=$this->grouped_product";
		elseif ($this->parent == 0)
			$link = null;
		else
			$link = KIWI_ESHOP . '?sg=' . $this->parent;

		return $link;
	}

	protected function consQS()
	{
		$qsa = array();

		if ($this->id)
			$qsa[] = "ei=$this->id";
		elseif ($this->parent !== null)
			$qsa[] = "sg=$this->parent";

		if ($this->grouped_product !== null)
			$qsa[] = "gp=$this->grouped_product";

		if (empty($qsa)) $qs = '';
		else $qs = '?' . implode('&', $qsa);

		return $qs;
	}
}
?>