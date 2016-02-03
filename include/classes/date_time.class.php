<?php
class Date_Time extends Date
{
	public function __construct($data = null)
	{
		parent::__construct($data);
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'second':
			case 'seconds':
				return $this->format('s');
			case 'minute':
			case 'minutes':
				return $this->format('i');
			case 'hour':
			case 'hours':
				return $this->format('G');
			case 'day':
				return $this->format('j');
			case 'month':
				return $this->format('n');
			case 'year':
				return $this->format('Y');
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	final public function __get($name)
	{
		return $this->get($name);
	}
}
?>
