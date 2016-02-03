<?php
require_once 'project.inc.php';
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_datarow.class.php';
require_once 'kiwi_anchor.class.php';

class Kiwi_Products_Form extends Page_Item
{
	protected $all_checked;
	protected $products;
	protected $index;
	protected $checked;
	protected $lastchange;
	protected $eshop_item; // pro připojování produktů k dané řadě
	protected $newsletter; // pro připojování produktů k newsletteru
	protected $anchor;
	protected $letter;
	protected $fletters;
	protected $page;
	protected $sort;
	protected $filter;
	protected $grouped_product;

	protected static $records_per_page = 50;

	const MAX_TITLE_LEN = 70;

	const SORT_BY_ALPHABET = 'a';
	const SORT_BY_TIME = 't';

	const ASCENDING_ORDER = 'a';
	const DESCENDING_ORDER = 'd';

	const FILTER_TITLE_OR_CODE = 'tc';

	public function __construct()
	{
		parent::__construct();
		$this->all_checked = false;
		$this->products = null;
		$this->index = array();
		$this->checked = array();
		$this->lastchange = null;
		$this->eshop_item = false;
		$this->anchor = new CurrentKiwiAnchor();
		$this->letter = null;
		$this->fletters = array();
		$this->page = 1;
		$this->sort = array
		(
			'by' => self::SORT_BY_ALPHABET,
			'order' => self::ASCENDING_ORDER
		);
		$this->filter = array();
		$this->grouped_product = null;
	}

