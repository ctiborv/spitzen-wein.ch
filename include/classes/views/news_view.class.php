<?php
class News_View extends Template_Based_View
{
	protected $_kiwi_module;
	protected $_items;
	protected $_total;
	protected $_qs;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_kiwi_module = null;
		$this->_items = null;
		$this->_total = null;
		$this->_qs = null;

		$this->_attributes->register('mid'); // module id in database
		$this->_attributes->register('name'); // module name in database
		$this->_attributes->register('mactive'); // module activity status
		$this->_attributes->register('ngid'); // newsgroup id in database
		$this->_attributes->register('newsgroup'); // newsgroup name in database
		$this->_attributes->register('size'); // number of newsitems per page to display
		$this->_attributes->register('listmode', 'S'); // listing mode ("S" means samples, "C" means complete texts)
		$this->_attributes->register('pagination'); // pagination display toggle ("", "0", "top", "bottom"; other means "on")
		$this->_attributes->register('detailpage'); // navigation point for displaying detailed newsitems
		$this->_attributes->register('item'); // id of newsitem to display in detail (dont set if you want list)
		$this->_attributes->register('page'); // page to display (when displaying list)
		$this->_attributes->register('ns'); // namespace (query string variables prefix)
		$this->_attributes->register('nsd'); // detail namespace (query string variables prefix) (if not defined, falls back to ns)
		// $this->_attributes->register('qs'); // query string to preserve // special variable
		$this->_attributes->register('images'); // attribute which says if images should be rendered in special way

		$this->template = 'news/default'; // default template
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

	protected function _initialize()
	{
		//$this->_kiwi_module = new Kiwi_News_Module($this->mid, $this->name, $this->ngid, $this->newsgroup, $this->size, $this->listmode, $this->pagination, $this->detailpage);
		$this->_kiwi_module = Kiwi_Object_Manager::load
		(
			'Kiwi_News_Module', 
			array
			(
				$this->mid,
				$this->name,
				$this->ngid,
				$this->newsgroup,
				$this->size,
				$this->listmode,
				$this->pagination,
				$this->detailpage
			)
		); // allows caching

		// non-overridable attributes (module attributes have priority)
		$attr_no = array
		(
			'mid' => 'id',
			'name' => 'name',
			'ngid' => 'ngid',
			'mactive' => 'active'
		);
		foreach ($attr_no as $attr => $mattr)
			if ($this->_kiwi_module->$mattr !== null)
				$this->$attr = $this->_kiwi_module->$mattr;

		// overridable attributes (tag attributes have priority)
		$attr_o = array
		(
			'size' => 'size',
			'listmode' => 'listmode',
			'pagination' => 'pagination',
			'detailpage' => 'detailpage'
		);
		foreach ($attr_o as $attr => $mattr)
			if ($this->$attr === null)
				$this->$attr = $this->_kiwi_module->$mattr;

		// default values of attributes
		$defaults = array
		(
			'size' => 5,
			'pagination' => 'both',
			'page' => 1,
			'ns' => '',
			'images' => ''
		);
		foreach ($defaults as $attr => $val)
			if ($this->$attr === null)
				$this->$attr = $val;

		// default for nsd is value of ns
		if ($this->nsd === null)
			$this->nsd = $this->ns;

		if ($this->ngid === null && $this->newsgroup === null)
			throw new Data_Insufficient_Exception('ngid or newsgroup');

		parent::_initialize();
	}

	protected function _handleInput()
	{
		if ($this->item === null)
		{
			$qsvar = $this->ns . 'ni';
			if (array_key_exists($qsvar, $_GET))
				$this->item = $_GET[$qsvar];
		}

		$qsvar = $this->ns . 'pg';
		if (array_key_exists($qsvar, $_GET))
			$this->page = $_GET[$qsvar];
	}

	protected function resolveTemplateSource()
	{
		if (!$this->mactive)
			$suffix = 'disabled';
		else
			$suffix = $this->item === null ? 'list' : 'detail';
		$base = $this->template;
		if ($base === null)
			throw new Data_Insufficient_Exception('template');
		$this->template = $base . '_' . $suffix;
	}

	protected function _updateTemplate()
	{
		$this->loadData();
		if (!$this->mactive)
			$this->updateNewsDisabledTemplate();
		elseif ($this->item === null)
			$this->updateNewsListTemplate();
		else
			$this->updateNewsDetailTemplate();
	}

	protected function updateHead()
	{
		Images_JS_Support::updateHead($this->images, $this->_view_manager->head);
	}

	protected function updateNewsDisabledTemplate()
	{
	}

