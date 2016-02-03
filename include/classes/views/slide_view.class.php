<?php
//TODO: dokoncit
//TODO: dobudoucna predelat jako kiwi modul
//TODO: vyrobit Kiwi_Slide tridu
class Slide_View extends Template_Based_View
{
	protected $_do;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_do = null;

		$this->_attributes->register('id', 'slider'); // ID of the parent slideshow div
		$this->_attributes->register('auto', 3); // Seconds to auto-advance, defaults to disabled
		$this->_attributes->register('resume', true); // Resume auto after interrupted, defaults to false
		$this->_attributes->register('vertical', false); // Direction, defaults to false
		$this->_attributes->register('navid', 'pagination'); // Optional ID of direct navigation UL
		$this->_attributes->register('activeclass', 'current'); // Class to set on the current LI
		$this->_attributes->register('position', 0); // Initial slide position, defaulting to index 0
		/*
		 * You can also optionally set width and height parameters for the applicable direction you are sliding.
		 * If it is not set the width or height will be automatically calculated using the offsetWidth/offsetHeight
		 * of the first list element.
		 */
		$this->_attributes->register('width');
		$this->_attributes->register('height');

		$this->template = 'slide/default'; // default template
	}

	protected function _initialize()
	{
		//$this->_do = new Kiwi_Slide;
		$this->_do = Kiwi_Object_Manager::load
		(
			'Kiwi_Slide'
		); // allows caching

		parent::_initialize();
	}

	protected function _updateTemplate()
	{
		$this->updateSlideTemplate();
	}

	protected function updateHead()
	{
		//TODO: pridat javascript do headu
	}

	protected function updateSlideTemplate()
	{
		$vars = array
		(
			'polozka_vzor',
			'nadpis',
			'ma_popis',
			'nema_popis',
			'popis',
			'ma_obrazek',
			'nema_obrazek',
			'obrazek',
			'ma_odkaz',
			'nema_odkaz',
			'odkaz',
			'ma_polozky',
			'nema_polozky',
			'polozky'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		if (!$polozka_vzor)
			throw new Template_Element_Missing_Exception('polozka_vzor');

		if (count($polozka_vzor) != 1)
			throw new Template_Invalid_Structure_Exception('The "polozka_vzor" element duplicity');

		$polozka_vzor = $polozka_vzor[0];

		$items = $this->_do->items;
		if (!empty($polozky))
		{
			foreach ($items as &$item)
			{
				$ma_popis_val = $item->Description !== '';
				$ma_obrazek_val = $item->Picture !== '';
				$ma_odkaz_val = $item->Link !== '';

				foreach ($ma_popis as $elem)
					$elem->active = $ma_popis_val;

				foreach ($nema_popis as $elem)
					$elem->active = !$ma_popis_val;

				foreach ($ma_obrazek as $elem)
					$elem->active = $ma_obrazek_val;

				foreach ($nema_obrazek as $elem)
					$elem->active = !$ma_obrazek_val;

				foreach ($ma_odkaz as $elem)
					$elem->active = $ma_odkaz_val;

				foreach ($nema_odkaz as $elem)
					$elem->active = !$ma_odkaz_val;

				foreach ($nadpis as $elem)
					$elem->text = $item->Title;

				foreach ($obrazek as $elem)
					$elem->src = $item->Picture; // TODO: pridat adresar

				foreach ($odkaz as $elem)
					$elem->href = $item->Link;
				
				foreach ($popis as $elem)
				{
					$content_elem = new HTML_Text($item->Description);
					$content_elem->raw = true;
					$elem->clear();
					$elem->add($content_elem);
				}

				$polozka_hotova = clone $polozka_vzor; // memory optimization
				foreach ($polozky as $elem)
					$elem->add($polozka_hotova);
			}
		}

		foreach ($ma_polozky as $elem)
			$elem->active = !empty($items);

		foreach ($nema_polozky as $elem)
			$elem->active = empty($items);
	}
}
?>
