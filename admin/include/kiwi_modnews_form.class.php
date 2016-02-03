<?php
require_once 'kiwi_module_form.class.php';
require_once 'kiwi_datarow.class.php';

define ('DEFAULT_NEWS_PER_PAGE', 4);
define ('MAX_NEWS_PER_PAGE', 99);

class Kiwi_ModNews_Form extends Kiwi_Module_Form
{
	protected $perpage;
	protected $ngid;
	protected $listmode;
	protected $showpages;
	protected $detaillink;
	protected $data;
	protected $lastchange;
	protected $newsgroups;

	protected $listmodes = array
	(
		'S' => 'kurze Text',
		'F' => 'Einführungseite',
		//'N' => 'Jen název',
		//'C' => 'Celý text'
	);

	public function __construct(&$rights)
	{
		parent::__construct($rights);
		// todo: doresit prava
		$this->perpage = DEFAULT_NEWS_PER_PAGE;
		$this->listmode = 'S';
		$this->showpages = true;
		$this->detaillink = '';
		$this->newsgroups = null;
		$this->data = null;
	}

	public function _getHTML()
	{
		$this->loadData();

		$self = basename($_SERVER['PHP_SELF']);
		$qsa = array();

		if ($this->id)
			$qsa[] = "m=$this->id";

		if ($this->menu_item)
			$qsa[] = "mi=$this->menu_item";

		if ($this->s_menu_item)
			$qsa[] = "smi=$this->s_menu_item";

		if (empty($qsa)) $qs = '';
		else $qs = '?' . implode('&', $qsa);

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$disabled_str = ' disabled';
			$onchange_str = '';
			$onchange_str2 = '';
			$onchange_str3 = '';
		}
		else
		{
			$readonly_str = '';
			$disabled_str = '';
			$onchange_str = ' onchange="Kiwi_ModNews_Form.onChange()" onkeydown="Kiwi_ModNews_Form.onKeyDown(event)"';
			$onchange_str2 = $onchange_str . ' onclick="Kiwi_ModNews_Form.onChange()"';
			$onchange_str3 = ' onchange="Kiwi_ModNews_Form.onChange()" onkeydown="Kiwi_ModNews_Form.onChange()"';
		}

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>News-Modul - [editieren]</h2>
	<div class="levyV">
 		<div class="form3">
			<fieldset>

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $this->lastchange</div>

EOT;

		$name = htmlspecialchars($this->name);
		$perpage = htmlspecialchars($this->perpage);
		$detaillink = htmlspecialchars($this->detaillink);
		$showpages_checked_str = $this->showpages ? ' checked' : '';

		$opts1_str = $opts2_str = '';

		foreach ($this->newsgroups as $ng)
		{
			$selected_str = $this->ngid == $ng->ID ? ' selected' : '';
			$ngtitle = htmlspecialchars($ng->Title);
			$opts1_str .= <<<EOT

									<option value="$ng->ID"$selected_str>$ngtitle</option>
EOT;
		}

		foreach ($this->listmodes as $lm_code => $lm_text)
		{
			$selected_str = $this->listmode == $lm_code ? ' selected' : '';

			$opts2_str .= <<<EOT

									<option value="$lm_code"$selected_str>$lm_text</option>
EOT;
		}

		$html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">News-Gruppe :</span></td>
							<td>
								<select class="sel1" id="km_news_group" name="skupina"$onchange_str3$disabled_str />$opts1_str
								</select>
							</td>
						</tr>
						<tr>
							<td><span class="span-form2">Bezeichnung :</span></td>
							<td><input type="text" id="km_news_nam" name="nazev" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">News auf der Seite :</span></td>
							<td><input type="text" maxlength="2" id="km_news_npp" name="pocet" value="$perpage" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Methode der Listenansicht der Aufzählung :</span></td>
							<td>
								<select class="sel1" id="km_list_mode" name="listmod"$onchange_str3$disabled_str />$opts2_str
								</select>
							</td>
						</tr>
						<tr>
							<td><span class="span-form2">Seite für das Detail :</span></td>
							<td><input type="text" id="km_news_dl" name="detaillink" value="$detaillink" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Leiste mit Seiten anzeigen:</span></td>
							<td><input type="checkbox" maxlength="2" id="km_news_sp" name="stranky"$showpages_checked_str$onchange_str2$disabled_str /></td>
						</tr>
					</table>
				</div>
				<input type="submit" id="km_news_cmd1" name="cmd" value="speichern" class="but3D" onclick="return Kiwi_ModNews_Form.onSave()" disabled/>
			</fieldset>
		</div>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		parent::handleInput($get, $post);

