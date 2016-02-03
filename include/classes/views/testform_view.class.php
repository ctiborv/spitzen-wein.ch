<?php
class TestForm_View extends Template_Based_Validated_View
{
	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_session_var = 'testform';

		$this->template = 'testform/default'; // default template
	}

	protected function _updateFormTemplate()
	{
	}

	protected function _updateSuccessTemplate()
	{
	}

	protected function _updateFailureTemplate()
	{
	}

	protected function validate_jmeno()
	{
		return $this->testRequiredNonDefault('jmeno') ? 'ok' : 'neni';
	}

	protected function validate_vek()
	{
		$vek = $this->_index->vek;
		if (!is_numeric($vek[0]->vo->value))
			return 'neni_cislo';
		elseif ($vek[0]->vo->value < 18)
			return 'prilis_mlad';
		elseif ($vek[0]->vo->value > 40)
			return 'prilis_star';
		else
			return 'ok';
	}

	protected function validate_pohlavi()
	{
		$muz = $this->_index->muz;
		$zena = $this->_index->zena;
		if ($zena[0]->vo->checked) return 'baba';
		elseif ($muz[0]->vo->checked) return 'ok';
		else return 'zadny';
	}

	protected function validate_mrtvoly()
	{
		$zijici = $this->_index->zijici;
		return $zijici[0]->vo->checked ? 'ok' : 'mrtvola';
	}

	protected function validate_submit()
	{
		$submit = $this->_index->submit;
		return $submit[0]->vo->value ? 'ok' : 'nespravne_tlacitko';
	}
}
?>