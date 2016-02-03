<?php
abstract class HTML_Heading extends HTML_Entity_Group implements HTML_Block
{
	public function __construct($tag)
	{
		$this->_tag = $tag;
		parent::__construct();
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