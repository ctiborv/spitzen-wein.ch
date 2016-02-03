<?php
class HTML_Scripts extends HTML_Head_Group
{
	protected $_index;

	public function __construct($id = null, $tag = 'scripts')
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
		if (!$content_check || $element instanceof HTML_Script)
			$this->addScript($element, $content_check);
		else
			throw new HTML_Content_Exception('Attempt to add contents of type: ' . get_class($element) . ' into a HTML_Scripts object.');
	}

	public function addScript(HTML_Script $script)
	{
		$type = $script->type;
		$src = $script->src;

		if (!array_key_exists($type, $this->_index))
			$this->_index[$type] = array('src' => array(), 'text' => array());

		if ($src === null)
			$this->_index[$type]['text'][md5($script->text)] = true;
		else
			$this->_index[$type]['src'][$src] = true;

		parent::add($script);
	}

	public function addUnique(HTML_Script $script)
	{
		$type = $script->type;
		$src = $script->src;

		$match = false;
		if (array_key_exists($type, $this->_index))
		{
			if ($src === null)
				$match = array_key_exists(md5($script->text), $this->_index[$type]['text']);
			else
				$match = array_key_exists($src, $this->_index[$type]['src']);
		}
		if (!$match)
		{
			$this->addScript($script);
			return true;
		}
		else
			return false;
	}
}
?>