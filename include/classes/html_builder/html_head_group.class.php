<?php
class HTML_Head_Group extends HTML_Group implements HTML_Head_Content
{
	public function __construct($id = null, $tag = 'hgroup')
	{
		parent::__construct($id, $tag);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Head_Content)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Head_Content interface.');
	}
}
?>