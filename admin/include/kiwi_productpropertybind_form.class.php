<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'upload.class.php';
require_once 'thumbnail_watermark/Thumbnail.class.php';

class Kiwi_ProductPropertyBind_Form extends Page_Item
{
	protected $propertyvalue_id;
	protected $product_id;
	protected $photo;
	//protected $to_cost;
	protected $read_only;
	protected $eshop_item;
	protected $s_eshop_item; // pro obnovení query stringu po editaci ze seznamu produktů
	protected $data;

	function __construct()
	{
		parent::__construct();
		$this->propertyvalue_id = 0;
		$this->product_id = 0;
		$this->photo = null;
		//$this->to_cost = 0;
		$this->read_only = false; // přidat práva // změnit na obecnější typ
		$this->eshop_item = false;
		$this->s_eshop_item = false;
		$this->data = null;
	}

	function _getHTML()
	{
		$this->loadData();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->constructQueryString();

		$max_file_size = 1 << 23; // 8MB

		$product_title = htmlspecialchars($this->data->ProductTitle);
		$property_title = htmlspecialchars($this->data->PropertyTitle);
		$property_value = htmlspecialchars($this->data->PropertyValue);

		$tname = "$this->product_id:$this->propertyvalue_id";

		$html = <<<EOT
<form enctype="multipart/form-data" action="$self$qs" method="post">
	<h2>Associated Eigenschaft des Artikels - $tname - [editieren]</h2>
	<div class="levyV">
 		<div class="form3">
			<fieldset>

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
			$onchange_str = ' onchange="Kiwi_ProductPropertyBind_Form.onChange()" onkeydown="Kiwi_ProductPropertyBind_Form.onKeyDown(event)"';
			$onchange_str2 = $onchange_str . ' onclick="Kiwi_ProductPropertyBind_Form.onChange()"';
		}

	$html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Artikel-Name :</span></td>
							<td><input type="text" id="kppbfc_product" name="Nazev_vyrobku" value="$product_title" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Eigenschaftsbezeichnung :</span></td>
							<td><input type="text" id="kppbfc_property" name="Nazev_vlastnosti" value="$property_title" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Wert der Eigenschaft :</span></td>
							<td><input type="text" id="kppbfc_propval" name="Hodnota_vlastnosti" value="$property_value" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'" readonly /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Artikel Foto :</span></td>
							<td>

EOT;

		if ($this->photo)
		{
			$psmall = KIWI_DIR_PRODUCTS . "catalog/$this->photo";
			$pbig = KIWI_DIR_PRODUCTS . "photo/$this->photo";
			$html .= <<<EOT
								<a href="$pbig"><img src="$psmall" alt="" title="náhled" /></a><input type="button" id="kppbfc_cmd2" name="removePhoto" value="entfernen" class="but3$readonly_str3" onclick="Kiwi_ProductPropertyBind_Form.onRemovePhoto('$self$qs')" $readonly_str2/>

EOT;
		}
		elseif ($this->read_only)
			$html .= <<<EOT
								není k dispozici

EOT;
		else
			$html .= <<<EOT
								<input type="file" id="kppbfc_upload" name="uploadPhoto" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str />

EOT;
		$html .= <<<EOT
							</td>
						</tr>

EOT;
/*
						<tr>
							<td><span class="span-form2">Vliv na cenu :</span></td>
							<td><input type="text" id="kppbfc_tocost" name="VlivNaCenu" value="$tocost" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOFF'"$onchange_str$readonly_str /></td>
						</tr>

EOT;
*/

		$onback_js_arg = $this->redirectLevelUpLink();

		$html .= <<<EOT
					</table>
				</div>
				<input type="submit" id="kppbfc_cmd1" name="cmd" value="speichern" class="but3D" onclick="return Kiwi_ProductPropertyBind_Form.onSave()" disabled />
				<input type="button" id="kppbfc_cmd2" name="cmd" value="Zurück" class="but3" onclick="return Kiwi_ProductPropertyBind_Form.onBack('$onback_js_arg')" />
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="MAX_FILE_SIZE" value="$max_file_size">
</form>

EOT;

