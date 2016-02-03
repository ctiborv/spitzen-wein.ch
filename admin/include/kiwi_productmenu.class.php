<?php
require_once 'kiwi_datarow.class.php';

class Kiwi_ProductMenu implements IteratorAggregate
{
	protected $_contents;
	protected $_ordered_list;

	public function __construct()
	{
		$this->_contents = null;
		$this->_ordered_list = null;
		$this->loadProductMenu();
	}

	public function getIterator()
	{
		return new ArrayIterator($this->_ordered_list);
	}

	protected function loadProductMenu()
	{
		if ($this->_contents === null)
		{
			$this->_contents = array();
			$result = mysql_query("SELECT ID, Name, URL, PageTitle, Subgroup, Parent FROM eshop ORDER BY Priority");
			while ($row = mysql_fetch_assoc($result))
				$this->_contents[$row['ID']] = array('data' => new Kiwi_DataRow($row), 'depth' => null, 'contains' => array());

			foreach ($this->_contents as $id => $item)
			{
				$parent = $item['data']->Parent;
				if ($parent != 0)
					$this->_contents[$parent]['contains'][] = $id;
			}

			foreach ($this->_contents as $id => $item)
				$this->countDepth($id);

			$this->countOrder();
		}
	}

	protected function countDepth($id) // recursive; can end up in endless loop if menu has a cycle
	{
		if ($this->_contents[$id]['depth'] === null)
		{
			$parent = $this->_contents[$id]['data']->Parent;
			if (array_key_exists($parent, $this->_contents))
			{
				$this->countDepth($parent);
				$this->_contents[$id]['depth'] = $this->_contents[$parent]['depth'] + 1;
			}
			else
				$this->_contents[$id]['depth'] = 0;
		}
	}

	protected function countOrder()
	{
		$this->_ordered_list = array();
		$this->addToOrderedList(1);
	}

	protected function addToOrderedList($parent)
	{
		foreach ($this->_contents[$parent]['contains'] as $child)
		{
			$this->_ordered_list[] =& $this->_contents[$child];
			$this->addToOrderedList($child);
		}
	}
}
?>
