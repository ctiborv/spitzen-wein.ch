<?php
class HTML_OptGroup extends HTML_Option_Group
{
	public function __construct($label = '')
	{
		$this->_tag = 'optgroup';
		parent::__construct();

		$this->_attributes->register('label', $label);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
		$renderer->renderNL();
	}
}
?>