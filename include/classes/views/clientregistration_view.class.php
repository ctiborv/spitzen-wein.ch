<?php
//TODO: otestovat
//EDIT: nahrazeny validacni vysledky OK za IRRELEVANT na prislusnych mistech
//EDIT: opraven Template_Mailer, přidána možnost explicitně nastavit from, to a subject přes speciální metody
//EDIT: přejmenována proměnná _ro na _do a odpovídající proměné a metody z *ro* na *do*
//EDIT: upravena metoda onValidationSuccess, přidány metody saveRO, trySaveRO, onSaveSuccess
//EDIT: upraveno na potomka Userregistration_View, a Registration_View
//EDIT: přejmenováno na clientregistration
//EDIT: otestována podpora vstupu query stringu preset a delims
//EDIT: předchozí úprava zobecněna pro libovolný input, formát je {ns}preset=field1:value1;...;field_n:value_n a {ns}delims=value
//      delims jsou oddělovače, pro preset, výchozí je ";:", směrodatné jsem jen první dva znaky, oba jsou nepovinné
//      příklad querystringu pro aplikaci na inputu s eid="email" při ns="abc" a vnitřním oddělovači "/" :
//      ?abcpreset=email/dont@mailme.com&abcdelims=;/
//EDIT: přidána feature pro načtení hodnoty emailu (a nastavení příslušného inputu na read-only) přes query string či formulář
//      (pro případ, že někdo po delší době neví, zda se již registroval, aby nebyl nucen vyplňovat celý formulář jen proto,
//       aby se pak dozvěděl, že už je registrován)
class Clientregistration_View extends Userregistration_View
{
	protected $_type; // for optimization of validation methods

	const TYPE_PERSON = 'osoba';
	const TYPE_COMPANY = 'spolecnost';

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_session_var = 'clientregistration';
		$this->_type = null;