		return $html;
	}

	function handleInput($get, $post)
	{
		// todo: přidat práva
		$self = basename($_SERVER['PHP_SELF']);

		if (!empty($get))
		{
			if (isset($get['ei']))
			{
				if (($ei = (int)$get['ei']) < 1)
					throw new Exception("Neplatná hodnota parametru \"ei\": $ei");

				$this->eshop_item = $ei;
			}

			if (isset($get['sei']))
			{
				if ($this->eshop_item)
					throw new Exception("Souběžné použití parametrů \"ei\" a \"sei\"");

				if (($sei = (int)$get['sei']) < 1)
					throw new Exception("Neplatná hodnota parametru \"sei\": $sei");

				$this->s_eshop_item = $sei;
			}

			if (isset($get['p']))
			{
				if (($p = (int)$get['p']) < 1)
					throw new Exception("Neplatná hodnota parametru \"p\": $p");

				$this->product_id = $p;
			}

			if (isset($get['pv']))
			{
				if (($pv = (int)$get['pv']) < 1)
					throw new Exception("Neplatná hodnota parametru \"pv\": $pv");

				$this->propertyvalue_id = $pv;
			}

			if (isset($get['rp']))
			{
				$qs = $this->constructQueryString();
				$this->removeProductPhoto();
				$this->redirection = $self . $qs;
				return;
			}
		}

		if (!empty($post))
		{
			$xpost = strip_gpc_slashes($post);
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->handleUploads();
					// handle tocost
					$this->saveData(); // upravi lastchange produktu
					$qs = $this->constructQueryString();
					$this->redirection = $self . $qs;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadData()
	{
		if ($this->data == null && $this->product_id && $this->propertyvalue_id)
		{
			$result = mysql_query("SELECT P.Title AS ProductTitle, PP.Name AS PropertyTitle, PV.Value AS PropertyValue, B.Photo, B.ToCost FROM prodpbinds AS B JOIN products AS P ON B.PID=P.ID JOIN prodpvals AS PV ON B.PPVID=PV.ID JOIN prodprops AS PP ON PV.PID=PP.ID WHERE B.PID=$this->product_id AND B.PPVID=$this->propertyvalue_id");
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
				$this->photo = $this->data->Photo;
			}
			else throw new Exception("Neplatná vazba produktu a hodnoty vlastnosti");
		}
	}

	protected function saveData()
	{
		$flds = array('photo'); // to_cost

		foreach ($flds as $fld)
			$$fld = mysql_real_escape_string($this->$fld);

		mysql_query("UPDATE prodpbinds SET Photo='$photo' WHERE PID=$this->product_id AND PPVID=$this->propertyvalue_id");
		mysql_query("UPDATE products SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->product_id");
		mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->product_id");
	}

	protected function removeProductPhoto()
	{
		if (!$this->product_id || !$this->propertyvalue_id) throw new Exception('Pokus odstranit fotografii produktu nespecifikované asociované vlastnosti produktu');
		$this->loadData();
		if ($this->photo)
		{
			$this->deleteProductFile($this->photo, array('detail', 'catalog', 'catalog2', 'collection'));
			mysql_query("UPDATE prodpbinds SET Photo='' WHERE PID=$this->product_id AND PPVID=$this->propertyvalue_id");
			mysql_query("UPDATE products SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->product_id");
			mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->product_id");
		}
		else throw new Exception('Pokus odstranit neexistující foto produktu');
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

	protected function handleUploads()
	{
		if (!$_FILES['uploadPhoto']['error'])
		{
			$up1 = $this->handleUpload('uploadPhoto');
			if ($up1->upload())
				$this->createThumbs($this->photo = $up1->file_copy, array('detail', 'catalog', 'catalog2', 'collection'));
		}
	}

	protected function handleUpload($upload)
	{
		$fu = new file_upload;
		$fu->upload_dir = KIWI_DIR_PRODUCTS . 'photo/';
		$fu->extensions = array('.jpg', '.png');
		$fu->language = 'en';
		$fu->max_length_filename = 32;
		$fu->rename_file = true;
		$fu->the_temp_file = $_FILES[$upload]['tmp_name'];
		$fu->the_file = $_FILES[$upload]['name'];
		$fu->http_error = $_FILES[$upload]['error'];
		$fu->replace = "n";
		$fu->do_filename_check = "y";
		return $fu;
	}

	protected function createThumbs($file, $targets)
	{
		global $eshop_picture_config;
		$dir_photo = KIWI_DIR_PRODUCTS;
		foreach ($targets as $target)
		{
			if (!array_key_exists($target, $eshop_picture_config))
				throw new Exception('Neznámý cíl pro miniaturu fotografie');
			if (is_array($eshop_picture_config[$target]))
			{
				$t = new Thumbnail("{$dir_photo}photo/$file");
				$t->size($eshop_picture_config[$target][0], $eshop_picture_config[$target][1]);
				$t->quality = 80;
				$t->output_format='JPG';
				$t->process();
				$status = $t->save("{$dir_photo}$target/$file");
				if (!$status)
					throw new Exception('Chyba při ukládání miniatury obrázku');
			}
		}
	}

	protected function redirectLevelUpLink()
	{
		if ($this->eshop_item)
			$xlink = "&ei=$this->eshop_item";
		elseif ($this->s_eshop_item)
			$xlink = "&sei=$this->s_eshop_item";
		else
			$xlink = '';

		return KIWI_EDIT_PRODUCT . "?p=$this->product_id" . $xlink;
	}

	protected function constructQueryString()
	{
		$qsa = array();

		if ($this->product_id)
			$qsa[] = "p=$this->product_id";

		if ($this->eshop_item)
			$qsa[] = "ei=$this->eshop_item";

		if ($this->s_eshop_item)
			$qsa[] = "sei=$this->s_eshop_item";

		if ($this->propertyvalue_id)
			$qsa[] = "pv=$this->propertyvalue_id";

		if (empty($qsa)) $qs = '';
		else $qs = '?' . implode('&', $qsa);

		return $qs;
	}
}
?>