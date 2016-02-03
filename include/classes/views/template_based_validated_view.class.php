<?php
/*
 * compared to Template_Based_View contains base code for validation
 * and a dispatcher for ready, success and failure templates
 * (success and failure are related to actual operation, not validation)
 * derived views will have to implement protected methods:
 *   validate_whatever(HTML_Group $group)
 *   _updateFormTemplate()
 *   _updateSuccessTemplate()
 *   _updateFailureTemplate()
 * and might want to override:
 *   _shouldValidate()
 *   _validationSuccess()
 *   _validationFailure()
 * where "whatever" corresponds with form template structure like this:
 * <flow eid="validation" specification="whatever/ok">
 *   ...
 * </flow>
 * <flow eid="validation" specification="whatever/non_ok_result1">
 *   ...
 * </flow>
 * <flow eid="validation" specification="whatever/non_ok_result2">
 *   ...
 * </flow>
 *
 * and finally for complete result:
 * <flow eid="validation_passed">
 *   ...
 * </flow>
 * <flow eid="validation_failed">
 *   ...
 * </flow>
 */
abstract class Template_Based_Validated_View extends Template_Based_View
{
	protected $_validation; // array of validation results and processing data
	protected $_validate; // array of predefined validations; should be set in constructor of derived classes
	protected $_status; // form status
	protected $_session_var; // name of session variable where status is stored
	protected $_session_vars; // list of variables being stored in session

	// basic validation results:
	const STR_OK = 'ok';
	const STR_IRRELEVANT = 'irrelevant';

	// status codes:
	const READY = 0;
	const SUCCESS = 1;
	const FAILURE = 2;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_validation = null;
		$this->_validate = array();
		$this->_status = self::READY;
		$this->_session_var = 'tbvv';
		$this->_session_vars = array('_status');

		$this->_attributes->register('ns'); // namespace (important for storing status of multiple forms in client session)
		$this->_attributes->register('successPage'); // page to redirect on success (NULL means current)

