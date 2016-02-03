<?php
class Mailer_View extends Template_Based_SpamProtected_Validated_View implements Mailing_View_Object
{
	protected $_error_info;

	const STR_MISSING = 'missing';
	const STR_INVALID = 'invalid';

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_attributes->register('lang', Project_Config::get('default_language')); // used for non-template emails
		$this->_attributes->register('contact', 'admin'); // name of the target contact
		$this->_attributes->register('subject', 'Vzkaz z webových stránek'); // subject of the email message
		$this->_session_var = 'mailer';
		$this->_session_vars[] = '_error_info';

		$this->_error_info = null;

		$this->template = 'mailer/default'; // default template
	}

	protected function setImplicitValidations()
	{
		parent::setImplicitValidations();
		$this->_validate[] = 'jmeno';
		$this->_validate[] = 'telefon_syntax';
		$this->_validate[] = 'email';
		$this->_validate[] = 'vzkaz';
	}

	protected function _initialize()
	{
		if ($this->contact === null || $this->contact === '')
			throw new Data_Insufficient_Exception('contact');

		if ($this->subject === null || $this->subject === '')
			throw new Data_Insufficient_Exception('subject');

		parent::_initialize();
	}

	protected function _validationSuccess()
	{
		if ($this->_status == self::READY)
			$this->sendMessage();
		parent::_validationSuccess();
	}

	protected function _updateFormTemplate()
	{
		$vars = array
		(
			'identification' => true,
			'jmeno' => true,
			'telefon' => false,
			'email' => true,
			'vzkaz' => true,
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
		$is_error = $this->_error_info !== null && $this->_error_info !== '';

		$je_chyba = $this->_index->je_chyba;
		foreach ($je_chyba as $elem)
			$elem->active = $is_error;

		if ($is_error)
		{
			$chyba = $this->_index->chyba;
			foreach ($chyba as $elem)
				$elem->add(new HTML_Text($this->_error_info));
		}
	}

	protected function _updateSpamTemplate()
	{
	}

	protected function sendMessage()
	{
		$tpl_mailer = new Template_Mailer($this);

		$this->_status = $tpl_mailer->send() ? self::SUCCESS : self::FAILURE;
		if ($this->_status == self::FAILURE)
			$this->_error_info = $tpl_mailer->ErrorInfo;
	}

	public function getMailTemplateSource()
	{
		return $this->template . '_mail';
	}

	public function updateMailBodyTemplate(HTML_Element $root, HTML_Indexer $index, array $fields)
	{
		$vars = array
		(
			'predmet',
			'je_jmeno',
			'neni_jmeno',
			'jmeno',
			'je_email',
			'neni_email',
			'email',
			'je_telefon',
			'neni_telefon',
			'telefon',
			'je_vzkaz',
			'neni_vzkaz',
			'vzkaz'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		foreach ($predmet as $elem)
			$elem->add(new HTML_Text($fields['predmet']));

		foreach ($je_jmeno as $elem)
			$elem->active = $fields['jmeno'] !== '';

		foreach ($neni_jmeno as $elem)
			$elem->active = $fields['jmeno'] === '';

		foreach ($jmeno as $elem)
			$elem->add(new HTML_Text($fields['jmeno']));

		foreach ($je_email as $elem)
			$elem->active = $fields['email'] !== '';

		foreach ($neni_email as $elem)
			$elem->active = $fields['email'] === '';

		foreach ($email as $elem)
			$elem->add(new HTML_Text($fields['email']));

		foreach ($je_telefon as $elem)
			$elem->active = $fields['telefon'] !== '';

		foreach ($neni_telefon as $elem)
			$elem->active = $fields['telefon'] === '';

		foreach ($telefon as $elem)
			$elem->add(new HTML_Text($fields['telefon']));

		foreach ($je_vzkaz as $elem)
			$elem->active = $fields['vzkaz'] !== '';

		foreach ($neni_vzkaz as $elem)
			$elem->active = $fields['vzkaz'] === '';

		if ($fields['vzkaz'] !== '')
		{
			$vzkaz_radky = explode("\n", str_replace("\r\n", "\n", $fields['vzkaz']));
			$ps = array();
			foreach ($vzkaz_radky as $vzkaz_radek)
			{
				$p = new HTML_P;
				$p->add(new HTML_Text($vzkaz_radek));
				$ps[] = $p;
			}
			foreach ($vzkaz as $elem)
				foreach ($ps as $pelem)
					$elem->add($pelem);
		}
	}

	public function getMailBodySimpleTable()
	{
		$keys = $this->getFieldKeys();
		return $this->getFieldsFromLanguageConstants($keys);
	}

	protected function getFieldKeys()
	{
		$keys = array
		(
			'predmet',
			'jmeno',
			'telefon',
			'email',
			'vzkaz'
		);

		return $keys;
	}

	protected function getFieldsFromLanguageConstants($keys)
	{
		$table = array();
		$constants = Project_Config::get('constants');
		foreach ($keys as $key)
		{
			$ckey = $this->lang . '_' . $key;
			$text = array_key_exists($ckey, $constants) ? $constants[$ckey] : mb_str_replace('_', ' ', $key, 'UTF-8');
			$table[$key] = mb_ucfirst($text, 'UTF-8') . ": %s\n";
		}

		return $table;
	}

	public function getMailBodyFields()
	{
		$fields = array('predmet' => $this->subject);
		$vars = array
		(
			'jmeno',
			'email',
			'vzkaz'
		);
		foreach ($vars as $var)
			$fields[$var] = $this->getFirstElementValueNonDefault($var);

		try
		{
			$fields['telefon'] = $this->getFirstElementValueNonDefault('telefon');
		}
		catch (Template_Element_Missing_Exception $e)
		{
		}

		return $fields;
	}

	public function getMailSender()
	{
		return array
		(
			'email' => $this->getFirstElementValueNonDefault('email'),
			'name' => $this->getFirstElementValueNonDefault('jmeno')
		);
	}

	public function getMailRecipient()
	{
		return $this->contact;
	}

	public function getMailSubject()
	{
		return $this->subject;
	}

	// validations:
	protected function validate_jmeno()
	{
		return $this->testRequiredNonDefault('jmeno') ? self::STR_OK : self::STR_MISSING;
	}

	// validations for phone separated, so it can be optional, whether phone is required
	protected function validate_telefon_povinny()
	{
		return $this->testRequiredNonDefault('telefon') ? self::STR_OK : self::STR_MISSING;
	}

	protected function validate_telefon_syntax()
	{
		try
		{
			return ($this->testRequiredNonDefault('telefon') && !Validator::validatePhone($this->getFirstElementValue('telefon'))) ? self::STR_INVALID : self::STR_OK;
		}
		catch (Template_Element_Missing_Exception $e)
		{
			return self::STR_OK;
		}
	}

	protected function validate_email()
	{
		if ($this->testRequiredNonDefault('email'))
			return Validator::validateEmail($this->getFirstElementValue('email')) ? self::STR_OK : self::STR_INVALID;
		else
			return self::STR_MISSING;
	}

	protected function validate_vzkaz()
	{
		return $this->testRequiredNonDefault('vzkaz') ? self::STR_OK : self::STR_MISSING;
	}
}
?>