	public function _getHTML()
	{
		$this->loadLastChange();
		$this->loadProducts();

		$self = basename($_SERVER['PHP_SELF']);
		$qs = $this->consQS();
		$qsep = $qs ? '&' : '?';

		$html = <<<EOT
<form action="$self$qs" method="post">
	<h2>KATALOG Artikel - [Liste]</h2>
	<div class="levyV">

EOT;

		if ($this->lastchange != null)
			$html .= <<<EOT
		<div class="zmena">Zuletzt Aktualisiert: {$this->lastchange->format()}</div>

EOT;

		$tabs_html = '';
		$tqs = $this->consQS(true, true);
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
			$pqs = $this->consQS(false, true);
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

		$search = htmlspecialchars($this->getSearchBoxText());

		$html .= <<<EOT
		<div class="searchbox">
			<input type="text" id="kprfc_sbox" name="search" value="$search" class="searchinp" />
			<!--[if IE]><input type="text" style="display: none;" disabled="disabled" size="1" /><![endif]-->
			<input type="submit" id="kprfc_sbtn" name="cmd" value="suchen" class="searchbtn" />
		</div>
		<br class="clear" />
		</div>

EOT;

		if ($pages_html !== '')
			$html .= <<<EOT
		<div id="stranky">Seite: $pages_html
			<br class="clear" />
		</div>

EOT;

		$disabled_str = sizeof($this->products) == 0 ? ' disabled' : '';

		$all_checked_str = $this->all_checked ? ' checked' : '';

		$html .= <<<EOT
		<div id="frame">
			<table class="tab-seznam" cellspacing="0" cellpadding="0">
				<tr>
					<th><input type="checkbox" name="checkall" value="Vsechny"$disabled_str onclick="checkUncheckAll(document.getElementsByName('check[]'),this);Kiwi_Products_Form.enableBtns(false);"$all_checked_str /></th>
					<th>Produkt</th>
					<th>Preis</th>
					<th><span title="Neu">N</span></th>
					<th><span title="Aktion">Ak</span></th>
					<th><span title="Rabatt">R</span></th>
					<th><span title="Ausferkauf">Au</span></th>
					<th>Nutzung</th>
					<th></th>
					<th>geändert</th>
					<th>aktiv</th>
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

			if (mb_strlen($product->Title) > self::MAX_TITLE_LEN)
			{
				$title = htmlspecialchars(mb_substr($product->Title, 0, self::MAX_TITLE_LEN) . '...');
			}
			else
				$title = htmlspecialchars($product->Title);

			$plink = KIWI_EDIT_PRODUCT . "?p=$product->ID";
			if ($this->eshop_item) $plink .= "&sei=$this->eshop_item";

			$sqs = $this->saveQS();
			if ($sqs) $plink .= "&sqs=$sqs";

			$anchor_str = ($this->anchor->ID == $product->ID) ? ' id="zmena"' : '';

			$new_cost = htmlspecialchars(sprintf("%01.2f", $product->NewCost));

			if ($product->Photo)
			{
				$photo = 'photoOK';
				$phototitle = 'Artikel mit Foto';
			}
			else
			{
				$photo = 'photoKO';
				$phototitle = 'Artikel ohne Foto';
			}
			$photostr = <<<EOT
<img src="./image/$photo.gif" alt="" title="$phototitle" />
EOT;

			$novelty = $product->Novelty != 0 ? 'ja' : 'nein';
			$action = $product->Action != 0 ? 'ja' : 'nein';
			$discount = $product->Discount != 0 ? 'ja' : 'nein';
			$sellout = $product->Sellout != 0 ? 'ja' : 'nein';

			if ($product->Usage)
			{
				$usage = $product->Usage;
				if ($product->Usage > $product->ActiveUsage)
				{
					$aktivni = 'aktiv';
					//if ($product->ActiveUsage > 4) $aktivni .= 'ch'; // only for czech lang
					$usage .= " ($product->ActiveUsage $aktivni)";
				}
			}
			else
				$usage = '0';

			$dt = parseDateTime($product->LastChange);
			$lastchange = date('j.n.Y H:i', $dt['stamp']);

			$active = $product->Active != 0 ? 'ja' : 'nein';

			$html .= <<<EOT
				<tr class="t-s-$sw">
					<td><input type="checkbox" name="check[]" value="$product->ID" onclick="Kiwi_Products_Form.enableBtns(this.checked)"$checked_str /></td>
					<td><a href="$plink"$anchor_str>$title</a></td>
					<td>$new_cost</td>
					<td><a href="$self$qs{$qsep}tn=$product->ID">$novelty</a></td>
					<td><a href="$self$qs{$qsep}ta=$product->ID">$action</a></td>
					<td><a href="$self$qs{$qsep}td=$product->ID">$discount</a></td>
					<td><a href="$self$qs{$qsep}ts=$product->ID">$sellout</a></td>
					<td>$usage</td>
					<td>$photostr</td>
					<td>$lastchange</td>
					<td><a href="$self$qs{$qsep}as=$product->ID">$active</a></td>
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

EOT;

		if ($this->eshop_item || $this->newsletter)
			$html .= <<<EOT
			<input type="submit" id="kprfc_cmd5" name="cmd" value="zufügen" class="$but_class"$disabled_str />

EOT;
		else
			$html .= <<<EOT
			<input type="submit" id="kprfc_cmd1" name="cmd" value="Artikel hinzufügen" class="but3" />

EOT;
		$html .= <<<EOT
			<input type="submit" id="kprfc_cmd2" name="cmd" value="entfernen" class="$but_class"$disabled_str onclick="return Kiwi_Products_Form.onDelete()" />
			<input type="submit" id="kprfc_cmd3" name="cmd" value="aktivieren" class="$but_class"$disabled_str />
			<input type="submit" id="kprfc_cmd4" name="cmd" value="deaktivieren" class="$but_class"$disabled_str />
		</fieldset>
	</div>
</form>

EOT;

		return $html;
	}

