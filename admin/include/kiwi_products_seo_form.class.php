<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_url_generator.class.php';

class Kiwi_Products_SEO_Form extends Page_Item
{
	protected $read_only;
	protected $all_checked;
	protected $products;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $letter;
	protected $fletters;
	protected $page;

	protected static $records_per_page = 50;

	public function __construct(&$rights)
	{
		parent::__construct();

		$srights = $rights->SEO;
		if (is_array($srights))
			$this->read_only = !$srights['Write'];
		else $this->read_only = !$srights;
		$this->all_checked = false;
		$this->products = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->letter = null;
		$this->fletters = array();
		$this->page = 1;
	}

	function _getHTML()
	{
		$this->loadLastChange();
		$this->loadProducts();

		mb_internal_encoding("UTF-8");

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->consQS();

		$readonly_str = $this->read_only ? ' readonly' : '';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>SEO Artikel - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$tabs_html = '';
		$tqs = $this->consQS(true);
		$tqsep = $tqs == '' ? '?' : '&';

		foreach ($this->fletters as $fletter => $flcount)
		{
			$fletterqs = urlencode($fletter);
			if ($this->letter == $fletter)
				$tabs_html .= <<<EOT

			<span title="$flcount">$fletter</span>
EOT;
			elseif ($flcount == 0)
				$tabs_html .= <<<EOT

			<b>$fletter</b>
EOT;
			else
				$tabs_html .= <<<EOT

			<a href="$self${tqs}${tqsep}l=$fletterqs" title="$flcount">$fletter</a>
EOT;
		}

		if ($this->letter === null)
			$prod_count = sizeof($this->products);
		else
			$prod_count = array_key_exists($this->letter, $this->fletters) ? $this->fletters[$this->letter] : 0;

		$pages = (int)($prod_count / self::$records_per_page + 1);

		$pages_html = '';
		if ($pages > 1)
		{
			$pqs = $this->consQS();
			$pqs .= $pqs == '' ? '?' : '&';

			for ($pg = 1; $pg <= $pages; $pg++)
			{
				if ($pg == $this->page)
					$pages_html .= <<<EOT

			<span>$pg</span>
EOT;
				else
					$pages_html .= <<<EOT

			<a href="$self{$pqs}pg=$pg">$pg</a>
EOT;
			}
		}

		$html .= <<<EOT
		<div id="zalozky">$tabs_html

EOT;

		$totalcount = array_sum($this->fletters);
		if ($this->letter === null)
			$html .= <<<EOT
			<span title="$totalcount">Alles</span>

EOT;
		else
			$html .= <<<EOT
			<a href="$self${tqs}" title="$totalcount">Alles</a>

EOT;

		$html .= <<<EOT
			<br class="clear" />
		</div>

EOT;

		if ($pages_html !== '')
			$html .= <<<EOT
		<div id="stranky">Seite: $pages_html
			<br class="clear" />
		</div>

EOT;

		$disabled_str = (sizeof($this->products) == 0 || $this->read_only) ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Products_SEO_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Produkt</th>

EOT;
		if (!$this->read_only)
			$html .= <<<EOT
					<th><a href="#" onclick="return Kiwi_Products_SEO_Form.recoverURLs()"><img src="./image/recover-url.gif" title="Wiederherstellen automatisch generierte URL um alle Artikel" /></a></th>
					<th>URL</th>
					<th><a href="#" onclick="return Kiwi_Products_SEO_Form.recoverPageTitles()"><img src="./image/recover-title.gif" title="Wiederherstellen eines automatisch generierten HTML-Titel um alle Artikel" /></a></th>
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

		$pi_end = $this->page * self::$records_per_page;
		$pi_start = $pi_end - self::$records_per_page + 1;
		$pi = 0;

		foreach ($this->products as $product)
		{
			if ($this->letter !== null)
			{
				$ltr = mb_strtoupper(mb_substr($product->Title, 0, 2));
				if ($ltr != 'CH')
					$ltr = $this->removeDiaT1(mb_substr($ltr, 0, 1));
				if ($ltr != $this->letter) continue;
			}

			$pi += 1;
			if ($pi < $pi_start || $pi > $pi_end) continue;

			$checked_str = (isset($this->checked[$product->ID]) && $this->checked[$product->ID]) ? ' checked' : '';

			$title = htmlspecialchars($product->Title);

			$plink = KIWI_EDIT_PRODUCT . "?p=$product->ID";
/*
			$dt = parseDateTime($product->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);
*/
			$url = htmlspecialchars($product->URL);
			$pagetitle = htmlspecialchars($product->PageTitle);

			$auto_url = $this->escapeArgJS($this->generateURL($product->Title));
			$auto_pagetitle = $this->escapeArgJS($this->generateTitle($product->Title));

			if (!$this->read_only)
			{
				$recover_url_str = <<<EOT
	<a href="#" name="r_url" onclick="return Kiwi_Products_SEO_Form.recoverURL($product->ID, '$auto_url')"><img src="./image/recover-url.gif" title="Wiederherstellen automatisch generierte URL" /></a>
EOT;
				$recover_pagetitle_str = <<<EOT
	<a href="#" name="r_title" onclick="return Kiwi_Products_SEO_Form.recoverPageTitle($product->ID, '$auto_pagetitle')"><img src="./image/recover-title.gif" title="Wiederherstellen eines automatisch generierten HTML-Titel" /></a>
EOT;
			}
			else
				$recover_url_str = $recover_pagetitle_str = '';

			$url_edit = <<<EOT
<input type="text" id="url$product->ID" name="url$product->ID" value="$url" class="inpOUT4" onfocus="this.className='inpON4'" onblur="this.className='inpOUT4'" onchange="Kiwi_Products_SEO_Form.setCCheck($product->ID)" onkeyup="Kiwi_Products_SEO_Form.onKeyUp($product->ID, event)"$readonly_str />
EOT;
			$pagetitle_edit = <<<EOT
<input type="text" id="pt$product->ID" name="pt$product->ID" value="$pagetitle" class="inpOUT4" onfocus="this.className='inpON4'" onblur="this.className='inpOUT4'" onchange="Kiwi_Products_SEO_Form.setCCheck($product->ID)" onkeyup="Kiwi_Products_SEO_Form.onKeyUp($product->ID, event)"$readonly_str />
EOT;

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" id="chb$product->ID" name="check[]" value="$product->ID" onclick="Kiwi_Products_SEO_Form.enableBtns(this.checked)"$checked_str$disabled_str /></td>
					<td><a href="$plink">$title</a></td>
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
			<input type="submit" id="kpsfc_cmd1" name="cmd" value="speichern" class="$but_class"$disabled_str />
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

			if (array_key_exists('pg', $get))
			{
				$pg = (int)$get['pg'];
				if ($pg > 0) $this->page = $pg;
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
					$this->redirection = KIWI_PRODUCTS_SEO . $qs;
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
			if (!mysql_query("UPDATE products SET URL='$url', PageTitle='$pagetitle', LastChange=CURRENT_TIMESTAMP WHERE ID=$id"))
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
			$this->lastchange = new Kiwi_LastChange('products', 'j.n. Y - H:i');
		if ($acquire)
			$this->lastchange->acquire();
	}

	protected function loadProducts()
	{
		if (!is_array($this->products))
		{
			$this->products = array();
			mb_internal_encoding("UTF-8");

			for ($c = ord('A'); $c <= ord('H'); $c++)
				$this->fletters[chr($c)] = 0;

			$this->fletters['CH'] = 0;

			for ($c = ord('I'); $c <= ord('Z'); $c++)
				$this->fletters[chr($c)] = 0;

			if ($result = mysql_query("SELECT ID, Title, URL, PageTitle FROM products ORDER BY Title"))
			{
				$i = 0;
				while ($row = mysql_fetch_object($result))
				{
					$this->products[$i] = new Kiwi_DataRow($row);
					$this->index[(int)$row->ID] = $i;
					$i++;
					$ltr = mb_strtoupper(mb_substr($row->Title, 0, 2));
					if ($ltr != 'CH')
						$ltr = $this->removeDiaT1(mb_substr($ltr, 0, 1));
					if (!array_key_exists($ltr, $this->fletters))
						$this->fletters[$ltr] = 0;
					$this->fletters[$ltr]++;
				}
			}
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

	protected function removeDiaT1($char)
	{
		$replacements = array
		(
			'Á' => 'A',
			'Ä' => 'A',
			'Č' => 'C',
			'Ď' => 'D',
			'É' => 'E',
			'Ě' => 'E',
			'Ë' => 'E',
			'Í' => 'I',
			'Ľ' => 'L',
			'Ö' => 'O',
			'Ň' => 'N',
			'Ó' => 'O',
			'Ř' => 'R',
			'Š' => 'S',
			'Ť' => 'T',
			'Ú' => 'U',
			'Ů' => 'U',
			'Ü' => 'U',
			'Ý' => 'Y',
			'Ž' => 'Z'
		);

		if (array_key_exists($char, $replacements))
			return $replacements[$char];
		else
			return $char;
	}

	protected function consQS($skipl = false)
	{
		$qsa = array();

		if (!$skipl)
		{
			if ($this->letter !== null)
				$qsa[] = 'l=' . urlencode($this->letter);
		}

		if (empty($qsa))
			$qs = '';
		else
			$qs = '?' . implode('&', $qsa);

		return $qs;
	}
}
?>