		$this->template = 'clientregistration/default'; // default template
	}

	protected function setImplicitValidations()
	{
		parent::setImplicitValidations();
		$this->_validate[] = 'jmeno';
		$this->_validate[] = 'prijmeni';
		$this->_validate[] = 'email';
		$this->_validate[] = 'firma_jmeno';
		$this->_validate[] = 'firma_ic';
		$this->_validate[] = 'firma_email';
		$this->_validate[] = 'ulice';
		$this->_validate[] = 'cislo_popisne';
		$this->_validate[] = 'psc';
		$this->_validate[] = 'stat';
	}

	protected function login()
	{
		parent::login();
		$cc = Current_Customer::getInstance();
		$cc->loadFrom($this->_do);
	}

	public function updateMailBodyTemplate(HTML_Element $root, HTML_Indexer $index, array $fields)
	{
		parent::updateMailBodyTemplate($root, $index, $fields);

		$vars = array
		(
			'je_osoba',
			'neni_osoba',
			'je_spolecnost',
			'neni_spolecnost',
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		foreach ($je_osoba as $elem)
			$elem->active = $this->_type == self::PERSON;

		foreach ($neni_osoba as $elem)
			$elem->active = $this->_type != self::PERSON;

		foreach ($je_spolecnost as $elem)
			$elem->active = $this->_type == self::COMPANY;

		foreach ($neni_spolecnost as $elem)
			$elem->active = $this->_type != self::COMPANY;
	}

	protected function updateRegistration()
	{
		$typ = $this->getFirstElementValue('typ');
		switch ($typ)
		{
			case self::TYPE_COMPANY:
				$this->_do->type = Kiwi_Client::COMPANY;
				break;
			case self::TYPE_PERSON:
				$this->_do->type = Kiwi_Client::PERSON;
				break;
			default:
				throw new Invalid_Argument_Value_Exception('typ', $typ);
		}

		// override to inject email as a username if username is not given
		$fields = $this->_do->getFields();
		foreach ($fields as $eid => $fdata)
		{
			try
			{
				$fvalue = $this->getFirstElementValueNonDefault($eid); // default values are taken as unfilled
				$this->_do->set($fdata['property'], $fvalue);
			}
			catch (Template_Element_Missing_Exception $e)
			{
				if ($eid == 'prihlasovaci_jmeno')
				{
					try
					{
						$fvalue = $this->getFirstElementValueNonDefault($this->_type == self::TYPE_PERSON ? 'email' : 'firma_email');
						$this->_do->set($fdata['property'], $fvalue);
					}
					catch (Template_Element_Missing_Exception $e)
					{
					}
				}
			}
		}
	}

	protected function getMailRecipientEmailAddress()
	{
		return $this->_do->Email;
	}

	protected function getMailRecipientName()
	{
		return $this->_do->getCompleteName();
	}

	// override to optimize reading of the type of registration
	protected function validate()
	{
		$selected_values = $this->getFirstSelectValues('typ');
		$this->_type = empty($selected_values) ? null : $selected_values[0];
		parent::validate();
	}

	// validations:

	protected function validate_typ()
	{
		return ($this->_type === self::TYPE_PERSON || $this->_type === self::TYPE_COMPANY) ? self::STR_OK : self::STR_MISSING;
	}

	// override for non-obligatory username (email will be used instead)
	protected function validate_prihlasovaci_jmeno()
	{
		$elems = $this->_index->get('prihlasovaci_jmeno');
		if (!empty($elems))
			return parent::validate_prihlasovaci_jmeno();
		$username = $this->getFirstElementValue($this->_type == self::TYPE_PERSON ? 'email' : 'firma_email');
		if ($username === '')
			return self::STR_MISSING;
		return Kiwi_User::getUserId($username) === null ? self::STR_OK : self::STR_USERNAME_DUPLICITY;
	}

	protected function validate_jmeno()
	{
		if ($this->_type == self::TYPE_PERSON)
			return $this->testRequired('jmeno') ? self::STR_OK : self::STR_MISSING;
		else
			return self::STR_IRRELEVANT;
	}

	protected function validate_prijmeni()
	{
		if ($this->_type == self::TYPE_PERSON)
			return $this->testRequired('prijmeni') ? self::STR_OK : self::STR_MISSING;
		else
			return self::STR_IRRELEVANT;
	}

	protected function validate_email()
	{
		if ($this->_type == self::TYPE_PERSON)
		{
			if ($this->testRequired('email', array('', '@')))
				return $this->testEmail('email') ? self::STR_OK : self::STR_BAD_EMAIL;
			else
				return self::STR_MISSING;
		}
		else
			return self::STR_IRRELEVANT;
	}

	protected function validate_firma_jmeno()
	{
		if ($this->_type == self::TYPE_COMPANY)
			return $this->testRequired('firma_jmeno') ? self::STR_OK : self::STR_MISSING;
		else
			return self::STR_IRRELEVANT;
	}

	protected function validate_firma_ic()
	{
		if ($this->_type == self::TYPE_COMPANY)
			return $this->testRequired('firma_ic') ? self::STR_OK : self::STR_MISSING;
		else
			return self::STR_IRRELEVANT;
	}

	protected function validate_firma_email()
	{
		if ($this->_type == self::TYPE_COMPANY)
		{
			if ($this->testRequired('firma_email', array('', '@')))
				return $this->testEmail('firma_email') ? self::STR_OK : self::STR_BAD_EMAIL;
			else
				return self::STR_MISSING;
		}
		else
			return self::STR_IRRELEVANT;
	}

	protected function validate_ulice()
	{
		return $this->testRequired('ulice') ? self::STR_OK : self::STR_MISSING;
	}

	protected function validate_cislo_popisne()
	{
		return $this->testRequired('cislo_popisne') ? self::STR_OK : self::STR_MISSING;
	}

	protected function validate_psc()
	{
		return $this->testRequired('psc') ? self::STR_OK : self::STR_MISSING;
	}

	protected function validate_mesto()
	{
		return $this->testRequired('mesto') ? self::STR_OK : self::STR_MISSING;
	}

	protected function validate_stat()
	{
		return $this->testRequired('stat') ? self::STR_OK : self::STR_MISSING;
	}
}
?>
