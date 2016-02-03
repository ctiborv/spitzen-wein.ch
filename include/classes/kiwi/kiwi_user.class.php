<?php
//TODO: init rights
class Kiwi_User extends Kiwi_Object
{
	protected $_data;
	protected $_rights;

	const USER = 1;

	protected static $_user_fields = array
	(
		'prihlasovaci_jmeno' => array('type' => self::USER, 'property' => 'Username'),
		'heslo'              => array('type' => self::USER, 'property' => 'Password'),
		'cas_registrace'     => array('type' => self::USER, 'property' => 'Created')
	);

	public function __construct($userid_or_login = 0) // integer (0 = new user), or array('username' => '?', 'password' => '?')
	{
		$this->_data = new Typed_Var_Pool(__CLASS__);

		$this->_data->register('ID');

		foreach (self::$_user_fields as $value)
			$this->_data->register($value['property']);

		$this->_data->register('LastLogin');

		$this->load($userid_or_login);
		$this->loadRights();
	}

	public static function getFields()
	{
		return self::$_user_fields;
	}

	protected function getProperty($name)
	{
		if ($name == 'Password') return 'n/a';
		return $this->_data->get($name);
	}

	protected function setProperty($name, $value)
	{
		$this->_data->set($name, $value);
	}

	final public function get($name)
	{
		switch ($name)
		{
			case 'rights':
				return $this->_rights;
			case 'groups':
				return $this->_rights->groups;
			default:
				try
				{
					return $this->getProperty($name);
				}
				catch (No_Such_Variable_Exception $e)
				{
					return parent::get($name);
				}
		}
	}

	final public function set($name, $value)
	{
		switch ($name)
		{
			case 'rights':
			case 'groups':
				throw new Readonly_Variable_Exception($name, __CLASS__);
			default:
				try
				{
					$this->setProperty($name, $value);
				}
				catch (No_Such_Variable_Exception $e)
				{
					return parent::set($name, $value);
				}
		}
	}

	public static function getUserId($username, $password = null)
	{
		$dbh = Project_DB::get();
		$query = "SELECT ID FROM users WHERE Username=:username";
		if ($password !== null)
			$query .= " AND Password=:password";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(":username", $username, PDO::PARAM_STR);
		if ($password !== null)
			$stmt->bindValue(":password", md5($password), PDO::PARAM_STR);

		try
		{
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				return $row['ID'];
		}
		catch (PDOException $e)
		{
		}

		return null;
	}

	public function load($userid_or_login)
	{
		$user_id = is_array($userid_or_login)
			? $this->getUserId($userid_or_login['username'], $userid_or_login['password'])
			: (int)$userid_or_login;

		if ($user_id === null)
		{
			$password_md5 = $userid_or_login['password'] === null ? 'null' : ("'" . md5($userid_or_login['password']) . "'");
			throw new Kiwi_User_Authentication_Failed_Exception("username = '{$userid_or_login['username']}', password = $password_md5");
		}

		if ($user_id == 0)
			$this->initUser();
		else
			$this->loadById($user_id);
	}
	
	protected function initUser()
	{
		$this->_data->ID = 0;
	}

	protected function loadById($user_id)
	{
		$dbh = Project_DB::get();
		$query = "SELECT ID, Username, Password, Created, LastLogin FROM users WHERE ID=:id";
		$stmt = $dbh->prepare($query);
		$stmt->bindValue(":id", (int)$user_id, PDO::PARAM_INT);
		
		try
		{
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				foreach ($row as $key => $value)
					$this->_data->set($key, $value);
			}
		}
		catch (PDOException $e)
		{
			throw new Kiwi_No_Such_User_Exception(is_array($userid_or_login) ? "username = {$userid_or_login['username']}, password = $password_md5" : "id = $user_id");
		}
	}

	public function loadRights()
	{
		//TODO
		//if ($this->_data->ID)
			//$this->_rights = new User_Rights($this->_data->ID);
	}

	protected function initRights()
	{
		//TODO
	}

	protected function checkDataSufficiency()
	{
		if ($this->_data->Username === null || $this->_data->Username === '')
			throw new Data_Insufficient_Exception(get_class($this) . '::Username');

		if ($this->_data->Password === null || $this->_data->Password === '')
			throw new Data_Insufficient_Exception(get_class($this) . '::Password');
	}

	public function save()
	{
		$this->checkDataSufficiency(); // throws exception if insufficient

		$dbh = Project_DB::get();
		$data = $this->_data->toArray();

		if ($this->_data->ID == 0)
			unset($data['ID']);

		foreach ($data as $key => $value)
			if ($value === null)
				unset($data[$key]);

		$columns = implode(', ', array_keys($data));
		$column_pdo_hooks = ':' . implode(', :', array_keys($data));

		$query = "REPLACE users ($columns) VALUES ($column_pdo_hooks)";
		$stmt = $dbh->prepare($query);

		foreach ($data as $key => $value)
			$stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

		try
		{
			$stmt->execute();
			if ($this->_data->ID == 0)
			{
				$this->_data->ID = $dbh->lastInsertedId();
				$this->initRights();
			}
		}
		catch (PDOException $e)
		{
			$ei = $e->errorInfo;
			if ($ei[0] == '23000') // Integrity constraint violation
				throw Kiwi_Username_Duplicity_Exception($data['Username']);
			throw Kiwi_Exception("Failed to save user - {$ei[2]} ({$ei[0]})");
		}		
	}

	public function addGroup($group_id)
	{
		if (($user_id = $this->_data->ID) == 0)
			throw Kiwi_Exception("Attempt to modify unregistered user's rights");

		$dbh = Project_DB::get();
		$query = "REPLACE usersgroups (UID, GID) VALUES (:user_id, :group_id)";
		$stmt = $dbh->prepare($query);

		$stmt->bindValue(":user_id", (int)$user_id, PDO::PARAM_INT);
		$stmt->bindValue(":group_id", (int)$group_id, PDO::PARAM_INT);

		try
		{
			$stmt->execute();
		}
		catch (PDOException $e)
		{
			$ei = $e->errorInfo;
			throw Kiwi_Exception("Failed to add user ($user_id) into group ($group_id) - {$ei[2]} ({$ei[0]})");
		}
	}

	public function remGroup($group_id)
	{
		if (($user_id = $this->_data->ID) == 0)
			throw Kiwi_Exception("Attempt to modify unregistered user's rights");

		$dbh = Project_DB::get();
		$query = "DELETE from usersgroups WHERE UID=:user_id AND GID=:group_id LIMIT 1";
		$stmt = $dbh->prepare($query);

		$stmt->bindValue(":user_id", (int)$user_id, PDO::PARAM_INT);
		$stmt->bindValue(":group_id", (int)$group_id, PDO::PARAM_INT);

		try
		{
			$stmt->execute();
		}
		catch (PDOException $e)
		{
			$ei = $e->errorInfo;
			throw Kiwi_Exception("Failed to remove user ($user_id) from group ($group_id) - {$ei[2]} ({$ei[0]})");
		}
	}

	public function login()
	{
		$cu = Current_User::getInstance();
		$cu->setUserID($this->ID);

		$dbh = Project_DB::get();
		$query = "UPDATE users SET LastLogin=CURRENT_TIMESTAMP";
		$dbh->exec($query);
	}
}
?>