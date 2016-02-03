<?php
class Data_Insufficient_Exception extends Exception
{
	protected $_resource;

	public function __construct($resource)
	{
		$this->_resource = $resource;
		parent::__construct('Data insufficient: ' . $this->_resource, 112);
	}

	public function __get($name)
	{
		if ($name == 'resource')
			return $this->_resource;
		else
			throw new No_Such_Variable_Exception($name, get_class($this));
	}
}
?>