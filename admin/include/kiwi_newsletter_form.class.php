<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_anchor.class.php';

require_once 'thumbnail_watermark/Thumbnail.class.php';
require_once FCK_EDITOR_DIR . 'fckeditor.php';

class Kiwi_Newsletter_Form extends Page_Item
{
	const DEFAULT_START_TIME = '20:00';

	protected $rights;
	protected $id;
	protected $title;
	protected $start;
	protected $content;
	protected $active;
	protected $read_only;
	protected $all_checked;
	protected $sqs; // pro obnovení query stringu
	protected $data;
	protected $products;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $anchor;

	public function __construct(&$rights)
	{
		parent::__construct();

		$this->rights = $rights;
		if (is_array($this->rights->WWW))
			$this->read_only = !$this->rights->WWW['Write'];

		$this->all_checked = false;
		$this->id = 0;
		$this->title = null;
		$this->start = date('j.n.Y') . ' '. self::DEFAULT_START_TIME;
		$this->content = null;
		$this->active = 0;
		$this->read_only = false; // přidat práva // změnit na obecnější typ
		$this->sqs = null;
		$this->data = null;
		$this->products = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->anchor = new CurrentKiwiAnchor();
	}

	public function _getHTML()
	{
		global $kiwi_config;

		$oFCKeditor_content = new FCKeditor('knlrfc_content');
		$oFCKeditor_content->Config['CustomConfigurationsPath'] = KIWI_DIRECTORY . 'fckcustom/fckconfig.js';
		$oFCKeditor_content->Config['StylesXmlPath'] = KIWI_DIRECTORY . 'fckcustom/fckstyles.xml';
		$oFCKeditor_content->Config['ToolbarStartExpanded'] = false;
		// $oFCKeditor_content->BasePath = FCK_EDITOR_DIR; // defaultní hodnota
		$oFCKeditor_content->Width = 720;
		$oFCKeditor_content->Height = 296;
		// $oFCKeditor_content->ToolbarSet = 'Kiwi';
		$oFCKeditor_content->ToolbarSet = 'Default';

		if ($this->id)
		{
			$this->loadData();
			$tname = htmlspecialchars($this->title);
			$dt = parseDateTime($this->data->LastChange);
			$lastchange = date('j.n. Y - H:i', $dt['stamp']);
		}
		else
		{
			$tname = 'neu';
			$lastchange = null;
		}

		if ($this->read_only)
		{
			$ro_disabled_str = ' disabled';
			$D_str = 'D';
		}
		else
		{
			$ro_disabled_str = $D_str = '';
		}

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->consQS();

		$title = htmlspecialchars($this->title);

		$active_checked_str = $this->active ? ' checked' : '';

		$oFCKeditor_content->Value = $this->content;

		if ($this->title != '') $tname = $title;
		else $tname = 'Neu';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>Newsletter - $tname - [editieren]</h2>
	<div class="levyV">
 		<div class="form3">
			<fieldset id="knlrfc_fs">

EOT;
		if ($lastchange !== null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: {$lastchange}</div>

EOT;
		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$readonly_str2 = ' disabled';
			$readonly_str3 = 'D';
			$onchange_str = '';
			$onchange_str2 = '';
		}
		else
		{
			$readonly_str = '';
			$readonly_str2 = '';
			$readonly_str3 = '';
			$onchange_str = ' onchange="Kiwi_Newsletter_Form.onChange()" onkeydown="Kiwi_Newsletter_Form.onKeyDown(event)"';
			$onchange_str2 = $onchange_str . ' onclick="Kiwi_Newsletter_Form.onChange()"';
		}

	$start = htmlspecialchars($this->start);

	$html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Newsletter-Name&nbsp;:</span></td>
							<td><input type="text" id="knlrfc_title" name="Nazev" value="$title" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Start :</span></td>
							<td><input type="text" id="knlrfc_start" name="Start" value="$start" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2"><input type="checkbox" id="knlrfc_active" name="Aktivni"$active_checked_str$onchange_str2$readonly_str2 /> aktiv</td>
						</tr>

EOT;

		//TODO: reflect readonly
		$content_html = $oFCKeditor_content->CreateHtml();

		$html .= <<<EOT
						<tr>
							<td><span class="span-form2">Content :</span></td>
							<td>$content_html</td>
						</tr>

EOT;

		$onback_js_arg = $this->redirectLevelUpLink();

		if ($this->id)
		{
			$preview = KIWI_NEWSLETTER_PREVIEW . '?nl=' . $this->id;
			$previewHtml = <<<EOT

				<div class="newsletter-preview"><a href="$preview" title="" target="_blank">Preview</a></div>
EOT;
		}
		else
		{
			$previewHtml = '';
		}

		$html .= <<<EOT
					</table>
				</div>
				<input type="submit" id="knlrfc_cmd1" name="cmd" value="speichern" class="but3$readonly_str3" onclick="return Kiwi_Newsletter_Form.onSave()"$readonly_str2 />
				<input type="button" id="knlrfc_cmd4" name="cmd" value="Zurück" class="but3" onclick="return Kiwi_Newsletter_Form.onBack('$onback_js_arg')" />$previewHtml
			</fieldset>
		</div>
	</div>

EOT;

		if ($this->id && $this->productsEnabled())
		{
			$this->loadLastChange();
			$this->loadProducts();

			$disabled_str = (count($this->products) == 0 || $this->read_only) ? ' disabled' : '';

			$all_checked_str = $this->all_checked ? ' checked' : '';

			$html .= <<<EOT
	<h2>[Posten] - $tname - [verbunden Artikel]</h2>
	<div class="levyV">
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Newsletter_Form.enableBtns(false);"$all_checked_str /></th>
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

				$plink = KIWI_EDIT_PRODUCT . "?p=$product->PID";

				$anchor_str = ($this->anchor->ID == $product->ID) ? ' id="zmena"' : '';

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

				$active = $product->Active != 0 ? 'ja' : 'nein';

				$dt = parseDateTime($product->LastChange);
				$lastchange = date('j.n.Y H:i', $dt['stamp']);

				$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$product->ID" onclick="Kiwi_Newsletter_Form.enableBtns(this.checked);"$disabled_str$checked_str /></td>
					<td><a href="$plink"$anchor_str>$title</a></td>
					<td>$new_cost</td>
					<td>$novelty</td>
					<td>$action</td>
					<td>$discount</td>
					<td>$sellout</td>
					<td>$photostr</td>
					<td>$lastchange</td>
					<td>$active</td>

EOT;

				if (!$this->read_only)
				{
					$nullimg = "<img src=\"./image/null.gif\" alt=\"\" title=\"\" width=\"18\" height=\"18\" />";
					$html .=
						"\t\t\t\t\t<td>" . ($i < count($this->products) - 1 ? "<a href=\"$self$qs&dd=$product->ID\"><img src=\"./image/alldown.gif\" alt=\"\" title=\"ganz unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i < count($this->products) ? "<a href=\"$self$qs&d=$product->ID\"><img src=\"./image/down.gif\" alt=\"\" title=\"unten\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 1 ? "<a href=\"$self$qs&u=$product->ID\"><img src=\"./image/up.gif\" alt=\"\" title=\"oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . ($i > 2 ? "<a href=\"$self$qs&uu=$product->ID\"><img src=\"./image/allup.gif\" alt=\"\" title=\"ganz oben\" width=\"18\" height=\"18\" /></a>" : $nullimg) . "</td>\n";
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

EOT;

			if ($this->read_only || count($this->checked) == 0)
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
			<input type="submit" id="knlrfc_cmd4" name="cmd" value="Artikel hinzufügen" class="but4$D_str"$ro_disabled_str />
			<input type="submit" id="knlrfc_cmd5" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Newsletter_Form.onDelete()" />
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
		// todo: přidat práva
		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (array_key_exists('sqs', $get))
			{
				$this->sqs = $get['sqs'];
			}

			if (isset($get['nl']))
			{
				if (($nl = (int)$get['nl']) < 1)
					throw new Excenlion("Neplatná hodnota parametru \"nl\": $nl");

				$this->id = $nl;

				$anchor = new CurrentKiwiAnchor();
				$anchor->set_key_value(KIWI_NEWSLETTERS, $this->id);
			}

			if (isset($get['d']) || isset($get['dd']) || isset($get['u']) || isset($get['uu']))
			{
				if ((int)isset($get['d']) + (int)isset($get['dd']) + (int)isset($get['u']) + (int)isset($get['uu']) != 1)
					throw new Exception("Neplatný vstup - více než jeden příkaz pro přesun položky");

				if (!$this->productsEnabled()) {
					throw new Exception("Neplatný vstup - produkty newsletterů nejsou aktivovány");
				}

				$dow = isset($get['d']) || isset($get['dd']);
				$tot = isset($get['dd']) || isset($get['uu']);

				$qv = $dow ? 'd' : 'u';
				if ($tot) $qv .= $qv;

				$this->loadProducts();

				if (($cp = (int)$get[$qv]) < 1 || !isset($this->index[$cp]))
					throw new Exception("Neplatné ID záznamu: $cp");

				$this->moveProduct($cp, $dow, $tot);

				$this->loadLastChange(false);
				$this->lastchange->register();
				$this->lastchange = null;

				$this->anchor->ID = $cp;
				$qs = $this->consQS();
				$this->redirection = KIWI_EDIT_NEWSLETTER . $qs . '#zmena';
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

			switch ($post['cmd'])
			{
				case 'speichern':
					$this->title = $xpost['Nazev'];
					if ($this->title == '') throw new Exception('Název newsletteru nebyl vyplněn');
					$this->content = $this->parseFckEditorInput($xpost['knlrfc_content']);
					$this->start = $xpost['Start'];
					$this->active = (array_key_exists('Aktivni', $xpost) && $xpost['Aktivni'] == 'on' ? 1 : 0);

					$this->saveData();
					$qs = $this->consQS();
					$this->redirection = $self . $qs . '#stred';
					break;
				case 'Artikel hinzufügen':
					if ($this->productsEnabled()) {
						$qs = $this->consQS();
						$this->redirection = KIWI_ADD_EXISTING_PRODUCT . $qs;
					}
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						$nlid = (int) $this->id;
						mysql_query("DELETE FROM nlproducts WHERE NLID=$nlid AND ID IN ($id_list)");
						$this->loadLastChange(false);
						$this->lastchange->register();
					}
					$qs = $this->consQS();
					$this->redirection = $self . $qs . '#stred';
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->id)
		{
			if ($this->lastchange == null)
				$this->lastchange = new Kiwi_LastChange(array('nlproducts', $this->id), 'j.n. Y - H:i');
			if ($acquire)
				$this->lastchange->acquire();
		}
	}

	protected function loadProducts()
	{
		if ($this->products === null)
		{
			$this->products = array();

			if ($this->id === null) return;

			$i = 0;
			$id = (int) $this->id;
			$result = mysql_query("SELECT PL.ID, PL.PID, P.Title, P.OriginalCost, P.NewCost, P.Photo, P.Discount, P.Action, P.Novelty, P.Sellout, PL.Priority, P.Active, PL.LastChange FROM nlproducts AS PL JOIN products AS P ON PL.PID=P.ID WHERE PL.NLID=$id ORDER BY PL.Priority");

			$this->products = array();
			while ($row = mysql_fetch_object($result)) {
				$this->products[$i] = $product = new Kiwi_DataRow($row);
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
			$result = mysql_query("SELECT {$mvals[$down][0]}(Priority) FROM nlproducts WHERE NLID=$this->id AND ID!=$pbid");
			if ($row = mysql_fetch_row($result))
			{
				$newpri = $row[0] + $mvals[$down][2];
				mysql_query("UPDATE nlproducts SET Priority=$newpri WHERE ID=$pbid");
			}
		}
		else
		{
			$result = mysql_query("SELECT Priority FROM nlproducts WHERE ID=$pbid");
			$row = mysql_fetch_row($result);
			$priority = $row[0];
			$result = mysql_query("SELECT {$mvals[!$down][0]}(Priority) FROM nlproducts WHERE NLID=$this->id AND Priority{$mvals[!$down][1]}$priority");

			if ($row = mysql_fetch_row($result))
			{
				$neigh = $row[0];
				mysql_query("UPDATE nlproducts SET Priority=$priority WHERE NLID=$this->id AND Priority=$neigh");
				mysql_query("UPDATE nlproducts SET Priority=$neigh WHERE ID=$pbid");
			}
		}

		mysql_query("COMMIT");
	}

	protected function loadData()
	{
		if ($this->data === null && $this->id)
		{
			$df = '%e.%c.%Y %H:%i';
			$result = mysql_query("SELECT ID, Title, Content, DATE_FORMAT(Start, '$df') AS Start, Active, LastChange FROM newsletters WHERE ID=$this->id");
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
				$this->title = $this->data->Title;
				$this->start = $this->data->Start;
				$this->content = $this->data->Content;
				$this->active = $this->data->Active;
			}
			else throw new Exception("Neplatný identifikátor newsletteru");
		}
	}

	protected function saveData()
	{
		$flds = array('title', 'content', 'active');

		foreach ($flds as $fld)
			$$fld = mysql_real_escape_string($this->$fld);

		$dateTimes = array('start');

		foreach ($dateTimes as $item)
			$$item = sqlDateTime($this->$item);

		if ($this->id) // úprava obsahu
		{
			mysql_query("UPDATE newsletters SET Title='$title', Content='$content', Start='$start', Active='$active', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
		}
		else // vytvoření Nevého newsletteru
		{
			mysql_query("INSERT INTO newsletters(Title, Content, Start, Active) VALUES ('$title', '$content', '$start', $active)");
			$this->id = mysql_insert_id();
		}
	}

	protected function redirectLevelUpLink()
	{
		$qsa = array();
		$page = KIWI_NEWSLETTERS;
		$hash = '#zmena';

		if ($this->sqs !== null)
		{
			$sqs = urldecode($this->sqs);
			$qsa[] = $sqs;
		}

		if (empty($qsa)) $qs = '';
		else $qs = '?' . implode('&', $qsa);

		$link = $page . $qs . $hash;
		return $link;
	}

	protected function parseFckEditorInput($html)
	{
		if ($html == "<p>&#160;</p>") return '';
		else return $html;
	}

	protected function productsEnabled()
	{
		global $kiwi_config;
		return isset($kiwi_config['eshop']['newsletters_products']) && $kiwi_config['eshop']['newsletters_products'];
	}

	protected function consQS()
	{
		$qsa = array();

		if ($this->id)
			$qsa[] = "nl=$this->id";

		if ($this->sqs)
			$qsa[] = "sqs=" . urlencode($this->sqs);

		if (empty($qsa)) $qs = '';
		else $qs = '?' . implode('&', $qsa);

		return $qs;
	}
}
?>