<?php
class Kiwi_Client extends Kiwi_User
{
	protected $_type;
	protected $_person;
	protected $_company;

	const PERSON = 2;
	const COMPANY = 4;
	const COMMON = 6; // PERSON | COMPANY

	const DEFAULT_TYPE = self::PERSON;

	protected static $_client_fields = array
	(
		'jmeno'            => array('type' => self::PERSON,  'property' => 'FirstName'),
		'prijmeni'         => array('type' => self::PERSON,  'property' => 'SurName'),
		'titul'            => array('type' => self::PERSON,  'property' => 'Title'),
		'email'            => array('type' => self::PERSON,  'property' => 'Email'),
		'telefon'          => array('type' => self::PERSON,  'property' => 'Phone'),
		'mobil'            => array('type' => self::PERSON,  'property' => 'MobilePhone'),
		'fax'              => array('type' => self::PERSON,  'property' => 'Fax'),
		'firma_jmeno'      => array('type' => self::COMPANY, 'property' => 'BusinessName'),
		'firma_ic'         => array('type' => self::COMPANY, 'property' => 'IC'),
		'firma_dic'        => array('type' => self::COMPANY, 'property' => 'DIC'),
		'firma_email'      => array('type' => self::COMPANY, 'property' => 'Email'),
		'firma_telefon'    => array('type' => self::COMPANY, 'property' => 'Phone'),
		'firma_fax'        => array('type' => self::COMPANY, 'property' => 'Fax'),
		'ulice'            => array('type' => self::COMMON,  'property' => 'FStreet'),
		'cislo_popisne'    => array('type' => self::COMMON,  'property' => 'FAddressNumber'),
		'mesto'            => array('type' => self::COMMON,  'property' => 'FCity'),
		'psc'              => array('type' => self::COMMON,  'property' => 'FPostalCode'),
		'stat'             => array('type' => self::COMMON,  'property' => 'FCountry'),
		'da_jmeno'         => array('type' => self::COMMON,  'property' => 'DName'),
		'da_ulice'         => array('type' => self::COMMON,  'property' => 'DStreet'),
		'da_cislo_popisne' => array('type' => self::COMMON,  'property' => 'DAddressNumber'),
		'da_mesto'         => array('type' => self::COMMON,  'property' => 'DCity'),
		'da_psc'           => array('type' => self::COMMON,  'property' => 'DPostalCode'),
		'da_stat'          => array('type' => self::COMMON,  'property' => 'DCountry'),
		'paypal_email'     => array('type' => self::COMMON,  'property' => 'PaypalEmail')
	);

	public function __construct($userid_or_login = 0) // integer (0 = new user), or array('username' => '?', 'password' => '?')
	{
		$this->_type = self::DEFAULT_TYPE;
		$this->_person = new Typed_Var_Pool(__CLASS__. '#p');
		$this->_company = new Typed_Var_Pool(__CLASS__. '#c');

		foreach (self::$_client_fields as $value)
		{
			if ($value['type'] & self::PERSON)
				$this->_person->register($value['property']);

			if ($value['type'] & self::COMPANY)
				$this->_company->register($value['property']);
		}

		parent::__construct($userid_or_login);
	}

	public static function getFields()
	{
		$fields = array();
		foreach (self::$_client_fields as $key => $value)
			if ($value['type'] & $this->_type)
				$fields[$key] = $value;

		return array_merge(parent::getFields(), $fields);
	}

	public static function getClientFields()
	{
		return self::$_client_fields;
	}

	protected function getProperty($name)
	{
		if ($name == 'type' || $name == 'Type')
			return $this->_type;

		if ($name == 'completename' || $name == 'CompleteName')
			return $this->getCompleteName();

		try
		{
			switch ($this->_type)
			{
				case self::PERSON:
					return $this->_person->get($name);
				case self::COMPANY:
					return $this->_company->get($name);
				default:
					throw new Kiwi_Exception('Kiwi_Client type unknown');
			}
		}
		catch (No_Such_Variable_Exception $e)
		{
			return parent::getProperty($name);
		}
	}

	protected function setProperty($name, $value)
	{
		if ($name == 'type' || $name == 'Type')
		{
			$this->_type = $value;
			return;
		}

		if ($name == 'completename' || $name == 'CompleteName')
			throw Readonly_Variable_Exception($name);

		try
		{
			switch ($this->_type)
			{
				case self::PERSON:
					$this->_person->set($name, $value);
					break;
				case self::COMPANY:
					$this->_company->set($name, $value);
					break;
				default:
					throw new Kiwi_Exception('Kiwi_Client type unknown');
			}
		}
		catch (No_Such_Variable_Exception $e)
		{
			parent::setProperty($name, $value);
		}
	}

