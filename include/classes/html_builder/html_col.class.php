<?php
class HTML_Col extends HTML_Entity implements HTML_Table_Content
{
	public function __construct($id = null, $class_name = null)
	{
		$this->_tag = 'col';
		parent::__construct();

		$this->_unpaired = true;

		$this->_attributes->register('span');
		$this->_attributes->register('width');
		$this->_attributes->register('align');
		$this->_attributes->register('char');
		$this->_attributes->register('charoff');
		$this->_attributes->register('valign');

		if ($id !== null) $this->id = $id;
		if ($class_name !== null) $this->class = $class_name;
	}
}
?>