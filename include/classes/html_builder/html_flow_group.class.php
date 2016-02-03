<?php
class HTML_Flow_Group extends HTML_Group implements HTML_Flow
{
	public function __construct($id = null, $tag = 'flow')
	{
		parent::__construct($id, $tag);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Flow)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Flow interface.');
	}
}
?>