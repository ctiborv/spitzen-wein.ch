<?php
class HTML_DD extends HTML_Entity_Group implements HTML_DL_Content
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'dd';
		parent::__construct();

		if ($id !== null) $this->id = $id;
		if ($class_name !== null) $this->class = $class_name;
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