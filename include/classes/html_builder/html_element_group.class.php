<?php
class HTML_Element_Group extends HTML_Element implements HTML_Container
{
	protected $_elements;

	public function __construct()
	{
		parent::__construct();
		$this->_elements = array();
	}

	public function __clone()
	{
		foreach ($this->_elements as &$element)
			$element = clone $element;
		parent::__clone();
	}

	public function getAt($index)
	{
		if (0 <= $index && $index < $this->count())
			return $this->_elements[$index];
		else
			throw new HTML_Index_Out_Of_Range_Exception($index, 0, $this->count());
	}

	public function getContents()
	{
		return $this->_elements;
	}

	public function isEmpty()
	{
		return empty($this->_elements);
	}

	public function count()
	{
		return sizeof($this->_elements);
	}

	public function countActive()
	{
		$count_active = 0;
		foreach ($this->_elements as $element)
			if ($element->active) $count_active++;

		return $count_active;
	}

	public function clear()
	{
		$this->_elements = array();
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		$this->_elements[] = $element;
	}

	public function addElements(Array $elements)
	{
		foreach ($elements as &$element)
			$this->add($element);
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		$renderer->descend();
		foreach ($this->_elements as $element)
			$element->render($renderer);
		$renderer->ascend();
	}

	public function removeByEId($eid)
	{
		foreach ($this->_elements as $ek => &$element)
		{
			try
			{
				if ($element->eid == $eid)
				{
					unset($this->_elements[$ek]);
					return true;
				}
				elseif ($element instanceof HTML_Container)
				{
					if ($element->removeByEId($eid))
						return true;
				}
			}
			catch (HTML_No_Such_Element_Attribute_Exception $e)
			{
			}
		}
		return false;
	}

	public function activateByEId($eid)
	{
		foreach ($this->_elements as $ek => &$element)
		{
			try
			{
				if ($element->eid == $eid)
				{
					$element->active = true;
					return true;
				}
				elseif ($element instanceof HTML_Container)
				{
					if ($element->activateByEId($eid))
						return true;
				}
			}
			catch (HTML_No_Such_Element_Attribute_Exception $e)
			{
			}
		}
		return false;
	}

	public function deactivateByEId($eid)
	{
		foreach ($this->_elements as $ek => &$element)
		{
			try
			{
				if ($element->eid == $eid)
				{
					$element->active = false;
					return true;
				}
				elseif ($element instanceof HTML_Container)
				{
					if ($element->deactivateByEId($eid))
						return true;
				}
			}
			catch (HTML_No_Such_Element_Attribute_Exception $e)
			{
			}
		}
		return false;
	}

	public function &getElementsByEId($eid)
	{
		return $this->getElementsBy('eid', $eid);
	}

	public function &getElementById($id)
	{
		if ($eref =& parent::getElementById($id))
			return $eref;

		foreach ($this->_elements as &$element)
			if ($eref =& $element->getElementById($id))
				break;

		return $eref;
	}

	public function getElementsBy($property, $value)
	{
		$elems_a = parent::getElementsBy($property, $value);
		foreach ($this->_elements as $element)
		{			
			$sub_elems_a = $element->getElementsBy($property, $value);
			foreach ($sub_elems_a as $match)
				$elems_a[] = $match;
		}
		return $elems_a;
	}
}
?>