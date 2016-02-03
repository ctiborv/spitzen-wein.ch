<?php
abstract class Template_Based_View extends View_Object
{
	protected $_root;
	protected $_index;
	protected $_sub_view_manager;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_root = null;
		$this->_index = null;
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
		$this->_sub_view_manager = new View_Manager;
		if ($this->_view_manager->head !== null)
			$this->_sub_view_manager->assignHead($this->_view_manager->head);
		if ($this->_view_manager->body !== null)
			$this->_sub_view_manager->assignBody($this->_view_manager->body);
		$this->updateHead();
	}	

	protected function _resolve()
	{
		$this->resolveTemplate();
	}

	protected function _handleRedirection()
	{
		parent::_handleRedirection();
		$this->_sub_view_manager->handleRedirections();
	}

	protected function resolveTemplate()
	{
		$this->loadTemplate();
		$this->_sub_view_manager->initialize();
		$this->_sub_view_manager->handleInput();
		$this->updateTemplate();
		$this->cleanupTemplate();
		$this->_sub_view_manager->resolve();
	}

	protected function loadTemplate()
	{
		$builder = new HTML_Custom_Builder($this->_sub_view_manager);
		$this->resolveTemplateSource();
		if ($this->template === null)
			throw new Data_Insufficient_Exception('template');
		$this->_root = Template_Loader::load($this->template, $builder);
		$this->_index = new HTML_Indexer($this->_root, 'eid', false);
	}

	protected function updateTemplate()
	{
		$this->_updateTemplate();
	}

	protected function resolveTemplateSource()
	{
	}
	
	abstract protected function _updateTemplate();

	protected function cleanupTemplate()
	{
		if ($this->_root !== null)
			$this->_root->removeByEId('cleanup');
		if ($this->_index !== null)
			$this->_index = null;
	}

	protected function updateHead()
	{
	}
}
?>