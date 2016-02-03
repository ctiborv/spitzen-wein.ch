<?php
class HTML_TH extends HTML_Entity_Group implements HTML_TR_Content
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'th';
		parent::__construct();

		$this->_attributes->register('abbr');
		$this->_attributes->register('axis');
		$this->_attributes->register('headers');
		$this->_attributes->register('scope');
		$this->_attributes->register('rowspan');
		$this->_attributes->register('colspan');
		$this->_attributes->register('align');
		$this->_attributes->register('char');
		$this->_attributes->register('charoff');
		$this->_attributes->register('valign');
		$this->_attributes->register('nowrap');
		$this->_attributes->register('bgcolor');
		$this->_attributes->register('width');
		$this->_attributes->register('height');

		if ($id !== null) $this->id = $id;
		if ($class_name !== null) $this->class = $class_name;
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Flow)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Flow interface.');
	}

	protected function renderAttribute($name, $value, Text_Renderer $renderer)
	{
		if (($name == 'rowspan' || $name == 'colspan') && $value == 1) return;
		parent::renderAttribute($name, $value, $renderer);
	}
}
?>