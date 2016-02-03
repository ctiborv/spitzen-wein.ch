<?php
class Money_Value
{
	protected $_numval;
	protected $_format;

	public static $default_format = array
	(
		'pattern' => '%s',
		'decimals' => 2,
		'dec_point' => '.',
		'thousands_sep' => ' ',
		'subst_zeros' => null
	);

	public static $format_presets = array
	(
		'us' => array
		(
			'pattern' => '$%s',
			'decimals' => 2,
			'dec_point' => '.',
			'thousands_sep' => ',',
			'subst_zeros' => null
		),
		'cz' => array
		(
			'pattern' => '%s Kč',
			'decimals' => 2,
			'dec_point' => ',',
			'thousands_sep' => '.',
			'subst_zeros' => null
		),
		'chf' => array
		(
			'pattern' => '%s CHF',
			'decimals' => 2,
			'dec_point' => ',',
			'thousands_sep' => '.',
			'subst_zeros' => null
		),
	 'sk' => array
		(
			'pattern' => '%s Sk',
			'decimals' => 2,
			'dec_point' => ',',
			'thousands_sep' => '.',
			'subst_zeros' => null
		)
	);


	public function __construct($number, $format = null)
	{
		$this->_numval = $number;
		$this->format = $format;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'format': return $this->_format;
			case 'value': return $this->_numval;
			case 'pattern': return $this->_format['pattern'];
			case 'decimals': return $this->_format['decimals'];
			case 'dec_point': return $this->_format['dec_point'];
			case 'thousands_sep': return $this->_format['thousands_sep'];
			case 'subst_zeros': return $this->_format['subst_zeros'];
			default: throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'format':
				if ($value === null)
					$this->_format = self::$default_format;
				elseif (is_array($value))
					$this->_format = $value;
				elseif (is_string($value))
				{
					$val = strtolower($value);
					if (array_key_exists($val, self::$format_presets))
						$this->_format = self::$format_presets[$val];
					else
						$this->_format = self::$default_format;
				}
				else
					throw new Invalid_Argument_Type_Exception($name, $value, __CLASS__);
				break;
			case 'value':
				$this->_numval = $value;
				break;
			case 'pattern':
				$this->_format['pattern'] = $value;
				break;
			case 'decimals':
				$this->_format['decimals'] = $value;
				break;
			case 'dec_point':
				$this->_format['dec_point'] = $value;
				break;
			case 'thousands_sep':
				$this->_format['thousands_sep'] = $value;
				break;
			case 'subst_zeros':
				$this->_format['subst_zeros'] = $value;
				break;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	public function format()
	{
		$fnumber = number_format($this->_numval, $this->_format['decimals'], $this->_format['dec_point'], $this->_format['thousands_sep']);
		if ($this->_format['subst_zeros'] !== null)
		{
			$search = $this->_format['dec_point'];
			for ($i = 0; $i < $this->_format['decimals']; $i++) $search .= '0';
			$replace = $this->_format['dec_point'] . $this->_format['subst_zeros'];
			$fnumber = str_replace($search, $replace, $fnumber);
		}
		$result = sprintf($this->_format['pattern'], $fnumber);
		return $result;
	}

	public function __toString()
	{
		return $this->format();
	}

	public static function setDefaultFormat($preset = null)
	{
		if ($preset === null)
			self::$default_format = $this->_format;
		else
		{
			$val = strtolower($preset);
			if (array_key_exists($val, self::$format_presets))
				self::$default_format = self::$format_presets[$val];
			else
				self::$default_format = $this->_format;
		}
	}
}

/*
 * examples:
 * $mv = new Money_Value(1000, 'us'); // default
 * echo $mv->format();
 *
 * $mv->_format = 'cz';
 * $mv->Subst_zeros = '-';
 * echo $mv->format();
 *
 * $mv = new Money_Value(500, 'cz'); // czech crowns
 * echo $mv->format();
 *
 * Money_Value::setDefaultFormat('sk');
 * $mvsk = new Money_Value(2500);
 * echo $mvsk->format();
 *
 */

?>
