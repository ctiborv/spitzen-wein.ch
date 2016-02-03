<?php
class Magic_Url_Content_View extends View_Object
{
	protected $_content;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_content = null;

		$this->_attributes->register('url');
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				if ($this->_content !== null)
					$this->_content->render($renderer);
				break;
			default:
				parent::render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
	}

	protected function _handleInput()
	{
		if (isset($_GET['url']))
			$this->url = $_GET['url'];
		else
			throw new Data_Insufficient_Exception('url');
	}

	protected function _resolve()
	{
		// TODO: replace following code with url specific handling code
		$this->_content = new HTML_Div('magic_urls_id', 'magic_urls_class');
		$this->_content->add(new HTML_Text("url = $this->url"));
	}
}
?>