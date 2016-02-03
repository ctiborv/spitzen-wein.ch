<?php
class HTML_A extends HTML_Entity_Group implements HTML_Inline
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'a';
		parent::__construct($id, $class_name);

		$this->_attributes->register('charset');
		$this->_attributes->register('type');
		$this->_attributes->register('name');
		$this->_attributes->register('href');
		$this->_attributes->register('hreflang');
		$this->_attributes->register('rel');
		$this->_attributes->register('rev');
		$this->_attributes->register('accesskey');
		$this->_attributes->register('shape');
		$this->_attributes->register('coords');
		$this->_attributes->register('target');
		$this->_attributes->register('tabindex');

		$this->_events->register('onfocus');
		$this->_events->register('onblur');
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Inline)
		{
			if ($content_check && $element instanceof HTML_A)
				throw new HTML_Content_Exception('Attempt of "a" tags nesting');
			else
				parent::add($element, $content_check);
		}
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Inline interface.');
	}

	protected function renderAttribute($name, $value, Text_Renderer $renderer)
	{
		if ($name == 'shape' && $value == 'rect') return;
		parent::renderAttribute($name, $value, $renderer);
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}
}
?>