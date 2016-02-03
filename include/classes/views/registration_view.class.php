<?php
abstract class Registration_View extends Template_Based_SpamProtected_Validated_View implements Mailing_View_Object
{
	protected $_do; // data object implementing the registrating code

	protected $_error_info;

	const STR_BAD_EMAIL = 'chybny_email';

	const DEFAULT_PRESET_DELIM_OUTER = ';';
	const DEFAULT_PRESET_DELIM_INNER = ':';

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_attributes->register('lang', Project_Config::get('default_language')); // used for non-template emails
		$this->_attributes->register('mailsender', 'admin');
		$this->_attributes->register('mailsubject');
		$this->_session_var = 'registration';
		$this->_session_vars[] = '_error_info';

		$this->_error_info = null;

		$this->initDO();
	}

	abstract protected function initDO(); // initializes the registration object (object that performs the save to DB)

	protected function _validationSuccess()
	{
		if ($this->_status == self::READY)
			$this->onValidationSuccess();
		parent::_validationSuccess();
	}

	protected function onValidationSuccess()
	{
		$this->updateRegistration();
		$this->saveDO();
		if ($this->_status != self::FAILURE)
			$this->onSaveSuccess();
	}

	protected function trySaveDO()
	{
		$this->_do->save();
	}

	protected function saveDO()
	{
		try
		{
			$this->trySaveDO();
		}
		catch (Exception $e)
		{
			$this->_status = self::FAILURE;
			$this->_error_info = 'Failed to store the data';
		}
	}

	protected function onSaveSuccess()
	{
		$this->sendMessage();
	}

	protected function _updateFormTemplate()
	{
		// if this form has not been submitted yet...
		if (!$this->_shouldValidate())
		{
			// ... and if any preset values have been passed through query string or form data....
			if (isset($_REQUEST[$this->ns . 'preset']))
			{
				$delims = isset($_REQUEST[$this->ns . 'delims'])
					? $_REQUEST[$this->ns . 'delims']
					: (self::DEFAULT_PRESET_DELIM_OUTER . self::DEFAULT_PRESET_DELIM_INNER);
				$del_o = isset($delims[0]) ? $delims[0] : self::DEFAULT_PRESET_DELIM_OUTER;
				$del_i = isset($delims[1]) ? $delims[1] : self::DEFAULT_PRESET_DELIM_INNER;
				$assignments = explode($del_o, $_REQUEST[$this->ns . 'preset']);
				// ... apply all assignments
				foreach ($assignments as $assignment)
				{
					$args = explode($del_i, $assignment);
					if (count($args) != 2) continue; // skip assignment if incorrect syntax
					$eid = $args[0];
					$value = $args[1];
					$elems = $this->_index->$eid;
					// ... set values of all input(s) (yet only one probably makes sense) and also enable the readonly flag
					foreach ($elems as $elem)
					{
						try
						{
							$elem->vo->value = $value;
							$elem->vo->readonly = true;
						}
						catch (HTML_No_Such_Element_Attribute_Exception $e)
						{
							try
							{
								$elem->value = $value;
								$elem->readonly = true;
							}
							catch (HTML_No_Such_Element_Attribute_Exception $e)
							{
							}
						}
						catch (No_Such_Variable_Exception $e)
						{
						}
					}
				}
			}
		}
	}

	protected function _updateSuccessTemplate()
	{
	}

	protected function _updateFailureTemplate()
	{
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
		foreach ($fields as $key => $value)
		{
			$elems = $index->get("je_$key");
			foreach ($elems as $elem)
				$elem->active = $value !== '';

			$elems = $index->get("neni_$key");
			foreach ($elems as $elem)
				$elem->active = $value === '';

			$elems = $index->get($key);
			foreach ($elems as $elem)
				$elem->add(new HTML_Text($value));
		}
	}

	public function getMailBodySimpleTable()
	{
		$table = array();
		$do_fields = $this->_do->getFields();
		$constants = Project_Config::get('constants');
		foreach ($do_fields as $key => $value)
		{
			$ckey = $this->lang . '_' . $key;
			$text = array_key_exists($ckey, $constants) ? $constants[$ckey] : mb_str_replace('_', ' ', $key, 'UTF-8');
			$table[$key] = mb_ucfirst($text, 'UTF-8') . ": %s\n";
		}
		
		return $table;
	}

	public function getMailBodyFields()
	{
		$fields = array();
		$do_fields = $this->_do->getFields();
		foreach ($do_fields as $key => $value)
		{
			try
			{
				$fields[$key] = $this->getDOFormattedValue($key, $value['property']);
			}
			catch (Template_Element_Missing_Exception $e)
			{
				$fields[$key] = '';
			}
		}
		return $fields;
	}

	public function getMailSender()
	{
		return $this->mailsender;
	}

	public function getMailRecipient()
	{
		$recipient = array();
		$recipient['email'] = $this->getMailRecipientEmailAddress();
		$name = $this->getMailRecipientName();
		if ($name !== '' && $name !== null)
			$recipient['name'] = $name;
		return $recipient;
	}

	abstract protected function getMailRecipientEmailAddress();

	abstract protected function getMailRecipientName();

	public function getMailSubject()
	{
		return $this->mailsubject;
	}

	protected function getDOFormattedValue($eid, $dop)
	{
		try
		{
			return $this->_do->get($dop);
		}
		catch (No_Such_Variable_Exception $e)
		{
			try
			{
				return $this->getFirstElementValueNonDefault($eid);
			}
			catch (Template_Element_Missing_Exception $e)
			{
				return '';
			}
		}
	}

	protected function updateRegistration()
	{
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
			}
		}
	}
}
?>
