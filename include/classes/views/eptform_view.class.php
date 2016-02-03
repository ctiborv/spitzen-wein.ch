<?php
class Eptform_View extends Mailer_View
{
	const VLASTNI_VAL = 'vlastní';


	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_attributes->register('subject', 'Poptávka z webových stránek', false); // subject of the email message
		$this->_session_var = 'eptform';

		$this->template = 'eptform/default'; // default template
	}

	protected function updateFormTemplate()
	{
		parent::updateFormTemplate();

		$new_inputs = array
		(
			'mam_zajem',
			'jak_odpovedet'
		);

	}

	public function updateMailBodyTemplate(HTML_Element $root, HTML_Indexer $index, array $fields)
	{
		parent::updateMailBodyTemplate($root, $index, $fields);

		$vars = array
		(
			'mam_zajem',
			'jak_odpovedet'
		);

		foreach ($vars as $varname)
			$$varname = $index->$varname;

		foreach ($jak_odpovedet as $elem)
			$elem->add(new HTML_Text($fields['mam_zajem']));

		foreach ($jak_odpovedet as $elem)
			$elem->add(new HTML_Text($fields['jak_odpovedet']));

	}

	protected function getFieldKeys()
	{
		$keys_all = array
		(
			// key => what_to_skip
			'predmet' => 0,
			'jmeno' => 0,
			'telefon' => 0,
			'email' => 0,
			'mam_zajem' => 0,
			'jak_odpovedet' => 0,
			'vzkaz' => 0
		);

	}

	public function getMailBodyFields()
	{
		$fields = parent::getMailBodyFields();

		$temp = $this->getFirstSelectValues('mam_zajem');
		$fields['mam_zajem'] = $temp[0];

		$temp = $this->getFirstSelectValues('jak_odpovedet');
		$fields['jak_odpovedet'] = $temp[0];


		return $fields;
	}

	// validations:
}
?>
