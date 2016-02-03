<?php
class Banners_View extends Template_Based_View
{
	protected $_kiwi_object;
	protected $_banners;

	const FEATURELIST_PLUGIN_VERSION = '1.0.0';

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_kiwi_object = null;
		$this->_banners = null;

		$this->_attributes->register('group'); // banner group id in database
		$this->_attributes->register('max'); // max banners

		$this->template = 'banners/default'; // default template
	}

	protected function _initialize()
	{
		if ($this->group === null)
			throw new Data_Insufficient_Exception('group');

		//$this->_kiwi_object = new Kiwi_Banners($this->group);
		$this->_kiwi_object = Kiwi_Object_Manager::load
		(
			'Kiwi_Banners',
			array
			(
				$this->group
			)
		); // allows caching

		parent::_initialize();
	}

	protected function _handleInput()
	{
	}

	protected function _updateTemplate()
	{
		$this->loadData();
		$this->updateBannersTemplate();
	}

	protected function updateHead()
	{
		$nav = new Project_Navigator;
		try
		{
			$featurelist_js_dir = $nav->featurelist_js_dir;
		}
		catch (Navigator_No_Such_Location_Exception $e)
		{
			$featurelist_js_dir = $nav->js_dir;
		}

		$match = $this->_view_manager->head->getElementsBy('tag', 'scripts');
		if (empty($match))
			throw new Template_Invalid_Structure_Exception('scripts tag missing in head template');
		$scripts = $match[0];

		$featurelist_scripts = array
		(
			'jquery.js' => $nav->js_dir,
			'jquery.featureList-' . self::FEATURELIST_PLUGIN_VERSION . '.js' => $featurelist_js_dir,
			'jquery.featureList-ready.js' => $featurelist_js_dir
		);
		foreach ($featurelist_scripts as $script => $dir)
			$scripts->addUnique(new HTML_JavaScript($dir . $script));
	}

	protected function updateBannersTemplate()
	{
		$vars = array
		(
			'banner_vzor',
			'bannery',
			'jsou_bannery',
			'nejsou_bannery',
			'vyber_vzor',
			'vyber_index',
			'bannery_vyber'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		if (!$banner_vzor)
			throw new Template_Element_Missing_Exception('banner_vzor');

		if (count($banner_vzor) != 1)
			throw new Template_Invalid_Structure_Exception('The "banner_vzor" element duplicity');

		if (!$vyber_vzor)
			throw new Template_Element_Missing_Exception('vyber_vzor');

		if (count($vyber_vzor) != 1)
			throw new Template_Invalid_Structure_Exception('The "vyber_vzor" element duplicity');

		if (!$vyber_index)
			throw new Template_Element_Missing_Exception('vyber_index');

		$banner_vzor = $banner_vzor[0];
		$vyber_vzor = $vyber_vzor[0];

		// bannery
		if (!empty($bannery))
		{
			$counter = 0;
			foreach ($this->_banners as &$banner)
			{
				if ($this->max !== null && $counter >= $this->max) break;
				$this->updateTemplate_Banner($banner);
				foreach ($bannery as $elem)
					$elem->add(clone $banner_vzor);
				++$counter;
			}

			foreach ($bannery_vyber as $elem)
				$elem->active = $counter > 1;

			if ($counter > 1)
			{
				foreach($bannery as $elem)
					$elem->addClass('slide');

				for ($page = 1; $page <= $counter; ++$page)
				{
					foreach ($vyber_index as $elem)
						$elem->text = (string)$page;

					foreach ($bannery_vyber as $elem)
						$elem->add(clone $vyber_vzor);
				}
			}
		}

		foreach ($jsou_bannery as $elem)
			$elem->active = $counter > 0;

		foreach ($nejsou_bannery as $elem)
			$elem->active = $counter == 0;
	}

/* update template helper methods: begin */

	protected function updateTemplate_Banner(Var_Pool $banner)
	{
		$this->updateTemplate_Link($banner);
		$this->updateTemplate_Title($banner);
		$this->updateTemplate_Description($banner);
		$this->updateTemplate_Picture($banner);
	}

	protected function updateTemplate_Link(Var_Pool $banner)
	{
		$odkaz_banneru = $this->_index->odkaz_banneru;

		if (!empty($odkaz_banneru))
		{
			$odkaz = $banner->Link ? $banner->Link : '#';
			foreach ($odkaz_banneru as $elem)
				$elem->href = $odkaz;
		}
	}

	protected function updateTemplate_Title(Var_Pool $banner)
	{
		$nadpis_banneru = $this->_index->nadpis_banneru;
		foreach ($nadpis_banneru as $elem)
			$elem->text = $banner->Title;
	}

	protected function updateTemplate_Description(Var_Pool $banner)
	{
		$popis_banneru = $this->_index->popis_banneru;
		$content_elem = new HTML_Text($banner->Description);
		$content_elem->raw = true;
		foreach ($popis_banneru as $elem)
		{
			$elem->clear();
			$elem->add($content_elem);
		}
	}

	protected function updateTemplate_Picture(Var_Pool $banner)
	{
		$vars = array
		(
			'ma_obrazek',
			'nema_obrazek',
			'obrazek_banneru'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		foreach ($ma_obrazek as $elem)
			$elem->active = $banner->Picture !== '';

		foreach ($nema_obrazek as $elem)
			$elem->active = $banner->Picture === '';

		if ($banner->Picture !== '')
		{
			$pnav = new Project_Navigator;
			$banners_directory = $pnav->banners_photos;
			foreach ($obrazek_banneru as $elem)
			{
				$elem->src = $banners_directory . $banner->Picture;
				$elem->alt = "obrázek banneru \"$banner->Title\"";
			}
		}
	}

/* update template helper methods: end */

	protected function loadData()
	{
		if ($this->_banners === null)
		{
			$this->_banners = $this->_kiwi_object->data;
		}
	}
}
?>
