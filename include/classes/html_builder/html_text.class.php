<?php
class HTML_Text extends HTML_Element implements HTML_Inline
{
	protected $_text;

	public function __construct($text = '')
	{
		parent::__construct();
		$this->_text = $text;
	}

	public function get($name)
	{
		if ($name == 'text') return $this->_text;
		else return parent::get($name);
	}

	public function set($name, $value)
	{
		if ($name == 'text') $this->_text = $value;
		else parent::set($name, $value);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		throw new HTML_Content_Exception('Attempt to add contents of type: ' . get_class($element) . ' into a HTML_Text object.');
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		$text = $this->_raw ? $this->_text : htmlspecialchars($this->_text);
		$renderer->render($text);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
	}
}
?>