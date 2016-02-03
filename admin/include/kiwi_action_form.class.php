<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'upload.class.php';
//require_once 'snapshot.class.php'; // orezaval obrazky, nahrazeno tridou Thumbnail
require_once 'thumbnail_watermark/Thumbnail.class.php';

class Kiwi_Action_Form extends Page_Item
{
	protected $id;
	protected $agid;
	protected $agtitle;
	protected $title;
	protected $description;
	protected $picture;
	protected $link;
	protected $lastchange;
	protected $read_only;
	protected $data;

	public function __construct(&$rights)
	{
		parent::__construct();

		if (is_array($rights))
			$this->read_only = !$rights['Write'];		

		$this->id = 0;
		$this->agid = 1;
		$this->agtitle = null;
		$this->title = null;
		$this->description = null;
		$this->lastchange = null;
		$this->picture = null;
		$this->link = null;
		$this->read_only = false; // přidat práva // změnit na obecnější typ
		$this->data = null;
	}

	public function _getHTML()
	{
		$this->loadData();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->constructQueryString();

		$max_file_size = 1 << 23; // 8MB

		$title = htmlspecialchars($this->title);
		$description = str_replace("\r\n", "\r", htmlspecialchars($this->description));
		$link = htmlspecialchars($this->link);

		if ($this->title != '') $tname = $title;
		else $tname = 'neu';

		$html = <<<EOT
<form enctype="multipart/form-data" action="$self$qs" method="post">
	<h2>Aktion - $tname - [editieren]</h2>
	<div class="levyV">
 		<div class="form3">
			<fieldset>

EOT;
		if ($this->lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange}</div>

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
			$onchange_str = ' onchange="Kiwi_Action_Form.onChange()" onkeydown="Kiwi_Action_Form.onKeyDown(event)"';
			$onchange_str2 = $onchange_str . ' onclick="Kiwi_Action_Form.onChange()"';
		}

    $html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Aktion-Name :</span></td>
							<td><input type="text" id="kactfc_title" name="Nazev_akce" value="$title" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
						<tr>
							<td><span class="span-form2">Aktion-Bild :</span></td>
							<td>

EOT;

		if ($this->picture)
		{
			$pic = KIWI_DIR_ACTIONS_PIC . $this->picture;
			$html .= <<<EOT
								<img src="$pic" alt="" title="" /><input type="button" id="kactfc_cmd2" name="remove_pic" value="entfernen" class="but3$readonly_str3" onclick="Kiwi_Action_Form.onRemovePic('$self$qs')"$readonly_str2 />

EOT;
		}
		elseif ($this->read_only)
			$html .= <<<EOT
								není k dispozici

EOT;
		else
			$html .= <<<EOT
								<input type="file" id="kactfc_upload_pic" name="upload_pic" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str />

EOT;
		$html .= <<<EOT
							</td>
						</tr>
						<tr>
							<td><span class="span-form2">Beschreibung :</span></td>
							<td><textarea class="texarOUT" id="kactfc_desc" name="Popis_akce" onfocus="this.className='texarON'" onblur="this.className='texarOFF'"$onchange_str$readonly_str>$description</textarea></td>
						</tr>
						<tr>
							<td><span class="span-form2">Link URL :</span></td>
							<td><input type="text" id="kactfc_link" name="Odkaz_akce" value="$link" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>

EOT;

		$html .= <<<EOT
					</table>
				</div>
				<input type="submit" id="kactfc_cmd1" name="cmd" value="speichern" class="but3D" onclick="return Kiwi_Action_Form.onSave()" disabled />
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="MAX_FILE_SIZE" value="$max_file_size" />
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (array_key_exists('a', $get))
			{
				if (($a = (int)$get['a']) < 1)
					throw new Exception("Neplatná hodnota parametru \"a\": $a");

				$this->id = $a;
			}

			if (isset($get['ag']))
			{
				if (($ag = (int)$get['ag']) < 1)
					throw new Exception("Neplatná hodnota parametru \"ag\": $ag");

				$this->agid = $ag;
			}

			if ($this->id == 0 && $this->agid == 0)
				throw new Exception("Minimálně jeden z parametrů \"a\" a \"ag\" je povinný!");

