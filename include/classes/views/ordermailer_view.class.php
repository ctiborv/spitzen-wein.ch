<?php
class Ordermailer_View extends Mailer_View
{
	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_attributes->register('subject', 'Poptávka z webových stránek', false); // subject of the email message
		$this->_session_var = 'ordermailer';

		$this->template = 'ordermailer/default'; // default template
	}

	protected function setImplicitValidations()
	{
		parent::setImplicitValidations();
		$this->_validate[] = 'poptavka';
	}

	protected function updateFormTemplate()
	{
		parent::updateFormTemplate();

		$poptavka = $this->_index->poptavka;

		if (empty($poptavka))
			throw new Template_Element_Missing_Exception('poptavka');

		if (count($poptavka) != 1)
			throw new Template_Invalid_Structure_Exception("The 'poptavka' element duplicity");
	}

	public function updateMailBodyTemplate(HTML_Element $root, HTML_Indexer $index, array $fields)
	{
		parent::updateMailBodyTemplate($root, $index, $fields);

		$vars = array
		(
			'je_poptavka',
			'neni_poptavka',
			'poptavka'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		foreach ($je_poptavka as $elem)
			$elem->active = $fields['poptavka'] !== '';

		foreach ($neni_poptavka as $elem)
			$elem->active = $fields['poptavka'] === '';

		foreach ($poptavka as $elem)
			$elem->add(new HTML_Text($fields['poptavka']));
	}

	protected function getFieldKeys()
	{
		$pkeys = parent::getFieldKeys();
		$keys = array();
		$inserted = false;
		foreach ($pkeys as $pkey)
		{
			// we add the demand row above the the message row
			if ($pkey == 'vzkaz')
			{
				$keys[] = 'poptavka';
				$inserted = true;
			}

			$keys[] = $pkey;
		}

		if (!$inserted)
			$keys[] = 'poptavka';

		return $keys;
	}

	public function getMailBodyFields()
	{
		$fields = parent::getMailBodyFields();
		$fields['poptavka'] = $this->getFirstElementValueNonDefault('poptavka');
		return $fields;
	}

	// validations:
	protected function validate_poptavka()
	{
		return $this->testRequiredNonDefault('poptavka') ? self::STR_OK : self::STR_MISSING;
	}
}
?>