	public function getCompleteName()
	{
		switch ($this->_type)
		{
			case self::PERSON:
				$cname = array();
				if ($this->_person->Title !== '')
					$cname[] = $this->_person->Title;
				if ($this->_person->FirstName !== '')
					$cname[] = $this->_person->FirstName;
				if ($this->_person->SurName !== '')
					$cname[] = $this->_person->SurName;
				return implode(' ', $cname);
				break;
			case self::COMPANY:
				return $this->_company->BusinessName;
				break;
			default:
				throw new Kiwi_Exception('Kiwi_Client type unknown');
		}
	}

	protected function loadById($user_id)
	{
		parent::loadById($user_id);

		$dbh = Project_DB::get();

		$query_person = 'SELECT ' . implode(' ,', array_keys($this->_person->toArray())) . ' FROM clientsp WHERE ID=:id';
		$stmt_person = $dbh->prepare($query_person);
		$stmt_person->bindValue(":id", (int)$user_id, PDO::PARAM_INT);

		$stmt_person->execute();
		if ($row = $stmt_person->fetch(PDO::FETCH_ASSOC))
		{
			$this->_type = self::PERSON;
			foreach ($row as $key => $value)
				$this->_person->set($key, $value);
		}
		else
		{
			$query_company = 'SELECT ' . implode(' ,', array_keys($this->_company->toArray())) . 'FROM clientsc WHERE ID=:id';
			$stmt_company =  $dbh->prepare($query_company);
			$stmt_company->bindValue(":id", (int)$user_id, PDO::PARAM_INT);

			$stmt_company->execute();
			if ($row = $stmt_company->fetch(PDO::FETCH_ASSOC))
			{
				$this->_type = self::COMPANY;
				foreach ($row as $key => $value)
					$this->_company->set($key, $value);
			}
			else
				throw new Kiwi_No_Such_Client_Exception("id = $user_id");
		}
	}

	protected function checkDataSufficiency()
	{
		parent::checkDataSufficiency();

		switch ($this->_type)
		{
			case self::PERSON:
				if ($this->_person->SurName === null || $this->_person->SurName === '')
					throw new Data_Insufficient_Exception(get_class($this) . '::SurName');
				break;
			case self::COMPANY:
				if ($this->_company->BusinessName === null || $this->_company->BusinessName === '')
					throw new Data_Insufficient_Exception(get_class($this) . '::BusinessName');
				break;
			default:
				throw new Kiwi_Exception('Kiwi_Client type unknown');
		}
	}

	public function save()
	{
		parent::save();

		$dbh = Project_DB::get();
		
		switch ($this->_type)
		{
			case self::PERSON:
				$data = $this->_person->toArray();
				$table = 'clientsp';
				$table_d = 'clientsc';
				break;
			case self::COMPANY:
				$data = $this->_company->toArray();
				$table = 'clientsc';
				$table_d = 'clientsp';
				break;
			default:
				throw new Kiwi_Exception('Kiwi_Client type unknown');
		}

		foreach ($data as $key => $value)
			if ($value === null)
				unset($data[$key]);

		$data['ID'] = $this->_data->ID;

		$columns = implode(', ', array_keys($data));
		$column_pdo_hooks = ':' . implode(', :', array_keys($data));

		$query = "REPLACE $table ($columns) VALUES ($column_pdo_hooks)";
		$stmt = $dbh->prepare($query);

		foreach ($data as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		$query2 = "DELETE FROM $table_d WHERE ID=:ID";
		$stmt2 = $dbh->prepare($query2);
		$stmt2->bindValue(':ID', $this->_data->ID, PDO::PARAM_INT);

		$dbh->exec("LOCK TABLES $table WRITE, $table_d WRITE");
		$locked = true;
		try
		{
			$stmt->execute();
			$stmt2->execute();
		}
		catch (PDOException $e)
		{
			$dbh->exec('UNLOCK TABLES');
			$locked = false;
			$ei = $e->errorInfo;
			throw Kiwi_Exception("Failed to save client - {$ei[2]} ({$ei[0]})");
		}
		if ($locked)
			$dbh->exec('UNLOCK TABLES');
	}
}
?>