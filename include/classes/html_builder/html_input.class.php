<?php
class HTML_Input extends HTML_Entity implements HTML_Inline
{
	const TEXT = 'text';
	const PASSWORD = 'password';
	const CHECKBOX = 'checkbox';
	const RADIO = 'radio';
	const SUBMIT = 'submit';
	const RESET = 'reset';
	const FILE = 'file';
	const HIDDEN = 'hidden';
	const IMAGE = 'image';
	const BUTTON = 'button';

	protected static $_types = array
	(
		self::TEXT => 'text',
		self::PASSWORD => 'password',
		self::CHECKBOX => 'checkbox',
		self::RADIO => 'radio',
		self::SUBMIT => 'submit',
		self::RESET => 'reset',
		self::FILE => 'file',
		self::HIDDEN => 'hidden',
		self::IMAGE => 'image',
		self::BUTTON => 'button'
	);

	public function __construct($type = self::TEXT, $name = null, $value = null)
	{
		$this->_tag = 'input';
		parent::__construct();

		$this->_unpaired = true;

		if (!array_key_exists($type, self::$_types))
			throw new HTML_Input_Exception('Invalid input type');

		$this->_attributes->register('type', self::$_types[$type]);
		$this->_attributes->register('name', $name);
		$this->_attributes->register('value', $value);
		$this->_attributes->register('checked');
		$this->_attributes->register('disabled');
		$this->_attributes->register('readonly');
		$this->_attributes->register('size');
		$this->_attributes->register('maxlength');
		$this->_attributes->register('src');
		$this->_attributes->register('alt');
		$this->_attributes->register('usemap');
		$this->_attributes->register('tabindex');
		$this->_attributes->register('accesskey');

		$this->_events->register('onfocus');
		$this->_events->register('onblur');
		$this->_events->register('onselect');
		$this->_events->register('onchange');
		$this->_events->register('accept');
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}
}
?>