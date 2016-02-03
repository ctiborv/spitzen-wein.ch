<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_productmenu.class.php';
require_once 'kiwi_url_generator.class.php';

class Kiwi_EShop_SEO_Form extends Page_Item
{
	protected $rights;
	protected $read_only;
	protected $all_checked;
	protected $catalog_menu;
	protected $index;
	protected $checked;
	protected $lastchange;

	public function __construct(&$rights)
	{
		parent::__construct();

		$this->rights = $rights->SEO;
		if (is_array($this->rights))
			$this->read_only = !$this->rights['Write'];
		else $this->read_only = !$this->rights;
		$this->all_checked = false;
		$this->catalog_menu = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadCatalogMenu();

		mb_internal_encoding("UTF-8");

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->consQS();

		$readonly_str = $this->read_only ? ' readonly' : '';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>SEO Gruppen und Serie - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$disabled_str = (sizeof($this->catalog_menu) == 0 || $this->read_only) ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_EShop_SEO_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Bezeichnung</th>

EOT;
		if (!$this->read_only)
			$html .= <<<EOT
					<th><a href="#" onclick="return Kiwi_EShop_SEO_Form.recoverURLs()"><img src="./image/recover-url.gif" title="Wiederherstellen automatisch generierte URL um alle Artikel" /></a></th>
					<th>URL</th>
					<th><a href="#" onclick="return Kiwi_EShop_SEO_Form.recoverPageTitles()"><img src="./image/recover-title.gif" title="Wiederherstellen eines automatisch generierten HTML-Titel um alle Artikel" /></a></th>
					<th>HTML-Titel</th>
				</tr>

EOT;
		else
			$html .= <<<EOT
					<th></th>
					<th>URL</th>
					<th></th>
					<th>HTML-Titel</th>
				</tr>

EOT;

		$sw = 1;
		$next_sw = array(1 => 2, 2 => 1);

		foreach ($this->catalog_menu as $item)
		{
			//if ($menuitem->ID == 1) continue; // root element
			$menuitem = $item['data'];
			$checked_str = (isset($this->checked[$menuitem->ID]) && $this->checked[$menuitem->ID]) ? ' checked' : '';

			$name = htmlspecialchars($menuitem->Name);
			$prefix = str_repeat('&emsp;', $item['depth'] - 1);
			if ($menuitem->Subgroup)
			{
				$icon = 'slozka.gif';
				$icontitle = 'Gruppe';
				$link = KIWI_ESHOP . "?sg=$menuitem->ID";
			}
			else
			{
				$icon = 'rada.gif';
				$icontitle = 'Serie';
				$link = KIWI_ESHOPITEM . "?ei=$menuitem->ID";
			}

/*
			$dt = parseDateTime($menuitem->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);
*/
			$url = htmlspecialchars($menuitem->URL);
			$pagetitle = htmlspecialchars($menuitem->PageTitle);

			$auto_url = $this->escapeArgJS($this->generateURL($menuitem->Name));
			$auto_pagetitle = $this->escapeArgJS($this->generateTitle($menuitem->Name));

			if (!$this->read_only)
			{
				$recover_url_str = <<<EOT
	<a href="#" name="r_url" onclick="return Kiwi_EShop_SEO_Form.recoverURL($menuitem->ID, '$auto_url')"><img src="./image/recover-url.gif" title="Wiederherstellen automatisch generierte URL" /></a>
EOT;
				$recover_pagetitle_str = <<<EOT
	<a href="#" name="r_title" onclick="return Kiwi_EShop_SEO_Form.recoverPageTitle($menuitem->ID, '$auto_pagetitle')"><img src="./image/recover-title.gif" title="Wiederherstellen eines automatisch generierten HTML-Titel" /></a>
EOT;
			}
			else
				$recover_url_str = $recover_pagetitle_str = '';

			$url_edit = <<<EOT
<input type="text" id="url$menuitem->ID" name="url$menuitem->ID" value="$url" class="inpOUT4" onfocus="this.className='inpON4'" onblur="this.className='inpOUT4'" onchange="Kiwi_EShop_SEO_Form.setCCheck($menuitem->ID)" onkeyup="Kiwi_EShop_SEO_Form.onKeyUp($menuitem->ID, event)"$readonly_str />
EOT;
			$pagetitle_edit = <<<EOT
<input type="text" id="pt$menuitem->ID" name="pt$menuitem->ID" value="$pagetitle" class="inpOUT4" onfocus="this.className='inpON4'" onblur="this.className='inpOUT4'" onchange="Kiwi_EShop_SEO_Form.setCCheck($menuitem->ID)" onkeyup="Kiwi_EShop_SEO_Form.onKeyUp($menuitem->ID, event)"$readonly_str />
EOT;

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" id="chb$menuitem->ID" name="check[]" value="$menuitem->ID" onclick="Kiwi_EShop_SEO_Form.enableBtns(this.checked)"$checked_str$disabled_str /></td>
					<td>$prefix<img src="./image/$icon" alt="" title="$icontitle" />&nbsp;<a href="$link">$name</a></td>
					<td>$recover_url_str</td>
					<td>$url_edit</td>
					<td>$recover_pagetitle_str</td>
					<td>$pagetitle_edit</td>
				</tr>

EOT;

			$sw = $next_sw[$sw];
		}

		if (sizeof($this->checked) == 0)
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
			</table>
		</div>
	</div>
	<div class="form2">
		<fieldset>
			<input type="submit" id="kesfc_cmd1" name="cmd" value="speichern" class="$but_class"$disabled_str />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		if (!empty($get))
		{
			if (array_key_exists('l', $get))
			{
				if ($get['l'] == 'all')
					$this->letter = null;
				else
				{
					$ltr = strtoupper(substr($get['l'], 0, 2));
					if ($ltr != 'CH')
						$ltr = substr($ltr, 0, 1);
					$this->letter = $ltr;
				}
			}
		}

		if (!$this->read_only && !empty($post))
		{
			$this->all_checked = isset($post['checkall']);
			if (isset($post['check']) && is_array($post['check']))
				foreach ($post['check'] as $value)
				{
					if (!is_numeric($value)) throw new Exception("Nepovolený vstup: check[]");
					$this->checked[$value] = true;
				}

			switch ($post['cmd'])
			{
				case 'speichern':
					$this->saveData(strip_gpc_slashes($post));
					$qs = $this->consQS();
					$this->redirection = KIWI_ESHOP_SEO . $qs;
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
	}

	protected function saveData(&$post)
	{
		mysql_query("START TRANSACTION");
		foreach ($post['check'] as $id)
		{
			$url = mysql_real_escape_string($post["url$id"]);
			$pagetitle = mysql_real_escape_string($post["pt$id"]);
			if (!mysql_query("UPDATE eshop SET URL='$url', PageTitle='$pagetitle', LastChange=CURRENT_TIMESTAMP WHERE ID=$id"))
			{
				mysql_query("ROLLBACK");
				return;
			}
			$this->loadLastChange(false);
			$this->lastchange->register();
		}
		mysql_query("COMMIT");
	}

	protected function loadLastChange($acquire = true)
	{
		if ($this->lastchange == null)
			$this->lastchange = new Kiwi_LastChange('eshop', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadCatalogMenu()
	{
		if ($this->catalog_menu === null)
		{
			$this->catalog_menu = new Kiwi_ProductMenu;
		}
	}

	protected function generateURL($str)
	{
		return Kiwi_URL_Generator::generate($str);
	}

	protected function generateTitle($str)
	{
		return $str;
	}

	protected function escapeArgJS($str)
	{
		$str = mb_ereg_replace("'", '\\x27', $str);
		$str = mb_ereg_replace('"', '\\x22', $str);
		return $str;
	}

	protected function consQS()
	{
		return '';
	}
}
?>