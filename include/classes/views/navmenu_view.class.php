<?php
class Navmenu_View extends Template_Based_View
{
	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_attributes->register('current'); // currently active menuitem
		$this->_attributes->register('autodetect'); // automatic detection of current menuitem (overrides the current attribute)

		$this->template = 'navmenu/default'; // default template
	}

	protected function _handleInput()
	{
		if ($this->autodetect)
			$this->current = Project_Navigator::getNavPoint(Server::get('REQUEST_URL'));
		elseif ($this->current !== null)
			Project_Navigator::get($this->current); // check if registered
	}

	protected function _updateTemplate()
	{
		$menuitem = $this->_index->menuitem;

		$activated = 0;
		$default = array();
		foreach ($menuitem as $item)
		{
			if (!($item instanceof HTML_Group))
				throw new Template_Exception('Navmenu menuitem element is not an instance of HTML_Group');
			if ($item->count() > 2)
				throw new Template_Exception('Navmenu menuitem group contains more than 2 sub-elements');
			if ($item->count() == 2)
			{
				if ($item->specification === 'default')
					$default[] = $item;
				else
				{
					$deact = $this->current !== null && $item->specification === $this->current ? 0 : 1;
					$item->getAt($deact)->active = false;
					if (!$deact) ++$activated;
				}
			}
		}

		if (!empty($default))
		{
			$deact = $activated ? 1 : 0;
			foreach ($default as $elem)
				$elem->getAt($deact)->active = false;
		}
	}
}
?>
