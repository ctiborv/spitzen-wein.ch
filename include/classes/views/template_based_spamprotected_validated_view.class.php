<?php
/*
 * compared to Template_Based_Validated_View contains code for spam detection
 * derived views might additionally override methods:
 *   _noSpamDetected()
 *   _spamDetected()
 *   _updateSpamTemplate()
 */
abstract class Template_Based_SpamProtected_Validated_View extends Template_Based_Validated_View
{
	protected $_spam; // triggered spam-trap element or null if no spam detected

	// new status codes:
	const SPAM = 3;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_session_var = 'tbsvv';
		$this->_spam = null;

		$this->_attributes->register('hidden'); // expected value of "hidden" form element
	}

	protected function resolveTemplateSourceSuffix()
	{
		return $this->_status == self::SPAM ? 'spam' : parent::resolveTemplateSourceSuffix();
	}

	protected function _updateTemplate()
	{
		if ($this->_status == self::SPAM)
			$this->_updateSpamTemplate();
		else
			parent::_updateTemplate();
	}

	abstract protected function _updateSpamTemplate();

	protected function updateTemplate()
	{
		Template_Based_View::updateTemplate();
		if ($this->_shouldValidate())
		{
			$this->detectSpam();
			if ($this->_status != self::SPAM)
			{
				$this->_noSpamDetected();
				$this->validate();
				if ($this->_validation['failed'] == 0)
					$this->_validationSuccess();
				else
					$this->_validationFailure();
			}
			else
				$this->_spamDetected();
		}
		$this->applyValidationResults();
		$this->_restoreSubmitValues();
	}

	protected function _restoreSubmitValues()
	{
		parent::_restoreSubmitValues();
		$hidden = $this->_index->hidden;
		if ($this->hidden !== null && !empty($hidden))
			$hidden[0]->vo->value = $hidden[0]->value;
	}

	protected function _noSpamDetected()
	{
	}

	protected function _spamDetected()
	{
		$this->saveSessionVars();
		$this->_redirection = Project_Navigator::getNavPoint(Server::get('REQUEST_URL'));
	}

	protected function detectSpam()
	{
		$traps = $this->_index->trap;
		try
		{
			$ti = 1;
			foreach ($traps as $trap)
			{
				switch ($trap->tag)
				{
					case 'Autoinput_View':
						$non_default = strtolower($trap->type) !== 'submit';
						break;
					case 'Autotextarea_View':
						$non_default = true;
						break;
					default:
						throw new Template_Invalid_Structure_Exception("The 'trap' element is neither autoinput nor autotextarea ('$trap->tag')");
				}

				try
				{
					if ($non_default ? $this->testRequiredNonDefault($trap) : $this->testRequired($trap, array(null, '')))
					{
						$this->_spam = $trap;
						//print_r("#" . $this->getFirstElementValue($trap) . "#"); die();
						throw new Spam_Detected_Exception("spambot has got trapped at trap input #$ti");
					}
				}
				catch (Template_Element_Missing_Exception $e)
				{
				}

				++$ti;
			}

			// onsubmit javascript check
			if ($this->hidden !== null)
			{
				try
				{
					if ($this->getFirstElementValue('hidden') != $this->hidden)
					{
						$hiddens = $this->_index->hidden;
						$this->_spam = $hiddens[0];
						$value = $this->_spam->vo->value;
						throw new Spam_Detected_Exception("spambot has got trapped at hidden js-modified input ('$value' != '$this->hidden')");
					}
				}
				catch (Template_Element_Missing_Exception $e)
				{
				}
			}
		}
		catch (Spam_Detected_Exception $e)
		{
			$this->_status = self::SPAM;
		}	
	}
}
?>