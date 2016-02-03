<?php
// pridana podpora pro fckeditor pro longdesc, zmeneno chovani save buttonu
// pridana podpora pro msdropdown select boxy u ikon a barev

require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_anchor.class.php';
require_once 'kiwi_url_generator.class.php';
require_once 'kiwi_product_copy.class.php';
require_once 'kiwi_eshop_indexer.class.php';

require_once 'upload.class.php';
require_once 'thumbnail_watermark/Thumbnail.class.php';
require_once FCK_EDITOR_DIR . 'fckeditor.php';

define ('PT_TEXT', 1);
define ('PT_ICON', 2);
define ('PT_COLOR', 3);

class Kiwi_Product_Form extends Page_Item
{
	protected $rights;
	protected $id;
	protected $title;
	protected $code;
	protected $code_unique;
	protected $auto;
	protected $url;
	protected $htitle;
	protected $shortdesc;
	protected $longdesc;
	protected $collection;
	protected $photo;
	protected $photo_extra;
	protected $photo_illustrative;
	protected $original_cost;
	protected $new_cost;
	protected $ws_cost;
	protected $novelty;
	protected $action;
	protected $discount;
	protected $sellout;
	protected $exposed;
	protected $active;
	protected $lastchange;
	protected $read_only;
	protected $eshop_item;
	protected $s_eshop_item; // pro obnovení query stringu po editaci ze seznamu produktů
	protected $sqs; // pro obnovení query stringu
	protected $data;
	protected $propvalues;
	protected $properties;
	protected $group;
	protected $group_name;
	protected $grouped_products;
	protected $grouped_product; // passed by query string, not relevant to current product

	public function __construct(&$rights)
	{
		parent::__construct();

		$this->rights = $rights;
		if (is_array($this->rights->EShop))
			$this->read_only = !$this->rights->EShop['Write'];

		$this->id = 0;
		$this->title = null;
		$this->code = null;
		$this->code_unique = null;
		$this->auto = true;
		$this->url = null;
		$this->htitle = null;
		$this->shortdesc = null;
		$this->longdesc = null;
		$this->lastchange = null;
		$this->collection = null;
		$this->photo = null;
		$this->photo_extra = array();
		$this->photo_illustrative = array();
		$this->original_cost = $this->new_cost = $this->ws_cost = 0.0;
		$this->novelty = $this->action = $this->discount = $this->sellout = 0;
		$this->exposed = 1;
		$this->active = 0;
		$this->read_only = false; // přidat práva // změnit na obecnější typ
		$this->eshop_item = false;
		$this->s_eshop_item = false;
		$this->sqs = null;
		$this->content = null;
		$this->data = null;
		$this->propvalues = null;
		$this->properties = array();
		$this->group = null;
		$this->group_name = null;
		$this->grouped_products = 0;
		$this->grouped_product = null;
	}

	public function _getHTML()
	{
		global $kiwi_config;

		$oFCKeditor_ldsc = new FCKeditor('kprofc_ldsc');
		$oFCKeditor_ldsc->Config['CustomConfigurationsPath'] = KIWI_DIRECTORY . 'fckcustom/fckconfig.js';
		$oFCKeditor_ldsc->Config['StylesXmlPath'] = KIWI_DIRECTORY . 'fckcustom/fckstyles.xml';
		$oFCKeditor_ldsc->Config['ToolbarStartExpanded'] = false;
		// $oFCKeditor_ldsc->BasePath = FCK_EDITOR_DIR; // defaultní hodnota
		$oFCKeditor_ldsc->Height = 296;
		$oFCKeditor_ldsc->ToolbarSet = 'Kiwi';

		$this->loadData();
		$this->loadProperties();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->consQS();

		$max_file_size = 1 << 23; // 8MB

		$title = htmlspecialchars($this->title);
		$code = htmlspecialchars($this->code);
		$url = htmlspecialchars($this->url);
		$htitle = htmlspecialchars($this->htitle);

		$code_not_unique_str = $this->code_unique === false ? '<span class="wrn">Product Code nicht einzigartig!</span>' : '';

		$shortdesc = str_replace("\r\n", "\r", htmlspecialchars($this->shortdesc));
		//$longdesc = str_replace("\r\n", "\r", htmlspecialchars($this->longdesc));
		$collection = htmlspecialchars($this->collection);
		$original_cost = htmlspecialchars(sprintf("%0.2f", $this->original_cost));
		$new_cost = htmlspecialchars(sprintf("%0.2f", $this->new_cost));
		$ws_cost = htmlspecialchars(sprintf("%0.2f", $this->ws_cost));

		$novelty_checked_str = $this->novelty ? ' checked' : '';
		$action_checked_str = $this->action ? ' checked' : '';
		$discount_checked_str = $this->discount ? ' checked' : '';
		$sellout_checked_str = $this->sellout ? ' checked' : '';
		$exposed_checked_str = $this->exposed ? ' checked' : '';
		$active_checked_str = $this->active ? ' checked' : '';

		$oFCKeditor_ldsc->Value = $this->longdesc;

		if ($this->title != '') $tname = $title;
		else $tname = 'Neu';

		$html = <<<EOT
<form enctype="multipart/form-data" action="$self$qs" method="post">
	<h2>Produkt - $tname - [editieren]</h2>
	<div class="levyV">
 		<div class="form3">
			<fieldset id="kprofc_fs">

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
			$onchange_str3 = '';
		}
		else
		{
			$readonly_str = '';
			$readonly_str2 = '';
			$readonly_str3 = '';
			$onchange_str = ' onchange="Kiwi_Product_Form.onChange()" onkeydown="Kiwi_Product_Form.onKeyDown(event)"';
			$onchange_str2 = $onchange_str . ' onclick="Kiwi_Product_Form.onChange()"';
			$onchange_str3 =  ' onchange="Kiwi_Product_Form.onChangeAuto()" onkeydown="Kiwi_Product_Form.onKeyDownAuto(event)" onclick="Kiwi_Product_Form.onChangeAuto()"';
		}

