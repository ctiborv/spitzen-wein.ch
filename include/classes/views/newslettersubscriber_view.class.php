<?php
class NewsletterSubscriber_View extends Template_Based_SpamProtected_Validated_View
{
	protected $_subscription_result;

	const STR_MISSING = 'missing';
	const STR_INVALID = 'invalid';

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_session_var = 'newslettersubscriber';
		$this->_session_vars[] = '_subscription_result';

		$this->_subscription_result = null;

		$this->template = 'newslettersubscriber/default'; // default template
	}

	protected function setImplicitValidations()
	{
		parent::setImplicitValidations();
		$this->_validate[] = 'email';
	}

	protected function _validationSuccess()
	{
		if ($this->_status == self::READY)
			$this->subscribe();
		parent::_validationSuccess(); // save state to session and redirect
	}

	protected function _updateFormTemplate()
	{
		$vars = array
		(
			'identification' => true,
			'email' => true,
			'odeslat' => true
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
	}

	protected function _updateSuccessTemplate()
	{
	}

	protected function _updateFailureTemplate()
	{
		$eids = array(
			'mail_jiz_odeslan' => FALSE,
			'mail_odeslan' => FALSE,
			'jiz_prihlasen' => FALSE,
			'zablokovan' => FALSE,
			'chyba' => FALSE,
		);

		switch ($this->_subscription_result) {
			case Newsletter_Subscription_Manager::RESUBSCRIPTION_MAIL_ALREADY_SENT:
				$eids['mail_jiz_odeslan'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::RESUBSCRIPTION_MAIL_SENT:
				$eids['mail_odeslan'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::ALREADY_SUBSCRIBED:
				$eids['jiz_prihlasen'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::BLOCKED:
				$eids['zablokovan'] = TRUE;
				break;
			case Newsletter_Subscription_Manager::UNKNOWN_ERROR:
			default:
				$eids['chyba'] = TRUE;
		}

		foreach ($eids as $eid => $active) {
			$elems = $this->_index->$eid;
			foreach ($elems as $elem) {
				$elem->active = $active;
			}
		}
	}

	protected function _updateSpamTemplate()
	{
	}

	protected function subscribe()
	{
		$rmb = new Custom_Newsletter_Resubscription_Message_Builder;
		$nsm = new Newsletter_Subscription_Manager;
		$nsm->setResubscriptionMessageBuilder($rmb);

		$email = $this->getFirstElementValueNonDefault('email');

		$this->_subscription_result = $nsm->subscribe(NULL, $email);

		$this->_status = $this->_subscription_result === Newsletter_Subscription_Manager::SUBSCRIBED ? self::SUCCESS : self::FAILURE;
	}

	protected function validate_email()
	{
		if ($this->testRequiredNonDefault('email'))
			return Validator::validateEmail($this->getFirstElementValue('email')) ? self::STR_OK : self::STR_INVALID;
		else
			return self::STR_MISSING;
	}
}
