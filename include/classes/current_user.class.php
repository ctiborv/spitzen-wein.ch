<?php
class Current_User extends Singleton
{
	protected $_id;
	protected $_data;

	protected function initialize()
	{
		$this->setUserID(null);
	}
	
	public function setUserID($uid)
	{
		if ($uid === null)
		{
			$so = Client_Session::getInstance();
			try
			{
				$uid = $so->get('user_id');
			}
			catch (No_Such_Variable_Exception $e)
			{
			}
		}

		$this->_id = $uid;
		$this->_data = null;

		$so = Client_Session::getInstance();
		$so->register('user_id', $this->_id, false);
	}

	public function isLogged()
	{
		return $this->_id !== null;
	}

	public function logout()
	{
		$so = Client_Session::getInstance();
		$this->_id = null;
		$so->register('user_id', null, false);
		$this->_data = null;
	}

	public function __call($name, $arguments)
	{
		if ($this->isLogged())
		{
			if ($this->_data === null)
				$this->instantiateDO();

			return call_user_func_array(array($this->_data, $name), $arguments);
		}
		else
			return null; // add an exception?
	}

	protected function instantiateDO()
	{
		$this->_data = new Kiwi_User($this->_id);
	}
}
?>