	if ($this->rights->Admin || $this->rights->EShop['EditURLs'])
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
							<td colspan="2"><input type="checkbox" id="kprofc_auto" name="Auto"$onchange_str3$readonly_str$checked_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Artikel URL :</span></td>
							<td><input type="text" id="kprofc_url" name="URL_vyrobku" value="$url" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str$disabled_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">HTML-Titel :</span></td>
							<td><input type="text" id="kprofc_htitle" name="htitle_vyrobku" value="$htitle" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str$disabled_str /></td>
						</tr>
EOT;

	}
	else
		$ue_html = '';

	$html .= <<<EOT
				<div id="frame">
					<table class="tab-form" cellspacing="0" cellpadding="0">
						<tr>
							<td><span class="span-form2">Artikel-Name :</span></td>
							<td><input type="text" id="kprofc_title" name="Nazev_vyrobku" value="$title" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Artikel Code :</span></td>
							<td><input type="text" id="kprofc_code" name="Kod_vyrobku" value="$code" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str />$code_not_unique_str</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2"><input type="checkbox" id="kprofc_active" name="Aktivni"$active_checked_str$onchange_str2$readonly_str2 /> aktiv</td>
						</tr>$ue_html
						<tr>
							<td><span class="span-form2">Artikel Foto :</span></td>
							<td>

EOT;

		if ($this->photo)
		{
			$psmall = KIWI_DIR_PRODUCTS . "catalog/$this->photo";
			$pbig = KIWI_DIR_PRODUCTS . "photo/$this->photo";
			$html .= <<<EOT
								<a href="$pbig"><img src="$psmall" alt="" title="Einsicht" /></a><input type="button" id="kprofc_cmd2" name="remove0" value="entfernen" class="but3$readonly_str3" onclick="Kiwi_Product_Form.onRemovePhoto('',0,'$self$qs')" $readonly_str2/>

EOT;
		}
		elseif ($this->read_only)
			$html .= <<<EOT
								není k dispozici

EOT;
		else
			$html .= <<<EOT
								<input type="file" id="kprofc_upload1" name="upload1" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str />

EOT;
		$html .= <<<EOT
							</td>
						</tr>

