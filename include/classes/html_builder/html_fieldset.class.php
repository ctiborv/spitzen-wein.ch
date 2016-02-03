<?php
class HTML_Fieldset extends HTML_Entity_Group implements HTML_Block
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'fieldset';
		parent::__construct($id, $class_name);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Flow)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Flow interface.');
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
		parent::renderBegin($renderer);
		$renderer->renderNL();
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
		$renderer->renderNL();
	}
}
?>