<?php
class HTML_TR extends HTML_Entity_Group implements HTML_Table_Content
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'tr';
		parent::__construct();

		$this->_attributes->register('align');
		$this->_attributes->register('char');
		$this->_attributes->register('charoff');
		$this->_attributes->register('valign');
		$this->_attributes->register('bgcolor');

		if ($id !== null) $this->id = $id;
		if ($class_name !== null) $this->class = $class_name;
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_TR_Content)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_TR_Content interface.');
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
	}
}
?>