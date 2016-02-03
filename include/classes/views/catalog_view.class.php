<?php
class Catalog_View extends Template_Based_View
{
	protected $_menu;
	protected $_catalog_urlbase;
	protected $_catalog_href;
	protected $_qs;
	protected $_catalog_data;
	protected $_product_data;
	protected $_search;
	protected $_sort;
	protected $_propval_photo;

	protected $_only;
	protected $_skipped;

	const CATALOG_ONLY = 1;
	const DETAIL_ONLY = 2;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_menu = null;
		$this->_catalog_urlbase = null;
		$this->_catalog_href = null;
		$this->_qs = null;
		$this->_catalog_data = null;
		$this->_product_data = null;
		$this->_search = new Kiwi_Product_Search;
		$this->_sort = new Kiwi_Product_Sort;
		$this->_propval_photo = null;

		$this->_only = null;
		$this->_skipped = null;

		$this->_attributes->register('name'); // catalog navpoint name
		$this->_attributes->register('catalogroot'); // root of catalog
		$this->_attributes->register('menuroot', 1); // root of menu
		$this->_attributes->register('line'); // current product line/group
		$this->_attributes->register('product'); // current product's id
		$this->_attributes->register('prodbind'); // current product's bind id
		$this->_attributes->register('url'); // magic url of current product or line
		$this->_attributes->register('width', 3); // number of products per row (only for product line view)
		$this->_attributes->register('rows', 3); // number of rows (only for product line view)
		$this->_attributes->register('pagination', 'both'); // pagination display toggle ("", "0", "top", "bottom"; other means "on")
		$this->_attributes->register('page', 1); // page to display (when displaying list)
		$this->_attributes->register('skip', 0); // products to skip from beginning (when displaying list)
		$this->_attributes->register('ns', ''); // namespace
		// $this->_attributes->register('qs'); // query string to preserve // special variable handled by get/set
		$this->_attributes->register('images', ''); // attribute which says if images should be rendered in special way

