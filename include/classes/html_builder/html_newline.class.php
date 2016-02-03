<?php
class HTML_NewLine extends HTML_Element implements HTML_Block, HTML_Inline
{
	public function __construct()
	{
		parent::__construct();
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		throw new HTML_Content_Exception('Attempt to add contents of type: ' . get_class($element) . ' into a HTML_NewLine object.');
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
	}
}
?>