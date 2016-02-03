<?php
class HTML_Base extends HTML_Element implements HTML_Head_Content
{
	public function __construct($href = '')
	{
		$this->_tag = 'base';
		parent::__construct();

		$this->_unpaired = true;

		$this->_attributes->register('id');
		$this->_attributes->register('href', $href);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
	}
}
?>