	public function handleInput($get, $post)
	{
		global $kiwi_config;
		// todo: dořešit práva
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

			if (isset($get['gp']))
			{
				if (($this->grouped_product = (int)$get['gp']) < 1)
					throw new Exception("Neplatné ID sdruženého produktu: $this->grouped_product");
			}

			if (array_key_exists('s', $get))
			{
				$sort = $get['s'];
				if (isset($sort[0]))
					switch ($sort[0])
					{
						case self::SORT_BY_ALPHABET:
						case self::SORT_BY_TIME:
							$this->sort['by'] = $sort[0];
							break;
					}

				if (isset($sort[1]))
					switch ($sort[1])
					{
						case self::ASCENDING_ORDER:
						case self::DESCENDING_ORDER:
							$this->sort['order'] = $sort[1];
							break;
					}
			}

			if (array_key_exists('f', $get) && is_array($get['f']))
				$this->filter = $get['f'];

			if (array_key_exists('ei', $get))
			{
				if (($ei = (int)$get['ei']) < 1)
					throw new Exception("Neplatná hodnota parametru \"ei\": $ei");

				$this->eshop_item = $ei;
			}

			if (array_key_exists('nl', $get))
			{
				if (!$this->productsEnabled()) {
					throw new Exception("Neplatný vstup - produkty newsletterů nejsou aktivovány");
				}

				if (($nl = (int)$get['nl']) < 1)
					throw new Exception("Neplatná hodnota parametru \"nl\": $nl");

				$this->newsletter = $nl;
			}

			if (array_key_exists('as', $get))
			{
				$this->loadLastChange();
				$this->loadProducts();

				if (($as = (int)$get['as']) < 1 || !isset($this->index[$as]))
					throw new Exception("Neplatné ID záznamu: $as");

				$nas = !$this->products[$this->index[$as]]->Active;

				mysql_query("UPDATE products SET Active='$nas', LastChange=CURRENT_TIMESTAMP WHERE ID=$as");

				$this->products[$this->index[$as]]->Active = $nas;
				$this->products[$this->index[$as]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $as;
				$qs = $this->consQS();
				$this->redirection = KIWI_PRODUCTS . $qs . '#zmena';
			}

			if (array_key_exists('tn', $get))
			{
				$this->loadLastChange();
				$this->loadProducts();

				if (($tn = (int)$get['tn']) < 1 || !isset($this->index[$tn]))
					throw new Exception("Neplatné ID záznamu: $tn");

				$ntn = !$this->products[$this->index[$tn]]->Novelty;

				mysql_query("UPDATE products SET Novelty='$ntn', LastChange=CURRENT_TIMESTAMP WHERE ID=$tn");

				$this->products[$this->index[$tn]]->Novelty = $ntn;
				$this->products[$this->index[$tn]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $tn;
				$qs = $this->consQS();
				$this->redirection = KIWI_PRODUCTS . $qs . '#zmena';
			}

			if (array_key_exists('ta', $get))
			{
				$this->loadLastChange();
				$this->loadProducts();

				if (($ta = (int)$get['ta']) < 1 || !isset($this->index[$ta]))
					throw new Exception("Neplatné ID záznamu: $ta");

				$nta = !$this->products[$this->index[$ta]]->Action;

				mysql_query("UPDATE products SET Action='$nta', LastChange=CURRENT_TIMESTAMP WHERE ID=$ta");

				$this->products[$this->index[$ta]]->Action = $nta;
				$this->products[$this->index[$ta]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $ta;
				$qs = $this->consQS();
				$this->redirection = KIWI_PRODUCTS . $qs . '#zmena';
			}

			if (array_key_exists('td', $get))
			{
				$this->loadLastChange();
				$this->loadProducts();

				if (($td = (int)$get['td']) < 1 || !isset($this->index[$td]))
					throw new Exception("Neplatné ID záznamu: $td");

				$ntd = !$this->products[$this->index[$td]]->Discount;

				mysql_query("UPDATE products SET Discount='$ntd', LastChange=CURRENT_TIMESTAMP WHERE ID=$td");

				$this->products[$this->index[$td]]->Discount = $ntd;
				$this->products[$this->index[$td]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $td;
				$qs = $this->consQS();
				$this->redirection = KIWI_PRODUCTS . $qs . '#zmena';
			}

			if (array_key_exists('ts', $get))
			{
				$this->loadLastChange();
				$this->loadProducts();

				if (($ts = (int)$get['ts']) < 1 || !isset($this->index[$ts]))
					throw new Exception("Neplatné ID záznamu: $ts");

				$nts = !$this->products[$this->index[$ts]]->Sellout;

				mysql_query("UPDATE products SET Sellout='$nts', LastChange=CURRENT_TIMESTAMP WHERE ID=$ts");

				$this->products[$this->index[$ts]]->Sellout = $nts;
				$this->products[$this->index[$ts]]->LastChange = date('Y-m-d H:i', time());
				$this->lastchange->register();
				$this->lastchange = null;
				$this->anchor->ID = $ts;
				$qs = $this->consQS();
				$this->redirection = KIWI_PRODUCTS . $qs . '#zmena';
			}
		}

		if (!empty($post))
		{
			$this->all_checked = isset($post['checkall']);
			if (isset($post['check']) && is_array($post['check']))
				foreach ($post['check'] as $value)
				{
					if (!is_numeric($value)) throw new Exception("Nepovolený vstup: check[]");
					$this->checked[$value] = true;
				}

			$act = 0;

			switch ($post['cmd'])
			{
				case 'suchen':
					$this->filter[self::FILTER_TITLE_OR_CODE] = $post['search'];
					$qs = $this->consQS(true, true, false);
					$this->redirection = KIWI_PRODUCTS . $qs;
					break;
				case 'aktivieren': $act = 1;
				case 'deaktivieren':
					$id_list = implode(',', $post['check']);
					if ($id_list)
						mysql_query("UPDATE products SET Active=$act, LastChange=CURRENT_TIMESTAMP WHERE ID IN ($id_list)");
					$this->loadLastChange(false);
					$this->lastchange->register();
					$this->lastchange = null;
					$qs = $this->consQS();
					$this->redirection = KIWI_PRODUCTS . $qs;
					break;
				case 'Artikel hinzufügen':
					$this->redirection = KIWI_ADD_PRODUCT;
					break;
				case 'zufügen':
					if (!$this->eshop_item && !$this->newsletter || $this->eshop_item && $this->newsletter)
						throw new Exception("Pokus o připojení produktu/ů k neznámému cíli.");
					if ($this->newsletter)
					{
						$this->addToNewsletter($post);
						break;
					}
					else
					{
						$result = mysql_query("SELECT Parent FROM eshop WHERE ID=$this->eshop_item");
						if ($row = mysql_fetch_row($result))
						{
							$parent = (int)$row[0];
							$result = mysql_query("SELECT Max(Priority) FROM prodbinds WHERE GID=$this->eshop_item");
							if ($row = mysql_fetch_row($result))
								$priority = (int)$row[0] + 1;
							else
								throw new Exception("Chyba při načítání priority produktů položky eshopu");

							// kvůli kontrole vícenásobného připojení téhož produktu do téže řady
							// (narozdíl od modulů nepřipouštíme vícenásobnou vazbu)
							$old_values = array();
							$result = mysql_query("SELECT PID FROM prodbinds WHERE GID=$this->eshop_item");
							while ($row = mysql_fetch_row($result))
								$old_values[$row[0]] = true;

							// kvůli kontrole připojení do většího počtu řad sdružených produktů než jedna
							$grouped_products_group = $kiwi_config['eshop']['grouped_products_group'];
							$grouped_products = array();
							if ($parent == $grouped_products_group)
							{
								$result = mysql_query("SELECT PB.PID FROM prodbinds AS PB JOIN eshop AS E ON PB.GID=E.ID WHERE E.Parent=$grouped_products_group GROUP BY PB.PID");
								while ($row = mysql_fetch_row($result))
									$grouped_products[$row[0]] = true;
							}

							$values = array();
							foreach ($post['check'] as $item)
							{
								if (isset($old_values[$item])) continue; // produkt již je připojen k této řadě
								if (isset($grouped_products[$item])) continue; // produkt již je sdružen v nějaké sdružovací řadě
								$values[] = "($item, $this->eshop_item, $priority)";
								$priority++;
							}

							if (count($values) > 0)
								mysql_query('INSERT INTO prodbinds(PID, GID, Priority) VALUES ' . implode(', ', $values));
							if ($this->grouped_product)
								$gp_qs = "&gp=$this->grouped_product";
							else
								$gp_qs = '';
							$this->redirection = KIWI_EDIT_ESHOPITEM . "?ei=$this->eshop_item" . $gp_qs;
							break;
						}
						throw new Exception("Chyba při pokusu připojit produkt k dané položce eshopu");
					}
					break;
				case 'entfernen':
					$id_list = implode(',', $post['check']);
					if ($id_list)
					{
						$this->deletePictureFiles($id_list);
						mysql_query("DELETE FROM prodepics WHERE PID IN ($id_list)");
						mysql_query("DELETE FROM prodipics WHERE PID IN ($id_list)");
						mysql_query("DELETE FROM products WHERE ID IN ($id_list)");
						$this->registerEItemsChanges($id_list);
						mysql_query("DELETE FROM prodbinds WHERE PID IN ($id_list)");
						mysql_query("DELETE FROM prodpbinds WHERE PID IN ($id_list)");
						mysql_query("DELETE FROM nlproducts WHERE PID IN ($id_list)");
						$this->checked = array();
						$this->loadLastChange(false);
						$this->lastchange->register();
						$this->lastchange = null;
						$qs = $this->consQS();
						$this->redirection = KIWI_PRODUCTS . $qs;
					}
					break;
				default: throw new Exception('Neočekávaný příkaz formuláře: ' . __CLASS__);
			}
		}
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
		if ($this->products === null)
		{
			$this->products = array();
			mb_internal_encoding("UTF-8");

			for ($c = ord('A'); $c <= ord('H'); $c++)
				$this->fletters[chr($c)] = 0;

			$this->fletters['CH'] = 0;

			for ($c = ord('I'); $c <= ord('Z'); $c++)
				$this->fletters[chr($c)] = 0;

			$sort_sql_by = array(self::SORT_BY_ALPHABET => 'Title', self::SORT_BY_TIME => 'LastChange');
			$sort_sql_order = array(self::ASCENDING_ORDER => 'ASC', self::DESCENDING_ORDER => 'DESC');

			$sort_sql = ' ORDER BY ' . $sort_sql_by[$this->sort['by']] . ' ' . $sort_sql_order[$this->sort['order']];

			$filters = array();
			foreach ($this->filter as $ftype => $fvalue)
			{
				if ($fvalue === '') continue;
				$efvalue = mysql_real_escape_string($fvalue);
				switch ($ftype)
				{
					case self::FILTER_TITLE_OR_CODE:
						$filters[] = "(Title LIKE '%$efvalue%' OR Code LIKE '%$efvalue%')";
						break;
					default:
						throw new Exception('Unknown filter type');
				}
			}

			$filters_sql = implode(' AND ', $filters);
			if ($filters_sql !== '')
				$filters_sql = ' WHERE ' . $filters_sql;


			$sql = "SELECT ID, Title, OriginalCost, NewCost, Photo, Discount, Action, Novelty, Sellout, Active, 0 AS `ActiveUsage`, 0 AS `Usage`, LastChange FROM products" . $filters_sql . $sort_sql;

			if ($result = mysql_query($sql))
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

				mysql_free_result($result);
				$result = mysql_query("SELECT PID, Count(NullIf(Active,0)) AS `ActiveUsage`, Count(*) AS `Usage` FROM prodbinds GROUP BY PID");

				while ($row = mysql_fetch_object($result))
				{
					if (!array_key_exists((int)$row->PID, $this->index))
						continue;
					$i = $this->index[(int)$row->PID];
					if (array_key_exists($i, $this->products))
					{
						$this->products[$i]->ActiveUsage = $row->ActiveUsage;
						$this->products[$i]->Usage = $row->Usage;
					}
					// jinak nekonzistence v databázi
				}

				mysql_free_result($result);
			}
		}
	}

	protected function deletePictureFiles($id_list)
	{
		if ($id_list == '') return;

		$result = mysql_query("SELECT Photo FROM products WHERE ID IN ($id_list)");
		while ($row = mysql_fetch_row($result))
			if ($row[0] != '') $this->deleteProductFile($row[0], array('detail', 'catalog', 'catalog2', 'collection'));

		$result = mysql_query("SELECT FileName FROM prodepics WHERE PID IN ($id_list)");
		while ($row = mysql_fetch_row($result))
			$this->deleteProductFile($row[0], array('extra'));

		$result = mysql_query("SELECT FileName FROM prodipics WHERE PID IN ($id_list)");
		while ($row = mysql_fetch_row($result))
			$this->deleteProductFile($row[0], array('illustrative'));

		$result = mysql_query("SELECT Photo FROM prodpbinds WHERE PID IN ($id_list)");
		while ($row = mysql_fetch_row($result))
			if ($row[0] != '') $this->deleteProductFile($row[0], array('detail', 'catalog', 'catalog2', 'collection'));
	}

	protected function deleteProductFile($filename, $thumbsloc)
	{
		global $eshop_picture_config;
		$dir = KIWI_DIR_PRODUCTS;

		if (!(unlink("{$dir}photo/$filename")))
		{
			//throw new Exception("Nepodařilo se smazat soubor s fotografií");
		}

		foreach ($thumbsloc as $loc)
		{
			if (!array_key_exists($loc, $eshop_picture_config))
				throw new Exception('Neznámá lokace miniatury fotografie');
			if (is_array($eshop_picture_config[$loc]))
				if (!(unlink("{$dir}$loc/$filename")))
				{
					//throw new Exception("Nepodařilo se smazat soubor s miniaturou fotografie");
				}
		}
	}

	protected function registerEItemsChanges($id_list)
	{
		if ($id_list == '') return;

		$result = mysql_query("SELECT GID FROM prodbinds WHERE PID IN ($id_list)");

		while ($row = mysql_fetch_row($result))
		{
			$lastchange = new Kiwi_LastChange(array('eshopitems', $row[0]), 'j.n. Y - H:i');
			$lastchange->register();
			$lastchange = null;
		}
	}

	protected function addToNewsletter($post)
	{
		$result = mysql_query("SELECT Max(Priority) FROM nlproducts WHERE NLID=$this->newsletter");
		if ($row = mysql_fetch_row($result))
			$priority = (int)$row[0] + 1;
		else
			throw new Exception("Chyba při načítání priority produktů newsletteru");

		// kvůli kontrole vícenásobného připojení téhož produktu do téže řady
		// (narozdíl od modulů nepřipouštíme vícenásobnou vazbu)
		$old_values = array();
		$result = mysql_query("SELECT PID FROM nlproducts WHERE NLID=$this->newsletter");
		while ($row = mysql_fetch_row($result))
			$old_values[$row[0]] = true;

		$values = array();
		foreach ($post['check'] as $item)
		{
			if (isset($old_values[$item])) continue; // produkt již je připojen k této řadě
			$values[] = "($item, $this->newsletter, $priority)";
			$priority++;
		}

		if (count($values) > 0)
			mysql_query('INSERT INTO nlproducts(PID, NLID, Priority) VALUES ' . implode(', ', $values));
		$this->redirection = KIWI_EDIT_NEWSLETTER . "?nl=$this->newsletter";
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

	protected function getSearchBoxText()
	{
		if (array_key_exists(self::FILTER_TITLE_OR_CODE, $this->filter))
			return $this->filter[self::FILTER_TITLE_OR_CODE];
		else
			return '';
	}

	protected function productsEnabled()
	{
		global $kiwi_config;
		return isset($kiwi_config['eshop']['newsletters_products']) && $kiwi_config['eshop']['newsletters_products'];
	}

	protected function consQS($skipl = false, $skipp = false, $skipf = false)
	{
		$qsa = array();

		if ($this->eshop_item)
			$qsa[] = "ei=$this->eshop_item";

		if ($this->newsletter)
			$qsa[] = "nl=$this->newsletter";

		$qsa[] = 's=' . $this->sort['by'] . $this->sort['order'];

		if (!$skipf)
			foreach ($this->filter as $type => $value)
				$qsa[] = 'f[' . $type .']=' . urlencode($value);

		if (!$skipl)
		{
			if ($this->letter !== null)
				$qsa[] = 'l=' . urlencode($this->letter);
		}

		if (!$skipp && $this->page > 1)
			$qsa[] = "pg=$this->page";

		if ($this->grouped_product)
			$qsa[] = "gp=$this->grouped_product";

		if (empty($qsa))
			$qs = '';
		else
			$qs = '?' . implode('&', $qsa);

		return $qs;
	}

	protected function saveQS()
	{
		$qs = $this->consQS();
		if ($qs !== '')
			$qs = substr($qs, 1);
		return urlencode($qs);
	}
}
?>