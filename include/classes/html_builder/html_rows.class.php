<?php
class HTML_Rows extends HTML_Group implements HTML_Table_Content
{
	public function __construct($id = null, $tag = 'rows')
	{
		parent::__construct($id, $tag);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_TR || $element instanceof HTML_Rows)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Attempt to add a non HTML_TR / HTML_Rows class: ' . get_class($element));
	}
}
?>