EOT;

		global $eshop_picture_config;
		if (is_array($eshop_picture_config['extra']))
		{
			$html .= <<<EOT
						<tr>
							<td><span class="span-form2">Nächste Fotos :</span>
EOT;
			foreach ($this->photo_extra as $cmdi => $photo)
			{
				$pesmall = KIWI_DIR_PRODUCTS . "extra/{$photo['FileName']}";
				$pebig = KIWI_DIR_PRODUCTS . "photo/{$photo['FileName']}";
				$html .= <<<EOT
</td>
							<td>
								<a href="$pebig"><img src="$pesmall" alt="" title="Einsicht" /></a><input type="button" id="kprofc_cmd3_$cmdi" name="removeXP$cmdi" value="entfernen" class="but3$readonly_str3" onclick="Kiwi_Product_Form.onRemovePhoto('e',$cmdi,'$self$qs')" $readonly_str2/>
							</td>
						</tr>
						<tr>
							<td>
EOT;
			}

			if (!empty($this->photo_extra))
				$html .= <<<EOT
<span class="span-form2">hinzufügen :</span>
EOT;

			$html .= <<<EOT
</td>
							<td>
								<input type="file" id="kprofc_upload2" name="upload2" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str />
							</td>
						</tr>

EOT;
		}

		if (is_array($eshop_picture_config['illustrative']))
		{
			$html .= <<<EOT
						<tr>
							<td><span class="span-form2">Illustrative Fotos :</span>
EOT;
			foreach ($this->photo_illustrative as $cmdi => $photo)
			{
				$pesmall = KIWI_DIR_PRODUCTS . "illustrative/{$photo['FileName']}";
				$pebig = KIWI_DIR_PRODUCTS . "photo/{$photo['FileName']}";
				$html .= <<<EOT
</td>
							<td>
								<a href="$pebig"><img src="$pesmall" alt="" title="Einsicht" /></a><input type="button" id="kprofc_cmd4_$cmdi" name="removeIP$cmdi" value="entfernen" class="but3$readonly_str3" onclick="Kiwi_Product_Form.onRemovePhoto('i',$cmdi,'$self$qs')" $readonly_str2/>
							</td>
						</tr>
						<tr>
							<td>
EOT;
			}

			if (!empty($this->photo_illustrative))
				$html .= <<<EOT
<span class="span-form2">hinzufügen :</span>
EOT;

			$html .= <<<EOT
</td>
							<td>
								<input type="file" id="kprofc_upload3" name="upload3" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str />
							</td>
						</tr>

EOT;
		}

		$collections_visibility = $kiwi_config['eshop']['collections'] ? '' : ' class="invisible"';

		if ($this->id > 0)
		{
			if ($this->grouped_products > 0)
			{
				$group_name = htmlspecialchars($this->group_name);
				$grouped_products_str = "<span>$group_name (Total Produkte: $this->grouped_products)</span>";
			}
			else
				$grouped_products_str = '';

			$multiple_groups_wrn_str = count($this->group) > 1 ? '<span class="wrn">Es gibt mehrere Reihen von verbindungen mit diesem Produkt! Sie können nur das erste bearbeiten.</span>' : '';

			$group_html = <<<EOT

						<tr>
							<td><span class="span-form2">Verwandte Produkte :</span></td>
							<td>$grouped_products_str<input type="button" id="kprofc_group" name="Sdruzene" value="editieren" class="but3$readonly_str3" onclick="Kiwi_Product_Form.onGroupProduct('$self$qs')" $readonly_str2/>$multiple_groups_wrn_str</td>
						</tr>
EOT;
		}
		else
			$group_html = '';

		//TODO: reflect readonly
		$ldsc_html = $oFCKeditor_ldsc->CreateHtml();

		/* original pre-fck code:

						<tr>
							<td><span class="span-form2">Beschreibung :</span></td>
							<td><textarea class="texarOUT" id="kprofc_ldsc" name="Popis" onfocus="this.className='texarON'" onblur="this.className='texarOUT'"$onchange_str$readonly_str>$longdesc</textarea></td>
						</tr>
		*/

		$html .= <<<EOT
						<tr>
							<td><span class="span-form2">Kurzbeschreibung :</span></td>
							<td><textarea class="texarOUT" id="kprofc_sdsc" name="ZkracenyPopis" onfocus="this.className='texarON'" onblur="this.className='texarOUT'"$onchange_str$readonly_str>$shortdesc</textarea></td>
						</tr>
						<tr>
							<td><span class="span-form2">Beschreibung :</span></td>
							<td>$ldsc_html</td>
						</tr>
						<tr$collections_visibility>
							<td><span class="span-form2">Kollektion :</span></td>
							<td><input type="text" id="kprofc_collection" name="Kolekce" value="$collection" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str /></td>
						</tr>$group_html
						<tr>
							<td></td>
							<td colspan="2"><input type="checkbox" id="kprofc_exposed" name="Exponovany"$exposed_checked_str$onchange_str2$readonly_str2 /> erscheinen im Katalog Serie</td>
						</tr>
						<tr>
							<td><span class="span-form2">Original Preis :</span></td>
							<td><input type="text" id="kprofc_cost1" name="Puvodni_cena" value="$original_cost" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Unsere Preis :</span></td>
							<td><input type="text" id="kprofc_cost2" name="Nova_cena" value="$new_cost" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td><span class="span-form2">Grosshandelpreis :</span></td>
							<td><input type="text" id="kprofc_wscost" name="VO_cena" value="$ws_cost" class="inpOUT" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$onchange_str$readonly_str /></td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2"><input type="checkbox" id="kprofc_novelty" name="Novinka"$novelty_checked_str$onchange_str2$readonly_str2 /> Neu</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2"><input type="checkbox" id="kprofc_action" name="Akce"$action_checked_str$onchange_str2$readonly_str2 /> Aktion</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2"><input type="checkbox" id="kprofc_discount" name="Sleva"$discount_checked_str$onchange_str2$readonly_str2 /> Rabatt</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2"><input type="checkbox" id="kprofc_sellout" name="Vyprodej"$sellout_checked_str$onchange_str2$readonly_str2 /> Ausverkauf</td>
						</tr>

EOT;
		if ($this->id) // u nových produktů nelze nastavovat vlastnosti
		{
			$elink = KIWI_EDIT_PRODUCT_PROPERTY_BIND;
			foreach ($this->properties as $propid => &$proprec)
			{
				$prop_values_a = array();
				$prop_options = '';
				foreach ($proprec['values'] as $pvid => $propval)
				{
					$value = $propval['value'];
					$html_value = htmlspecialchars($value);

					switch ($proprec['datatype']) {
						case PT_COLOR:
							$icon = $this->getColorPropertyIcon($propval);
							break;
						case PT_ICON:
							$icon = $this->getIconPropertyIcon($propval);
							break;
						default:
							$icon = '';
					}

					if (array_key_exists($pvid, $this->propvalues))
					{
						$prop_values_a[] = <<<EOT
<a href="$elink$qs&pv=$pvid" onclick="return Kiwi_Product_Form.onEditValue()" title="deutlicher erklären"><img src="$icon" height="16" width="16" /> $html_value</a><a href="$self$qs&rpv=$pvid" onclick="return Kiwi_Product_Form.onRemValue()" title="entnehmen"><img src="./image/remove.gif" alt="" title="entnehmen" /></a>
EOT;
					}
					else
					{
						$msDDTitle = $icon ? " title=\"$icon\"" : '';
						$prop_options .= <<<EOT

									<option value="$pvid"$msDDTitle>$html_value</option>
EOT;
					}
				}

				if (sizeof($prop_values_a) > 0)
					$prop_values_list = implode(', ', $prop_values_a);
				else
					$prop_values_list = 'kein Eintrag';

				$msDDclass = $icon ? ' msdropdown' : '';
				$html .= <<<EOT
						<tr id="prp$propid">
							<td><span class="span-form2">{$proprec['name']} :</span></td>
							<td>$prop_values_list</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<select id="kprofc_prop$propid" name="propsel$propid" class="sel1$msDDclass" onchange="Kiwi_Product_Form.onChangeValue($propid, this.value)"$readonly_str2>
									<option value="0" selected>----- Wählen Sie einen Wert -----</option>$prop_options
									<option value="-1">Wählen Sie anderen Wert</option>
								</select>
								<input type="text" id="kprofc_propt$propid" name="proptext$propid" value="" class="invisible" onfocus="this.className='inpON'" onblur="this.className='inpOUT'"$readonly_str />
								<input type="button" id="kprofc_propb$propid" name="propadd$propid" value="hinzufügen" class="but3$readonly_str3" onclick="Kiwi_Product_Form.onAddValue($propid, '$self$qs')"$readonly_str />
							</td>
						</tr>

EOT;
			}
		}
		else
			$html .= <<<EOT
						<tr>
							<td colspan=2><i>Das Produkt-Eigenschaften können bearbeitet werden, danach wird Produkt gespeichert.</i></td>
						</tr>

