<?php
class HTML_Option extends HTML_Entity_Group
{
	public function __construct($value = '', $text = '', $selected = false, $disabled = false)
	{
		$this->_tag = 'option';
		parent::__construct();

		$this->_attributes->register('value', $value);
		$this->_attributes->register('selected', $selected);
		$this->_attributes->register('disabled', $disabled);

		if ($text != '')
			$this->add(new HTML_Text($text));
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
		if (!$content_check || $element instanceof HTML_Text || $element instanceof HTML_Text_Group)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Attempt to add a non HTML_Text class: ' . get_class($element));
	}
}
?>