<?php
class Datetime_View extends Template_Based_View
{
	protected $_datetime;
	protected $_enumerations;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_datetime = null;
		$this->_enumerations = array();

		$this->_attributes->register('timestamp');
		$this->_attributes->register('year');
		$this->_attributes->register('month');
		$this->_attributes->register('day');
		$this->_attributes->register('hour');
		$this->_attributes->register('minute');
		$this->_attributes->register('second');

		$this->template = 'datetime/default'; // default template
	}

	public function set($name, $value)
	{
		parent::set($name, $value);
		if ($this->_datetime !== null)
		{
			if ($value === null)
				throw new Invalid_Argument_Value_Exception($name, $value);
			if ($name == 'timestamp')
				$this->updateByTimestamp();
			else
				$this->updateTimestamp();
		}
	}

	protected function updateByTimestamp()
	{
		$this->_datetime = new Date_Time($this->timestamp);
		list
		(
			$this->_attributes->year,
			$this->_attributes->month,
			$this->_attributes->day,
			$this->_attributes->hour,
			$this->_attributes->minute,
			$this->_attributes->second
		) = explode(' ', $this->_datetime->format('Y n j G i s'));
	}

	protected function updateTimestamp()
	{
		if ($this->month === null) $this->month = 1;
		if ($this->day === null) $this->day = 1;
		if ($this->hour === null) $this->hour = 0;
		if ($this->minute === null) $this->minute = 0;
		if ($this->second === null) $this->second = 0;

		$this->_datetime = new Date_Time("$this->year-$this->month-$this->day $this->hour:$this->minute:$this->second");
		$this->_attributes->timestamp = $this->_datetime->getTimestamp();
	}

	protected function _initialize()
	{
		if ($this->timestamp !== null)
		{
			$fields = array('year', 'month', 'day', 'hour', 'minute', 'second');
			foreach ($fields as $field)
				if ($this->$field !== null)
					throw new Data_Superfluous_Exception('datetime::timestamp', "datetime::$field");
			$this->updateByTimestamp();
		}
		elseif ($this->year !== null)
			$this->updateTimestamp();
		elseif ($this->month !== null || $this->day !== null || $this->hour !== null || $this->minute !== null || $this->second !== null)
			throw new Data_Insufficient_Exception('datetime::year');
		else
		{
			$this->timestamp = time();
			$this->updateByTimestamp();
		}

		parent::_initialize();
	}

	protected function _handleInput()
	{
	}

	protected function _updateTemplate()
	{
		$this->parseEnumerations();

		$vars = array
		(
			'rok' => 'year',
			'mesic' => 'month',
			'den' => 'day',
			'hodina' => 'hour',
			'minuta' => 'minute',
			'sekunda' => 'second',
			'0mesic' => 'month',
			'0den' => 'day',
			'0hodina' => 'hour',
			'0minuta' => 'minute',
			'0sekunda' => 'second'
		);

		foreach ($vars as $varname => $propertyname)
		{
			$value = $this->$propertyname;
			$elements = $this->_index->$varname;
			foreach ($elements as $elem)
			{
				$enum = null;
				try
				{
					$enum = $elem->specification;
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
				}

				if ($enum !== null && array_key_exists($enum, $this->_enumerations) && array_key_exists($value, $this->_enumerations[$enum]))
					$result_value = $this->_enumerations[$enum][$value];
				else
				{
					if ($varname[0] == '0')
						$result_value = new HTML_Text(sprintf("%02d", $value));
					else
						$result_value = new HTML_Text($value);
				}

				$elem->add(clone $result_value);
			}
		}
	}

	protected function cleanupTemplate()
	{
		$this->_enumerations = null;
		parent::cleanupTemplate();
	}

	protected function parseEnumerations()
	{
		$enumerations = $this->_index->posloupnost;
		try
		{
			foreach ($enumerations as $elem)
			{
				$enum = $elem->specification;
				if ($enum === null)
					throw new Template_Invalid_Structure_Exception('enumeration specification missing');
				if (array_key_exists($enum, $this->_enumerations))
					throw new Template_Invalid_Structure_Exception("enumeration duplicity: $enum");

				$this->_enumerations[$enum] = array();
				$content = $elem->getContents();
				$i = 1;
				foreach ($content as &$subelem)
				{
					try
					{
						$spec = $subelem->specification;
						if ($spec !== null)
						{
							if ($spec == (int)$spec)
								$i = (int)$spec;
						}
					}
					catch (HTML_No_Such_Element_Attribute_Exception $e)
					{
					}
					if (array_key_exists($i, $this->_enumerations[$enum]))
						throw new Template_Invalid_Structure_Exception("enumeration element duplicity: $enum: $i");
					$this->_enumerations[$enum][$i++] = $subelem;
				}
			}
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			throw new Template_Invalid_Structure_Exception('enumeration is not a group');
		}
	}
}
?>
