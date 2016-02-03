<?php
class Kiwi_URL_Generator
{
	protected static $char_map = array
	(
		',' => '-',
		'+' => '',
		'0' => '0',
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'a' => 'a',
		'b' => 'b',
		'c' => 'c',
		'd' => 'd',
		'e' => 'e',
		'f' => 'f',
		'g' => 'g',
		'h' => 'h',
		'i' => 'i',
		'j' => 'j',
		'k' => 'k',
		'l' => 'l',
		'm' => 'm',
		'n' => 'n',
		'o' => 'o',
		'p' => 'p',
		'q' => 'q',
		'r' => 'r',
		's' => 's',
		't' => 't',
		'u' => 'u',
		'v' => 'v',
		'w' => 'w',
		'x' => 'x',
		'y' => 'y',
		'z' => 'z',
		'A' => 'A',
		'B' => 'B',
		'C' => 'C',
		'D' => 'D',
		'E' => 'E',
		'F' => 'F',
		'G' => 'G',
		'H' => 'H',
		'I' => 'I',
		'J' => 'J',
		'K' => 'K',
		'L' => 'L',
		'M' => 'M',
		'N' => 'N',
		'O' => 'O',
		'P' => 'P',
		'Q' => 'Q',
		'R' => 'R',
		'S' => 'S',
		'T' => 'T',
		'U' => 'U',
		'V' => 'V',
		'W' => 'W',
		'X' => 'X',
		'Y' => 'Y',
		'Z' => 'Z',
		'á' => 'a',
		'ä' => 'a',
		'č' => 'c',
		'ď' => 'd',
		'é' => 'e',
		'ě' => 'e',
		'ë' => 'e',
		'í' => 'i',
		'ľ' => 'l',
		'ö' => 'o',
		'ň' => 'n',
		'ó' => 'o',
		'ř' => 'r',
		'š' => 's',
		'ť' => 't',
		'ú' => 'u',
		'ů' => 'u',
		'ü' => 'u',
		'ý' => 'y',
		'ž' => 'z',
		'Á' => 'A',
		'Ä' => 'A',
		'Č' => 'C',
		'Ď' => 'D',
		'É' => 'E',
		'Ě' => 'E',
		'Ë' => 'E',
		'Í' => 'I',
		'Ľ' => 'L',
		'Ö' => 'O',
		'Ň' => 'N',
		'Ó' => 'O',
		'Ř' => 'R',
		'Š' => 'S',
		'Ť' => 'T',
		'Ú' => 'U',
		'Ů' => 'U',
		'Ü' => 'U',
		'Ý' => 'Y',
		'Ž' => 'Z'
	);

	const DEFAULT_MAPPED_TO = '-';

	protected static $cleanup = array
	(
		'--+' => '-',
		'^-+' => '',
		'-+$' => ''
	);

	protected function __construct()
	{
	}

	public static function generate($str)
	{
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');

		$result = '';
		$char = mb_substr($str, 0, 1);
		while ($char !== '')
		{
			if (array_key_exists($char, self::$char_map))
				$result .= self::$char_map[$char];
			else
				$result .= self::DEFAULT_MAPPED_TO;

			$str = mb_substr($str, 1);
			$char = mb_substr($str, 0, 1);
		}

		foreach (self::$cleanup as $key => $val)
			$result = mb_ereg_replace($key, $val, $result);

		return $result;
	}
}
?>