<?php
class Userregistration_View extends Registration_View
{
	const STR_MISSING = 'neni';
	const STR_PASSWORD_MISMATCH = 'neshodna_hesla';
	const STR_USERNAME_DUPLICITY = 'duplicitni_prihlasovaci_jmeno';

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_attributes->register('autologin', true);
		$this->_session_var = 'userregistration';

		$this->template = 'userregistration/default'; // default template
	}

	protected function setImplicitValidations()
	{
		parent::setImplicitValidations();
		$this->_validate[] = 'prihlasovaci_jmeno';
		$this->_validate[] = 'heslo'; // validation is based on the "heslo" input element presence
	}

	protected function initDO()
	{
		$this->_do = new Kiwi_User;
	}

	// override to handle username duplicity
	protected function trySaveDO()
	{
		try
		{
			parent::trySaveDO();
		}
		catch (Kiwi_Username_Duplicity_Exception $e)
		{
			$this->_status = self::FAILURE;
			$this->_error_info = 'Username duplicity';
		}
	}

	protected function onSaveSuccess()
	{
		parent::onSaveSuccess();
		if ($this->autologin)
			$this->login();
	}

	protected function login()
	{
		$this->_do->login();
	}

	protected function getDOFormattedValue($eid, $dop)
	{
		if ($eid === 'cas_registrace')
		{
			$constants = Project_Config::get('constants');
			// format time of registration
			$ckey = $this->_lang . '_datetime_format';
			$time_format = array_key_exists($ckey, $constants) ? $constants[$ckey] : 'r';
			$cas_registrace = new Date($this->_do->get($dop));
			return $cas_registrace->format($time_format);
		}
		else
			return parent::getDOFormattedValue($eid, $dop);
	}

	protected function getMailRecipientEmailAddress()
	{
		return $this->_do->Username;
	}

	protected function getMailRecipientName()
	{
		return '';
	}

	// validations:

	protected function validate_prihlasovaci_jmeno()
	{
		$elems = $this->_index->get('prihlasovaci_jmeno');
		if (empty($elems))
			return self::STR_MISSING;
		$username = $elems[0]->vo->value;
		if ($username === '')
			return self::STR_MISSING;
		return Kiwi_User::getUserId($username) === null ? self::STR_OK : self::STR_USERNAME_DUPLICITY;
	}

	protected function validate_heslo()
	{
		$heslo = $this->_index->heslo;
		if (empty($heslo))
			return self::STR_OK;
		$kontrola = $this->_index->kontrola;
		if ($heslo[0]->vo->value === '')
			return self::STR_MISSING;
		elseif (!empty($kontrola) && $heslo[0]->vo->value !== $kontrola[0]->vo->value)
			return self::STR_PASSWORD_MISMATCH;
		else
			return self::STR_OK;
	}
}
?>
