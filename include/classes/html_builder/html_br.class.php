<?php
class HTML_Br extends HTML_Entity implements HTML_Inline
{
	public function __construct()
	{
		$this->_tag = 'br';
		parent::__construct();

		$this->_unpaired = true;
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}
}
?>