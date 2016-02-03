<?php
class HTML_Head extends HTML_Element_Group
{
	public function __construct()
	{
		$this->_tag = 'head';
		parent::__construct();

		$this->_attributes->register('profile');
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Head_Content)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Head_Content interface.');
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
		$renderer->renderNL();
	}
}
?>