		if (!empty($post) && !$this->read_only)
		{
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->ngid = $post['skupina'];

					$this->name = strip_gpc_slashes($post['nazev']);
					if ($this->name == '') throw new Exception('Název nebyl vyplněn');

					$this->perpage = strip_gpc_slashes($post['pocet']);
					if ($this->perpage == '') throw new Exception('Počet novinek na stránku nebyl vyplněn');
					if (!(ctype_digit($this->perpage) && $this->perpage > 0 && $this->perpage <= MAX_NEWS_PER_PAGE))
						throw new Exception('Počet novinek není korektní');

					$this->listmode = $post['listmod'];

					$this->showpages = array_key_exists('stranky', $post) && $post['stranky'] == 'on';
					$this->detaillink = strip_gpc_slashes($post['detaillink']);

					$this->saveData();
					$this->redirectLevelUp();
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadData()
	{
		if ($this->data == null && $this->id)
		{
			$result = mysql_query("SELECT M.ID, X.NGID, M.Name, X.Count, X.ListMode, X.ShowPages, X.DetailLink, M.LastChange FROM modules AS M LEFT OUTER JOIN mod_news AS X ON M.ID=X.ID WHERE M.ID=$this->id");
			if ($row = mysql_fetch_assoc($result))
			{
				$this->data = new Kiwi_DataRow($row);
				$this->name = $this->data->Name;
				$this->perpage = (int)$this->data->Count;
				$this->ngid = $this->data->NGID;
				$this->listmode = $this->data->ListMode;
				$this->showpages = $this->data->ShowPages != 0;
				$this->detaillink = $this->data->DetailLink;
			  $dt = parseDateTime($this->data->LastChange);
				$this->lastchange = date('j.n.Y H:i', $dt['stamp']);
			}
			else throw new Exception("Neplatný identifikátor modulu");
		}

		if (!is_array($this->newsgroups))
		{
			$this->newsgroups = array();
			$result = mysql_query("SELECT ID, Title FROM newsgroups ORDER BY Title");
			while ($row = mysql_fetch_array($result))
				$this->newsgroups[] = new Kiwi_DataRow($row);
		}
	}

	protected function saveData()
	{
		$name = mysql_real_escape_string($this->name);
		$count = (int)$this->perpage;
		$showpages = $this->showpages ? 1 : 0;
		$detaillink = mysql_real_escape_string($this->detaillink);
		$type = MODT_NEWS;

		if ($this->id) // úprava obsahu
		{
			mysql_query("UPDATE modules SET Name='$name', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE mod_news SET NGID=$this->ngid, Count=$count, ListMode='$this->listmode', ShowPages=$showpages, DetailLink='$detaillink' WHERE ID=$this->id");
			mysql_query("UPDATE modbinds SET LastChange=CURRENT_TIMESTAMP WHERE ModID=$this->id");
		}
		elseif ($this->menu_item) // vytvoření nového modulu a jeho navázání na položku menu
		{
			$result = mysql_query("SELECT Count(ID) FROM menuitems WHERE ID=$this->menu_item AND Submenu=0");
			$row = mysql_fetch_row($result);
			if ($row[0] != 1) throw new Exception("Neplatná hodnota parametru \"mi\"");

			$result = mysql_query("SELECT MAX(Priority) FROM modbinds WHERE MIID=$this->menu_item");
			$row = mysql_fetch_row($result);
			$priority = (int)$row[0] + 1;

			mysql_query("INSERT INTO modules(Name, Type) VALUES ('$name', $type)");
			$this->id = mysql_insert_id();
			mysql_query("INSERT INTO mod_news(ID, NGID, Count, ListMode, ShowPages, DetailLink) VALUES ($this->id, $this->ngid, $count, '$this->listmode', $showpages, '$detaillink')");
			mysql_query("INSERT INTO modbinds(ModID, MIID, Priority) VALUES ($this->id, $this->menu_item, $priority)");
			$mi_lastchange = new Kiwi_LastChange(array('menuitems', $this->menu_item));
			$mi_lastchange->register();
			$mi_lastchange = null;
			$qs = "?m=$this->id";
			$this->redirection = KIWI_EDIT_MODULE . $qs;
		}
		else // vytvoření nového modulu
		{
			mysql_query("INSERT INTO modules(Name, Type) VALUES ('$name', $type)");
			$this->id = mysql_insert_id();
			mysql_query("INSERT INTO mod_news(ID, NGID, Count, ListMode, ShowPages, DetailLink) VALUES ($this->id, $this->ngid, $count, '$this->listmode', $showpages, '$detaillink')");
			$qs = "?m=$this->id";
			$this->redirection = KIWI_EDIT_MODULE . $qs;
		}
	}
}
?>