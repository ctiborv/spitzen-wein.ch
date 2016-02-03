<?php

abstract class Page_Item
{
	protected $redirection;

	function __construct()
	{
		$this->redirection = null;
	}

	function __get($name)
	{
		switch ($name)
		{
			case 'Redirection':
				return $this->redirection;
			default: throw new Exception('Došlo k pokusu o čtení neexistující vlastnosti objektu třídy ' . __CLASS__);
		}
	}

	abstract protected function _getHTML();
	abstract function handleInput($get, $post);

	protected function apply_prefix($text, $prefix)
	{
		return preg_replace("/^(.+)$/m", "$prefix\\1", $text);
	}

	function getHTML($prefix = '')
	{
		return $this->apply_prefix($this->_getHTML(), $prefix);
	}

}

?>