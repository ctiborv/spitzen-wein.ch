<?php
abstract class HTML_Option_Group extends HTML_Entity_Group
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get($name)
	{
		if ($name == 'value')
			return $this->getSelected();
		else
			return parent::get($name);
	}

	public function set($name, $value)
	{
		if ($name == 'value')
			$this->select($value);
		else
			parent::set($name, $value);
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Option)
			parent::add($element, $content_check);
		else
			throw new HTML_Content_Exception('Cannot add an instance of class: ' . get_class($element));
	}

	public function addOption($value, $text, $selected = false, $disabled = false, $raw = false)
	{
		$option = new HTML_Option($value, $text, $selected, $disabled);
		if ($raw) $option->raw = true;
		parent::add($option);
	}

	public function select($value)
	{
		if (is_array($value))
		{
			$fvalue = array_flip($value);
			foreach ($this->_elements as &$option)
				if ($option instanceof HTML_Option_Group)
					$option->select($value);
				else
					$option->selected = array_key_exists($option->value, $fvalue);
		}
		else
			foreach ($this->_elements as &$option)
				if ($option instanceof HTML_Option_Group)
					$option->select($value);
				else
					$option->selected = $option->value == $value;
	}

	public function getSelected()
	{
		$result = array();
		$this->_getSelected($result);
		return $result;
	}

	public function _getSelected(array &$result)
	{
		foreach ($this->_elements as &$option)
		{
			if ($option instanceof HTML_Option_Group)
				$option->_getSelected($result);
			elseif ($option->selected)
				$result[] = $option->value;
		}
	}

	public function disable($value)
	{
		if (is_array($value))
		{
			$value = array_flip($value);
			foreach ($this->_elements as &$option)
				if (array_key_exists($option->value, $value)) $option->disabled = true;
		}
		else
			foreach ($this->_elements as &$option)
				if ($option->value == $value) $option->disabled = true;
	}

	public function enable($value)
	{
		if (is_array($value))
		{
			$value = array_flip($value);
			foreach ($this->_elements as &$option)
				if (array_key_exists($option->value, $value)) $option->disabled = false;
		}
		else
			foreach ($this->_elements as &$option)
				if ($option->value == $value) $option->disabled = false;
	}
}
?>