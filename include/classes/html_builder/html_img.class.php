<?php
class HTML_Img extends HTML_Entity implements HTML_Inline
{
	public function __construct($src = null, $alt = null)
	{
		$this->_tag = 'img';
		parent::__construct();

		$this->_unpaired = true;

		$this->_attributes->register('src', $src);
		$this->_attributes->register('alt', $alt);
		$this->_attributes->register('longdesc');
		$this->_attributes->register('name');
		$this->_attributes->register('height');
		$this->_attributes->register('width');
		$this->_attributes->register('usemap');
		$this->_attributes->register('ismap');
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}
}
?>