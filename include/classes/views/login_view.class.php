<?php
//TODO: predelat testovani loginu pomoci validace
class Login_View extends Template_Based_SpamProtected_Validated_View
{
	protected $_error_info;
	protected $_type;

	const STR_MISSING = 'missing';
	const STR_INCORRECT = 'incorrect';

	const LOGIN_FORM = 1;
	const LOGOUT_FORM = 2;

	const E_NOT_LOGGED_IN = 1;
	const E_ALREADY_LOGGED_IN = 2;
	const E_INVALID_CREDENTIALS = 3;

	const M_USER = 'user';
	const M_CLIENT = 'client';

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_attributes->register('mode', self::M_USER);
		$this->_session_var = 'login';
		$this->_session_vars[] = '_error_info';

		$this->_error_info = null;

		$user = $this->getUserObject();
		$this->_type = $user->isLogged() ? self::LOGOUT_FORM : self::LOGIN_FORM;

		$this->template = 'login/default'; // default template
	}

	protected function setImplicitValidations()
	{
		parent::setImplicitValidations();
		$this->_validate[] = 'username';
		$this->_validate[] = 'password';
	}

	protected function _validationSuccess()
	{
		// login_view is always in valid input mode, regardless on $this->_status
		switch ($this->_type)
		{
			case self::LOGOUT_FORM:
				$this->logout();
				break;
			case self::LOGIN_FORM:
			default:
				$this->login();
				break;
		}
		parent::_validationSuccess();
	}

	protected function logout()
	{
		$user = $this->getUserObject();
		if ($user->isLogged())
		{
			$user->logout();
			$this->_status = self::SUCCESS;
		}
		else
		{
			$this->_status = self::FAILURE;
			$this->_error_info = array('code' => self::E_NOT_LOGGED_IN, 'msg' => 'Not logged in!');
		}
	}

	protected function login()
	{
		$user = $this->getUserObject();
		if ($user->isLogged())
		{
			$this->_status = self::FAILURE;
			$this->_error_info = array('code' => self::E_ALREADY_LOGGED_IN, 'msg' => 'Already logged in! Log out first!');
		}
		else
		{
			$credentials = array
			(
				'username' => $this->getFirstElementValue('username'),
				'password' => $this->getFirstElementValue('password')
			);
			try
			{
				$kiwi_user = new Kiwi_User($credentials);
				$this->_status = self::SUCCESS;
				$kiwi_user->login();
			}
			catch (Kiwi_User_Authentication_Failed_Exception $e)
			{
				$this->_status = self::FAILURE;
				$this->_error_info = array('code' => self::E_INVALID_CREDENTIALS, 'msg' => 'Incorrect login or password!');
			}
		}
	}

	protected function resolveTemplateSourceSuffix()
	{
		switch ($this->_status)
		{
			case self::SUCCESS:
				$this->_type = $this->_type === self::LOGIN_FORM ? self::LOGOUT_FORM : self::LOGIN_FORM;
			case self::READY:
			case self::FAILURE:
			case self::SPAM:
			default:
				return $this->_type === self::LOGIN_FORM ? 'login' : 'logout';
		}
	}

	protected function _updateFormTemplate()
	{
		$this->_error_info = null;
		$this->__updateFormTemplate();
	}
	
	protected function __updateFormTemplate()
	{
		switch ($this->_type)
		{
			case self::LOGOUT_FORM;
				return $this->updateLogoutFormTemplate();
				break;
			case self::LOGIN_FORM;
			default:
				return $this->updateLoginFormTemplate();
				break;
		}
	}

	protected function updateLoginFormTemplate()
	{
		$vars = array
		(
			'identification' => true,
			'username' => true,
			'password' => true,
			'login' => true
		);

		foreach ($vars as $varname => $required)
		{
			$$varname = $this->_index->$varname;
			if ($required && empty($$varname))
				throw new Template_Element_Missing_Exception($varname);
			if (empty($$varname)) continue;
			elseif (count($$varname) == 1)
			{
				$temp_ar =& $$varname;
				$$varname = $temp_ar[0];
			}
			else
				throw new Template_Invalid_Structure_Exception("The '$varname' element duplicity");
		}

		$this->updateTemplate_ErrorInfo();
	}

	protected function updateLogoutFormTemplate()
	{
		$vars = array
		(
			'identification' => true,
			'logout' => true
		);

		foreach ($vars as $varname => $required)
		{
			$$varname = $this->_index->$varname;
			if ($required && empty($$varname))
				throw new Template_Element_Missing_Exception($varname);
			if (empty($$varname)) continue;
			elseif (count($$varname) == 1)
			{
				$temp_ar =& $$varname;
				$$varname = $temp_ar[0];
			}
			else
				throw new Template_Invalid_Structure_Exception("The '$varname' element duplicity");
		}

		$user = $this->getUserObject();

		$typ_uzivatele = $this->_index->typ_uzivatele;
		if (!empty($typ_uzivatele))
		{
			try
			{
				$type = $user->get('type');
				foreach ($typ_uzivatele as $elem)
				{
					try
					{
						$elem->active = (bool)($elem->specification & $type);
					}
					catch (HTML_No_Such_Element_Attribute_Exception $e)
					{
						throw Template_Invalid_Structure_Exception('typ_uzivatele element is of bad type (must be a html_group)');
					}
				}
			}
			catch (No_Such_Variable_Exception $e)
			{
				foreach ($typ_uzivatele as $elem)
					$elem->active = false;
			}
		}

		$uvars = $user->getFields();
		$uvars['cele_jmeno'] = array('property' => 'CompleteName');

		foreach ($uvars as $eid => $pdata)
		{
			$elems = $this->_index->$eid;
			if (!empty($elems))
			{
				try
				{
					$value = $user->get($pdata['property']);
					foreach ($elems as $elem)
						$elem->add(new HTML_Text($value));
				}
				catch (No_Such_Variable_Exception $e)
				{
					foreach ($elems as $elem)
						$elem->active = false;
				}
			}
		}

		$this->updateTemplate_ErrorInfo();
	}

	protected function updateTemplate_ErrorInfo()
	{
		$is_error = $this->_index->is_error;
		foreach ($is_error as $elem)
			$elem->active = $this->_error_info !== null && ($elem->specification === null || $elem->specification == $this->_error_info['code']);

		$no_error = $this->_index->no_error;
		foreach ($no_error as $elem)
			$elem->active = $this->_error_info === null;

		if ($this->_error_info !== null)
		{
			$error = $this->_index->error_msg;
			foreach ($error as $elem)
				$elem->add(new HTML_Text($this->_error_info['msg']));
		}	
	}

	protected function _updateSuccessTemplate()
	{
		//$this->__updateFormTemplate();
		$this->_redirection = Project_Navigator::getNavPoint(Server::get('REQUEST_URL'));
	}

	protected function _updateFailureTemplate()
	{
		$this->__updateFormTemplate();
	}

	protected function _updateSpamTemplate()
	{
		if ($this->_type === self::LOGIN_FORM)
			$this->_error_info = array('code' => self::E_INVALID_CREDENTIALS, 'msg' => 'Incorrect login or password!');
		$this->__updateFormTemplate();
	}

	protected function getUserObject()
	{
		switch ($this->mode)
		{
			case self::M_CLIENT:
				return Current_Client::getInstance();
			case self::M_USER:
			default:
				return Current_User::getInstance();
		}
	}

	// validations:
	protected function validate_username()
	{
		if ($this->_type === self::LOGIN_FORM)
			return $this->testRequiredNonDefault('username') ? self::STR_OK : self::STR_MISSING;
		else
			return self::STR_IRRELEVANT;
	}

	protected function validate_password()
	{
		if ($this->_type === self::LOGIN_FORM)
		{
			if (!$this->testRequiredNonDefault('password'))
				return self::STR_MISSING;

			if (!$this->testRequiredNonDefault('username'))
				return self::STR_IRRELEVANT;

			$credentials = array
			(
				'username' => $this->getFirstElementValue('username'),
				'password' => $this->getFirstElementValue('password')
			);
			try
			{
				$kiwi_user = new Kiwi_User($credentials);
				return self::STR_OK;
			}
			catch (Kiwi_User_Authentication_Failed_Exception $e)
			{
				return self::STR_INCORRECT;
			}
		}
		else
			return self::STR_IRRELEVANT;
	}
}
?>
