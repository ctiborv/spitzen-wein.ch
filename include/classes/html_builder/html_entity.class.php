<?php
abstract class HTML_Entity extends HTML_Element
{
	protected $_events;

	public function __construct($id = null, $class_name = null, $style = null, $title = null)
	{
		parent::__construct();

		$this->_attributes->register('id', $id);
		$this->_attributes->register('class', $class_name);
		$this->_attributes->register('style', $style);
		$this->_attributes->register('title', $title);

		// originally intended to use Var_Pool, but Typed_Var_Pool wastes less memory
		// $this->_tag has to be defined at this point
		$this->_events = new Typed_Var_Pool($this->_tag . '#e');

		$this->_events->register('onclick');
		$this->_events->register('ondblclick');
		$this->_events->register('onmousedown');
		$this->_events->register('onmouseup');
		$this->_events->register('onmouseover');
		$this->_events->register('onmousemove');
		$this->_events->register('onmouseout');
		$this->_events->register('onkeypress');
		$this->_events->register('onkeydown');
		$this->_events->register('onkeyup');
	}

	public function __clone()
	{
		parent::__clone();
		$this->_events = clone $this->_events;
	}

	public function get($name)
	{
		try
		{
			$value = parent::get($name);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			try
			{
				$value = $this->_events->get($name);
			}
			catch (No_Such_Variable_Exception $e)
			{
				throw new HTML_No_Such_Element_Attribute_Exception(get_class($this) . '::' . $name);
			}
		}

		return $value;
	}

	public function set($name, $value)
	{
		try
		{
			parent::set($name, $value);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			try
			{
				$this->_events->set($name, $value);
			}
			catch (No_Such_Variable_Exception $f)
			{
				throw new HTML_No_Such_Element_Attribute_Exception(get_class($this) . '::' . $name);
			}
		}
	}

	public function addClass($class_name)
	{
		$class = $this->_attributes->class;
		if ($class == '')
			$this->_attributes->class = $class_name;
		else
		{
			$classes = explode(' ', $class);
			foreach ($classes as $item)
				if ($class_name == $item) return;
			$classes[] = $class_name;
			$this->_attributes->class = implode(' ', $classes);
		}
	}

	public function remClass($class_name)
	{
		$classes = explode(' ', $this->_attributes->class);
		$rclasses = array();
		foreach ($classes as $item)
			if ($class_name != $item) $rclasses[] = $item;
		$this->_attributes->class = implode(' ', $rclasses);
	}

	protected function renderAttributes(Text_Renderer $renderer)
	{
		$html = parent::renderAttributes($renderer);

		// todo: sort the array by predefined order
		$events = $this->_events->toArray();

		foreach ($events as $key => $val)
		{
			if ($val !== null)
				$this->renderEventAttribute($key, $val, $renderer);
		}
	}

	protected function renderEventAttribute($name, $value, Text_Renderer $renderer)
	{
		$val = (string)$value;
		$fvalue = $this->_raw ? $val : str_replace('"', '\"', $val);
		$renderer->render(" $name=\"$fvalue\"");	
	}
}
?>