<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_lastchange.class.php';

require_once 'upload.class.php';

define ('PT_TEXT', 1);
define ('PT_ICON', 2);
define ('PT_COLOR', 3);

class Kiwi_ProductPropertyValue_Form extends Page_Item
{
	protected $read_only;
	protected $id;
	protected $pid;
	protected $property;
	protected $record;
	protected $lastchange;
	protected $icon;

	protected static $color_column_widths = array
	(
		1 => array(24),
		2 => array(12, 12),
		3 => array(8, 8, 8),
		4 => array(6, 6, 6, 6),
		5 => array(5, 5, 4, 5, 5)
	);

	public function __construct()
	{
		parent::__construct();
		$this->read_only = false; // todo: Přidat práva - má právo upravovat?
		$this->id = 0;
		$this->pid = 0;
		$this->record = null;
		$this->icon = null;
	}

	public function _getHTML()
	{
		$self = basename($_SERVER['PHP_SELF']);

		$this->loadRecord();

		if ($this->id)
		{
			$qs = "?pv=$this->id&pp=$this->pid";
			$tname = $value = htmlspecialchars($this->record->Value);
			$extradata = htmlspecialchars($this->record->ExtraData);
			$desc = str_replace("\r\n", "\r", htmlspecialchars($this->record->Description));
			$dt = parseDateTime($this->record->LastChange);
			$lastchange = date('j.n. Y - H:i', $dt['stamp']);
		}
		else
		{
			$qs = "?pp=$this->pid";

			$value = '';
			$tname = 'neu';
			$extradata = '';
			$desc = '';
			$lastchange = null;
		}

		$property = htmlspecialchars($this->property);

		if ($this->read_only)
		{
			$readonly_str = ' readonly';
			$onchange_str = '';
			$ro_disabled_str = ' disabled';
			$D_str = 'D';
		}
		else
		{
			$readonly_str = $ro_disabled_str = $D_str = '';
			$onchange_str = ' onchange="Kiwi_ProductPropertyValue_Form.onChange()" onkeydown="return Kiwi_ProductPropertyValue_Form.onKeyDown(event)"';
		}

		$extradata_a = array
		(
			1 => '',
			2 => 'Icon-Datei',
			3 => 'Hex Color-Registrierung'
		);
		$ed_title = $extradata_a[$this->record->DataType];

		$html = <<<EOT
<form enctype="multipart/form-data" action="$self$qs" method="post">
	<h2>[Eigenschaft: $property] - $tname - [editieren]</h2>
	<div class="levyV">
		<div class="form3">
			<fieldset>
				<input type="hidden" id="kppvfc_datatype" name="Datovy_typ" value="{$this->record->DataType}" />
				<input type="hidden" id="kppvfc_edtitle" value="$ed_title" />

EOT;

		if ($lastchange != null)
			$html .= <<<EOT
				<div class="zmena">Zuletzt Aktualisiert: $lastchange</div>

EOT;

		$ed_html = '';
		if ($ed_title !== '')
		{
			$icon_html = '';
			if ($this->id)
			{
				$imgdir = KIWI_DIR_PRODUCTS . 'properties/';
				switch ($this->record->DataType)
				{
					case PT_ICON:
						if ($this->record->ExtraData !== '')
						{
							$icon_html = <<<EOT
<img src="{$imgdir}icons/$extradata" title="$extradata" alt="$tname" />
EOT;
							$ed_html = <<<EOT

						<tr>
							<td><span class="span-form2">$ed_title :</span></td>
							<td>$icon_html<input type="button" id="kppvfc_cmd9" name="removeIcon" value="entfernen" class="but3$D_str" onclick="Kiwi_ProductPropertyValue_Form.onRemoveIcon('$self$qs')" $ro_disabled_str/></td>
						</tr>
EOT;
							//TODO: remove possibility
						}
						else
							$ed_html = <<<EOT

						<tr>
							<td><span class="span-form2">$ed_title :</span></td>
							<td><input type="file" id="kppvfc_extradata" name="Extra_data" class="inpOUT"$onchange_str /></td>
						</tr>
EOT;
						break;
					case PT_COLOR:
						$colors_a = array_map('strtolower', preg_split('/[,;\|]/', $extradata));
						$filename = implode('', $colors_a);
						$icon_html = <<<EOT
<img src="{$imgdir}colors/$filename.gif" title="" alt="$tname" />
EOT;
						$ed_html = <<<EOT

						<tr>
							<td><span class="span-form2">$ed_title :</span></td>
							<td><input type="text" id="kppvfc_extradata" name="Extra_data" value="$extradata" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str />&nbsp;$icon_html</td>
						</tr>
EOT;
						break;
					default:
						$ed_html = '';
						break;
				}
			}
			else
			{
				switch ($this->record->DataType)
				{
					case PT_ICON:
						$ed_html = <<<EOT

						<tr>
							<td><span class="span-form2">$ed_title :</span></td>
							<td><input type="file" id="kppvfc_extradata" name="Extra_data" class="inpOUT"$onchange_str /></td>
						</tr>
EOT;
						break;
					case PT_COLOR:
						$ed_html = <<<EOT

						<tr>
							<td><span class="span-form2">$ed_title :</span></td>
							<td><input type="text" id="kppvfc_extradata" name="Extra_data" value="" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>
EOT;
						break;
					default:
						$ed_html = '';
						break;
				}
			}
		}

		$html .= <<<EOT
				<div id="frame3">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Wert der Eigenschaft :</span></td>
							<td><input type="text" id="kppvfc_value" name="Hodnota_vlastnosti" value="$value" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>$ed_html
						<tr>
							<td><span class="span-form2">Wertbeschreibung :</span></td>
							<td><textarea class="texarOUT" id="kppyfc_desc" name="Popis_hodnoty" onfocus="this.className='texarON'" onblur="this.className='texarOFF'"$onchange_str$readonly_str>$desc</textarea></td>
						</tr>
					</table>
				</div>
				<input type="submit" id="kppvfc_cmd1" name="cmd" value="speichern" class="but3D" disabled onclick="return Kiwi_ProductPropertyValue_Form.onSave()" />
			</fieldset>
		</div>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		$qsa = array();

		if (!empty($get))
		{
			if (isset($get['pv']))
			{
				if (($pv = (int)$get['pv']) < 1)
					throw new Exception("Neplatné ID záznamu: $pv");

				$this->id = $pv;
				$qsa[] = "pv=$this->id";
			}

			if (isset($get['pp']))
			{
				if (($pp = (int)$get['pp']) < 1)
					throw new Exception("Neplatné ID záznamu: $pp");

				$this->pid = $pp;
				$qsa[] = "pp=$this->pid";
			}

			if ($this->id == 0 && $this->pid == 0)
				throw new Exception("Nedostatek vstupních parametrů query stringu");

			$qs = empty($qsa) ? '' : ('?' . implode('&', $qsa));

			if (isset($get['ri']))
			{
				$this->removeIcon();
				$this->redirection = KIWI_EDIT_PRODUCT_PROPERTY_VALUE . $qs;
				return;
			}
		}
		else throw new Exception("Chybějící ID vlastnosti produktu");

		if (!empty($post))
		{
			$act = 0;
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->handleUploads();
					if ($this->pid == 0)
						throw new Exception("Chybějící ID vlastnosti produktu");
					$value = mysql_real_escape_string(strip_gpc_slashes($post['Hodnota_vlastnosti']));
					$popis = mysql_real_escape_string(strip_gpc_slashes($post['Popis_hodnoty']));
					if ($value == '') throw new Exception('Některá z povinných položek nebyla vyplněna');
					if ($this->icon !== null)
					{
						$extra = mysql_real_escape_string($this->icon);
						$extra_sql = array
						(
							'update' => ", ExtraData='$extra'",
							'insert' => ", '$extra'"
						);
					}
					elseif (array_key_exists('Extra_data', $post))
					{
						$extra = mysql_real_escape_string(strip_gpc_slashes($post['Extra_data']));
						if ($extra == '')
							throw new Exception('Některá z povinných položek nebyla vyplněna');

						if ($post['Datovy_typ'] == PT_COLOR)
						{
							if ($this->isColorValid($extra)) $this->createColorImage($extra);
							else throw new Exception('Nekorektní zápis barvy');
						}

						$extra_sql = array
						(
							'update' => ", ExtraData='$extra'",
							'insert' => ", '$extra'"
						);
					}
					else
						$extra_sql = array
						(
							'update' => '',
							'insert' => ", ''"
						);

					if ($this->id)
					{
						mysql_query("UPDATE prodpvals SET Value='$value'{$extra_sql['update']}, Description='$popis', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
						mysql_query("UPDATE prodprops SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->pid");
					}
					else
					{
						mysql_query("LOCK TABLES prodpvals WRITE, prodprops WRITE");
						$result = mysql_query("SELECT MAX(Priority) FROM prodpvals WHERE PID=$this->pid");
						$row = mysql_fetch_row($result);
						$priority = (int)$row[0] + 1;

						mysql_query("INSERT INTO prodpvals(PID, Value, ExtraData, Description, Priority) VALUES ($this->pid, '$value'{$extra_sql['insert']}, '$popis', $priority)");
						$this->id = mysql_insert_id();
						mysql_query("UPDATE prodprops SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->pid");
						mysql_query("UNLOCK TABLES");
					}
					$qs = "?pp=$this->pid";
					$this->redirection = KIWI_EDIT_PRODUCT_PROPERTY . $qs;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function removeIcon()
	{
		if (!$this->id) throw new Exception('Pokus odstranit ikonu nespecifikované hodnoty vlastnosti produktu');
		$this->loadRecord();
		$icon = $this->record->ExtraData;
		if ($icon !== '' && $this->isValidIconFile($icon))
		{
			if (!(unlink(KIWI_DIR_PRODUCTS . 'properties/icons/' . $icon)))
			{
				//throw new Exception("Nepodařilo se smazat soubor s ikonou");
			}
			mysql_query("UPDATE prodpvals SET ExtraData='', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
		}
	}

	protected function isValidIconFile($filename)
	{
		return preg_match('/.+\.(jpg|gif|png)/i', $filename) != 0;
	}

	protected function handleUploads()
	{
		//$h = fopen('./logs/upload.log.html', 'a');

		if (isset($_FILES['Extra_data']) && !$_FILES['Extra_data']['error'])
		{
			$up1 = $this->handleUpload('Extra_data');
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
		$fu->upload_dir = KIWI_DIR_PRODUCTS . 'properties/icons/';
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

	protected function loadRecord()
	{
		if ($this->record === null)
		{
			if ($this->id)
			{
				$result = mysql_query("SELECT PV.ID, PV.PID, PV.Value, PV.ExtraData, PV.Description, PP.Name, PP.DataType, PV.Active, PV.LastChange FROM prodpvals AS PV JOIN prodprops AS PP ON PV.PID=PP.ID WHERE PV.ID=$this->id");
				if ($row = mysql_fetch_array($result))
				{
					$this->record = new Kiwi_DataRow($row);
					$this->pid = $this->record->PID;
					$this->property = $this->record->Name;
				}
				else
					throw new Exception("Neplatné ID hodnoty vlastnosti produktu: $this->id");
			}
			else if ($this->pid)
			{
				$result = mysql_query("SELECT Name, DataType FROM prodprops WHERE ID=$this->pid");
				if ($row = mysql_fetch_array($result))
				{
					$this->record = new Kiwi_DataRow($row);
					$this->property = $this->record->Name;
				}
				else
					throw new Exception("Neplatné ID vlastnosti produktu: $this->pid");
			}
		}
	}

	protected function isColorValid($color_str)
	{
		$pattern = '/^[0-9a-fA-F]{6}([,;\|][0-9a-fA-F]{6}){0,4}$/';
		return (preg_match($pattern, $color_str) == 1);
	}

	protected function createColorImage($colors_str)
	{
		$colors_a = array_map(strtolower, preg_split('/[,;\|]/', $colors_str));
		$fn = implode('', $colors_a);
		$filename = KIWI_DIR_PRODUCTS . "properties/colors/$fn.gif";
		$colors_n = sizeof($colors_a);
		if ($colors_n > sizeof(self::$color_column_widths))
			throw new Exception('Příliš mnoho barev v rámci jedné hodnoty');
		$color_column_widths = self::$color_column_widths[$colors_n];
		$image = imagecreatetruecolor(24, 24);
		$ci = 0;
		$x2 = -1;
		foreach ($colors_a as $color_str)
		{
			$x1 = $x2 + 1;
			$x2 += $color_column_widths[$ci++];
			$red = hexdec(substr($color_str, 0, 2));
			$green = hexdec(substr($color_str, 2, 2));
			$blue = hexdec(substr($color_str, 4, 2));
			$color = imagecolorallocate($image, $red, $green, $blue);
			imagefilledrectangle($image, $x1, 0, $x2, 23, $color);
		}
		imagegif($image, $filename);
	}
}
?>