<?php
class HTML_Link extends HTML_Entity implements HTML_Head_Content
{
	public function __construct($rel = null, $type = null, $href = null)
	{
		$this->_tag = 'link';
		parent::__construct();

		$this->_unpaired = true;

		$this->_attributes->register('charset');
		$this->_attributes->register('href', $href);
		$this->_attributes->register('hreflang');
		$this->_attributes->register('type', $type);
		$this->_attributes->register('rel', $rel);
		$this->_attributes->register('rev');
		$this->_attributes->register('media');
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
	}
}
?>