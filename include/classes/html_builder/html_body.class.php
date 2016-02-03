<?php
class HTML_Body extends HTML_Entity_Group
{
	public function __construct()
	{
		$this->_tag = 'body';
		parent::__construct();

		$this->_events->register('onload');
		$this->_events->register('onunload');
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Block)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Block interface.');
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		foreach ($this->_elements as $element)
			$element->render($renderer);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
		$renderer->renderNL();
	}
}
?>