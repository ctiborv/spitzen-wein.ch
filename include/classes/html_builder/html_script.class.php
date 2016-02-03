<?php
class HTML_Script extends HTML_Element implements HTML_Head_Content, HTML_Block, HTML_Inline
{
	protected $_text;

	public function __construct($type = '', $src = null)
	{
		$this->_tag = 'script';
		parent::__construct();

		$this->_text = '';
		$this->_raw = true;

		$this->_attributes->register('charset');
		$this->_attributes->register('type', $type);
		$this->_attributes->register('src', $src);
		$this->_attributes->register('defer');
		$this->_attributes->register('space');
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
		if (!$content_check || $element instanceof HTML_Text)
			$this->_text = $element->text;
		else
			throw new HTML_Content_Exception('Attempt to add a non HTML_Text class: ' . get_class($element));
	}

	protected function renderAttribute($name, $value, Text_Renderer $renderer)
	{
		if ($name == 'space' && $value == 'preserve') return;
		parent::renderAttribute($name, $value, $renderer);
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		$text = $this->_raw ? $this->_text : htmlspecialchars($this->_text);
		$renderer->render($text);
	}	
}
?>