<?php
class Current_Client extends Current_User
{
/*
	protected function initialize()
	{
		parent::initialize();
	}
*/
	protected function instantiateDO()
	{
		$this->_data = new Kiwi_Client($this->_id);
	}
}
?>