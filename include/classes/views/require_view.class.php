<?php
class Require_View extends View_Object
{
	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_attributes->register('unique', true);
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
		if ($this->content === null)
			throw new Data_Insufficient_Exception('content');

		$nav = new Project_Navigator;
		$links = $this->getLinks($this->_view_manager->head);
		$scripts = $this->getScripts($this->_view_manager->head);

		$content = $this->content->getContents();	
		foreach ($content as $elem)
		{
			switch ($elem->tag)
			{
				case 'link':
					if ($this->unique)
						$links->addUnique(clone $elem);
					else
						$links->add(clone $elem);
					break;
				case 'script':
					if ($this->unique)
						$scripts->addUnique(clone $elem);
					else
						$scripts->add(clone $elem);
					break;
				default:
					throw new Invalid_Content_Exception("$elem->tag element", 'link or script elements');
			}
		}
	}

	protected function _handleInput()
	{
	}

	protected function _resolve()
	{
	}

	protected static function getLinks(HTML_Head $head)
	{
		$match = $head->getElementsBy('tag', 'links');
		return $match[0];
	}

	protected static function getScripts(HTML_Head $head)
	{
		$match = $head->getElementsBy('tag', 'scripts');
		return $match[0];
	}
}
?>