		$this->setImplicitValidations();
	}

	protected function setImplicitValidations() // implicit validations, regardless on template
	{
	}

	protected function _initialize()
	{
		if ($this->ns === null)
			throw new Data_Insufficient_Exception('ns');

		parent::_initialize();
	}

	protected function resolveTemplateSource()
	{
		$suffix = $this->resolveTemplateSourceSuffix();

		$base = $this->template;
		if ($base === null)
			throw new Data_Insufficient_Exception('template');
		$this->template = $base . '_' . $suffix;
	}

	protected function resolveTemplateSourceSuffix()
	{
		switch ($this->_status)
		{
			case self::READY:
				return 'form';
			case self::SUCCESS:
				return 'success';
			case self::FAILURE:
			default:
				return 'failure';
		}
	}

	protected function _handleInput()
	{
		$this->loadSessionVars();

		// non-ready status applies only once, then reverts to ready
		if ($this->_status !== self::READY)
			$this->saveSessionVar('_status', self::READY);
	}

	protected function loadSessionVars()
	{
		$client_session = Client_Session::getInstance();
		try
		{
			$sv = $client_session->get($this->ns . $this->_session_var);
			foreach ($this->_session_vars as $varname)
				if (array_key_exists($varname, $sv))
					$this->$varname = $sv[$varname];
		}
		catch (No_Such_Variable_Exception $e)
		{
		}
	}

	protected function saveSessionVars()
	{
		$client_session = Client_Session::getInstance();

		$svar = array();
		foreach ($this->_session_vars as $varname)
			$svar[$varname] = $this->$varname;

		$client_session->register($this->ns . $this->_session_var, $svar, false);
	}

	protected function getSessionVarValue($varname)
	{
		$client_session = Client_Session::getInstance();
		try
		{
			$sv = $client_session->get($this->ns . $this->_session_var);
		}
		catch (No_Such_Variable_Exception $e)
		{
			return null;
		}

		return array_key_exists($varname, $sv) ? $sv[$varname] : null;
	}

	protected function saveSessionVar($varname, $value = null)
	{
		$client_session = Client_Session::getInstance();
		if ($value === null)
			$value = $this->$varname;
		$svar = $client_session->get($this->ns . $this->_session_var);
		$svar[$varname] = $value;
		$client_session->register($this->ns . $this->_session_var, $svar, false);
	}

	protected function _updateTemplate()
	{
		switch ($this->_status)
		{
			case self::READY:
				$this->_updateFormTemplate();
				break;
			case self::SUCCESS:
				$this->_updateSuccessTemplate();
				break;
			case self::FAILURE:
			default:
				$this->_updateFailureTemplate();
				break;
		}
	}

	abstract protected function _updateFormTemplate();

	abstract protected function _updateSuccessTemplate();

	abstract protected function _updateFailureTemplate();

	protected function updateTemplate()
	{
		parent::updateTemplate();
		if ($this->_shouldValidate())
		{
			$this->validate();
			if ($this->_validation['failed'] == 0)
				$this->_validationSuccess();
			else
				$this->_validationFailure();
		}

		$this->applyValidationResults();
		$this->_restoreSubmitValues();
	}

	protected function _restoreSubmitValues()
	{
		$auto_inputs = $this->_root->getElementsBy('tag', 'Autoinput_View');
		foreach ($auto_inputs as $elem)
		{
			if (strtolower($elem->type) == 'submit')
				$elem->vo->value = $elem->value;
		}
	}

	protected function _shouldValidate()
	{
		try
		{
			$identified = $this->checkForInputMatch('identification');
		}
		catch (Template_Element_Missing_Exception $e)
		{
			$identified = true;
		}
		return $identified && $this->_status == self::READY;
	}

	protected function _validationSuccess()
	{
		$this->saveSessionVars();
		$this->_redirection = $this->_getSuccessPageNavPoint();
	}

	protected function _getSuccessPageNavPoint()
	{
		return $this->successPage !== NULL
			? $this->successPage
			: Project_Navigator::getNavPoint(Server::get('REQUEST_URL'));
	}

	protected function _validationFailure()
	{
	}

	protected function validate()
	{
		$this->_validation = array('requests' => array(), 'results' => array(), 'passed' => 0, 'failed' => 0);

		// predefined validations
		foreach ($this->_validate as $spec)
		{
			if (strpos($spec, '/') === false)
			{
				$validation_name = $spec;
				$validation_result = self::STR_OK;
			}
			else
				list($validation_name, $validation_result) = explode('/', $spec, 2);
			$this->_validation['requests'][$validation_name][$validation_result] = array();
		}

		// search the template file for additional requested validations
		$validation_elems = $this->_index->validation;
		foreach ($validation_elems as $elem)
		{
			try
			{
				$spec = $elem->specification;
				if (strpos($spec, '/') === false)
				{
					$validation_name = $spec;
					$validation_result = self::STR_OK;
				}
				else
					list($validation_name, $validation_result) = explode('/', $spec, 2);

				$this->_validation['requests'][$validation_name][$validation_result][] = $elem;
			}
			catch (HTML_No_Such_Element_Attribute_Exception $e)
			{
				throw new Template_Invalid_Structure_Exception('Validation element must be of HTML_Group type');
			}
		}

		// perform requested validation checks
		$reflection = new ReflectionClass(get_class($this));
		foreach ($this->_validation['requests'] as $validation_name => $validation_results)
		{
			$method = "validate_$validation_name";
			if ($reflection->hasMethod($method))
				$result = $this->{$method}();
			else
				$result = 'unknown';
			$this->_validation['results'][$validation_name] = $result;
			$this->_validation[$result === self::STR_OK || $result === self::STR_IRRELEVANT ? 'passed' : 'failed'] += 1;
		}
	}

	protected function applyValidationResults()
	{
		if ($this->_validation === null)
		{
			$validation = $this->_index->validation;
			foreach ($validation as $elem)
				$elem->active = false;

			$passed = $failed = false;
		}
		else
		{
			foreach ($this->_validation['requests'] as $validation_name => $validation_results)
			{
				$result = $this->_validation['results'][$validation_name];
				foreach ($validation_results as $res => $elems)
					foreach ($elems as $elem)
						$elem->active = ($res === $result);
			}

			$passed = $this->_validation['failed'] == 0;
			$failed = !$passed;
		}

		$passed_elems = $this->_index->validation_passed;
		$failed_elems = $this->_index->validation_failed;
		foreach ($passed_elems as $elem)
			$elem->active = $passed;
		foreach ($failed_elems as $elem)
			$elem->active = $failed;
		$failed_count_elems = $this->_index->validation_failures;
		foreach ($failed_count_elems as $elem)
			$elem->add(new HTML_Text($this->_validation['failed']));
	}

	protected function checkForInputMatch($eid)
	{
		$elems = $this->_index->$eid;
		if (empty($elems))
			throw new Template_Element_Missing_Exception($eid);

		try
		{
			$name = $elems[0]->name;
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception("Element of given eid ($eid) is of wrong type");
		}
		if ($name === '')
			throw new Template_Invalid_Structure_Exception("Element of given eid ($eid) must have the 'name' attribute set");
		return array_key_exists($name, $_POST) && $_POST[$name];
	}

	// helper test methods:
	protected function getFirstElementValue($eid_or_elem)
	{
		$elem = $this->parseEidOrElem($eid_or_elem);
		try
		{
			return $elem->vo->value;
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception("Element of given eid ($elem->eid) is of wrong type");
		}
		catch (No_Such_Variable_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception("Element of given eid ($elem->eid) is of wrong type");
		}
	}

	protected function getFirstElementValueDefault($eid_or_elem)
	{
		$elem = $this->parseEidOrElem($eid_or_elem);
		try
		{
			return $elem->value;
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception("Element of given eid ($elem->eid) is of wrong type");
		}
		catch (No_Such_Variable_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception("Element of given eid ($elem->eid) is of wrong type");
		}
	}

	protected function getFirstElementValueNonDefault($eid_or_elem, $novalue = '')
	{
		$elem = $this->parseEidOrElem($eid_or_elem);
		try
		{
			$vo_value = $elem->vo->value;
			$value = $elem->value;
			return $vo_value === $value ? $novalue : $vo_value;
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception("Element of given eid ($elem->eid) is of wrong type");
		}
		catch (No_Such_Variable_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception("Element of given eid ($elem->eid) is of wrong type");
		}
	}

	protected function getFirstCheckedValue($eid)
	{
		$elems = $this->_index->get($eid);
		foreach ($elems as $elem)
			if ($elem->vo->checked)
				return $elem->vo->value;
		return null;
	}

	protected function getCheckedValues($eid)
	{
		$elems = $this->_index->get($eid);
		$values = array();
		foreach ($elems as $elem)
			if ($elem->vo->checked)
				$values[] = $elem->vo->value;
		return $values;
	}

	protected function getFirstSelectValues($eid, $get_text = false)
	{
		$elems = $this->_index->get($eid);
		if (empty($elems))
			return null;
		$elem = $elems[0];
		return $elem->vo->getSelected($get_text);
	}

	protected function getSelectValues($eid, $get_text = false)
	{
		$elems = $this->_index->get($eid);
		$values = array();
		foreach ($elems as $elem)
			$values[] = $elem->vo->getSelected($get_text);
		return $values;
	}

	protected function testRequired($eid_or_elem, $novalue = '')
	{
		$val = $this->getFirstElementValue($eid_or_elem);
		if (is_array($novalue))
		{
			foreach ($novalue as $noval)
				if ($val === $noval)
					return false;
			return true;
		}
		else
			return $val !== $novalue;
	}

	protected function testRequiredNonDefault($eid_or_elem)
	{
		$elem = $this->parseEidOrElem($eid_or_elem);
		$empty = array(null, '');
		$value = $elem->value;
		if ($value !== null && $value !== '')
			$empty[] = $value;
		return $this->testRequired($elem, $empty);
	}

	protected function testEmail($eid_or_elem)
	{
		return Validator::validateEmail($this->getFirstElementValue($eid_or_elem));
	}

	protected function parseEidOrElem($eid_or_elem)
	{
		if (is_object($eid_or_elem))
			$elem = $eid_or_elem;
		else
		{
			$elems = $this->_index->get($eid_or_elem);
			if (empty($elems))
				throw new Template_Element_Missing_Exception($eid_or_elem);
			$elem = $elems[0];
		}
		return $elem;
	}
}
