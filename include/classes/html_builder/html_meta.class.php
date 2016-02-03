<?php
class HTML_Meta extends HTML_Element implements HTML_Head_Content
{
	public function __construct($name = null,  $content = '', $http_equiv = null)
	{
		$this->_tag = 'meta';
		parent::__construct();

		$this->_unpaired = true;

		$this->_attributes->register('id');
		$this->_attributes->register('http-equiv', $http_equiv);
		$this->_attributes->register('name', $name);
		$this->_attributes->register('content', $content);
		$this->_attributes->register('scheme');
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
	}
}
?>