EOT;

		$onback_js_arg = $this->redirectLevelUpLink();

		if ($this->id > 0)
			$copy_html = <<<EOT

				<input type="submit" id="kprofc_cmd3" name="cmd" value="kopieren" class="but3" onclick="return Kiwi_Product_Form.onCopy()" />
EOT;
		else
			$copy_html = <<<EOT

				<input type="submit" id="kprofc_cmd3" name="cmd" value="kopieren" class="but3D" disabled />
EOT;

		$html .= <<<EOT
					</table>
				</div>
				<input type="submit" id="kprofc_cmd1" name="cmd" value="speichern" class="but3$readonly_str3" onclick="return Kiwi_Product_Form.onSave()"$readonly_str2 />$copy_html
				<input type="button" id="kprofc_cmd4" name="cmd" value="Zurück" class="but3" onclick="return Kiwi_Product_Form.onBack('$onback_js_arg')" />
				<input type="hidden" name="MAX_FILE_SIZE" value="$max_file_size">
			</fieldset>
		</div>
	</div>
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
			if (array_key_exists('ei', $get))
			{
				if (($ei = (int)$get['ei']) < 1)
					throw new Exception("Neplatná hodnota parametru \"ei\": $ei");

				$this->eshop_item = $ei;
			}

			if (array_key_exists('sei', $get))
			{
				if ($this->eshop_item)
					throw new Exception("Souběžné použití parametrů \"ei\" a \"sei\"");

				if (($sei = (int)$get['sei']) < 1)
					throw new Exception("Neplatná hodnota parametru \"sei\": $sei");

				$this->s_eshop_item = $sei;
			}

			if (isset($get['gp']))
			{
				if (($this->grouped_product = (int)($get['gp'])) < 1)
					throw new Exception("Neplatné ID sdruženého produktu: $this->grouped_product");
			}

			if (array_key_exists('sqs', $get))
			{
				$this->sqs = $get['sqs'];
			}

			if (isset($get['p']))
			{
				if (($p = (int)$get['p']) < 1)
					throw new Exception("Neplatná hodnota parametru \"p\": $p");

				$this->id = $p;

				$anchor = new CurrentKiwiAnchor();
				$anchor->set_key_value(KIWI_PRODUCTS, $this->id);
			}

			if (isset($get['rp']))
			{
				$qs = $this->consQS();
				$this->removeProductPhoto();
				$this->redirection = $self . $qs . '#stred';
				return;
			}

			if (isset($get['rpe']))
			{
				if (($rpe = (int)$get['rpe']) < 1)
					throw new Exception("Neplatná hodnota parametru \"rpe\": $rpe");
				$qs = $this->consQS();
				$this->removeExtraPhoto($rpe);
				$this->redirection = $self . $qs . '#stred';
				return;
			}

			if (isset($get['rpi']))
			{
				if (($rpi = (int)$get['rpi']) < 1)
					throw new Exception("Neplatná hodnota parametru \"rpi\": $rpi");
				$qs = $this->consQS();
				$this->removeIllustrativePhoto($rpi);
				$this->redirection = $self . $qs . '#stred';
				return;
			}

			if (isset($get['rpv']))
			{
				$qs = $this->consQS();
				if (($rpv = (int)$get['rpv']) < 1)
					throw new Exception("Neplatná hodnota parametru \"rpv\": $rpv");
				$propid = $this->removePropertyValue($rpv);
				$this->redirection = $self . $qs . '#prp' . $propid;
				return;
			}

			if (isset($get['apv']))
			{
				$qs = $this->consQS();
				if (($apv = (int)$get['apv']) < 1)
					throw new Exception("Neplatná hodnota parametru \"apv\": $apv");
				$propid = $this->addPropertyValue($apv);
				$this->redirection = $self . $qs . '#prp' . $propid;
				return;
			}

			if (isset($get['anpv']))
			{
				$qs = $this->consQS();
				$anpv = explode(':', $get['anpv'], 2);
				try
				{
					if (sizeof($anpv) != 2) throw new Exception();
					$propid = $anpv[0];
					if ($propid < 1) throw new Exception();
					$pval = $anpv[1];
				}
				catch (Exception $e)
				{
					throw new Exception("Neplatná hodnota parametru \"anpv\": $anpv");
				}
				$this->addNewPropertyValue($propid, $pval);
				$this->redirection = $self . $qs . '#prp' . $propid;
				return;
			}

			if (array_key_exists('eg', $get))
			{
				if ($this->id == 0)
					throw new Exception('Pokus o editaci sdružených produktů s dosud nevytvořeným produktem');
				$group = $this->acquireGroupedProductsGroup();
				$this->redirection = KIWI_EDIT_ESHOPITEM . "?ei=$group&gp=$this->id";
				return;
			}
		}

		if (!empty($post))
		{
			$xpost = strip_gpc_slashes($post);
			switch ($post['cmd'])
			{
				case 'speichern':
					$this->title = $xpost['Nazev_vyrobku'];
					if ($this->title == '') throw new Exception('Název výrobku nebyl vyplněn');
					$this->code = $xpost['Kod_vyrobku'];
					$this->shortdesc = $xpost['ZkracenyPopis'];
					$this->longdesc = $this->parseFckEditorInput($xpost['kprofc_ldsc']);
					//$this->longdesc = $xpost['Popis'];
					$this->auto = array_key_exists('Auto', $xpost);
					if (array_key_exists('URL_vyrobku', $xpost))
						$this->url = $xpost['URL_vyrobku'];
					if (array_key_exists('htitle_vyrobku', $xpost))
						$this->htitle = $xpost['htitle_vyrobku'];
					$this->collection = $xpost['Kolekce'];
					$this->original_cost = (float)$xpost['Puvodni_cena'];
					$this->new_cost = (float)$xpost['Nova_cena'];
					$this->ws_cost = (float)$xpost['VO_cena'];

					$this->novelty = (array_key_exists('Novinka', $xpost) && $xpost['Novinka'] == 'on' ? 1 : 0);
					$this->action = (array_key_exists('Akce', $xpost) && $xpost['Akce'] == 'on' ? 1 : 0);
					$this->discount = (array_key_exists('Sleva', $xpost) && $xpost['Sleva'] == 'on' ? 1 : 0);
					$this->sellout = (array_key_exists('Vyprodej', $xpost) && $xpost['Vyprodej'] == 'on' ? 1 : 0);
					$this->exposed = (array_key_exists('Exponovany', $xpost) && $xpost['Exponovany'] == 'on' ? 1 : 0);
					$this->active = (array_key_exists('Aktivni', $xpost) && $xpost['Aktivni'] == 'on' ? 1 : 0);

					$this->handleUploads();
					$this->saveData();
					$qs = $this->consQS();
					$this->redirection = $self . $qs . '#stred';
					break;
				case 'kopieren':
					if ($this->id === 0)
						throw new Exception('Pokus kopírovat dosud nevytvořený produkt!');
					$copy = new Kiwi_Product_Copy($this->id);
					$this->redirection = $self . "?p=" . $copy->getCopyPID();
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function loadGroup()
	{
		global $kiwi_config;
		if ($this->group === null)
		{
			$this->group = array();
			$grouped_products_group = $kiwi_config['eshop']['grouped_products_group'];
			$result = mysql_query("SELECT E.ID, E.Name FROM eshop AS E JOIN prodbinds AS PB ON E.ID=PB.GID WHERE E.Parent=$grouped_products_group AND PB.PID=$this->id ORDER BY E.ID");
			while ($row = mysql_fetch_row($result))
			{
				if ($this->group_name === null)
					$this->group_name = $row[1];
				$this->group[] = $row[0];
			}

			if (!empty($this->group))
			{
				$groups = implode(', ', $this->group);
				$result = mysql_query("SELECT Count(*) FROM prodbinds WHERE GID IN ($groups)");
				if ($row = mysql_fetch_row($result))
					$this->grouped_products = $row[0];
			}
		}
	}

	protected function loadData()
	{
		if ($this->data === null && $this->id)
		{
			$result = mysql_query("SELECT ID, Title, Code, ShortDesc, URL, PageTitle, LongDesc, Collection, OriginalCost, NewCost, WSCost, Photo, Discount, Sellout, Action, Novelty, Exposed, Active, LastChange FROM products WHERE ID=$this->id");
			if ($row = mysql_fetch_array($result))
			{
				$this->data = new Kiwi_DataRow($row);
				$this->title = $this->data->Title;
				$this->code = $this->data->Code;
				$this->auto = $this->data->URL == '';
				$this->url = $this->data->URL;
				$this->htitle = $this->data->PageTitle;
				$this->shortdesc = $this->data->ShortDesc;
				$this->longdesc = $this->data->LongDesc;
				$this->collection = $this->data->Collection;
				$this->original_cost = $this->data->OriginalCost;
				$this->new_cost = $this->data->NewCost;
				$this->ws_cost = $this->data->WSCost;
				$this->photo = $this->data->Photo;
				$this->novelty = $this->data->Novelty;
				$this->action = $this->data->Action;
				$this->discount = $this->data->Discount;
				$this->sellout = $this->data->Sellout;
				$this->exposed = $this->data->Exposed;
				$this->active = $this->data->Active;
				$dt = parseDateTime($this->data->LastChange);
				$this->lastchange = date('j.n.Y H:i', $dt['stamp']);

				$result = mysql_query("SELECT ID, FileName FROM prodepics WHERE PID=$this->id ORDER BY ID");
				while ($row = mysql_fetch_array($result))
					$this->photo_extra[$row['ID']] = array('FileName' => $row['FileName']);

				$result = mysql_query("SELECT ID, FileName FROM prodipics WHERE PID=$this->id ORDER BY ID");
				while ($row = mysql_fetch_array($result))
					$this->photo_illustrative[$row['ID']] = array('FileName' => $row['FileName']);

				$code = mysql_real_escape_string($this->code);
				$result = mysql_query("SELECT Count(*) FROM products WHERE Code='$code'");
				if ($row = mysql_fetch_row($result))
					$this->code_unique = $row[0] < 2;
				else
					throw new Exception("Chyba při ověřování unikátnosti kódu produktu");

				$this->loadGroup();
			}
			else throw new Exception("Neplatný identifikátor produktu");
		}
	}

	protected function loadProperties()
	{
		if ($this->propvalues == null)
		{
			$result = mysql_query("SELECT ID, Name, DataType FROM prodprops ORDER BY Priority");
			while ($row = mysql_fetch_row($result))
				$this->properties[$row[0]] = array('name' => $row[1], 'datatype' => (int) $row[2], 'values' => array());

			$result = mysql_query("SELECT ID, PID, Value, ExtraData FROM prodpvals ORDER BY Priority");
			while ($row = mysql_fetch_row($result))
				if (array_key_exists($row[1], $this->properties))
					$this->properties[$row[1]]['values'][$row[0]] = array('value' => $row[2], 'extradata' => $row[3]);
				else throw new Exception("Nekonzistentní stav databáze - vlastnosti produktů: vlastnost: {$row[1]}");

			$this->propvalues = array();
			$result = mysql_query("SELECT V.ID, V.PID, B.Photo, B.ToCost FROM prodpbinds AS B JOIN prodpvals AS V ON B.PPVID = V.ID WHERE B.PID=$this->id ORDER BY V.Priority");
				while ($row = mysql_fetch_array($result))
					$this->propvalues[$row[0]] = new Kiwi_DataRow($row);
		}
	}

	protected function saveData()
	{
		$flds = array('title', 'code', 'shortdesc', 'longdesc', 'collection', 'original_cost', 'new_cost', 'ws_cost', 'photo', 'discount', 'sellout', 'action', 'novelty', 'exposed', 'active');

		$ue = $this->rights->Admin || $this->rights->EShop['EditURLs'];

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

		foreach ($flds as $fld)
			$$fld = mysql_real_escape_string($this->$fld);

		if ($this->id) // úprava obsahu
		{
			$xp_sql = $photo ? ", Photo='$photo'" : '';
			$ue_sql = $ue ? ", URL='$url', PageTitle='$htitle'" : '';

			mysql_query("UPDATE products SET Title='$title', Code='$code', ShortDesc='$shortdesc'$ue_sql, LongDesc='$longdesc', Collection='$collection', OriginalCost='$original_cost', NewCost='$new_cost', WSCost='$ws_cost'$xp_sql, Discount='$discount', Sellout='$sellout', Action='$action', Novelty='$novelty', Exposed='$exposed', Active='$active', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->id");
		}
		elseif ($this->eshop_item) // vytvoření nového produktu a jeho navázání na položku eshopu
		{
			$result = mysql_query("SELECT Count(ID) FROM eshop WHERE ID=$this->eshop_item AND Subgroup=0");
			$row = mysql_fetch_row($result);
			if ($row[0] != 1) throw new Exception("Neplatná hodnota parametru \"ei\"");

			$result = mysql_query("SELECT MAX(Priority) FROM prodbinds WHERE GID=$this->eshop_item");
			$row = mysql_fetch_row($result);
			$priority = (int)$row[0] + 1;

			if ($ue)
			{
				$ue_sql1 = ', URL, PageTitle';
				$ue_sql2 = ",'$url', '$htitle'";
			}
			else
				$ue_sql1 = $ue_sql2 = '';

			mysql_query("INSERT INTO products(Title, Code, ShortDesc$ue_sql1, LongDesc, Collection, OriginalCost, NewCost, WSCost, Photo, Discount, Sellout, Action, Novelty, Exposed, Active) VALUES ('$title', '$code', '$shortdesc'$ue_sql2, '$longdesc', '$collection', $original_cost, $new_cost, $ws_cost, '$photo', $discount, $sellout, $action, $novelty, $exposed, $active)");
			$this->id = mysql_insert_id();
			mysql_query("INSERT INTO prodbinds(PID, GID, Priority) VALUES ($this->id, $this->eshop_item, $priority)");
			$mi_lastchange = new Kiwi_LastChange(array('eshopitems', $this->eshop_item));
			$mi_lastchange->register();
			$mi_lastchange = null;
		}
		else // vytvoření Neuho produktu
		{
			if ($ue)
			{
				$ue_sql1 = ', URL, PageTitle';
				$ue_sql2 = ",'$url', '$htitle'";
			}
			else
				$ue_sql1 = $ue_sql2 = '';

			mysql_query("INSERT INTO products(Title, Code, ShortDesc$ue_sql1, LongDesc, Collection, OriginalCost, NewCost, WSCost, Photo, Discount, Sellout, Action, Novelty, Exposed, Active) VALUES ('$title', '$code', '$shortdesc'$ue_sql2, '$longdesc', '$collection', $original_cost, $new_cost, $ws_cost, '$photo', $discount, $sellout, $action, $novelty, $exposed, $active)");
			$this->id = mysql_insert_id();
		}

		if (array_key_exists('new', $this->photo_extra))
		{
			$filename = mysql_real_escape_string($this->photo_extra['new']['FileName']);
			mysql_query("INSERT INTO prodepics(PID, FileName) VALUES ($this->id, '$filename')");
		}

		if (array_key_exists('new', $this->photo_illustrative))
		{
			$filename = mysql_real_escape_string($this->photo_illustrative['new']['FileName']);
			mysql_query("INSERT INTO prodipics(PID, FileName) VALUES ($this->id, '$filename')");
		}
	}

	protected function removeProductPhoto()
	{
		if (!$this->id) throw new Exception('Pokus odstranit fotografie produktu z nespecifikovaného produktu');
		$this->loadData();
		if ($this->photo)
		{
			$this->deleteProductFile($this->photo, array('detail', 'catalog', 'catalog2', 'collection'));
			mysql_query("UPDATE products SET Photo='', LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->id");
		}
		else throw new Exception('Pokus odstranit neexistující foto produktu');
	}

	protected function removeExtraPhoto($ei)
	{
		if (!$this->id) throw new Exception('Pokus odstranit doplňující foto z nespecifikovaného produktu');
		$this->loadData();
		if (array_key_exists($ei, $this->photo_extra))
		{
			$this->deleteProductFile($this->photo_extra[$ei]['FileName'], array('extra'));
			mysql_query("DELETE FROM prodepics WHERE ID=$ei AND PID=$this->id");
			mysql_query("UPDATE products SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->id");
		}
		else throw new Exception('Pokus odstranit neexistující doplňující foto produktu');
	}

	protected function removeIllustrativePhoto($ii)
	{
		if (!$this->id) throw new Exception('Pokus odstranit doplňující foto z nespecifikovaného produktu');
		$this->loadData();
		if (array_key_exists($ii, $this->photo_illustrative))
		{
			$this->deleteProductFile($this->photo_illustrative[$ii]['FileName'],  array('illustrative'));
			mysql_query("DELETE FROM prodipics WHERE ID=$ii AND PID=$this->id");
			mysql_query("UPDATE products SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->id");
		}
		else throw new Exception('Pokus odstranit neexistující ilustrativní foto produktu');
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
				if (!(@unlink("{$dir}$loc/$filename")))
				{
					//throw new Exception("Nepodařilo se smazat soubor s miniaturou fotografie");
				}
		}
	}

	protected function handleUploads()
	{
		global $eshop_picture_config;
		//$h = fopen('./logs/upload.log.html', 'a');

		if (isset($_FILES['upload1']) && !$_FILES['upload1']['error'])
		{
			$up1 = $this->handleUpload('upload1');
			if ($up1->upload())
				$this->createThumbs($this->photo = $up1->file_copy, array('detail', 'catalog', 'catalog2', 'collection'));
			$error_string = $up1->show_error_string();
			//fwrite($h, $error_string);
		}

		if ($eshop_picture_config['extra'] !== false && isset($_FILES['upload2']) && !$_FILES['upload2']['error'])
		{
			$up2 = $this->handleUpload('upload2');
			if ($up2->upload())
			{
				$this->createThumbs($up2->file_copy, array('extra'));
				$this->photo_extra['new'] = array ('FileName' => $up2->file_copy);
			}
			$error_string = $up2->show_error_string();
			//fwrite($h, $error_string);
		}

		if ($eshop_picture_config['illustrative'] !== false && isset($_FILES['upload3']) && !$_FILES['upload3']['error'])
		{
			$up3 = $this->handleUpload('upload3');
			if ($up3->upload())
			{
				$this->createThumbs($up3->file_copy, array('illustrative'));
				$this->photo_illustrative['new'] = array ('FileName' => $up3->file_copy);
			}
			$error_string = $up3->show_error_string();
			//fwrite($h, $error_string);
		}

		//fclose($h);
	}

	protected function handleUpload($upload)
	{
		$fu = new file_upload;
		$fu->upload_dir = KIWI_DIR_PRODUCTS . 'photo/';
		$fu->extensions = array('.jpg', '.png');
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

	protected function removePropertyValue($pvid)
	{
		if ($this->id == 0) return;

		$result = mysql_query("SELECT B.Photo, V.PID FROM prodpbinds AS B JOIN prodpvals AS V ON B.PPVID=V.ID WHERE B.PID=$this->id AND B.PPVID=$pvid");
		$row = mysql_fetch_row($result);
		$photo = $row[0];
		$propid = $row[1];

		$result = mysql_query('SELECT FOUND_ROWS()');
		$row = mysql_fetch_row($result);

		if ($row[0] > 0)
		{
			if ($photo != '') $this->deleteProductFile($photo, array('detail', 'catalog', 'catalog2', 'collection'));
			mysql_query("DELETE FROM prodpbinds WHERE PID=$this->id AND PPVID=$pvid LIMIT 1");
			mysql_query("UPDATE products SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->id");
		}

		return $propid;
	}

	protected function addPropertyValue($pvid)
	{
		if ($this->id == 0) return;

		$result = mysql_query("SELECT PID, ToCost FROM prodpvals WHERE ID=$pvid");
		$row = mysql_fetch_assoc($result);
		$propid = $row['PID'];
		$tocost = mysql_real_escape_string($row['ToCost']);

		mysql_query("SELECT * FROM prodpbinds WHERE PID=$this->id AND PPVID=$pvid LIMIT 1");
		$result = mysql_query('SELECT FOUND_ROWS()');
		$row = mysql_fetch_row($result);
		if ($row[0] == 0)
		{
			mysql_query("INSERT INTO prodpbinds(PID, PPVID, ToCost) VALUES ($this->id, $pvid, '$tocost')");
			mysql_query("UPDATE products SET LastChange=CURRENT_TIMESTAMP WHERE ID=$this->id");
			mysql_query("UPDATE prodbinds SET LastChange=CURRENT_TIMESTAMP WHERE PID=$this->id");
		}

		return $propid;
	}

	protected function addNewPropertyValue($propid, $pval)
	{
		if ($this->id == 0) return;

		$pvid = $this->registerNewPropertyValue($propid, $pval);
		$this->addPropertyValue($pvid);
	}

	protected function registerNewPropertyValue($propid, $pval)
	{
		$xpval = mysql_real_escape_string($pval);
		$result = mysql_query("SELECT ID FROM prodpvals WHERE PID=$propid AND Value='$xpval'");

		if ($row = mysql_fetch_row($result))
			return $row[0];

		$result = mysql_query("SELECT Max(Priority) FROM prodpvals");

		if ($row = mysql_fetch_row($result))
			$priority = (int)$row[0] + 1;
		else
		  $priority = 1;

		mysql_query("INSERT INTO prodpvals(PID, Value, Priority) VALUES ($propid, '$xpval', $priority)");
		return mysql_insert_id();
	}

	protected function acquireGroupedProductsGroup()
	{
		global $kiwi_config;
		$newgid = null;
		$this->loadData();
		if (!empty($this->group)) // existuje sdružovací řada k tomuto produktu?
			$gid = $this->group[0];
		else
		{
			$grouped_products_group = $kiwi_config['eshop']['grouped_products_group'];
			mysql_query("LOCK TABLES eshop WRITE, eshop AS E READ, prodbinds WRITE, prodbinds AS PB READ");
			$result = mysql_query("SELECT E.ID FROM eshop AS E LEFT OUTER JOIN prodbinds AS PB ON E.ID=PB.GID WHERE E.parent=$grouped_products_group GROUP BY E.ID HAVING Count(PB.GID)=0 LIMIT 1");
			$name = mysql_real_escape_string('Gruppiert mit ' . $this->title);
			if ($row = mysql_fetch_row($result)) // existuje nějaká prázdná sdružovací řada?
			{
				$gid = $row[0];
				$result = mysql_query("UPDATE eshop SET Name='$name' WHERE ID=$gid");
			}
			else
			{
				$result = mysql_query("SELECT MAX(E.Priority) FROM eshop AS E WHERE E.Parent=$grouped_products_group");
				$row = mysql_fetch_row($result);
				$priority = (int)$row[0] + 1;
				mysql_query("INSERT INTO eshop(Name, Subgroup, Parent, Priority, Active) VALUES ('$name', 0, $grouped_products_group, $priority, 1)");
				$newgid = $gid = mysql_insert_id();
			}
			mysql_query("INSERT INTO prodbinds(PID, GID, Priority, Active) VALUES ($this->id, $gid, 0, 1)");
			mysql_query("UNLOCK TABLES");
			if ($newgid !== null)
				Kiwi_EShop_Indexer::index($newgid, $grouped_products_group);
		}

		return $gid;
	}

	protected function generateURL()
	{
		$this->url = Kiwi_URL_Generator::generate($this->title);
	}

	protected function generateTitle()
	{
		$this->htitle = $this->title;
	}

	protected function getColorPropertyIcon($value)
	{
		$propdir = KIWI_DIR_PRODUCTS . 'properties/';
		$colors_a = array_map('strtolower', preg_split('/[,;\|]/', $value['extradata']));
		$picname = htmlspecialchars(implode('', $colors_a) . '.gif');
		return "{$propdir}colors/$picname";
	}

	protected function getIconPropertyIcon($value)
	{
		$propdir = KIWI_DIR_PRODUCTS . 'properties/';
		$picname = htmlspecialchars($value['extradata']);
		return "{$propdir}icons/$picname";
	}

	protected function redirectLevelUpLink()
	{
		$qsa = array();
		$page = KIWI_PRODUCTS;
		$hash = '';

		if ($this->eshop_item)
		{
			$page = KIWI_EDIT_ESHOPITEM;
			$qsa[] = "ei=$this->eshop_item";
		}
		elseif ($this->s_eshop_item)
		{
			$qsa[] = "ei=$this->s_eshop_item";
		}
		else
		{
			$hash = '#zmena';
		}

		if ($this->sqs !== null)
		{
			$sqs = urldecode($this->sqs);
			$qsa[] = $sqs;
		}

		if ($this->grouped_product !== null)
			$qsa[] = "gp=$this->grouped_product";

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

	protected function consQS()
	{
		$qsa = array();

		if ($this->id)
			$qsa[] = "p=$this->id";

		if ($this->eshop_item)
			$qsa[] = "ei=$this->eshop_item";

		if ($this->s_eshop_item)
			$qsa[] = "sei=$this->s_eshop_item";

		if ($this->grouped_product)
			$qsa[] = "gp=$this->grouped_product";

		if ($this->sqs)
			$qsa[] = "sqs=" . urlencode($this->sqs);

		if (empty($qsa)) $qs = '';
		else $qs = '?' . implode('&', $qsa);

		return $qs;
	}
}
?>