<?php
class HTML_TextArea extends HTML_Entity_Group implements HTML_Inline
{
	public function __construct($name = null)
	{
		$this->_tag = 'textarea';
		parent::__construct();

		$this->_attributes->register('name', $name);
		$this->_attributes->register('rows');
		$this->_attributes->register('cols');
		$this->_attributes->register('disabled');
		$this->_attributes->register('readonly');
		$this->_attributes->register('tabindex');
		$this->_attributes->register('accesskey');

		$this->_events->register('onfocus');
		$this->_events->register('onblur');
		$this->_events->register('onselect');
		$this->_events->register('onchange');
	}

	public function get($name)
	{
		if ($name == 'text' || $name == 'value')
		{
			if (sizeof($this->_elements) == 0)
				return '';
			elseif (sizeof($this->_elements) == 1)
				return $this->_elements[0]->text;
			else
			{
				$text = '';
				foreach ($this->_elements as $element)
					$text .= $this->_elements->text;
				return $text;
			}
		}
		else
			return parent::get($name);
	}

	public function set($name, $value)
	{
		if ($name == 'text' || $name == 'value')
		{
			if (sizeof($this->_elements) == 1)
				$this->_elements[0]->text = $value;
			else
			{
				$this->clear();
				$this->add(new HTML_Text($value));
			}
		}
		else
			parent::set($name, $value);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Text)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Attempt to add a non HTML_Text class: ' . get_class($element));
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->nls = false; // to avoid prefixing the closing tag
		parent::renderEnd($renderer);
	}
}
?>