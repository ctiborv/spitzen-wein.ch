<?php
class HTML_Style extends HTML_Element implements HTML_Head_Content
{
	protected $_text;

	public function __construct($type = null)
	{
		$this->_tag = 'style';
		parent::__construct();

		$this->_attributes->register('id');
		$this->_attributes->register('type', $type);
		$this->_attributes->register('media');
		$this->_attributes->register('title');
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

	protected function renderContent(Text_Renderer $renderer)
	{
		$text = $this->_raw ? $this->_text : htmlspecialchars($this->_text);
		$renderer->render($text);
	}
}
?>