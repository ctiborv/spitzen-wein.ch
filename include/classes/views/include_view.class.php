<?php
/*
 * NOTE: When using tag <include> in template files, mind that referencing via "vid" attribute will work only in the scope
 *       of the content of the included template file.
 *       I.e. you cannot reference to the view objects defined outside of the included template file.
 */

/*
 * NOTE: Unlike Template_Based_View descendants, the subviews defined in the included template handle the input at the same
 *       time as the Include_View object. In case of Template_Based_View, subviews typically handle the input just before 
 *       Template_Based_View object is rendered.
 *       Cause for this is, that Template_Based_View descendants may not know which template files they will want to load
 *       before the input is handled. In case of Include_View object, this is known prior to any input handling.
 */

class Include_View extends View_Object
{
	protected $_root;
	protected $_sub_view_manager;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_root = null;
		$this->_sub_view_manager = null;

		$this->_attributes->register('template');
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				$this->_root->render($renderer);
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
		if ($this->template === null)
			throw new Data_Insufficient_Exception('template');
		$this->_sub_view_manager = new View_Manager;
		if ($this->_view_manager->head !== null)
			$this->_sub_view_manager->assignHead($this->_view_manager->head);
		if ($this->_view_manager->body !== null)
			$this->_sub_view_manager->assignBody($this->_view_manager->body);
		$builder = new HTML_Custom_Builder($this->_sub_view_manager);
		$this->_root = Template_Loader::load($this->template, $builder);
		$this->_sub_view_manager->initialize();
	}

	protected function _handleInput()
	{
		$this->_sub_view_manager->handleInput();
	}

	protected function _resolve()
	{
		$this->_sub_view_manager->resolve();
	}

	protected function _handleRedirection()
	{
		parent::_handleRedirection();
		$this->_sub_view_manager->handleRedirections();
	}
}
?>