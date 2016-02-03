<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_eshopgroup.class.php';
require_once 'kiwi_eshopitem.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_anchor.class.php';
require_once 'kiwi_url_generator.class.php';
require_once 'kiwi_eshop_indexer.class.php';

require_once 'upload.class.php';

class Kiwi_EShop_Form extends Page_Item
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
	protected $record;
	protected $records;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $new_subgroup;
	protected $anchor;

	const FLAG_FRONTMENU = 1;
	const FLAG_NODELETE = 2;

	const FLAGS_DEFAULT = 0;

	public function __construct(&$rights)
	{
		parent::__construct();

		$this->rights = $rights->EShop;
		if (is_array($this->rights))
			$this->read_only = !$this->rights['Write'];
		else $this->read_only = !$this->rights;
		$this->all_checked = false;
		$this->id = 1;
		$this->title = null;
		$this->auto = true;
		$this->url = null;
		$this->htitle = null;
		$this->description = null;
		$this->icon = null;
		$this->flags = self::FLAGS_DEFAULT;
		$this->record = null;
		$this->records = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->new_subgroup = false;
		$this->anchor = new CurrentKiwiAnchor();
	}

	public function _getHTML()
	{
		$qs = "?sg=$this->id";
		$tname = $lastchange = null;

		$this->loadRecord();
		if (!$this->new_subgroup)
		{
			$this->loadEShopItems();

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
			$url = '';
			$htitle = '';
			$description = '';
			$frontmenu_checked_str = '';
			$tname = 'neu';
			$qs .= '&n';
		}

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$readonly_str2 = ' disabled';
			$readonly_str3 = 'D';
			$onchange_str = '';
			$onchange_str3 = '';
		}
		else
		{
			$readonly_str = '';
			$readonly_str2 = '';
			$readonly_str3 = '';
			$onchange_str = ' onchange="return Kiwi_EShop_Form.onChange()" onkeydown="return Kiwi_EShop_Form.onKeyDown(event)"';
			$onchange_str3 = ' onchange="Kiwi_EShop_Form.onChangeAuto()" onkeydown="Kiwi_EShop_Form.onKeyDownAuto(event)" onclick="Kiwi_EShop_Form.onChangeAuto()"';
		}

		$self = basename($_SERVER['PHP_SELF']);

		$html = <<<EOT
<form enctype="multipart/form-data" action="$self$qs" method="post">

EOT;

		if ($this->new_subgroup || $this->id > 1)
		{
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
							<td colspan="2"><input type="checkbox" id="kesfc_auto" name="Auto"$onchange_str3$readonly_str$checked_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Gruppe/Serie URL :</span></td>
							<td><input type="text" id="kesfc_url" name="URL_skupiny" value="$url" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str$disabled_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Header Titelseite :</span></td>
							<td><input type="text" id="kesfc_htitle" name="htitle_skupiny" value="$htitle" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str$disabled_str /></td>
						</tr>
EOT;
			}
			else
				$ue_html = '';

			$html .= <<<EOT
	<h2>[Gruppe] - $tname - [editieren]</h2>
	<div class="levyV">
		<div class="form3">
			<fieldset>

EOT;
			if ($lastchange != null)
				$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $lastchange</div>

EOT;

			if ($this->record->Parent == 0)
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
							<td><input type="text" id="kesfc_name" name="Nazev" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Ikona skupiny :</span></td>

EOT;

			if ($this->icon)
			{
				$iconimg = KIWI_DIR_GROUPICONS . $this->icon;
				$html .= <<<EOT
							<td><img src="$iconimg" alt="" /><input type="button" id="kesfc_cmd9" name="removeIcon" value="entfernen" class="but3$readonly_str3" onclick="Kiwi_EShop_Form.onRemoveIcon('$self$qs')" $readonly_str2/></td>

EOT;
			}
			elseif ($this->read_only)
				$html .= <<<EOT
							<td>není k dispozici</td>

EOT;
			else
				$html .= <<<EOT
							<td><input type="file" id="kesfc_upload1" name="upload1" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str /></td>

