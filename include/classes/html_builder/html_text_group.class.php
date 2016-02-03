<?php
class HTML_Text_Group extends HTML_Entity_Group implements HTML_Inline
{
	public function __construct($id = null)
	{
		parent::__construct($id);
	}

	public function get($name)
	{
		if ($name == 'text')
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
		if ($name == 'text')
		{
			$this->clear();
			if ($value instanceof HTML_Text || $value instanceof HTML_HE)
				$this->add($value);
			elseif (is_string($value))
				$this->add(new HTML_Text($value));
			else throw new HTML_Content_Exception('Bad value type');
		}
		else
			parent::set($name, $value);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Text || $element instanceof HTML_HE)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Attempt to add a non HTML_Text / HTML_HE class: ' . get_class($element));
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		foreach ($this->_elements as $element)
			$element->render($renderer);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
	}
}
?>