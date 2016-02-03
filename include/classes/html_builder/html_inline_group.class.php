<?php
class HTML_Inline_Group extends HTML_Group implements HTML_Inline
{
	public function __construct($id = null, $tag = 'inline')
	{
		parent::__construct($id, $tag);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Inline)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Inline interface.');
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}
}
?>