			if (!$this->read_only && array_key_exists('rp', $get))
			{
				$qs = $this->constructQueryString();
				$this->removeActionPicture();
				$this->redirection = $self . $qs;
				return;
			}
		}

		if (!$this->read_only && !empty($post))
		{
			$xpost = strip_gpc_slashes($post);
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->title = $xpost['Nazev_akce'];
					if ($this->title == '') throw new Exception('Název akce nebyl vyplněn');
					$this->description = $xpost['Popis_akce'];
					$this->link = $xpost['Odkaz_akce'];

					$this->handleUploads();
					$this->saveData();
					$qs = $this->constructQueryString();
					$this->redirection = $self . $qs;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadData()
	{
		if ($this->data == null && $this->id)
		{
			$result = mysql_query("SELECT ID, AGID, Title, Description, Picture, Link, LastChange FROM eshopactions WHERE ID=$this->id");
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
				$this->agid = $this->data->AGID;
				$this->title = $this->data->Title;
				$this->description = $this->data->Description;
				$this->link = $this->data->Link;
				$this->picture = $this->data->Picture;
				$dt = parseDateTime($this->data->LastChange);
				$this->lastchange = date('j.n.Y H:i', $dt['stamp']);
			}
			else throw new Exception("Neplatný identifikátor akce");
		}

		if ($this->agtitle == '')
		{
			$result = mysql_query("SELECT Title FROM actiongroups WHERE ID=$this->agid");
			if ($row = mysql_fetch_row($result))
				$this->agtitle = $row[0];
			else
				throw new Exception("Neplatný identifikátor skupiny akcí!");
		}
	}

	protected function saveData()
	{
		foreach(array('title', 'description', 'picture', 'link') as $item)
			$$item = mysql_real_escape_string($this->$item);

		if ($this->id != 0) // úprava obsahu
		{
			$xp = '';
			if ($picture) $xp = ", Picture='$picture'";

			mysql_query("UPDATE eshopactions SET Title='$title', Description='$description', Link='$link'$xp, LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
		}
		else // vytvoření Neu akce
		{
			mysql_query("INSERT INTO eshopactions(AGID, Title, Description, Picture, Link) VALUES ($this->agid, '$title', '$description', '$picture', '$link')");
			$this->id = mysql_insert_id();
		}
	}

	protected function constructQueryString()
	{
		$qsa = array();

		if ($this->agid)
			$qsa[] = "ag=$this->agid";

		if ($this->id)
			$qsa[] = "a=$this->id";

		if (empty($qsa)) $qs = '';
		else $qs = '?' . implode('&', $qsa);

		return $qs;
	}

	protected function removeActionPicture()
	{
		if (!$this->id) throw new Exception('Pokus odstranit obrázek z nespecifikované akce');
		$this->loadData();
		if ($this->picture)
		{
			$this->deleteActionFile($this->picture);
			mysql_query("UPDATE eshopactions SET Picture='', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
		}
		else throw new Exception('Pokus odstranit neexistující foto akce');
	}

	protected function deleteActionFile($filename)
	{
		if (!(unlink(KIWI_DIR_ACTIONS_PIC . $filename)))
			throw new Exception("Nepodařilo se smazat soubor s obrázkem");
	}

	protected function handleUploads()
	{
		if (!$_FILES['upload_pic']['error'])
		{
			$upl = $this->handleUpload('upload_pic');
			if ($upl->upload())
				$this->scalePicture($this->picture = $upl->file_copy);
		}
	}

	protected function handleUpload($upload, $dir = KIWI_DIR_ACTIONS_PIC)
	{
		$my_upload = new file_upload;
		$my_upload->upload_dir = $dir;
		$my_upload->extensions = array('.jpg', '.png');
		$my_upload->language = 'en';
		$my_upload->max_length_filename = 32;
		$my_upload->rename_file = true;
		$my_upload->the_temp_file = $_FILES[$upload]['tmp_name'];
		$my_upload->the_file = $_FILES[$upload]['name'];
		$my_upload->http_error = $_FILES[$upload]['error'];
		$my_upload->replace = "n";
		$my_upload->do_filename_check = "y";
		return $my_upload;
	}

	protected function scalePicture($file)
	{
		global $eshop_picture_config;
		$t = new Thumbnail(KIWI_DIR_ACTIONS_PIC . $file);
		$t->size($eshop_picture_config['action'][0], $eshop_picture_config['action'][1]);
		$t->quality = 80;
		$t->output_format='JPG';
		$t->process();
		$status = $t->save(KIWI_DIR_ACTIONS_PIC . $file);
		if (!$status)
			throw new Exception('Chyba při ukládání miniatury obrázku');
	}
}
?>