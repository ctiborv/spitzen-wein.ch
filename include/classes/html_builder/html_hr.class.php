<?php
class HTML_HR extends HTML_Entity implements HTML_Block
{
	public function __construct()
	{
		$this->_tag = 'hr';
		parent::__construct();

		$this->_unpaired = true;
	}
}
?>