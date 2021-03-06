<?php
class HTML_TBody extends HTML_Entity_Group implements HTML_Table_Content
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'tbody';
		parent::__construct();

		$this->_attributes->register('align');
		$this->_attributes->register('char');
		$this->_attributes->register('charoff');
		$this->_attributes->register('valign');

		if ($id !== null) $this->id = $id;
		if ($class_name !== null) $this->class = $class_name;
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_TR)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' not allowed here, only HTML_TR allowed.');
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
	}
}
?>