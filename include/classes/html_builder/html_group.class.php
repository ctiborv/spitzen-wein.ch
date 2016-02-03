<?php
// base class for "invisible" groups
class HTML_Group extends HTML_Element_Group
{
	public function __construct($id = null, $tag = 'group')
	{
		$this->_tag = $tag;
		parent::__construct();

		$this->_attributes->register('id', $id);
		$this->_attributes->register('specification'); // special attribute for further group content specification
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		foreach ($this->_elements as $element)
			$element->render($renderer);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
	}
}
?>