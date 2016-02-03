<?php
class HTML_Indexer
{
	protected $_index;
	protected $_property;
	protected $_unique;
	protected $_require_unique;

	public static $defaults = array('property' => 'id', 'require_unique' => true);

	public function __construct(HTML_Element $root, $property = null, $require_unique = null)
	{
		$this->_property = null;
		$this->_require_unique = null;
		$this->rebuild($root, $property, $require_unique);
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'property':
				return $this->_property;
			case 'unique':
				return $this->_unique;
			default:
				return $this->search($name);
		}
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	public function rebuild(HTML_Element $root, $property = null, $require_unique = null)
	{
		if ($property === null)
		{
			if ($this->_property === null)
				$this->_property = self::$defaults['property'];
		}
		else
			$this->_property = $property;

		if ($require_unique === null)
		{
			if ($this->_require_unique === null)
				$this->_require_unique = self::$defaults['require_unique'];
		}
		else
			$this->_require_unique = $require_unique;

		$this->_index = array();
		$this->_unique = true;
		$this->indexElement($root);
	}

	protected function indexElement(HTML_Element $element)
	{
		try
		{
			$property = $this->_property;
			$pval = $element->$property;
			if ($pval !== null)
			{
				if (array_key_exists($pval, $this->_index))
				{
					$this->_unique = false;
					if ($this->_require_unique) throw new HTML_Duplicate_Attribute_Exception ($property, $pval);
					$this->_index[$pval][] = $element;
				}
				else
					$this->_index[$pval] = array($element);
			}
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
		}

		if ($element instanceof HTML_Container)
		{
			$sub_elements = $element->getContents();
			foreach ($sub_elements as $sub_element)
				$this->indexElement($sub_element);
		}
	}

	public function search($value)
	{
		if ($this->_require_unique)
		{
			if (array_key_exists($value, $this->_index))
				return $this->_index[$value][0];
			else
				return null;
		}
		else
		{
			if (array_key_exists($value, $this->_index))
			{
				return $this->_index[$value];
			}
			else
				return array();
		}
	}
}
?>