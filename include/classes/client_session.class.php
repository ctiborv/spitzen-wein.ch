<?php
class Client_Session extends Singleton
{
	protected $_data;

	public function initialize()
	{
		$this->_data = new Session_Var_Pool(Project_Config::get('project') . '_client');
	}

	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->_data, $name), $arguments);
	}
}
?>