		$this->template = 'catalog/default'; // default template
	}

	public function get($name)
	{
		if ($name == 'qs')
		{
			if ($this->_qs !== null)
				return $this->_qs->__toString();
			else
				return null;
		}
		else
			return parent::get($name);
	}

	public function set($name, $value)
	{
		if ($name == 'qs')
		{
			if ($value !== null)
				$this->_qs = new Query_String($value);
			else
				$this->_qs = null;
		}
		else
			parent::set($name, $value);
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'menu':
				if ($this->_menu === null)
					$this->loadMenu();
				$this->_menu->render($renderer);
				break;
			case 'catalog only':
				if ($this->_root === null)
				{
					try
					{
						$this->_only = self::CATALOG_ONLY;
						$this->resolveTemplate();
						if (!$this->_skipped)
							$this->_root->render($renderer);
					}
					catch (Data_Insufficient_Exception $e)
					{
						if ($e->resource != 'template')
							throw $e;
					}
				}
				break;
			case 'detail only':
				if ($this->_root === null)
				{
					try
					{
						$this->_only = self::DETAIL_ONLY;
						$this->resolveTemplate();
						if (!$this->_skipped)
							$this->_root->render($renderer);
					}
					catch (Data_Insufficient_Exception $e)
					{
						if ($e->resource != 'template')
							throw $e;
					}
				}
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
		if ($this->name === null)
			throw new Data_Insufficient_Exception('name');

		if ($this->catalogroot !== null && $this->line !== null)
			throw new Data_Superfluous_Exception('catalogroot', 'line');

		$pnav = new Project_Navigator;
		$this->_catalog_urlbase = $pnav->get($this->name . '_urlbase');
		$this->_catalog_href = $pnav->get($this->name);

/*
		// javascript support for catalog menu no longer necessary
		$match = $this->_view_manager->head->getElementsBy('tag', 'scripts');
		if (empty($match))
			throw new Template_Invalid_Structure_Exception('scripts tag missing in head template');
		$scripts = $match[0];

		$js_dir = $pnav->js_dir;
		$scripts->addUnique(new HTML_JavaScript($js_dir . 'catalog_menu.js'));
*/

		parent::_initialize();
	}

	protected function _handleInput()
	{
		if ($this->product === null && $this->line === null && $this->url === null)
		{
			$qsp = $this->ns . 'p';
			$qspb = $this->ns . 'pb';
			$qspmi = $this->ns . 'pmi';
			$qsurl = $this->ns . 'url';
			$qspvp = $this->ns . 'pvp';

			if (array_key_exists($qsp, $_GET))
			{
				$this->product = (int)$_GET[$qsp];
				$this->prodbind = null;
				$this->line = null;
				$this->url = null;
			}

			if (array_key_exists($qspb, $_GET))
			{
				$this->product = null;
				$this->prodbind = (int)$_GET[$qspb];
				$this->line = null;
				$this->url = null;
			}
			elseif (array_key_exists($qspmi, $_GET))
			{
				$this->product = null;
				$this->prodbind = null;
				$this->line = (int)$_GET[$qspmi];
				$this->url = null;
			}
			elseif (array_key_exists($qsurl, $_GET))
			{
				$this->product = null;
				$this->prodbind = null;
				$this->line = null;
				$this->url = $_GET[$qsurl];
			}

			if (array_key_exists($qspvp, $_GET))
				$this->_propval_photo = $_GET[$qspvp];
		}

		$qspg = $this->ns . 'pg';
		if (array_key_exists($qspg, $_GET))
			$this->page = $_GET[$qspg];
	}

	protected function resolveTemplateSource()
	{
		$this->loadIDs();
		try
		{
			$this->loadData();
			$this->_skipped = false;

			if ($this->product !== null || $this->prodbind !== null)
			{
				if ($this->_only !== self::CATALOG_ONLY)
					$suffix = 'product';
				else
					$this->_skipped = true;
			}
			elseif ($this->line !== null)
			{
				if ($this->_only !== self::DETAIL_ONLY)
					$suffix = 'catalog';
				else
					$this->_skipped = true;
			}
			elseif ($this->url !== null)
				$suffix = 'badurl';
			else // default line
				$suffix = 'catalog';
		}
		catch (Kiwi_Bad_URL_Exception $e)
		{
			/*
			 * dochazi pri neexistujicim produktu odkazovaneho pres id (u neexistujici rady se jen nezobrazi zadne produkty)
			 * kontrola existence rady by znamenala pridat dalsi sql dotaz (tj. bylo by to na ukor rychlosti)
			 */
			$suffix = 'badurl';
			$this->product = null;
			$this->prodbind = null;
			$this->line = null;
			if ($this->url === null)
				$this->url = $e.getURL();
		}

		if (!$this->_skipped)
		{
			$base = $this->template;
			if ($base === null)
				throw new Data_Insufficient_Exception('template');
			$this->template = $base . '_' . $suffix;
		}
		else
			$this->template = null;
	}

	protected function loadData()
	{
		if ($this->product !== null || $this->prodbind !== null)
			$this->loadProductData();
		elseif ($this->line !== null)
		{
			$this->_search->pmi = $this->line;
			$this->_sort->sort_by = 'PB.Priority';
			$this->_sort->sort_dir = 'ASC';
			$this->loadCatalogData();
		}
		elseif ($this->url === null) // default line
		{
			if ($this->catalogroot !== null) $this->_search->pmi = $this->catalogroot;
			$this->_sort->sort_by = 'P.Title';
			$this->_sort->sort_dir = 'ASC';
			$this->loadCatalogData();
		}
	}

	protected function _updateTemplate()
	{
		if ($this->product !== null || $this->prodbind !== null)
			$this->updateProductTemplate();
		elseif ($this->line !== null)
			$this->updateCatalogTemplate();
		elseif ($this->url !== null)
			$this->updateBadURLTemplate();
		else // default line
			$this->updateCatalogTemplate();
	}

	protected function updateHead()
	{
		Images_JS_Support::updateHead($this->images, $this->_view_manager->head);
	}

	protected function loadIDs()
	{
		if ($this->product === null && $this->prodbind === null && $this->line === null && $this->url !== null)
		{
			if (($this->prodbind = Kiwi_URL::loadProductBindID($this->url, $this->catalogroot)) === null)
				if (($this->product = Kiwi_URL::loadProductID($this->url, $this->catalogroot)) === null)
					$this->line = Kiwi_URL::loadProductLineID($this->url, $this->catalogroot);
		}
	}

	protected function loadProductData()
	{
		//$this->_product_data = new Kiwi_Product($this->product, $this->prodbind);
		$this->_product_data = Kiwi_Object_Manager::load('Kiwi_Product', array($this->product, $this->prodbind)); // allows caching
	}

	protected function loadCatalogData()
	{
		$this->_search->handleQS($this->ns);
		$this->_sort->handleQS($this->ns);
		//$this->_catalog_data = new Kiwi_Catalog($this->width * $this->rows, $this->page, $this->_search, $this->_sort);
		$this->_catalog_data = Kiwi_Object_Manager::load('Kiwi_Catalog', array($this->width * $this->rows, $this->page, $this->_search, $this->_sort)); // allows caching
		if ($this->skip)
			$this->_catalog_data->skip = $this->skip;
	}

	protected function updateProductTemplate()
	{
		// generování řádků popisujících umístění produktu v katalogu
		$this->updateTemplate_Location();

		// generování názvu produktu
		$this->updateTemplate_Title();

		// generování kódu produktu
		$this->updateTemplate_Code();

		// generování fotografie
		$this->updateTemplate_DetailPhoto();

		// generování dalších fotografií
		$this->updateTemplate_AdditionalPhotos();

		// generování ilustrativních fotografií
		$this->updateTemplate_IllustrativePhotos();

		// generování fotografií produktů z kolekce
		$this->updateTemplate_CollectionPhotos();

		// generování příloh
		$this->updateTemplate_Attachments();

		// generování odkazu pro objednání
		$this->updateTemplate_OrderLink();

		// generování příznaku novinky
		$this->updateTemplate_Novelty();

		// generování příznaku akce
		$this->updateTemplate_Action();

		// generování příznaku slevy
		$this->updateTemplate_Discount();

		// generování ceny (cen)
		$this->updateTemplate_Cost();

		// generování popisu
		$this->updateTemplate_Description();

		// generování vlastností
		$this->updateTemplate_Properties();
	}

	protected function updateCatalogTemplate()
	{
		$main_vars = array
		(
			'radek_struktura',
			'produkt_vzory',
			'produkty',
			'jsou_produkty',
			'nejsou_produkty',
			'prazdna_stranka',
			'horni_paginace',
			'dolni_paginace',
			'je_rada', // experimental
			'neni_rada', // experimental
			'jmeno_rady' // experimental
		);

		foreach ($main_vars as $varname)
			$$varname = $this->_index->$varname;

		if ($horni_paginace && count($horni_paginace) !== 1)
			throw new Template_Invalid_Structure_Exception('The "horni_paginace" element duplicity');

		if ($dolni_paginace && count($dolni_paginace) !== 1)
			throw new Template_Invalid_Structure_Exception('The "dolni_paginace" element duplicity');

		if ($horni_paginace)
			$horni_paginace = $horni_paginace[0];
		if ($dolni_paginace)
			$dolni_paginace = $dolni_paginace[0];

		if (!empty($produkty))
		{
			$required_unique_vars = array
			(
				'radek_struktura',
				'produkt_vzory'
			);

			foreach ($required_unique_vars as $varname)
			{
				if (empty($$varname))
					throw new Template_Element_Missing_Exception($varname);

				if (count($$varname) !== 1)
					throw new Template_Invalid_Structure_Exception("The \"$varname\" element duplicity");

				$temp = $$varname;
				$$varname = $temp[0];
			}

			$produkt_vzory_index = new HTML_Indexer($produkt_vzory, 'eid', false);

			$produkt_vzory_subvars = array
			(
				'produkt_vzor' => true,
				'neni_produkt_vzor' => true,
				'levy_produkt_vzor' => false,
				'pravy_produkt_vzor' => false,
				'levy_neni_produkt_vzor' => false,
				'pravy_neni_produkt_vzor' => false
			);

			foreach ($produkt_vzory_subvars as $varname => $required)
			{
				$$varname = $produkt_vzory_index->$varname;

				if ($required && empty($$varname))
					throw new Template_Element_Missing_Exception($varname);

				// kontrola specifikací vzorových elementů
				$vzor_fields = array();
				foreach ($$varname as $elem)
				{
					if ($elem->specification === null)
						throw new Template_Invalid_Structure_Exception("The \"$varname\" element specification required");
					if (array_key_exists($elem->specification, $vzor_fields))
						throw new Template_Invalid_Structure_Exception("The \"$varname\" element specification ($elem->specification) duplicity");

					$vzor_fields[$elem->specification] = true;
				}
			}

			$produkt = $radek_struktura->getElementsBy('eid', 'produkt');
			if (empty($produkt))
				throw new Template_Invalid_Structure_Exception('The "radek_struktura" element doesn\'t countain element "produkt"');

			// kontrola specifikací placeholderů pro produkty
			$produkt_fields = array();
			foreach ($produkt as $elem)
			{
				if ($elem->specification === null)
					throw new Template_Invalid_Structure_Exception("The \"produkt\" element specification required");
				if (array_key_exists($elem->specification, $produkt_fields))
					throw new Template_Invalid_Structure_Exception("The \"produkt\" element specification ($elem->specification) duplicity");

				$produkt_fields[$elem->specification] = $elem;
			}

			$products = $this->_catalog_data->products;
			$products_count = count($products);
			$rows = (int)(($products_count + $this->width - 1) / $this->width);
			$products_rendered = 0;

			for ($i = 0; $i < $rows; $i++)
			{
				foreach ($produkt_fields as $produkt_field)
					$produkt_field->clear();

				for ($j = 0; $j < $this->width; $j++)
				{
					$vzor = 'produkt_vzor';
					$pi = $i * $this->width + $j;
					if ($pi < $products_count)
					{
						$p = $products[$pi];
						$products_rendered++;
					}
					else
					{
						$p = null;
						$vzor = "neni_$vzor";
					}

					if ($j == 0 && $j != $this->width - 1)
						$pozicovany_vzor = "levy_$vzor";
					elseif ($j == $this->width - 1)
						$pozicovany_vzor = "pravy_$vzor";
					else
						$pozicovany_vzor = $vzor;

					$this->updateTemplate_CatalogProduct($p, $produkt_fields, !empty($$pozicovany_vzor) ? $$pozicovany_vzor : $$vzor);
				} // for columns
				// přidání vygenerovaného řádku
				foreach ($produkty as $elem)
					$elem->add(clone $radek_struktura);
			} // for rows
		} // if produkty

		$products_per_page = $this->width * $this->rows;
		$pages = (int)($this->_catalog_data->total / $products_per_page);
		if ($pages * $products_per_page < $this->_catalog_data->total) $pages++;

		if ($horni_paginace)
		{
			$horni_paginace->vo->total = $pages;
			$horni_paginace->vo->current = $this->page;
			$horni_paginace->vo->qsvar = $this->ns . 'pg';
			$horni_paginace->active = $this->pagination && $this->pagination != 'bottom';
		}

		if ($dolni_paginace)
		{
			$dolni_paginace->vo->total = $pages;
			$dolni_paginace->vo->current = $this->page;
			$dolni_paginace->vo->qsvar = $this->ns . 'pg';
			$dolni_paginace->active = $this->pagination && $this->pagination != 'top';
		}

		foreach ($jsou_produkty as $elem)
			$elem->active = $products_rendered != 0;

		foreach ($nejsou_produkty as $elem)
			$elem->active = $this->_catalog_data->total == 0;

		foreach ($prazdna_stranka as $elem)
			$elem->active = $this->_catalog_data->total != 0 && $products_rendered == 0;

		// experimental line name:
		$line = $this->_catalog_data->line;
		foreach ($je_rada as $elem)
			$elem->active = $line !== 0;

		foreach ($neni_rada as $elem)
			$elem->active = $line === 0;

		foreach ($jmeno_rady as $elem)
			$elem->text = $line->Name;

	}

	protected function updateBadURLTemplate()
	{
		$url = $this->_index->url;
		$html_url = new HTML_Text($this->url);
		foreach ($url as $elem)
			$elem->add($html_url);
	}

	protected function loadMenu()
	{
		$this->loadIDs();

		$menuroot = $this->menuroot ? $this->menuroot : 1;
		//$kmenu = new Kiwi_Catalog_Menu;
		$kmenu = Kiwi_Object_Manager::load('Kiwi_Catalog_Menu', array($menuroot));
		$kmar = $kmenu->getArray();

		if ($this->name === null)
			throw new Data_Insufficient_Exception('name');

		$this->_menu = new HTML_UL($this->ns . 'pg1', $this->ns . 'ul1');
		$this->buildMenu($this->_menu, $kmar[0]['Contents']);
	}

	protected function buildMenu(&$menu, &$ar, $depth = 2)
	{
		$ccur = false;
		foreach ($ar as &$item)
		{
			$li = new HTML_LI;
			$text = new HTML_Text($item['Name']);

			$is_current = $this->line !== null && $this->line == $item['ID'];
			if ($item['Contents'] === false)
			{
				if ($is_current)
				{
					$span = new HTML_Span;
					$span->add($text);
					$li->add($span);
					$ccur = true;
				}
				else
				{
					$a = new HTML_A;
					$a->title = '';
					$a->add($text);
					if ($item['URL'] === '')
						$a->href = "$this->_catalog_href?{$this->ns}pmi={$item['ID']}";
					else
						$a->href = $this->_catalog_urlbase . $item['URL'];

					// check if current product belongs to this line
					if ($this->_product_data !== null)
					{
						$lines = $this->_product_data->lines;
						if (in_array($item['ID'], $lines))
						{
							$a->addClass('current-product-line');
							$ccur = true;
						}
					}

					$li->add($a);
				}
				$menu->add($li);
			}
			else
			{
				if ($is_current)
				{
					$span = new HTML_Span;
					$span->add($text);
					$li->add($span);
					$ccur = true;
				}
				else
				{
					$a = new HTML_A;
					$a->title = '';
					$a->add($text);
					if ($item['URL'] === '')
						$a->href = "$this->_catalog_href?{$this->ns}pmi={$item['ID']}";
					else
						$a->href = $this->_catalog_urlbase . $item['URL'];
					$li->add($a);
				}

				$menu->add($li);

				if (!empty($item['Contents']))
				{
					$ul = new HTML_UL($this->ns . 'pg' . $item['ID']);
					$contains_current = $this->buildMenu($ul, $item['Contents'], $depth + 1);
					$ccur = $ccur || $contains_current;
					if ($is_current || $contains_current)
					{
						$ul_class = $this->ns . "ul$depth";
						$ul->class = $ul_class;
						$li2 = new HTML_LI;
						$li2->add($ul);
						if ($contains_current && !$is_current) $a->addClass('current-supergroup');
						$menu->add($li2);
					}
				}
			}
		}

		return $ccur;
	}

	protected function getProductPhoto()
	{
		$default_photo = $this->_product_data->Photo;
		if ($this->_propval_photo === null) return $default_photo;
		if (($propval = $this->_product_data->getPropertyValue($this->_propval_photo)) !== null)
			$photo = $propval['Photo'] !== '' ? $propval['Photo'] : $default_photo;
		else
			$photo = $default_photo;
		return $photo;
	}

	protected function prepareProductDescriptionHTML($desc)
	{
		$pattern = "#(\r\n)#";
		$replacement = "\n";
		$result = preg_replace($pattern, $replacement, $desc);
		return $result;
	}

	protected function buildProductPropertyValuesText($values)
	{
		$comma = false;
		$inl = new HTML_Inline_Group();
		foreach ($values as $propval)
		{
			if ($comma)
				$inl->add(new HTML_Text(', '));
			else
				$comma = true;
			if ($propval['Description'] !== '')
			{
				$acro = new HTML_Acronym;
				$acro->title = $propval['Description'];
				$acro->add(new HTML_Text($propval['Value']));
				$inl->add($acro);
			}
			else
				$inl->add(new HTML_Text($propval['Value']));
		}
		return $inl;
	}

	protected function buildProductPropertyValuesIcons($values, $obrazek_vlastnost_ikona, $prod = null)
	{
		$nav = new Project_Navigator;
		$property_icons_dir = $nav->get($this->name . '_property_icons');

		$sep = false;
		$inl = new HTML_Inline_Group();
		foreach ($values as $propval)
		{
			if ($sep)
				$inl->add(new HTML_Text(' '));
			else
				$sep = true;

			$picname = htmlspecialchars(strtolower($propval['ExtraData']));
			$filename = $property_icons_dir . $picname;

			$img = clone $obrazek_vlastnost_ikona;
			$img->src = $filename;
			$img->alt = $propval['Value'] . ' ';
			$img->title = $propval['Value'];

			if ($prod !== null && $propval['Photo'] !== '')
			{
				$a = new HTML_A;
				$qs = new Query_String;
				if (array_key_exists('URL', $prod))
					$url = $this->_catalog_urlbase . $prod['URL'];
				else
				{
					$url = $this->_catalog_href;
					if (array_key_exists('PBID', $prod))
						$qs->set($this->ns . 'pb', $prod['PBID']);
					else
						$qs->set($this->ns . 'p', $prod['ID']);
				}
				$qs->set($this->ns . 'pvp', $propval['ID']);
				$a->href = $url . $qs->__toString();
				$a->add($img);
				$inl->add($a);
			}
			else
				$inl->add($img);
		}
		return $inl;
	}

	protected function buildProductPropertyValuesColors($values, $obrazek_vlastnost_barva, $prod = null)
	{
		$nav = new Project_Navigator;
		$property_colors_dir = $nav->get($this->name . '_property_colors');

		$sep = false;
		$inl = new HTML_Inline_Group();
		foreach ($values as $propval)
		{
			if ($sep)
				$inl->add(new HTML_Text(' '));
			else
				$sep = true;
			$colors_a = array_map('strtolower', preg_split('/[,;\|]/', $propval['ExtraData']));
			$picname = htmlspecialchars(implode('', $colors_a) . '.gif');
			$filename = $property_colors_dir . $picname;

			$img = clone $obrazek_vlastnost_barva;
			$img->src = $filename;
			$img->alt = $propval['Value'] . ' ';
			$img->title = $propval['Value'];

			if ($prod !== null && $propval['Photo'] !== '')
			{
				$a = new HTML_A;
				$qs = new Query_String;
				if (array_key_exists('URL', $prod))
					$url = $this->_catalog_urlbase . $prod['URL'];
				else
				{
					$url = $this->_catalog_href;
					if (array_key_exists('PBID', $prod))
						$qs->set($this->ns . 'pb', $prod['PBID']);
					else
						$qs->set($this->ns . 'p', $prod['ID']);
				}
				$qs->set($this->ns . 'pvp', $propval['ID']);
				$a->href = $url . $qs->__toString();
				$a->add($img);
				$inl->add($a);
			}
			else
				$inl->add($img);
		}
		return $inl;
	}

	protected function buildProductPropertyValuesList($property, $obrazek_vlastnost_ikona, $obrazek_vlastnost_barva, $product_data = null)
	{
		try
		{
			if ($product_data !== null)
			{
				$prod_sign = array
				(
					'URL' => $product_data->URL,
					'PBID' => $product_data->PBID,
					'ID' => $product_data->ID
				);
			}
			else
				throw No_Such_Variable_Exception('product_data');
		}
		catch (No_Such_Variable_Exception $e)
		{
			$prod_sign = array
			(
				'URL' => $this->_product_data->URL,
				'PBID' => $this->prodbind,
				'ID' => $this->product
			);
		}

		switch ($property['DataType'])
		{
			case Kiwi_Product::PT_TEXT:
				$inl = $this->buildProductPropertyValuesText($property['Values'], $prod_sign);
				break;
			case Kiwi_Product::PT_ICON:
				if ($obrazek_vlastnost_ikona)
					$inl = $this->buildProductPropertyValuesIcons($property['Values'], $obrazek_vlastnost_ikona, $prod_sign);
				else
					$inl = $this->buildProductPropertyValuesText($property['Values'], $prod_sign);
				break;
			case Kiwi_Product::PT_COLOR:
				if ($obrazek_vlastnost_barva)
					$inl = $this->buildProductPropertyValuesColors($property['Values'], $obrazek_vlastnost_barva, $prod_sign);
				else
					$inl = $this->buildProductPropertyValuesText($property['Values'], $prod_sign);
				break;
			default:
				throw new Kiwi_Bad_Property_DataType_Exception($property['DataType']);
		}

		return $inl;
	}

	protected function buildProductPropertiesValuesList($vlastnosti_elem, $ma_vlastnost_vzor, $nema_vlastnost_vzor, $nazev_vlastnosti, $hodnoty_vlastnosti, $obrazek_vlastnost_ikona, $obrazek_vlastnost_barva, $product_data = null)
	{
		if ($product_data === null)
			$product_data = $this->_product_data;

		$specs = $vlastnosti_elem->specification;
		if ($specs === null || $specs === '')
		{
			$negative = true;
			$specs_a = array();
		}
		elseif ($specs[0] == '!')
		{
			$negative = true;
			$specs_a = explode(',', substr($specs, 1));
		}
		else
		{
			$negative = false;
			$specs = explode(',', $specs);
		}

		$proplist = array();
		foreach ($specs_a as $specprop)
			$proplist[(int)$specprop] = true;

		$properties = $product_data->properties;
		foreach ($properties as $propid => $property)
		{
			if (array_key_exists($propid, $proplist) == $negative)
				continue;

			foreach ($nazev_vlastnosti as $elem)
			{
				$elem->clear();
				$elem->add(new HTML_Text($property['Name']));
			}

			foreach ($hodnoty_vlastnosti as $elem)
				$elem->clear();

			if (empty($property['Values']))
			{
				$vlastnosti_elem->add(clone $nema_vlastnost_vzor);
			}
			else
			{
				$values = $this->buildProductPropertyValuesList($property, $obrazek_vlastnost_ikona, $obrazek_vlastnost_barva, $product_data);
				foreach ($hodnoty_vlastnosti as $elem)
					$elem->add($values);

				$vlastnosti_elem->add(clone $ma_vlastnost_vzor);
			}
		}
	}

