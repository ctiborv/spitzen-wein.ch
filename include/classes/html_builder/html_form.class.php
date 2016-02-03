<?php
class HTML_Form extends HTML_Entity_Group implements HTML_Block
{
	const GET = 'get';
	const POST = 'post';

	protected static $_methods = array
	(
		self::GET => 'get',
		self::POST => 'post'
	);

	public function __construct($action = '', $method = self::POST)
	{
		$this->_tag = 'form';
		parent::__construct();

		if (!array_key_exists($method, self::$_methods))
			throw new HTML_Form_Exception('Invalid form method');

		$this->_attributes->register('action', $action);
		$this->_attributes->register('method', self::$_methods[$method]);
		$this->_attributes->register('enctype');
		$this->_attributes->register('accept');
		$this->_attributes->register('accept-charset');

		$this->_events->register('onsubmit');
		$this->_events->register('onreset');
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if (!$content_check || $element instanceof HTML_Block)
		{
			if ($content_check && $element instanceof HTML_Form)
				throw new HTML_Content_Exception('Attempt of "form" tags nesting');
			else
				parent::add($element, $content_check);
		}
		else
			throw new HTML_Content_Exception('Class ' . get_class($element) . ' doesn\'t implement the HTML_Block interface.');
	}

	protected function renderAttribute($name, $value, Text_Renderer $renderer)
	{
		if ($name == 'enctype' && $value == 'application/x-www-form-urlencoded') return;
		parent::renderAttribute($name, $value, $renderer);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		$renderer->renderNL();
		parent::renderEnd($renderer);
		$renderer->renderNL();
	}
}
?>