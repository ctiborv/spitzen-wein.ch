<?php
class Autoselect_View extends View_Object
{
	protected $_select;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_select = new HTML_Select;

		$this->_attributes->register('method', 'post');
		$this->_attributes->register('formid'); // allows detection of data transfer (expecting a hidden input with an appropriate name)
	}

	public function get($name)
	{
		try
		{
			return $this->_select->get($name);
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
			$this->_select->set($name, $value);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			parent::set($name, $value);
		}
	}

	public function select($value)
	{
		$this->_select->select($value);
	}

	public function getSelected()
	{
		return $this->_select->getSelected();
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				$this->_select->render($renderer);
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
		if ($this->content === null)
			throw new Data_Insufficient_Exception('content');

		$content = $this->content->getContents();
		foreach ($content as $elem)
			$this->_select->add($elem);
	}

	protected function _handleInput()
	{
		if ($this->_select->name === null || $this->_select->name === '')
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

		if (array_key_exists($this->_select->name, $source))
			$this->_select->select($source[$this->_select->name]);
	}

	protected function _resolve()
	{
	}
}
?>
