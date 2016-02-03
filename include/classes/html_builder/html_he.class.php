<?php
class HTML_HE extends HTML_Element implements HTML_Inline
{
	protected $_code;

	public function __construct($code = '')
	{
		parent::__construct();

		$this->_code = $code;
	}

	public function get($name)
	{
		if ($name == 'code') return $this->_code;
		else return parent::get($name);
	}

	public function set($name, $value)
	{
		if ($name == 'code') $this->_code = $value;
		else parent::set($name, $value);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		throw new HTML_Content_Exception('Attempt to add contents of type: ' . get_class($element) . ' into a HTML_HE object.');
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
		if ($this->_code !== '')
			$renderer->render("&$this->_code;");
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
	}
}
?>