<?php
//TODO: promyslet, zda resolveHRef nelze resit v _resolve misto v _render
class Mailto_View extends View_Object
{
	protected $_a;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_a = new HTML_A;

		$this->_attributes->register('contact');
		$this->_attributes->register('at'); // only relevant if mailto tag has no content; then if null, name of contact is rendered, else email address with given @ replacement is rendered
		$this->_attributes->register('method');
		$this->_attributes->register('qs');
	}

	public function get($name)
	{
		try
		{
			return $this->_a->get($name);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			return parent::get($name);
		}
	}

	public function set($name, $value)
	{
		try
		{
			$this->_a->set($name, $value);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			parent::set($name, $value);
		}
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				$this->resolveHRef();
				$this->_a->render($renderer);
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
	}

	protected function _handleInput()
	{
	}

	protected function _resolve()
	{
	}

	protected function resolveHRef()
	{
		if ($this->contact !== null)
		{
			$contacts = Project_Config::get('contacts');
			if (array_key_exists($this->contact, $contacts))
				$contact = $contacts[$this->contact];
			else
				throw new Template_Invalid_Argument_Exception('contact', $this->contact);

			if (!array_key_exists('email', $contact))
				throw new Config_Exception('contact has no email address defined: ' . $this->contact);
			
			$email = $contact['email'];
			$name = array_key_exists('name', $contact) ? $contact['name'] : '';

			if (is_array($email)) // if contact has multiple email addresses assigned, use the first one
				$email = $email[0];

			$qs = $this->qs === null ? '' : "?$this->qs";

			switch ($this->method)
			{
				case 'js':
					$mailto_str = $this->formatMailtoJS($email, $name, $qs);
					break;
				default:
					$mailto_str = $this->formatMailto($email, $name, $qs);
					break;
			}

			$this->_a->href = $mailto_str;
			if ($this->content !== null)
			{
				$this->_a->clear();
				$this->_a->add($this->content, false);
			}
			else
			{
				$this->_a->clear();
				$content = $this->at === null ? $contact['name'] : str_replace('@', $this->at, $contact['email']);
				$this->_a->add(new HTML_Text($content));
			}
		}
		else
			throw new Data_Insufficient_Exception('contact');
	}

	protected function formatMailto($email, $name = '', $qs = '')
	{
		$result = 'mailto:';
		if ($name === '')
			$result .= str_replace('@', '%40', $email);
		else
			$result .= str_replace(array(' ', '@'), array('%20', '%40'), "$name<$email>");
		if ($qs !== '')
			$result .= $qs;
		return $result;
	}

	protected function formatMailtoJS($email, $name = '', $qs = '')
	{
		$enc = $this->toCharCodes($name === '' ? $email : "$name<$email>");
		$result = "javascript:location.href='mailto:'+String.fromCharCode($enc)";
		if ($qs !== '')
			$result .= "+'" . str_replace("'", "\\'", $qs) . "'";
		return $result;
	}

	protected function toCharCodes($str)
	{
		$len = strlen($str);
		$ar = array();
		for ($i = 0; $i < $len; ++$i)
			$ar[] = ord($str[$i]);
		return implode(',', $ar);
	}
}
?>