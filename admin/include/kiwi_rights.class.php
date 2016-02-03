<?php
define ('ADMIN_USERNAME', 'admin');
define ('ADMIN_USERID', 1);
define ('DEFAULT_USER', 'guest');
define ('DEFAULT_USERID', 2);

define ('ADMIN_RIGHT', 'Admin');

class Kiwi_Rights
{
	protected $_username;
	protected $_userid;
	protected $_rights;

	public function __construct()
	{
		$this->_userid = 0;
		$this->_rights = array();

		if (session_id() == "") session_start();

		if (isset($_SESSION['user']))
		{
			$this->_username = $_SESSION['user'];
			$this->loadRights();
		}
		else
		{
			$this->_username = DEFAULT_USER;
			$this->_userid = DEFAULT_USERID;
			// $this->_rights['EShop'] = array('Read' => true, 'Write' => false);
		}
	}

	protected function loadRights()
	{
		// docasne
		if ($this->_username == ADMIN_USERNAME)
		{
			$this->_userid = ADMIN_USERID;
			$this->_rights[ADMIN_RIGHT] = true;
		}
		elseif ($this->_username == 'readonly')
		{
			$this->_userid = 3;
			$this->_rights['EShop'] = array('Read' => true, 'Write' => false, 'EditURLs' => false);
			$this->_rights['WWW'] = array('Read' => true, 'Write' => false, 'EditTextModule' => false);
			$this->_rights['SEO'] = array('Read' => true, 'Write' => false);
		}
		elseif ($this->_username == 'spitzen')
		{
			$this->_userid = 4;
			$this->_rights['EShop'] = array('Read' => true, 'Write' => true, 'EditURLs' => true);
			$this->_rights['WWW'] = array('Read' => true, 'Write' => true, 'EditTextModule' => true, 'WriteNews' => true);
			$this->_rights['SEO'] = array('Read' => true, 'Write' => true);
		}/*
		elseif ($this->_username == 'xxxxxx')
		{
			$this->_userid = 5;
			$this->_rights['EShop'] = array('Read' => true, 'Write' => true, 'EditURLs' => true);
			$this->_rights['WWW'] = array('Read' => true, 'Write' => true, 'EditTextModule' => true, 'WriteNews' => true);
			$this->_rights['SEO'] = array('Read' => true, 'Write' => true);
		}*/
	}

	public function __get($name)
	{
		if ($name == 'Username' || $name == 'UserName') return $this->_username;
		if ($name == 'UserID') return $this->_userid;
		return (array_key_exists($name, $this->_rights)) ? $this->_rights[$name] : (array_key_exists(ADMIN_RIGHT, $this->_rights));
	}
}
?>
