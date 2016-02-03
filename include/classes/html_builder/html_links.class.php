<?php
class HTML_Links extends HTML_Head_Group
{
	protected $_index;

	public function __construct($id = null, $tag = 'links')
	{
		parent::__construct($id, $tag);
		$this->_index = array();
	}

	public function clear()
	{
		parent::clear();
		$this->_index = array();
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Link)
			$this->addLink($element, $content_check);
		else
			throw new HTML_Content_Exception('Attempt to add contents of type: ' . get_class($element) . ' into a HTML_Links object.');
	}

	public function addLink(HTML_Link $link)
	{
		$type = $link->type;
		$href = $link->href;
/*
		if (!array_key_exists($type, $this->_index))
			$this->_index[$type] = array('href' => array());
*/
		if ($href !== null)
			$this->_index[$type]['href'][$href] = true;

		parent::add($link);
	}

	public function addUnique(HTML_Link $link)
	{
		$type = $link->type;
		$href = $link->href;

		$match = false;
		if (array_key_exists($type, $this->_index))
		{
			if ($href !== null)
				$match = array_key_exists($href, $this->_index[$type]['href']);
		}
		if (!$match)
		{
			$this->addLink($link);
			return true;
		}
		else
			return false;
	}
}
?>