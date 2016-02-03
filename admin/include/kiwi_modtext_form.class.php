<?php
require_once 'kiwi_module_form.class.php';
require_once 'kiwi_datarow.class.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/fckeditor/fckeditor.php';
require_once '../fckeditor/fckeditor.php';

class Kiwi_ModText_Form extends Kiwi_Module_Form
{
	protected $content;
	protected $data;
	protected $lastchange;

	function __construct(&$rights)
	{
		parent::__construct($rights);
		// todo: doresit prava
		$this->content = null;
		$this->data = null;
	}

	function _getHTML()
	{
		$oFCKeditor = new FCKeditor('km_text_ta1') ;
		$oFCKeditor->Config['CustomConfigurationsPath'] = KIWI_DIRECTORY . 'fckcustom/fckconfig.js';
		$oFCKeditor->Config['StylesXmlPath'] = KIWI_DIRECTORY . 'fckcustom/fckstyles.xml';
		// $oFCKeditor->BasePath = '/fckeditor/'; // defaultní hodnota
		$oFCKeditor->Height = 296;
		$oFCKeditor->ToolbarSet = 'Kiwi';

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

		$read_only_str = $this->read_only ? ' readonly' : '';
		$disabled_str = $this->read_only ? ' disabled' : '';
		$but_class = $this->read_only ? 'but3D' : 'but3';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>Modul Text - [editieren]</h2>
	<div class="levyV">
		<div class="form3">
			<fieldset>

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $this->lastchange</div>

EOT;

		$name = htmlspecialchars($this->name);
		$oFCKeditor->Value = $this->content;

		$html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Bezeichnung :</span></td>
							<td><input type="text" id="km_text_nam" name="nazev" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$read_only_str /></td>
						</tr>
					</table>
					<br />

EOT;

		if (!$this->read_only)
			$html .= <<<EOT
					{$oFCKeditor->CreateHtml()}

EOT;
		else
			$html .= <<<EOT
					$this->content

EOT;

		$html .= <<<EOT
				</div>
				<input type="submit" id="km_text_cmd1" name="cmd" value="speichern" class="$but_class" onclick="return Kiwi_ModText_Form.onSave()"$disabled_str />
			</fieldset>
		</div>
	</div>
</form>

EOT;

		return $html;
	}

	function handleInput($get, $post)
	{
		parent::handleInput($get, $post);

		if (!empty($post) && !$this->read_only)
		{
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->name = strip_gpc_slashes($post['nazev']);
					if ($this->name == '') throw new Exception('Název nebyl vyplněn');
					$this->content = strip_gpc_slashes($post['km_text_ta1']);
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
			$result = mysql_query("SELECT M.ID, M.Name, X.Content, M.LastChange FROM modules AS M LEFT OUTER JOIN mod_text AS X ON M.ID=X.ID WHERE M.ID=$this->id");
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
				$this->name = $this->data->Name;
				$this->content = $this->data->Content;
				$dt = parseDateTime($this->data->LastChange);
				$this->lastchange = date('j.n.Y H:i', $dt['stamp']);
			}
			else throw new Exception("Neplatný identifikátor modulu");
		}
	}

	protected function saveData()
	{
		$name = mysql_real_escape_string($this->name);
		$content = mysql_real_escape_string($this->content);
		$type = MODT_TEXT;

		if ($this->id) // úprava obsahu
		{
			mysql_query("UPDATE modules SET Name='$name', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE mod_text SET Content='$content' WHERE ID=$this->id");
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
			mysql_query("INSERT INTO mod_text(ID, Content) VALUES ($this->id, '$content')");
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
			mysql_query("INSERT INTO mod_text(ID, Content) VALUES ($this->id, '$content')");
			$qs = "?m=$this->id";
			$this->redirection = KIWI_EDIT_MODULE . $qs;
		}
	}
}
?>