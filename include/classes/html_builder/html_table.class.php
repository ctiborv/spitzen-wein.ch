<?php
class HTML_Table extends HTML_Entity_Group implements HTML_Block
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'table';
		parent::__construct();

		$this->_attributes->register('summary');
		$this->_attributes->register('width');
		$this->_attributes->register('border');
		$this->_attributes->register('frame');
		$this->_attributes->register('rules');
		$this->_attributes->register('cellspacing');
		$this->_attributes->register('cellpadding');

		if ($id !== null) $this->id = $id;
		if ($class_name !== null) $this->class = $class_name;
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Table_Content)
		{
			if ($content_check && $element instanceof HTML_Caption)
				if (!empty($this->_elements))
					throw new HTML_Content_Exception('Caption permitted only as the first child element of the table element.');
			parent::add($element, $content_check);
		}
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Table_Content interface.');
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
	}
}
?>