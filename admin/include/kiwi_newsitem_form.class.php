<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';
require_once FCK_EDITOR_DIR . 'fckeditor.php';

class Kiwi_NewsItem_Form extends Page_Item
{
	protected $id;
	protected $ngid;
	protected $ngtitle;
	protected $name;
	protected $author;
	protected $sample;
	protected $content;
	protected $when;
	protected $start;
	protected $end;
	protected $data;
	protected $read_only;
	protected $lastchange;
	protected $error;

	public function __construct(&$rights)
	{
		$nrights = $rights->WWW;
		if (is_array($nrights))
		{
			if (!array_key_exists('WriteNews', $nrights)) $nrights['WriteNews'] = false;
			$this->read_only = !($nrights['Write'] || $nrights['WriteNews']);
		}
		else $this->read_only = !$nrights;
		$this->id = 0;
		$this->ngid = 1;
		$this->name = null;
		$this->lastchange = null;
		$this->sample = null;
		$this->content = null;
		$today = date('j.n.Y');
		$this->end = $this->start = $this->when = $today;
		$this->data = null;
		$this->error = false;
	}

	public function _getHTML()
	{
		$oFCKeditor1 = new FCKeditor('kni_ta1');
		$oFCKeditor2 = new FCKeditor('kni_ta2');
		$oFCKeditor1->Config['CustomConfigurationsPath'] = KIWI_DIRECTORY . 'fckcustom/fckconfig.js';
		$oFCKeditor2->Config['CustomConfigurationsPath'] = KIWI_DIRECTORY . 'fckcustom/fckconfig.js';
		$oFCKeditor1->Config['StylesXmlPath'] = KIWI_DIRECTORY . 'fckcustom/fckstyles.xml';
		$oFCKeditor2->Config['StylesXmlPath'] = KIWI_DIRECTORY . 'fckcustom/fckstyles.xml';
		$oFCKeditor1->Config['ToolbarStartExpanded'] = false;
		$oFCKeditor2->Config['ToolbarStartExpanded'] = false;
		// $oFCKeditor1->BasePath = FCK_EDITOR_DIR; // defaultní hodnota
		// $oFCKeditor2->BasePath = FCK_EDITOR_DIR; // defaultní hodnota
		$oFCKeditor1->Height = 198;
		$oFCKeditor2->Height = 296;
		$oFCKeditor1->ToolbarSet = 'Kiwi';
		$oFCKeditor2->ToolbarSet = 'Kiwi';

		$this->loadData();

		$self = basename($_SERVER['PHP_SELF']);
		$qsa = array("ng=$this->ngid");
		if ($this->id) $qsa[] = "ni=$this->id";
		$qs = '?' . implode('&', $qsa);

		if ($this->ngtitle == '')
			$htitle = '';
		else
			$htitle = ' - ' . $this->ngtitle;

		$read_only_str = $this->read_only ? ' readonly' : '';
		$disabled_str = $this->read_only ? ' disabled' : '';
		$but_class = $this->read_only ? 'but3D' : 'but3';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>News / Artikel $htitle - [editieren]</h2>
	<div class="levyV">
		<div class="form3">
			<fieldset>

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $this->lastchange</div>

EOT;

		if ($this->error)
			$html .= <<<EOT
				<span>Bei der Verarbeitung des Formular ist ein Fehler aufgetreten, überprüfen Sie den Inhalt der Beiträge.</span>

EOT;

		$fields = array('name', 'author', 'when', 'start', 'end');

		foreach ($fields as $item)
			$$item = htmlspecialchars($this->$item);

		$oFCKeditor1->Value = $this->sample;
		$oFCKeditor2->Value = $this->content;

		$html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Bezeichnung :</span></td>
							<td><input type="text" id="kni_nam" name="nazev" value="$name" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$read_only_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Autor :</span></td>
							<td><input type="text" id="kni_author" name="autor" value="$author" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$read_only_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Datum :</span></td>
							<td><input type="text" id="kni_when" name="kdy" value="$when" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$read_only_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">erscheinen ab :</span></td>
							<td><input type="text" id="kni_start" name="od" value="$start" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$read_only_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">erscheinen bis :</span></td>
							<td><input type="text" id="kni_end" name="do" value="$end" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$read_only_str /></td>
						</tr>
					</table>
					<br />

EOT;

		if (!$this->read_only)
			$html .= <<<EOT
					<span>kurze Text News / Artikel:</span>
					{$oFCKeditor1->CreateHtml()}
					<span>ganze Text News / Artikel:</span>
					{$oFCKeditor2->CreateHtml()}

EOT;
		else
			$html .= <<<EOT
					$this->sample
					<br />
					$this->content
EOT;

		$html .= <<<EOT
				</div>
				<input type="submit" id="kni_cmd1" name="cmd" value="speichern" class="$but_class" onclick="return Kiwi_NewsItem_Form.onSave()"$disabled_str />
			</fieldset>
		</div>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		if (!empty($get))
		{
			if (isset($get['ni']))
			{
				if (($ni = (int)$get['ni']) < 1)
					throw new Exception("Neplatná hodnota parametru \"ni\": $ni");

				$this->id = $ni;
			}

			if (isset($get['ng']))
			{
				if (($ng = (int)$get['ng']) < 1)
					throw new Exception("Neplatná hodnota parametru \"ng\": $ng");

				$this->ngid = $ng;
			}

			if (isset($get['error']))
				$this->error = true;
		}

		if ($this->id == 0 && $this->ngid == 0)
			throw new Exception("Minimálně jeden z parametrů \"ni\" a \"ng\" je povinný!");

		if (!empty($post) && !$this->read_only)
		{
			switch ($post['cmd'])
			{
				case 'speichern':
					$xpost = strip_gpc_slashes($post);
					$this->name = $xpost['nazev'];
					$this->author = $xpost['autor'];
					$this->sample = $this->parseFckEditorInput($xpost['kni_ta1']);
					$this->content = $this->parseFckEditorInput($xpost['kni_ta2']);
					$this->when = $xpost['kdy'];
					$this->start = $xpost['od'];
					$this->end = $xpost['do'];

					if ($this->name == '')
					{
						$this->name = 'Unbenannt';
						$this->error = true;
					}
					if (!isDateValid($this->when))
					{
						$this->when = date('j.n.Y');
						$this->error = true;
					}
					if (!isDateValid($this->start))
					{
						$this->start = date('j.n.Y');
						$this->error = true;
					}
					if (!isDateValid($this->end))
					{
						$this->end = date('j.n.Y');
						$this->error = true;
					}

					$this->saveData();
					if ($this->id) $qs1 = "&ni=$this->id";
					elseif ($this->ngid) $qs1 = "&ng=$this->ngid";
					$qs2 = "?ng=$this->ngid";

					$this->redirection = $this->error ? (KIWI_EDIT_NEWSITEM . "?error$qs1") : (KIWI_NEWS . $qs2);
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadData()
	{
		if ($this->data == null && $this->id)
		{
			$df = '%e.%c.%Y';
			$result = mysql_query("SELECT NGID, Name, Author, Sample, Content, DATE_FORMAT(`When`, '$df') AS `When`, DATE_FORMAT(Start, '$df') AS Start, DATE_FORMAT(End, '$df') AS End, LastChange FROM news WHERE ID=$this->id");
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
				$this->ngid = $this->data->NGID;
				$this->name = $this->data->Name;
				$this->author = $this->data->Author;
				$this->sample = $this->data->Sample;
				$this->content = $this->data->Content;
				$this->when = $this->data->When;
				$this->start = $this->data->Start;
				$this->end = $this->data->End;
				$dt = parseDateTime($this->data->LastChange);
				$this->lastchange = date('j.n.Y H:i', $dt['stamp']);
			}
			else throw new Exception("Neplatný identifikátor novinky");
		}

		if ($this->ngtitle == '')
		{
			$result = mysql_query("SELECT Title FROM newsgroups WHERE ID=$this->ngid");
			if ($row = mysql_fetch_row($result))
				$this->ngtitle = $row[0];
			else
				throw new Exception("Neplatný identifikátor skupiny novinek!");
		}
	}

	protected function saveData()
	{
		$fields = array('name', 'author', 'sample', 'content');

		foreach ($fields as $item)
			$$item = mysql_real_escape_string($this->$item);

		$dates = array('when', 'start', 'end');

		foreach ($dates as $item)
			$$item = sqlDate($this->$item);

		if ($this->id) // úprava obsahu
		{
			mysql_query("UPDATE news SET Name='$name', Author='$author', Sample='$sample', Content='$content', `When`='$when', Start='$start', End='$end', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
		}
		else // vytvoření nové novinky
		{
			mysql_query("INSERT INTO news(NGID, Name, Author, Sample, Content, `When`, Start, End) VALUES ($this->ngid, '$name', '$author', '$sample', '$content', '$when', '$start', '$end')");
			$this->id = mysql_insert_id();
		}
	}

	protected function parseFckEditorInput($html)
	{
		if ($html == "<p>&#160;</p>") return '';
		else return $html;
	}
}
?>