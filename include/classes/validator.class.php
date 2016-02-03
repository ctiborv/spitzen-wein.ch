<?php
class Validator
{
	protected function __construct()
	{
	}

	public static function validateEmail($str)
	{
		return preg_match("~^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$~i", $str);
	}

	public static function validateHex($str)
	{
		return preg_match("~^[0-9a-f]+$~i", $str);
	}

	public static function validatePhone($str)
	{
		return preg_match("~^(\\+[0-9]{3} ?)?[0-9]{9}$~", $str);
	}

	public static function validateRC($str)
	{
		return preg_match("~[0-9]{6}[/ ]?[0-9]{3,4}~", $str);
	}
}
?>
