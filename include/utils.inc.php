<?php
// Useful functions that might be missing in PHP

// mb_ucfirst
if (!function_exists('mb_ucfirst') && function_exists('mb_substr'))
{
	function mb_ucfirst($string, $encoding = null)
	{
		if ($encoding !== null)
			mb_internal_encoding($encoding);
		$string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
		return $string;
	}
}

// mb_str_replace
if (!function_exists('mb_str_replace') && function_exists('mb_substr'))
{
	function mb_str_replace($search, $replace, $subject, $encoding = 'auto')
	{
		if (!is_array($search))
			$search = array($search);

		if (!is_array($replace))
			$replace = array($replace);

		if (strtolower($encoding) === 'auto')
			$encoding = mb_internal_encoding();

		if (is_array($subject))
		{
			$result = array();
			foreach($subject as $key => $val)
				$result[$key] = mb_str_replace($search, $replace, $val, $encoding);
			return $result;
		}

		$currentpos = 0;
		while (true)
		{
			$index = $minpos = -1;
			foreach ($search as $key => $find)
			{
				if ($find == '') continue;
				$findpos = mb_strpos($subject, $find, $currentpos, $encoding);
				if ($findpos !== false)
				{
					if ($minpos < 0 || $findpos < $minpos)
					{
						$minpos = $findpos;
						$index = $key;
					}
				}
			}
			if ($minpos < 0) break;
			$r = array_key_exists($index, $replace) ? $replace[$index] : '';
			$subject = sprintf
			(
				'%s%s%s',
				mb_substr($subject, 0, $minpos, $encoding),
				$r,
				mb_substr
				(
					$subject,
					$minpos + mb_strlen($search[$index], $encoding),
					mb_strlen($subject, $encoding),
					$encoding
				)
			);
			$currentpos = $minpos + mb_strlen($r, $encoding);
		}
		return $subject;
	}
}

// mb_trim
if (!function_exists('mb_trim') && function_exists('preg_replace'))
{
	/**
	 * Trim characters from either (or both) ends of a string in a way that is
	 * multibyte-friendly.
	 *
	 * Mostly, this behaves exactly like trim() would: for example supplying 'abc' as
	 * the charlist will trim all 'a', 'b' and 'c' chars from the string, with, of
	 * course, the added bonus that you can put unicode characters in the charlist.
	 *
	 * We are using a PCRE character-class to do the trimming in a unicode-aware
	 * way, so we must escape ^, \, - and ] which have special meanings here.
	 * As you would expect, a single \ in the charlist is interpretted as
	 * "trim backslashes" (and duly escaped into a double-\ ). Under most circumstances
	 * you can ignore this detail.
	 *
	 * As a bonus, however, we also allow PCRE special character-classes (such as '\s')
	 * because they can be extremely useful when dealing with UCS. '\pZ', for example,
	 * matches every 'separator' character defined in Unicode, including non-breaking
	 * and zero-width spaces.
	 *
	 * It doesn't make sense to have two or more of the same character in a character
	 * class, therefore we interpret a double \ in the character list to mean a
	 * single \ in the regex, allowing you to safely mix normal characters with PCRE
	 * special classes.
	 *
	 * *Be careful* when using this bonus feature, as PHP also interprets backslashes
	 * as escape characters before they are even seen by the regex. Therefore, to
	 * specify '\\s' in the regex (which will be converted to the special character
	 * class '\s' for trimming), you will usually have to put *4* backslashes in the
	 * PHP code - as you can see from the default value of $charlist.
	 *
	 * @param string
	 * @param charlist list of characters to remove from the ends of this string.
	 * @param boolean trim the left?
	 * @param boolean trim the right?
	 * @return String
	 */
	function mb_trim($string, $charlist = '\\\\s', $ltrim = true, $rtrim = true)
	{
			$both_ends = $ltrim && $rtrim;

			$char_class_inner = preg_replace
			(
				array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
				array( '\\\\\\0', '\\' ),
				$charlist
			);

			$work_horse = '[' . $char_class_inner . ']+';
			$ltrim && $left_pattern = '^' . $work_horse;
			$rtrim && $right_pattern = $work_horse . '$';

			if ($both_ends)
				$pattern_middle = $left_pattern . '|' . $right_pattern;
			elseif ($ltrim)
				$pattern_middle = $left_pattern;
			else
				$pattern_middle = $right_pattern;

		return preg_replace("/$pattern_middle/usSD", '', $string);
	}
}
?>