/* update template helper methods: begin */

	protected function updateTemplate_Location(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'obrazek_sipka',
			'umisteni_vzor',
			'umisteni_obsah',
			'umisteni'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		if (!empty($umisteni))
		{
			$required_unique_vars = array
			(
				'umisteni_vzor',
				'umisteni_obsah',
				'obrazek_sipka'
			);

			foreach ($required_unique_vars as $varname)
			{
				if (empty($$varname))
					throw new Template_Element_Missing_Exception($varname);

				if (count($$varname) !== 1)
					throw new Template_Invalid_Structure_Exception("The \"$varname\" element duplicity");

				$temp = $$varname;
				$$varname = $temp[0];
			}

			$paths = $product_data->paths;
			foreach ($paths as $path)
			{
				$umisteni_obsah->clear();
				$delim = false;
				foreach ($path as $step)
				{
					if ($delim)
					{
						$umisteni_obsah->add(new HTML_Text(' '));
						$umisteni_obsah->add(clone $obrazek_sipka);
						$umisteni_obsah->add(new HTML_Text(' '));
					}

					if ($step['Subgroup'])
					{
						$span = new HTML_Span;
						$span->add(new HTML_Text($step['Name']));
						$umisteni_obsah->add($span);
					}
					else
					{
						$a = new HTML_A;
						$qso = new Query_String;

						if ($step['URL'] == '')
						{
							$qso->set($this->ns . 'pmi', $step['ID']);
							$a->href = $this->_catalog_href . $qso->__toString();
						}
						else
							$a->href = $this->_catalog_urlbase . $step['URL'] . $qso->__toString();

						$a->title = '';
						$a->add(new HTML_Text($step['Name']));
						$umisteni_obsah->add($a);
					}

					$delim = true;
				}

				$umisteni_obsah->add(new HTML_Text(' '));
				$umisteni_obsah->add(clone $obrazek_sipka);
				$umisteni_obsah->add(new HTML_Text(' '));
				$span = new HTML_Span;
				$span->add(new HTML_Text($product_data->Title));
				$umisteni_obsah->add($span);

				foreach ($umisteni as $elem)
					$elem->add(clone $umisteni_vzor);
			}
		}
	}

	protected function updateTemplate_DetailLink(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$detail = $index->detail;

		if (!empty($detail))
		{
			$this->resolveQS();
			$qso = clone $this->_qs;

			if ($product_data->URL === '')
			{
				$qso->set($this->ns . 'p', $product_data->PBID);
				$plink = $this->_catalog_href . $qso->__toString();
			}
			else
				$plink = $this->_catalog_urlbase . $product_data->URL . $qso->__toString();

			foreach ($detail as $elem)
				$elem->href = $plink;
		}
	}

	protected function updateTemplate_Title(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$nazev = $index->nazev;

		foreach ($nazev as $elem)
			$elem->text = $product_data->Title;
	}

	protected function updateTemplate_Code(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$nazev = $index->kod;

		foreach ($nazev as $elem)
			$elem->text = $product_data->Code;
	}

	protected function updateTemplate_CatalogPhoto(HTML_Indexer $index, Kiwi_Product $product_data)
	{
		if ($index === null)
			$index = $this->_index;

		$vars = array
		(
			'obrazek_produktu',
			'ma_fotografii',
			'nema_fotografii',
			'fotografie',
			'fotografie_odkaz'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$photo = $product_data->Photo;

		foreach ($ma_fotografii as $elem)
			$elem->active = $photo !== '';

		foreach ($nema_fotografii as $elem)
			$elem->active = $photo === '';

		if ((!empty($fotografie) || !empty($fotografie_odkaz)) && $photo !== '')
		{
			if (empty($obrazek_produktu))
				throw new Template_Element_Missing_Exception('obrazek_produktu');

			if (count($obrazek_produktu) !== 1)
				throw new Template_Invalid_Structure_Exception('The "obrazek_produktu" element duplicity');

			$obrazek_produktu = $obrazek_produktu[0];

			$nav = new Project_Navigator;
			$big_photos_dir = $nav->get($this->name . '_photos_big');

			foreach($fotografie as $elem)
			{
				switch ($elem->specification)
				{
					case 'detail':
						$catalog_photos_dir = $nav->get($this->name . '_photos_detail');
						break;
					default:
						$catalog_photos_dir = $nav->get($this->name . '_photos');
						break;
				}
				$photo_catalog = $catalog_photos_dir . $photo;
				$img = clone $obrazek_produktu;
				$img->src = $photo_catalog;
				$elem->add($img);
			}

			foreach($fotografie_odkaz as $elem)
			{
				$photo_big = $big_photos_dir . $photo;
				$elem->href = $photo_big;
				$elem->title = '';
				Images_JS_Support::updateElement($this->images, $elem);
			}
		}
	}

	protected function updateTemplate_DetailPhoto(HTML_Indexer $index = null)
	{
		if ($index === null)
			$index = $this->_index;

		$vars = array
		(
			'obrazek_hlavni',
			'ma_fotografii',
			'nema_fotografii',
			'fotografie'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$photo = $this->getProductPhoto();

		foreach ($ma_fotografii as $elem)
			$elem->active = $photo !== '';

		foreach ($nema_fotografii as $elem)
			$elem->active = $photo === '';

		if (!empty($fotografie) && $photo !== '')
		{
			if (empty($obrazek_hlavni))
				throw new Template_Element_Missing_Exception('obrazek_hlavni');

			if (count($obrazek_hlavni) !== 1)
				throw new Template_Invalid_Structure_Exception('The "obrazek_hlavni" element duplicity');

			$obrazek_hlavni = $obrazek_hlavni[0];

			$nav = new Project_Navigator;

			foreach($fotografie as $elem)
			{
				switch ($elem->specification)
				{
					case 'catalog':
						$detail_photos_dir = $nav->get($this->name . '_photos');
						break;
					default:
						$detail_photos_dir = $nav->get($this->name . '_photos_detail');
						break;
				}
				$big_photos_dir = $nav->get($this->name . '_photos_big');
				$photo_big = $big_photos_dir . $photo;
				$photo_small = $detail_photos_dir . $photo;
				$img = clone $obrazek_hlavni;
				$img->src = $photo_small;
				$a = new HTML_A;
				$a->href = $photo_big;
				$a->title = '';
				Images_JS_Support::updateElement($this->images, $a);
				$a->add($img);
				$elem->add($a);
			}
		}
	}

	protected function updateTemplate_AdditionalPhotos(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'obrazek_dalsi',
			'ma_dalsi_fotografie',
			'nema_dalsi_fotografie',
			'dalsi_fotografie'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$extra_photos = $product_data->extra_photos;

		foreach ($ma_dalsi_fotografie as $elem)
			$elem->active = !empty($extra_photos);

		foreach ($nema_dalsi_fotografie as $elem)
			$elem->active = empty($extra_photos);

		if (!empty($dalsi_fotografie))
		{
			if (empty($obrazek_dalsi))
				throw new Template_Element_Missing_Exception('obrazek_dalsi');

			if (count($obrazek_dalsi) !== 1)
				throw new Template_Invalid_Structure_Exception('The "obrazek_dalsi" element duplicity');

			$obrazek_dalsi = $obrazek_dalsi[0];

			if (!empty($extra_photos))
			{
				$nav = new Project_Navigator;
				$big_photos_dir = $nav->get($this->name . '_photos_big');
				$extra_photos_dir = $nav->get($this->name . '_photos_extra');

				foreach ($extra_photos as $ephoto)
				{
					$photo_big = $big_photos_dir . $ephoto['FileName'];
					$photo_small = $extra_photos_dir . $ephoto['FileName'];
					$img = clone $obrazek_dalsi;
					$img->src = $photo_small;
					$a = new HTML_A;
					$a->href = $photo_big;
					$a->title = '';
					Images_JS_Support::updateElement($this->images, $a);
					$a->add($img);
					foreach ($dalsi_fotografie as $elem)
						$elem->add($a);
				}
			}
		}
	}

	protected function updateTemplate_IllustrativePhotos(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'obrazek_ilustrativni',
			'ma_ilustrativni_fotografie',
			'nema_ilustrativni_fotografie',
			'ilustrativni_fotografie'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$illustrative_photos = $product_data->illustrative_photos;

		foreach ($ma_ilustrativni_fotografie as $elem)
			$elem->active = !empty($illustrative_photos);

		foreach ($nema_ilustrativni_fotografie as $elem)
			$elem->active = empty($illustrative_photos);

		if (!empty($ilustrativni_fotografie))
		{
			if (empty($obrazek_ilustrativni))
				throw new Template_Element_Missing_Exception('obrazek_ilustrativni');

			if (count($obrazek_ilustrativni) !== 1)
				throw new Template_Invalid_Structure_Exception('The "obrazek_ilustrativni" element duplicity');

			$obrazek_ilustrativni = $obrazek_ilustrativni[0];

			if (!empty($illustrative_photos))
			{
				$nav = new Project_Navigator;
				$big_photos_dir = $nav->get($this->name . '_photos_big');
				$illustrative_photos_dir = $nav->get($this->name . '_photos_illustrative');

				foreach ($illustrative_photos as $iphoto)
				{
					$photo_big = $big_photos_dir . $iphoto['FileName'];
					$photo_small = $illustrative_photos_dir . $iphoto['FileName'];
					$img = clone $obrazek_ilustrativni;
					$img->src = $photo_small;
					$a = new HTML_A;
					$a->href = $photo_big;
					$a->title = '';
					Images_JS_Support::updateElement($this->images, $a);
					$a->add($img);
					foreach ($ilustrativni_fotografie as $elem)
						$elem->add($a);
				}
			}
		}
	}

	protected function updateTemplate_CollectionPhotos(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'obrazek_kolekce',
			'ma_fotografie_kolekce',
			'nema_fotografie_kolekce',
			'fotografie_kolekce'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$collection = $product_data->collection;

		foreach ($ma_fotografie_kolekce as $elem)
			$elem->active = !empty($collection);

		foreach ($nema_fotografie_kolekce as $elem)
			$elem->active = empty($collection);

		if (!empty($fotografie_kolekce))
		{
			if (empty($obrazek_kolekce))
				throw new Template_Element_Missing_Exception('obrazek_kolekce');

			if (count($obrazek_kolekce) !== 1)
				throw new Template_Invalid_Structure_Exception('The "obrazek_kolekce" element duplicity');

			$obrazek_kolekce = $obrazek_kolekce[0];

			if (!empty($collection))
			{
				$nav = new Project_Navigator;
				$collection_photos_dir = $nav->get($this->name . '_photos_collection');

				$max_cp = 60;
				foreach ($collection as $citem)
				{
					if ($max_cp-- == 0) break;
					$photo_small = $collection_photos_dir . $citem['FileName'];
					$img = clone $obrazek_kolekce;
					$img->src = $photo_small;
					$a = new HTML_A;
					$qso = new Query_String;

					if ($citem['URL'] == '')
					{
						$qso->set($this->ns . 'p', $citem['ID']);
						$a->href = $this->_catalog_href . $qso->__toString();
					}
					else
						$a->href = $this->_catalog_urlbase . $citem['URL'] . $qso->__toString();

					$a->title = $citem['Title'];
					$a->add($img);
					foreach ($fotografie_kolekce as $elem)
						$elem->add($a);
				}
			}
		}
	}

	protected function updateTemplate_Attachments(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'priloha_vzor',
			'priloha_odkaz',
			'priloha_nazev',
			'priloha_pripona',
			'ma_prilohy',
			'nema_prilohy',
			'prilohy'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$attachments = $product_data->attachments;

		foreach ($ma_prilohy as $elem)
			$elem->active = !empty($attachments);

		foreach ($nema_prilohy as $elem)
			$elem->active = empty($attachments);

		if (!empty($prilohy))
		{
			if (empty($priloha_vzor))
				throw new Template_Element_Missing_Exception('priloha_vzor');

			if (count($priloha_vzor) !== 1)
				throw new Template_Invalid_Structure_Exception('The "priloha_vzor" element duplicity');

			$priloha_vzor = $priloha_vzor[0];

			if (!empty($attachments))
			{
				$nav = new Project_Navigator;
				$attachments_dir = $nav->get($this->name . '_attachments');

				foreach ($attachments as $attachment)
				{
					$file = $attachments_dir . $attachment['FileName'];
					foreach ($priloha_odkaz as $elem)
						$elem->href = $file;

					foreach ($priloha_nazev as $elem)
					{
						$elem->clear();
						$elem->add(new HTML_Text($attachment['Title']));
					}

					$path_parts = pathinfo($attachment['FileName']);
					$ext = $path_parts['extension'];
					$found = 0;
					foreach ($priloha_pripona as $elem)
					{
						if ($elem->active = $elem->specification === strtolower($ext)) // intentionally =
						{
							$found += 1;
						}
					}

					if ($found === 0)
					{
						foreach ($priloha_pripona as $elem)
						{
							$elem->active = $elem->specification === 'default';
						}
					}

					$attachment_elem = clone $priloha_vzor;
					foreach ($prilohy as $elem)
						$elem->add($attachment_elem);
				}
			}
		}
	}

	protected function updateTemplate_OrderLink(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'link_objednat',
			'vyberova_vlastnost_prirustek_height_popup',
			'objednani'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		if (!empty($objednani))
		{
			if (empty($link_objednat))
				throw new Template_Element_Missing_Exception('link_objednat');

			if (count($link_objednat) !== 1)
				throw new Template_Invalid_Structure_Exception('The "link_objednat" element duplicity');

			if (count($vyberova_vlastnost_prirustek_height_popup) > 1)
				throw new Template_Invalid_Structure_Exception('The "vyberova_vlastnost_prirustek_height_popup" element duplicity');

			if (!empty($vyberova_vlastnost_prirustek_height_popup))
			{
				$vyberova_vlastnost_prirustek_height_popup = $vyberova_vlastnost_prirustek_height_popup[0];
				try
				{
					$prirustek_height = $vyberova_vlastnost_prirustek_height_popup->specification;
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
					throw new Template_Invalid_Structure_Exception('The "vyberova_vlastnost_prirustek_height_popup" element must be group-derived element');
				}
			}
			else
				$prirustek_height = null;

			$link_objednat = $link_objednat[0];

			if ($link_objednat->tag != 'Navpoint_View')
				throw new Template_Invalid_Structure_Exception('The "link_objednat" element must be of type "Navpoint_View", not "' . $link_objednat->tag . '"');

			$navpoint = clone $link_objednat;
			$npqs = $this->ns . 'p=' . $product_data->ID;
			if ($this->_propval_photo !== null)
				$npqs .= '&' . $this->ns . 'pvp=' . $this->_propval_photo;
			$navpoint->vo->qs = $npqs;
			if ($prirustek_height !== null && $navpoint->vo->popup !== null)
			{
				mb_ereg_search_init($navpoint->vo->popup);
				mb_ereg_search("^(.*)height=([0-9]+)(.*)$", 'i');
				if ($match = mb_ereg_search_getregs() !== false)
				{
					$new_height = (int)$match[2] + (int)$prirustek_height * $product_data->selectablePropertiesCount;
					$new_popup = $match[1] . "height=$new_height" . $match[3];
					$navpoint->vo->popup = $new_popup;
				}
			}
			foreach ($objednani as $elem)
				$elem->add($navpoint);
		}
	}

	protected function updateTemplate_Novelty(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'je_novinka',
			'neni_novinka'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$novinka = (bool)$product_data->Novelty;

		foreach ($je_novinka as $elem)
			$elem->active = $novinka;

		foreach ($neni_novinka as $elem)
			$elem->active = !$novinka;
	}

	protected function updateTemplate_Action(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'je_akce',
			'neni_akce'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$akce = (bool)$product_data->Action;

		foreach ($je_akce as $elem)
			$elem->active = $akce;

		foreach ($neni_akce as $elem)
			$elem->active = !$akce;
	}

	protected function updateTemplate_Discount(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'je_sleva',
			'neni_sleva'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$sleva = (bool)$product_data->Discount;

		foreach ($je_sleva as $elem)
			$elem->active = $sleva;

		foreach ($neni_sleva as $elem)
			$elem->active = !$sleva;
	}

	protected function updateTemplate_Cost(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'ma_starou_cenu',
			'nema_starou_cenu',
			'ma_novou_cenu',
			'nema_novou_cenu',
			'stara_cena_bez_dph',
			'nova_cena_bez_dph',
			'stara_cena_s_dph',
			'nova_cena_s_dph',
			'stara_cena_dph',
			'nova_cena_dph',
			'ma_zmenu_ceny',
			'nema_zmenu_ceny',
			'zmena_ceny',
			'zmena_ceny_procent'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$conf = new Project_Config;
		$costs_with_vat = $conf->costs_with_vat;
		$vat_coefs = $conf->vat_coefs;
		$monetary_format = $conf->monetary_format;

		foreach ($ma_starou_cenu as $elem)
			$elem->active = $product_data->OriginalCost != 0;

		foreach ($nema_starou_cenu as $elem)
			$elem->active = $product_data->OriginalCost == 0;

		foreach ($ma_novou_cenu as $elem)
			$elem->active = $product_data->NewCost != 0;

		foreach ($nema_novou_cenu as $elem)
			$elem->active = $product_data->NewCost == 0;

		if (!empty($stara_cena_bez_dph))
		{
			if ($product_data->OriginalCost)
			{
				if ($costs_with_vat)
				{
					$vat = round($product_data->OriginalCost * $vat_coefs['top'], 2);
					$orig_cost_value_novat = $product_data->OriginalCost - $vat;
				}
				else
					$orig_cost_value_novat = $product_data->OriginalCost;
				$orig_cost = new Money_Value($orig_cost_value_novat, $monetary_format);
				$html_cost = new HTML_Text($orig_cost->format());
				foreach ($stara_cena_bez_dph as $elem)
					$elem->add($html_cost);
			}
			else
				foreach($stara_cena_bez_dph as $elem)
					$elem->active = false;
		}

		if (!empty($nova_cena_bez_dph))
		{
			if ($costs_with_vat)
			{
				$vat = round($product_data->NewCost * $vat_coefs['top'], 2);
				$new_cost_value_novat = $product_data->NewCost - $vat;
			}
			else
				$new_cost_value_novat = $product_data->NewCost;
			$new_cost = new Money_Value($new_cost_value_novat, $monetary_format);
			$html_cost = new HTML_Text($new_cost->format());
			foreach ($nova_cena_bez_dph as $elem)
				$elem->add($html_cost);
		}

		if (!empty($stara_cena_s_dph))
		{
			if ($product_data->OriginalCost)
			{
				if ($costs_with_vat)
					$orig_cost_value_vat = $product_data->OriginalCost;
				else
				{
					$vat = round($product_data->OriginalCost * $vat_coefs['bottom'], 2);
					$orig_cost_value_vat = $product_data->OriginalCost + $vat;
				}
				$orig_cost = new Money_Value($orig_cost_value_vat, $monetary_format);
				$html_cost = new HTML_Text($orig_cost->format());
				foreach ($stara_cena_s_dph as $elem)
					$elem->add($html_cost);
			}
			else
				foreach ($stara_cena_s_dph as $elem)
					$elem->active = false;
		}

		if (!empty($nova_cena_s_dph))
		{
			if ($costs_with_vat)
				$new_cost_value_vat = $product_data->NewCost;
			else
			{
				$vat = round($product_data->NewCost * $vat_coefs['bottom'], 2);
				$new_cost_value_vat = $product_data->NewCost + $vat;
			}
			$new_cost = new Money_Value($new_cost_value_vat, $monetary_format);
			$html_cost = new HTML_Text($new_cost->format());
			foreach ($nova_cena_s_dph as $elem)
				$elem->add($html_cost);
		}

		if (!empty($stara_cena_dph))
		{
			$vat = round($product_data->OriginalCost * $vat_coefs[$costs_with_vat ? 'top' : 'bottom']);
			$vat_mv = new Money_Value($vat, $monetary_format);
			$html_vat = new HTML_Text($vat_mv->format());
			foreach ($stara_cena_dph as $elem)
				$elem->add($html_vat);
		}

		if (!empty($nova_cena_dph))
		{
			$vat = round($product_data->NewCost * $vat_coefs[$costs_with_vat ? 'top' : 'bottom']);
			$vat_mv = new Money_Value($vat, $monetary_format);
			$html_vat = new HTML_Text($vat_mv->format());
			foreach ($nova_cena_dph as $elem)
				$elem->add($html_vat);
		}

		$cost_change = $product_data->OriginalCost != 0 && $product_data->OriginalCost != $product_data->NewCost;

		foreach ($ma_zmenu_ceny as $elem)
			$elem->active = $cost_change;

		foreach ($nema_zmenu_ceny as $elem)
			$elem->active = !$cost_change;

		if (!empty($zmena_ceny))
		{
			$dif = $product_data->NewCost - $product_data->OriginalCost;
			$dif_mv = new Money_Value($dif, $monetary_format);
			$html_dif = new HTML_Text($dif_mv->format());
			foreach ($zmena_ceny as $elem)
				$elem->add($html_dif);
		}

		if (!empty($zmena_ceny_procent))
		{
			$dif = $product_data->NewCost - $product_data->OriginalCost;
			if ($product_data->OriginalCost != 0)
				$dif_pct = round($dif * 100 / $product_data->OriginalCost);
			else
				$dif_pct = 0;
			$html_dif_pct = new HTML_Text($dif_pct);
			foreach ($zmena_ceny_procent as $elem)
				$elem->add($html_dif_pct);
		}
	}

	protected function updateTemplate_Description(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'ma_popis',
			'nema_popis',
			'popis',
			'ma_kratky_popis',
			'nema_kratky_popis',
			'kratky_popis'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		$longdesc = $product_data->LongDesc;
		$shortdesc = $product_data->ShortDesc;
		$has_desc = $longdesc !== '';
		$has_shortdesc = $shortdesc !== '';

		foreach ($ma_popis as $elem)
			$elem->active = $has_desc;

		foreach ($nema_popis as $elem)
			$elem->active = !$has_desc;

		foreach ($ma_kratky_popis as $elem)
			$elem->active = $has_shortdesc;

		foreach ($nema_kratky_popis as $elem)
			$elem->active = !$has_shortdesc;

		if ($has_desc && !empty($popis))
		{
			$html_ldsc = new HTML_Text($this->prepareProductDescriptionHTML($longdesc));
			$html_ldsc->raw = true;
			foreach ($popis as $elem)
				$elem->add($html_ldsc);
		}

		if ($has_shortdesc && !empty($kratky_popis))
		{
			$html_sdsc = new HTML_Text($this->prepareProductDescriptionHTML($shortdesc));
			$html_sdsc->raw = true;
			foreach ($kratky_popis as $elem)
				$elem->add($html_sdsc);
		}
	}

	protected function updateTemplate_Properties(HTML_Indexer $index = null, Kiwi_Product $product_data = null)
	{
		if ($index === null)
			$index = $this->_index;

		if ($product_data === null)
			$product_data = $this->_product_data;

		$vars = array
		(
			'obrazek_vlastnost_ikona',
			'obrazek_vlastnost_barva',
			'ma_vlastnost_vzor',
			'nema_vlastnost_vzor',
			'nazev_vlastnosti',
			'hodnoty_vlastnosti',
			'ma_vlastnost',
			'nema_vlastnost',
			'vlastnost',
			'vlastnosti'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		if (!empty($vlastnosti))
		{
			$required_unique_vars = array
			(
				'obrazek_vlastnost_ikona',
				'obrazek_vlastnost_barva',
				'ma_vlastnost_vzor',
				'nema_vlastnost_vzor'
			);

			foreach ($required_unique_vars as $varname)
			{
				if (empty($$varname))
					throw new Template_Element_Missing_Exception($varname);

				if (count($$varname) !== 1)
					throw new Template_Invalid_Structure_Exception("The \"$varname\" element duplicity");

				$temp = $$varname;
				$$varname = $temp[0];
			}

			foreach ($vlastnosti as $elem)
				$this->buildProductPropertiesValuesList($elem, $ma_vlastnost_vzor, $nema_vlastnost_vzor, $nazev_vlastnosti, $hodnoty_vlastnosti, $obrazek_vlastnost_ikona, $obrazek_vlastnost_barva, $product_data);
		}

		if (!empty($vlastnost) || !empty($ma_vlastnost) || !empty($nema_vlastnost))
		{
			$properties = $product_data->properties;

			foreach ($vlastnost as $elem)
			{
				$propid = (int)$elem->specification;
				if ($propid == 0)
					throw new Template_Invalid_Argument_Exception('vlastnost::specification', $elem->specification);

				if (array_key_exists($propid, $properties))
				{
					$values = $this->buildProductPropertyValuesList($properties[$propid], $obrazek_vlastnost_ikona, $obrazek_vlastnost_barva, $product_data);
					$elem->add($values);
				}
			}

			foreach ($ma_vlastnost as $elem)
			{
				$propid = (int)$elem->specification;
				if ($propid == 0)
					throw new Template_Invalid_Argument_Exception('ma_vlastnost::specification', $elem->specification);
				$elem->active = array_key_exists($propid, $properties) && !empty($properties[$propid]['Values']);
			}

			foreach ($nema_vlastnost as $elem)
			{
				$propid = (int)$elem->specification;
				if ($propid == 0)
					throw new Template_Invalid_Argument_Exception('nema_vlastnost::specification', $elem->specification);
				$elem->active = !array_key_exists($propid, $properties) || empty($properties[$propid]['Values']);
			}
		}
	}

	protected function updateTemplate_CatalogProduct($p, array &$produkt_fields, array $p_vzor)
	{
		$vzor_fields = array();
		$vzor_group = new HTML_Group;
		$zaklad_vzory = $this->_index->zaklad_vzory;

		if (empty($zaklad_vzory))
			throw new Template_Element_Missing_Exception('zaklad_vzory');
		if (count($zaklad_vzory) !== 1)
			throw new Template_Invalid_Structure_Exception("The \"zaklad_vzory\" element duplicity");

		$vzor_group->add(clone $zaklad_vzory[0]);

		foreach ($p_vzor as $elem)
		{
			$vzor_clone = clone $elem;
			$vzor_fields[$vzor_clone->specification] = $vzor_clone;
			$vzor_group->add($vzor_clone);
		}

		$index = new HTML_Indexer($vzor_group, 'eid', false);

		if ($p !== null)
		{
			//$po = new Kiwi_Product($p->PID);
			$po = Kiwi_Object_Manager::load('Kiwi_Product', array($p->PID)); // allows caching

			// generování řádků popisujících umístění produktu v katalogu
			$this->updateTemplate_Location($index, $po);

			// generování názvu produktu
			$this->updateTemplate_Title($index, $po);

			// generování kódu produktu
			$this->updateTemplate_Code($index, $po);

			// generování fotografie
			$this->updateTemplate_CatalogPhoto($index, $po);

			// generování odkazu na detail produktu
			$this->updateTemplate_DetailLink($index, $po);

			// generování dalších fotografií
			$this->updateTemplate_AdditionalPhotos($index, $po);

			// generování ilustrativních fotografií
			$this->updateTemplate_IllustrativePhotos($index, $po);

			// generování fotografií produktů z kolekce
			$this->updateTemplate_CollectionPhotos($index, $po);

			// generování příloh
			$this->updateTemplate_Attachments($index, $po);

			// generování odkazu pro objednání
			$this->updateTemplate_OrderLink($index, $po);

			// generování příznaku novinky
			$this->updateTemplate_Novelty($index, $po);

			// generování příznaku akce
			$this->updateTemplate_Action($index, $po);

			// generování příznaku slevy
			$this->updateTemplate_Discount($index, $po);

			// generování ceny (cen)
			$this->updateTemplate_Cost($index, $po);

			// generování popisu
			$this->updateTemplate_Description($index, $po);

			// generování vlastností
			$this->updateTemplate_Properties($index, $po);
		}

		foreach ($vzor_fields as $section => $field)
			if (array_key_exists($section, $produkt_fields))
				$produkt_fields[$section]->add($field);
	}

/* update template helper methods: end */

	protected function resolveQS()
	{
		if ($this->_qs === null)
		{
			$this->qs = Server::get('REQUEST_QUERY_STRING');
			$this->_qs->remove($this->ns . 'p');
			$this->_qs->remove($this->ns . 'pmi');
			$this->_qs->remove($this->ns . 'pg');
		}
	}
}
?>
