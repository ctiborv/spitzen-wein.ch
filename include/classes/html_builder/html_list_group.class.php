<?php
class HTML_List_Group extends HTML_Group implements HTML_List_Content
{
	public function __construct($id = null, $tag = 'list')
	{
		parent::__construct($id, $tag);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_List_Content)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_List_Content interface.');
	}
}
?>