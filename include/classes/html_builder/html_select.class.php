<?php
class HTML_Select extends HTML_Option_Group implements HTML_Inline
{
	public function __construct($name = null)
	{
		$this->_tag = 'select';
		parent::__construct();

		$this->_attributes->register('name', $name);
		$this->_attributes->register('size');
		$this->_attributes->register('multiple');
		$this->_attributes->register('disabled');		
		$this->_attributes->register('tab_index');

		$this->_events->register('onfocus');
		$this->_events->register('onblur');
		$this->_events->register('onchange');
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_OptGroup)
			HTML_Entity_Group::add($element, $content_check);
		else parent::add($element, $content_check);
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		$options_present = false;
		foreach ($this->_elements as &$option)
		{
			if ($options_present) break;
			else
			{
				if ($option instanceof HTML_OptGroup)
					$options_present = $option->countActive() > 0;
				else
					$options_present = $option->active;
			}
		}

		if (!$options_present)
			throw new HTML_Empty_Select_Exception();

		parent::renderContent($renderer);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
	}

	public function select($value)
	{
		if (is_array($value) && !$this->_attributes->multiple)
			throw new HTML_Select_Not_Multiple_Exception();
		else
			parent::select($value);
	}
}
?>