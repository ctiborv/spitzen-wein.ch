<?php
class Autoinput_View extends View_Object
{
	protected $_input;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_input = new HTML_Input;

		$this->_attributes->register('method', 'post');
		$this->_attributes->register('formid'); // allows detection of data transfer (expecting a hidden input with an appropriate name)
	}

	public function get($name)
	{
		try
		{
			return $this->_input->get($name);
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
			$this->_input->set($name, $value);
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
				$this->_input->render($renderer);
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
		if ($this->_input->name === null || $this->_input->name === '')
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

		switch (strtolower($this->_input->type))
		{
			case 'text':
			case 'password':
			case 'hidden':
			case 'button':
			case 'submit':
				if (array_key_exists($this->_input->name, $source))
					$this->_input->value = $source[$this->_input->name];
				else
					$this->_input->value = null;
				break;
			case 'checkbox':
				if ($this->_input->value === null || $this->_input->value === '')
					break;
				if (substr($this->_input->name, -2) == '[]')
				{
					$name = substr($this->_input->name, 0, -2);
					if (array_key_exists($name, $source))
						$this->_input->checked = in_array($this->_input->value, $source[$name]);
					else
						$this->_input->checked = null;
				}
				else
				{
					if (array_key_exists($this->_input->name, $source))
						$this->_input->checked = $source[$this->_input->name] === $this->_input->value;
					else
						$this->_input->checked = null;
				}
				break;
			case 'radio':
				if ($this->_input->value === null || $this->_input->value === '')
					break;
				if (array_key_exists($this->_input->name, $source))
					$this->_input->checked = $source[$this->_input->name] === $this->_input->value;
				else
					$this->_input->checked = null;
				break;
			case 'file':
			case 'image':
			case 'reset':
				break;
			default:
				throw new Invalid_Argument_Value_Exception('type', $this->_input->type, 'HTML_Input');
		};
	}

	protected function _resolve()
	{
	}
}
?>
