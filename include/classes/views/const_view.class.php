<?php
class Const_View extends View_Object
{
	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_attributes->register('name');
		$this->_attributes->register('raw');
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':				
				$this->renderConstant($renderer);
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
	}

	protected function _resolve()
	{
	}

	protected function renderConstant(Text_Renderer $renderer)
	{
		$constants = Project_Config::get('constants');
		if (array_key_exists($this->name, $constants))
			$constant = $constants[$this->name];
		else
			throw new Template_Invalid_Argument_Exception('name', $this->name);

		$renderer->renderText($this->raw ? $constant : htmlspecialchars($constant));
	}
}
?>