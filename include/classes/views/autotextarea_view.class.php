<?php
class Autotextarea_View extends View_Object
{
	protected $_textarea;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_textarea = new HTML_TextArea;

		$this->_attributes->register('method', 'post');
		$this->_attributes->register('formid'); // allows detection of data transfer (expecting a hidden input with an appropriate name)
	}

	public function get($name)
	{
		try
		{
			return $this->_textarea->get($name);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			return parent::get($name);
		}
	}

	public function set($name, $value)
	{
		try
		{
			$this->_textarea->set($name, $value);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			parent::set($name, $value);
		}
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				$this->_textarea->render($renderer);
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
	}

	protected function _handleInput()
	{
		if ($this->_textarea->name === null || $this->_textarea->name === '')
			return;

		switch (strtolower($this->method))
		{
			case 'get':
				$source =& $_GET;
				break;
			case 'post':
				$source =& $_POST;
				break;
			case 'both':
			case 'request':
				$source =& $_REQUEST;
				break;
			default:
				throw new Invalid_Argument_Value_Exception('method', $this->method, get_class($this));
		}

		if (!($this->formid === null || array_key_exists($this->formid, $source)))
			return;

		if (array_key_exists($this->_textarea->name, $source))
			$this->_textarea->value = $source[$this->_textarea->name];
		else
			$this->_textarea->value = null;
	}

	protected function _resolve()
	{
	}
}
?>