	protected function updateNewsListTemplate()
	{
		$vars = array
		(
			'novinka_vzor',
			'ma_detail',
			'nema_detail',
			'detail',
			'nadpis',
			'obsah',
			'datum',
			'autor',
			'autor_jako_link',
			'zadne_novinky',
			'prazdna_stranka',
			'horni_paginace',
			'novinky',
			'dolni_paginace'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		if (!$novinka_vzor)
			throw new Template_Element_Missing_Exception('novinka_vzor');

		if (count($novinka_vzor) != 1)
			throw new Template_Invalid_Structure_Exception('The "novinka_vzor" element duplicity');

		if ($horni_paginace && count($horni_paginace) != 1)
			throw new Template_Invalid_Structure_Exception('The "horni_paginace" element duplicity');

		if ($dolni_paginace && count($dolni_paginace) != 1)
			throw new Template_Invalid_Structure_Exception('The "dolni_paginace" element duplicity');

		$novinka_vzor = $novinka_vzor[0];
		if ($horni_paginace)
			$horni_paginace = $horni_paginace[0];
		if ($dolni_paginace)
			$dolni_paginace = $dolni_paginace[0];

		$detailpage = $this->detailpage ? Project_Navigator::getPage($this->detailpage) : '';
		$this->resolveQS();
		$this->_qs->remove($this->nsd . 'ni');
		$this->_qs->set($this->nsd . 'ni', ''); // nastavi query string tak, aby koncil na "ni="

		$detail_link = $detailpage . $this->qs;

		if (!empty($novinky))
		{
			foreach ($this->_items as &$item)
			{
				$ma_detail_val = $item->Content !== '';
				foreach ($ma_detail as $elem)
					$elem->active = $ma_detail_val;

				foreach ($nema_detail as $elem)
					$elem->active = !$ma_detail_val;

				foreach ($nadpis as $elem)
					$elem->text = $item->Name;

				foreach ($detail as $elem)
					$elem->href = $detail_link . $item->ID;
				
				foreach ($obsah as $elem)
				{
					switch ($this->listmode)
					{
						case 'S':
							$content = $item->Sample;
							break;
						case 'C':
							$content = $item->Content;
							break;
						default:
							throw new Template_Invalid_Argument_Exception('listmode', $this->listmode);
					}

					$content_elem = new HTML_Text(Images_JS_Support::updateHTMLRaw($this->images, $content));
					$content_elem->raw = true;
					$elem->clear();
					$elem->add($content_elem);
				}

				foreach ($datum as $elem)
				{
					try
					{
						$elem->timestamp = $item->When; // nikoli view_object, protoze se to bude jeste klonovat
					}
					catch (HTML_No_Such_Element_Attribute_Exception $e)
					{
						$datum_spec = null;
						try
						{
							$datum_spec = $elem->specification;
						}
						catch (HTML_No_Such_Element_Attribute_Exception $e)
						{
						}

						$datetime = new Date_Time($item->When);
						$elem->add(new HTML_Text($datetime->format($datum_spec === null ? 'j.n. Y' : $datum_spec)));
					}
				}

				foreach ($autor as $elem)
					$elem->text = $item->Author;

				if (!empty($autor_jako_link))
				{
					$autor_jako_link->clear();
					$a = new HTML_A;
					$a->href = 'http://' . $item->Author;
					$a->add(new HTML_Text($item->Author));
					foreach ($autor_jako_link as $elem)
						$elem->add($a);
				}

				foreach ($novinky as $elem)
					$elem->add(clone $novinka_vzor);
			}
		}

		foreach ($zadne_novinky as $elem)
			$elem->active = empty($this->_items) && $this->_total == 0;

		foreach ($prazdna_stranka as $elem)
			$elem->active = empty($this->_items) && $this->_total > 0;

		$pages = (int)($this->_total / $this->size);
		if ($pages * $this->size < $this->_total) $pages++;

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
	}

	protected function updateNewsDetailTemplate()
	{
		$vars = array
		(
			'je_novinka',
			'neni_novinka',
			'nadpis',
			'obsah',
			'datum',
			'autor',
			'autor_jako_link'
		);
		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		foreach ($je_novinka as $elem)
			$elem->active = !empty($this->_items);

		foreach ($neni_novinka as $elem)
			$elem->active = empty($this->_items);

		if (!empty($this->_items))
		{
			$item =& $this->_items[0];

			foreach ($nadpis as $elem)
				$elem->text = $item->Name;

			if (!empty($obsah))
			{
				$content = $item->Content !== '' ? $item->Content : $item->Sample;			
				$content_elem = new HTML_Text(Images_JS_Support::updateHTMLRaw($this->images, $content));
				$content_elem->raw = true;
				foreach ($obsah as $elem)
					$elem->add($content_elem);
			}

			foreach ($datum as $elem)
			{
				try
				{
					$elem->vo->timestamp = $item->When;
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
					$datum_spec = null;
					try
					{
						$datum_spec = $elem->specification;
					}
					catch (HTML_No_Such_Element_Attribute_Exception $e)
					{
					}

					$datetime = new Date_Time($item->When);
					$elem->add(new HTML_Text($datetime->format($datum_spec === null ? 'j.n. Y' : $datum_spec)));
				}
			}

			foreach ($autor as $elem)
				$elem->text = $item->Author;

			if (!empty($autor_jako_link))
			{
				$autor_jako_link->clear();
				$a = new HTML_A;
				$a->href = 'http://' . $item->Author;
				$a->add(new HTML_Text($item->Author));
				foreach ($autor_jako_link as $elem)
					$elem->add($a);
			}
		}
	}

	protected function loadData()
	{
		if ($this->_items === null)
		{
			if ($this->item === null)
			{
				$page = $this->page === null ? 1 : $this->page;
				$size = (int)$this->size;
				$this->_kiwi_module->loadNewsList($this->_items, $this->_total, $page, $size);
			}
			else
				$this->_kiwi_module->loadNewsItem($this->_items, $this->item);
		}
	}

	protected function resolveQS()
	{
		if ($this->_qs === null)
		{
			$this->qs = Server::get('REQUEST_QUERY_STRING');
			$this->_qs->remove($this->ns . 'ni');
			$this->_qs->remove($this->ns . 'pg');
		}
	}
}
?>
