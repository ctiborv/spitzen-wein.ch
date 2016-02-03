<?php
//Current_Customer shares some of the Kiwi_Client's specifics
//but unlike Kiwi_Client, Current_Customer is not a Kiwi_User object, but Singleton
class Current_Customer extends Singleton
{
	protected function initialize() 
	{
		$so = Client_Session::getInstance();

		if ($so->isEmpty())
		{			
			$type = Kiwi_Client::DEFAULT_TYPE;
			$person = new Typed_Var_Pool('Kiwi_Client#p'));
			$company = new Typed_Var_Pool('Kiwi_Client#c'));

			$_client_fields = Kiwi_Client::getClientFields();
			foreach ($_client_fields as $value)
			{
				if ($value['type'] & Kiwi_Client::PERSON)
					$person->register($value['property']);

				if ($value['type'] & Kiwi_Client::COMPANY)
					$company->register($value['property']);
			}

			$so->register('type', $type);
			$so->register('person', $person); 
			$so->register('company', $company);
			$so->register('client_id');
		}
	}

	public function loadFrom(Kiwi_Client $source)
	{
		//this method is usually called after client login
		$so = Client_Session::getInstance();
		$so->person->clear();
		$so->company->clear();
		$so->client_id = $source->ID;
		$so->type = $source->type;
		$fields = $source->getClientFields();
		foreach ($fields as $key => $value)
			if ($value['type'] & $so->type)
				$this->set($key, $source->get($key));
	}

	public static function getFields()
	{
		$fields = array();
		$_client_fields = Kiwi_Client::getClientFields();
		foreach ($_client_fields as $key => $value)
			if ($value['type'] & $this->_type)
				$fields[$key] = $value;

		return $fields;
	}

	public function get($name)
	{
		$so = Client_Session::getInstance();
		if ($name == 'type' || $name == 'Type')
			return $so->type;

		switch ($so->type)
		{
			case Kiwi_Client::PERSON:
				return $so->person->get($name);
			case Kiwi_Client::COMPANY:
				return $so->company->get($name);
			default:
				throw new Kiwi_Exception('Kiwi_Client type unknown');
		}
	}

	public function set($name, $value)
	{
		$so = Client_Session::getInstance();
		if ($name == 'type' || $name == 'Type')
		{
			$so->type = $value;
			return;
		}

		switch ($so->type)
		{
			case Kiwi_Client::PERSON:
				$so->person->set($name, $value);
				break;
			case Kiwi_Client::COMPANY:
				$so->company->set($name, $value);
				break;
			default:
				throw new Kiwi_Exception('Kiwi_Client type unknown');
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

	public function getCompleteName()
	{
		$so = Client_Session::getInstance();
		switch ($so->type)
		{
			case Kiwi_Client::PERSON:
				$cname = array();
				if ($so->person->Title !== '')
					$cname[] = $so->person->Title;
				if ($so->person->FirstName !== '')
					$cname[] = $so->person->FirstName;
				if ($so->person->SurName !== '')
					$cname[] = $so->person->SurName;
				return implode(' ', $cname);
				break;
			case Kiwi_Client::COMPANY:
				return $so->company->BusinessName;
				break;
			default:
				throw new Kiwi_Exception('Kiwi_Client type unknown');
		}
	}

	protected function checkDataSufficiency()
	{
		$so = Client_Session::getInstance();
		switch ($so->type)
		{
			case Kiwi_Client::PERSON:
				if ($so->person->SurName === null || $so->person->SurName === '')
					throw new Data_Insufficient_Exception(get_class($this) . '::SurName');
				break;
			case Kiwi_Client::COMPANY:
				if ($so->company->BusinessName === null || $so->company->BusinessName === '')
					throw new Data_Insufficient_Exception(get_class($this) . '::BusinessName');
				break;
			default:
				throw new Kiwi_Exception('Kiwi_Client type unknown');
		}
	}

	public function save()
	{
		$this->checkDataSufficiency(); // throws exception if insufficient		
		$so = Client_Session::getInstance();
		$so->save();
	}
}
?>