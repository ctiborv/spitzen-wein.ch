<?php
define ('DEFAULT_TEXT_RENDERER_PREFIX', "\t");
define ('DEFAULT_TEXT_RENDERER_NEWLINE', "\n");

class Text_Renderer
{
	protected $_text;
	protected $_prefix;
	protected $_level;

	protected $_nls; // new line status
	protected $_newline; // new line string

	public function __construct($prefix = DEFAULT_TEXT_RENDERER_PREFIX, $newline = DEFAULT_TEXT_RENDERER_NEWLINE)
	{
		$this->_text = '';
		$this->_prefix = $prefix;
		$this->_level = 0;
		$this->_nls = true;
		if ($newline != "\n" && $newline != "\r\n")
			throw new Invalid_Argument_Type_Exception('newline', $newline, __CLASS__);
		$this->_newline = $newline;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'text':
				return $this->_text;
			case 'prefix':
				return $this->_prefix;
			case 'level':
				return $this->_level;
			case 'nls':
				return $this->_nls;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'text':
				throw new Readonly_Variable_Exception($name, __CLASS__);
			case 'prefix':
				$this->_prefix = $value;
				break;
			case 'level':
				if (((int)$value . '') == ($value . '') && $value >= 0)
					$this->_level = $value;
				else
					throw new Invalid_Argument_Type_Exception($name, $value, __CLASS__);
				break;
			case 'nls':
				$this->_nls = (bool)$value;
				break;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	public function clear()
	{
		$this->_text = '';
		$this->_level = 0;
		$this->_nls = true;
	}

	protected function renderPrefix()
	{
		$level = $this->level;
		while ($level-- > 0)
			$this->_text .= $this->_prefix;
	}

	public function render($data)
	{
		if ($data instanceof Renderable)
			$data->render($this);
		else
			$this->renderText($data);
	}

	public function renderText($text)
	{
		if ($text == '') return;

		if ($text == "\n" || $text == "\r\n")
		{
			$this->_text .= $this->_newline;
			$this->_nls = true;
			return;
		}

		if ($this->_nls)
			$this->renderPrefix();

		if ($this->_newline == "\r\n")
		{
			$text = mb_ereg_replace("([^\r])\n", "\\1\r\n", $text, 'p');
			if (substr($text, 0, 1) == "\n")
				$text = "\r" . $text;
		}
		else
			$text = mb_ereg_replace("\r\n", "\n", $text, 'p');

		$this->_text .= $text;
		$this->_nls = substr($text, -1) == "\n";
	}

	public function renderNL($text = null)
	{
		if (!$this->_nls)
		{
			$this->_text .= $this->_newline;
			$this->_nls = true;
		}
		if ($text !== null) $this->renderText($text);
	}

	public function descend($levels = 1)
	{
		if (((int)$levels . '') == ($levels . '') && $levels >= 0)
			$this->level += $levels;
		else
			throw new Invalid_Argument_Type_Exception('levels', $levels, __CLASS__);
	}

	public function ascend($levels = 1)
	{
		if (((int)$levels . '') == ($levels . '') && $levels >= 0)
		{
			$this->level -= $levels;
			if ($this->level < 0)
				$this->level = 0;
		}
		else
			throw new Invalid_Argument_Type_Exception('levels', $levels, __CLASS__);
	}
}
?>