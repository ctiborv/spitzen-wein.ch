<?php
class Kiwi_Bad_URL_Exception extends Kiwi_Exception
{
	protected $_identification;

	public function __construct($identification, $subcode = 5)
	{
		$this->_identification = $identification;
		parent::__construct('Bad URL: ' . $identification, $subcode);
	}

	public function getURL()
	{
		return $this->_identification;
	}
}
?>