EOT;

			$html .= <<<EOT
						</tr>
						<tr>
							<td><span class="span-form2">Beschreibung der Gruppe :</span></td>
							<td><textarea type="text" id="kesfc_desc" name="Popis" class="texarOUT" onfocus="this.className='texarON'" onblur="this.className='texarOUT'"$onchange_str$readonly_str>$description</textarea></td>
						</tr>$ue_html
						<tr>
							<td><span class="span-form2">Einführen im Hauptmenü :</span></td>
							<td colspan="2"><input type="checkbox" id="kesfc_mainmenu" name="mainmenu_flag"$onchange_str$readonly_str$frontmenu_checked_str /></td>
						</tr>
					</table>
				</div>
				<input type="submit" id="kesfc_cmd1" name="cmd" value="speichern" class="but3D" disabled onclick="return Kiwi_EShop_Form.onSave()"/>
				<input type="submit" id="keifc_cmd8" name="cmd" value="Zurück" class="but3$back_disabled_D"$back_disabled_str />
			</fieldset>
		</div>
	</div>

EOT;
		}

		if (!$this->new_subgroup) // todo: Přidat práva - má právo vidět obsah?
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
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_EShop_Form.enableBtns(false);"$all_checked_str /></th>
					<th><img src="./image/null.gif" width="16" height="0" />&nbsp;Serie / Gruppe</th>
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
				$anchor_str = ($this->anchor->ID == $record->ID) ? ' name="zmena"' : '';

				if ($record->Subgroup)
				{
					$icon = 'slozka.gif';
					$icontitle = 'Gruppe';
					$link = KIWI_ESHOP . "?sg=$record->ID";
				}
				else
				{
					$icon = 'rada.gif';
					$icontitle = 'Serie';
					$link = KIWI_ESHOPITEM . "?ei=$record->ID";
				}

				$dt = parseDateTime($record->LastChange);
				$lastchange = date('j.n.Y H:i', $dt['stamp']);

				$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$record->ID" onclick="Kiwi_EShop_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><img src="./image/$icon" alt="" title="$icontitle" />&nbsp;<a href="$link"$anchor_str>$name</a></td>
					<td>$lastchange</td>

