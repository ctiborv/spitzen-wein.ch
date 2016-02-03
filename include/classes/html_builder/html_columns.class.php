<?php
class HTML_Columns extends HTML_Group implements HTML_TR_Content
{
	public function __construct($id = null, $tag = 'columns')
	{
		parent::__construct($id, $tag);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_TD || $element instanceof HTML_Columns)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Attempt to add a non HTML_TD / HTML_Columns class: ' . get_class($element));
	}
}
?>
