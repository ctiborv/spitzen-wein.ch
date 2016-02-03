<?php
abstract class HTML_Element implements Renderable
{
	protected $_active;
	protected $_raw;
	protected $_comment;
	protected $_if;
	protected $_render;

	protected $_tag;
	protected $_unpaired;
	protected $_attributes;

	protected static $attributes_order = array
	(
		'type',
		'href',
		'hreflang',
		'rel',
		'rev',
		'id',
		'name',
		'value',
		'http-equiv',
		'content',
		'lang',
		'src',
		'title',
		'style',
		'class',
		'tabindex'
	);

	public function __construct()
	{
		$this->_active = true;
		$this->_raw = false;
		$this->_comment = '';
		$this->_if = null;
		$this->_render = null;

		$this->_unpaired = false;

		// originally intended to use Var_Pool, but Typed_Var_Pool wastes less memory
		// $this->_tag has to be defined at this point
		$this->_attributes = new Typed_Var_Pool($this->_tag . '#a');

		$this->_attributes->register('lang');
		$this->_attributes->register('dir');

		$this->_attributes->register('eid'); // pomocnı atribut, jen se v renderovaném vısledku neobjevuje
	}

	public function __clone()
	{
		$this->_attributes = clone $this->_attributes;
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'active':
				return $this->_active;
			case 'raw':
				return $this->_raw;
			case 'comment':
				return $this->_comment;
			case 'if':
				return $this->_if;
			case 'render':
				return $this->_render;
			case 'tag':
				return $this->_tag;
			case 'unpaired':
				return $this->_unpaired;
			default:
				try
				{
					$value = $this->_attributes->get($name);
				}
				catch (No_Such_Variable_Exception $e)
				{
					throw new HTML_No_Such_Element_Attribute_Exception(get_class($this) . '::' . $name);
				}
				return $value;
		}
	}

	public function set($name, $value)
	{
		switch ($name)
		{
			case 'active':
				$this->_active = (bool)$value;
				break;
			case 'raw':
				$this->_raw = (bool)$value;
				break;
			case 'comment':
				$this->_comment = $value;
				break;
			case 'if':
				$this->_if = $value;
				break;
			case 'render':
				$this->_render = $value;
				break;
			default:
				try
				{
					$this->_attributes->set($name, $value);
				}
				catch (No_Such_Variable_Exception $e)
				{
					throw new HTML_No_Such_Element_Attribute_Exception(get_class($this) . '::' . $name);
				}
		}
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	public function addComments($comments)
	{
		if (is_array($this->_comment))
		{
			if (is_array($comments))
				$this->_comment = array_merge($this->_comment, $comments);
			else
				$this->_comment[] = $comments;
		}
		else
		{
			if (is_array($comments))
				$this->_comment = array_merge(array($this->_comment), $comments);
			else
				$this->_comment = array($this->_comment, $comments);
		}
	}

	protected function renderAttributes(Text_Renderer $renderer)
	{
		$attribs = $this->_attributes->toArray();

		foreach (self::$attributes_order as $attr)
			if (array_key_exists($attr, $attribs))
			{
				$val = $attribs[$attr];
				// "content" attribute can contain object
				// which we don't want to render as regular attribute
				if ($val !== null && !is_object($val))
					$this->renderAttribute($attr, $val, $renderer);
				unset($attribs[$attr]);
			}

		foreach ($attribs as $key => $val)
			if ($val !== null && $key != 'eid')
				$this->renderAttribute($key, $val, $renderer);
	}

	protected function renderAttribute($name, $value, Text_Renderer $renderer)
	{
		$to_render = null;
		if (is_bool($value))
		{
			if ($value)
				$to_render = " $name=\"$name\"";
		}
		else
		{
			$fvalue = $this->_raw ? $value : htmlspecialchars($value);
			$to_render = " $name=\"$fvalue\"";
		}

		if ($to_render !== null)
		{
			switch ($this->_render)
			{
				case null:
				case '':
				case 'default':
					break;
				case 'js':
				case 'javascript':
					$to_render = ',' . $this->toCharCodes($to_render);
					break;
				default:
					throw new HTML_Invalid_Rendering_Mode_Exception($this->_render);
			}
			$renderer->render($to_render);
		}
	}

	protected function renderComment(Text_Renderer $renderer)
	{
		if (is_array($this->_comment))
		{
			foreach ($this->_comment as $comment_item)
				$this->renderSingleComment($comment_item, $renderer);
		}
		else
			$this->renderSingleComment($this->_comment, $renderer);
	}

	private function renderSingleComment($comment, Text_Renderer $renderer)
	{
		if ($comment == '' || $comment == "\n")
		{
			$renderer->render($comment);
			return;
		}

		$comment = $this->_raw ? $comment : str_replace('-->', '~~>', $comment);
		if (strpos($comment, "\n") !== false)
		{
			$comment_a = explode("\n", $comment);
			$renderer->render('<!--');
			$renderer->descend();
			foreach ($comment_a as $comment_line)
				$renderer->renderNL($comment_line);
			$renderer->ascend();
			$renderer->renderNL("-->\n");
		}
		else
			$renderer->render("<!-- $comment -->");
	}

	protected function renderINL(Text_Renderer $renderer)
	{
		$renderer->renderNL();
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
		if ($this->_if !== null)
			$renderer->render("<!--[if $this->_if]>");

		$to_render = "<$this->_tag";
		switch ($this->_render)
		{
			case null:
			case '':
			case 'default':
				break;
			case 'js':
			case 'javascript':
				$to_render = '<script type="text/javascript">document.write(String.fromCharCode(' . $this->toCharCodes($to_render);
				break;
			default:
				throw new HTML_Invalid_Rendering_Mode_Exception($this->_render);		
		}
		$renderer->render($to_render);
		$this->renderAttributes($renderer);
		if (!$this->_unpaired)
			$to_render = '>';
		else
			$to_render = ' />';
			
		switch ($this->_render)
		{
			case null:
			case '':
			case 'default':
				break;
			case 'js':
			case 'javascript':
				$to_render = ',' . $this->toCharCodes($to_render) . '));</script>';
				break;
			default:
				throw new HTML_Invalid_Rendering_Mode_Exception($this->_render);		
		}
		$renderer->render($to_render);

		if ($this->_unpaired && $this->_if !== null)
			$renderer->render('<![endif]-->');
	}

	protected function renderContent(Text_Renderer $renderer)
	{
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
		if (!$this->_unpaired)
		{
			$to_render = "</$this->_tag>";
			switch ($this->_render)
			{
				case null:
				case '':
				case 'default':
					break;
				case 'js':
				case 'javascript':
					$to_render = '<script type="text/javascript">document.write(String.fromCharCode(' . $this->toCharCodes($to_render) . '));</script>';
					break;
				default:
					throw new HTML_Invalid_Rendering_Mode_Exception($this->_render);		
			}
			$renderer->render($to_render);

			if ($this->_if !== null)
				$renderer->render('<![endif]-->');			
		}
	}

	public function render(Text_Renderer $renderer)
	{
		if ($this->_active)
		{
			$this->renderINL($renderer);
			$this->renderComment($renderer);
			$this->renderINL($renderer);
			$this->renderBegin($renderer);
			if (!$this->_unpaired)
				$this->renderContent($renderer);
			$this->renderEnd($renderer);
		}
	}

	public function &getElementById($id)
	{
		$eref = null;
		try
		{
			if ($this->_attributes->id == $id)
				$eref =& $this;
		}
		catch (No_Such_Variable_Exception $e)
		{
		}

		return $eref;
	}

	public function getElementsBy($property, $value)
	{
		$elems_a = array();
		try
		{
			if ($this->__get($property) == $value)
				$elems_a[] = &$this;
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
		}
		return $elems_a;
	}

	public function getElementsByName($name)
	{
		return $this->getElementsBy('name', $name);
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