EOT;

				$rac = $record->Active;
				$active = $rac ? 'ja' : 'nein';

				if (!$this->read_only && ($this->record->Active || $rac))
				{
					$asqvs = $rac ? 'd' : 'a';
					$html .= <<<EOT
					<td><a href="$self$qs&as$asqvs=$record->ID">$active</a></td>

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

			if (!$this->record->Active)
			{
				$disabled_str3 =' disabled';
				$cmd57 = 7;
				$cmd68 = 8;
			}
			else
			{
				$disabled_str3 = $disabled_str;
				$cmd57 = 5;
				$cmd68 = 6;
			}

			$html .= <<<EOT
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>
			<input type="submit" id="kesfc_cmd3" name="cmd" value="Gruppe hinzufügen" class="$but_class2"$disabled_str2 />
			<input type="submit" id="kesfc_cmd2" name="cmd" value="Serie hinzufügen" class="$but_class2"$disabled_str2 />
			<input type="submit" id="kesfc_cmd4" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_EShop_Form.onDelete()" />
			<input type="submit" id="kesfc_cmd$cmd57" name="cmd" value="aktivieren" class="$but_class"$disabled_str3 />
			<input type="submit" id="kesfc_cmd$cmd68" name="cmd" value="deaktivieren" class="$but_class"$disabled_str3 />
		</fieldset>
	</div>

EOT;
		}

		$html .=
			"</form>\n";

		return $html;
	}

	public function handleInput($get, $post)
	{
		// todo: ohlidat prava

		$self = basename($_SERVER['PHP_SELF']);
		$qs = '';

		if (!empty($get))
		{
			if (isset($get['sg']))
			{
				if (($sg = (int)$get['sg']) < 1)
					throw new Exception("Neplatné ID záznamu: $sg");

				$this->id = $sg;
				$qs = "?sg=$sg";
			}

			if (isset($get['n']))
			{
				$result = mysql_query("SELECT Count(*) FROM eshop WHERE ID=$this->id AND Subgroup=1");
				if ($row = mysql_fetch_row($result))
					if ($row[0] == 1) $this->new_subgroup = true;

				if (!$this->new_subgroup) throw new Exception("Neplatné ID záznamu: $this->id");
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
				$this->loadEShopItems();

				if (($as = (int)$get[$qsv]) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				if ($nas && !$this->record->Active)
					throw new Exception("Pokud o nepřípustnou aktivaci záznamu: $as");

				$id_list_rec = implode(',', $this->getRecursiveIdList(array($as)));
				$this->activateGroupsAndLines($id_list_rec, $nas);
				
/*
				$this->records[$this->index[$as]]->Active = $nas;
				$this->records[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
*/
				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $as;
				$this->redirection = $self . $qs . '#zmena';
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
				$this->loadEShopItems();

				if (($cp = (int)$get[$qv]) < 1 || !isset($this->index[$cp]))
					throw new Exception("Neplatné ID záznamu: $cp");

				$this->moveItem($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $cp;
				$this->redirection = $self . $qs . '#zmena';
			}
		}

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
					if ($this->title == '') throw new Exception('Název skupiny nebyl vyplněn');
					$this->description = $xpost['Popis'];
					$this->auto = array_key_exists('Auto', $xpost);
					if (array_key_exists('URL_skupiny', $xpost))
						$this->url = $xpost['URL_skupiny'];
					if (array_key_exists('htitle_skupiny', $xpost))
						$this->htitle = $xpost['htitle_skupiny'];

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

					$this->flags = array_key_exists('mainmenu_flag', $xpost) ? ($this->flags | self::FLAG_FRONTMENU) : ($this->flags & ~self::FLAG_FRONTMENU);

					foreach ($flds as $fld)
						$$fld = mysql_real_escape_string($this->$fld);

					if (!$this->id) throw new Exception("Neplatné ID záznamu: $this->id");
					if (!$this->new_subgroup) // úprava obsahu
					{
						if ($this->id == 1) throw new Exception('Neplatná operace: editace nastavení hlavní skupiny');
						else
						{
							$ue_sql = $ue ? ", URL='$url', PageTitle='$htitle'" : '';
							$icon_sql = $icon !== '' ? ", Icon='$icon'" : '';
							mysql_query("UPDATE eshop SET Name='$title', Description='$description'$ue_sql$icon_sql, Flags=$flags, LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
						}
					}
					elseif ($title != '') // vytvoření nové skupiny
					{
						$result = mysql_query("SELECT Max(Priority) FROM eshop WHERE Parent=$this->id");
						if ($row = mysql_fetch_row($result))
							$priority = (int)$row[0] + 1;
						else
							throw new Exception("Chyba při načítání priority položek eshopu");

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

						mysql_query("INSERT INTO eshop(Name, Description$ue_sql1$icon_sql1, Subgroup, Parent, Flags, Priority) VALUES ('$title', '$description'$ue_sql2$icon_sql2, 1, $this->id, $flags, $priority)");
						$new_id = mysql_insert_id();
						Kiwi_EShop_Indexer::index($new_id, $this->id);
						$qs = "?sg=$new_id";
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$this->redirection = $self . $qs;
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
					$id_list_rec = implode(',', $this->getRecursiveIdList($xpost['check']));
					$this->activateGroupsAndLines($id_list_rec, $act);
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$this->redirection = $self . $qs;
					break;
				case 'Serie hinzufügen':
					$this->redirection = KIWI_ADD_ESHOPITEM . "?sg=$this->id";
					break;
				case 'Gruppe hinzufügen':
					$this->redirection = KIWI_ADD_ESHOPGROUP . "?sg=$this->id&n";
					break;
				case 'entfernen':
					if (sizeof($xpost['check']) > 0)
					{
						$id_list_rec = implode(',', $this->getRecursiveIdList($xpost['check']));
						$this->deleteGroupsAndLines($id_list_rec);
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->redirection = $self . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function handleUploads()
	{
		//$h = fopen('./logs/upload.log.html', 'a');

		if (isset($_FILES['upload1']) && !$_FILES['upload1']['error'])
		{
			$up1 = $this->handleUpload('upload1');
			if ($up1->upload())
				$this->icon = $up1->file_copy;
			else
			{
				throw new Exception($up1->show_error_string());
			}

			//$error_string = $up1->show_error_string();
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
		if (!$this->id) throw new Exception('Pokus odstranit ikonu nespecifikované produktové skupiny');
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
		else throw new Exception('Pokus odstranit ikonu neexistující produktové skupiny');
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
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange(array('eshop', $this->id), 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadRecord()
	{
		$result = mysql_query("SELECT ID, Name, URL, PageTitle, Description, Icon, Subgroup, Parent, Flags, Active, LastChange FROM eshop WHERE ID=$this->id");
		if ($row = mysql_fetch_object($result))
		{
			$this->record = new Kiwi_EShopGroup($row);
			$this->title = $this->record->Name;
			$this->auto = $this->record->URL == '';
			$this->url = $this->record->URL;
			$this->htitle = $this->record->PageTitle;
			$this->description = $this->record->Description;
			$this->icon = $this->record->Icon;
			$this->flags = $this->record->Flags;
		}
		else throw new Exception("Neplatné ID záznamu: $this->id");
	}

	protected function loadEShopItems()
	{
		if ($this->records == null)
		{
			$this->records = array();
			$i = 0;
			foreach ($this->record->EShopItems as $item)
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
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM eshop WHERE Parent=$this->id AND ID!=$iid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE eshop SET Priority=$newpri WHERE ID=$iid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM eshop WHERE ID=$iid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM eshop WHERE Parent=$this->id AND Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE eshop SET Priority=$priority WHERE Parent=$this->id AND Priority=$neigh");
				mysql_query("UPDATE eshop SET Priority=$neigh WHERE ID=$iid");
			}
		}

		mysql_query("COMMIT");
	}

	protected function getRecursiveIdList($id_list)
	{
		// doplni seznam ID o vsechna ID ze vsech obsazenych podskupin
		if (!is_array($id_list)) throw Exception();
		if (sizeof($id_list) > 0)
		{
			$ids = implode(',', $id_list);
			$result = mysql_query("SELECT ID FROM eshop WHERE ID IN ($ids) AND Subgroup=1");
			$newids = array();
			while ($row = mysql_fetch_row($result))
			{
				$res2 = mysql_query("SELECT ID FROM eshop WHERE Parent={$row[0]}");
				while ($row2 = mysql_fetch_row($res2))
					$newids[] = $row2[0];
				mysql_free_result($res2);
			}
			mysql_free_result($result);
			$newids_rec = $this->getRecursiveIdList($newids);
			$id_list = array_merge($id_list, $newids_rec);
		}
		return $id_list;
	}

	protected function deleteIcons($ids)
	{
		$nodelete = self::FLAG_NODELETE;
		$result = mysql_query("SELECT Icon FROM eshop WHERE ID IN ($ids) AND Flags & $nodelete=0");
		while ($row = mysql_fetch_row($result))
			if ($row[0] != '')
			{
				if (!(unlink(KIWI_DIR_GROUPICONS . $row[0])))
				{
					//throw new Exception("Nepodařilo se smazat soubor s ikonou");
				}				
			}
	}

	protected function deleteGroupsAndLines($ids)
	{
		if ($ids !== '')
		{
			$this->deleteIcons($ids);
			$nodelete = self::FLAG_NODELETE;
			$result = mysql_query("SELECT ID FROM eshop WHERE ID IN ($ids) AND Flags & $nodelete=0");
			$deleted_groups = array();
			while ($row = mysql_fetch_row($result))
				$deleted_groups[] = $row[0];
			mysql_query("DELETE FROM eshop WHERE ID IN ($ids) AND Flags & $nodelete=0");
			// ID 1 odpovídá hlavní skupině, kterou nelze odstranit
			mysql_query("DELETE FROM prodbinds WHERE GID IN ($ids)");
			Kiwi_EShop_Indexer::unindex($deleted_groups);
		}
	}

	protected function activateGroupsAndLines($ids, $act = 1)
	{
		if ($ids !== '')
		{
			mysql_query("UPDATE eshop SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($ids) AND ID>1");
			// ID 1 odpovídá hlavní skupině, kterou nelze deaktivovat
			mysql_query("UPDATE prodbinds SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE GID IN ($ids)");
			mysql_query("UPDATE lastchanges SET `When`=CURRENT_TIMESTAMP WHERE Section='eshopitems' AND Item IN ($ids)");
		}
	}

	protected function getBackLink()
	{
		if ($this->record->Parent == 0)
			$link = null;
		else
			$link = KIWI_ESHOP . '?sg=' . $this->record->Parent;

		return $link;
	}
}
?>