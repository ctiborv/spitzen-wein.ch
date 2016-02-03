<?php
class Empty_View extends View_Object
{
	// TODO: add class variables

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		// TODO: add class variables initialization code

		// TODO: register view attributes
		// $this->_attributes->register('attribute_name'/*, 'default_value'*/);
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			// TODO: add view cases 
			case 'default':
				// TODO: add the rendering code 
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
		// TODO: add initialization code, making rendering of the object possible
		// NOTE: this method is called after the tag attributes are set but before external input is handled
		// NOTE: it is also possible to add communication between view objects here, but be careful about cycles
	}

	protected function _handleInput()
	{
		// TODO: add the low level input handling code, such as loading of $_GET and $_POST content
	}

	protected function _resolve()
	{
		// TODO: add the high level input handling code and other code
	}
}
?>