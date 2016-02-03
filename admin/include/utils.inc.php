<?php
function isEmailValid($email)
{
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
}

function isDateValid($date)
{
	if (!isset($date) || $date=="")
		return false;

	list($dd, $mm, $yy) = explode('.', $date);

	if ($dd != '' && $mm != '' && $yy != '')
	  return checkdate($mm, $dd, $yy);

  return false;
}

function isDateTimeValid($datetime)
{
	if (!isset($datetime) || $datetime=="")
		return false;

	list($date, $time) = explode (' ', $datetime);

	list($dd, $mm, $yy) = explode('.', $date);
	list($hour, $min) = explode(':', $time);

	$timeok = false;
	$dateok = false;

	if ($hour != '')
		$timeok = $min != '' && ctype_digit($hour) && ctype_digit($min) && $hour < 24 && min < 60;
	else
		$timeok = $min == '';

	if ($dd != '' && $mm != '' && $yy != '')
	  $dateok = checkdate($mm, $dd, $yy);

  return $dateok && $timeok;
}

function getPostVar($name)
{
	return htmlspecialchars($_POST[$name]);
}

function escapeVar($name)
{
	return htmlspecialchars($name);
}

function sqlEscapePostVar($name)
{
	return mysql_real_escape_string($_POST[$name]);
}

function strip_gpc_slashes ($input)
{
	if (!get_magic_quotes_gpc() || (!is_string($input) && !is_array($input)))
		return $input;

	if (is_string($input))
		$output = stripslashes($input);
	elseif (is_array($input))
	{
		$output = array();
		foreach ($input as $key => $val)
		{
			$new_key = stripslashes($key);
			$new_val = strip_gpc_slashes($val);
			$output[$new_key] = $new_val;
		}
	}

	return $output;
}

function sqlDate($dstr)
{
	$args = array();
	$re = '/^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{1,4})$/';
	if (preg_match($re, $dstr, $args))
	{
		$d = (int)$args[1];
		$m = (int)$args[2];
		$y = (int)$args[3];
		if (!($d < 1 || $d > 31 || $m < 1 || $m > 12 || $y < 1000))
			return sprintf("%04d-%02d-%02d", $y, $m, $d);
	}
	return '';
}

function sqlDateTime($dtstr)
{
	$args = array();
	$re = '/^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{1,4})( ([0-9]{1,2}:([0-9]{2})))?$/';
	if (preg_match($re, $dtstr, $args))
	{
		$d = (int)$args[1];
		$m = (int)$args[2];
		$y = (int)$args[3];
		$h = (int)$args[5];
		$mi = (int)$args[6];
		if (!($d < 1 || $d > 31 || $m < 1 || $m > 12 || $y < 1000))
			return sprintf("%04d-%02d-%02d %02d:%02d", $y, $m, $d, $h, $mi);
	}
	return '';
}

function parseDateTime($str)
{
	$year = substr($str, 0, 4);
	$mon = substr($str, 5, 2);
	$day = substr($str, 8, 2);
	$hour = substr($str, 11, 2);
	$min = substr($str, 14, 2);
	$sec = 0;
	$stamp = mktime($hour, $min, $sec, $mon, $day, $year);
	return array
	(
		'year' => $year,
		'month' => $mon,
		'day' => $day,
		'hour' => $hour,
		'min' => $min,
		'stamp' => $stamp
	);
}

function parseDate($str)
{
	return parseDateTime($str . ' 00:00');
}

function fileExtension($filename)
{
	return ereg('\.([^.]*)$', $filename, $v) ? $v[1] : '';
}

function redirectPage($to, $code = 303)
{
	if (headers_sent()) throw new Exception('Pokus o redirekci po odeslání HTTP hlavičky.');

	$schema = $_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http';
	$host = strlen($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];

	if ($to[0] != '/')
	{
		$dirname = dirname($_SERVER['PHP_SELF']);
		if ($dirname == '/') $to = "/$to";
		else $to = "$dirname/$to";
	}

	switch($code)
	{
		case 301: header("HTTP/1.1 301 Moved Permanently"); break;
		case 302: header("HTTP/1.1 302 Found"); break;
		case 307: header('HTTP/1.1 307'); break;
		case 303:
		default: header("HTTP/1.1 303 See Other"); break; // Use when redirecting POST
	}
	header("Location: $schema://$host$to");
	exit();
}

function fileSizeHR($size)
{
	$i = 0;
	$iec = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	while (($nsize = $size / 1024) > 1)
	{
		$size = $nsize;
		$i++;
	}
	return str_replace('.', ',', round($size, 1) . ' ' . $iec[$i]);
}

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
function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true)
{
		$both_ends = $ltrim && $rtrim;

		$char_class_inner = preg_replace(
				array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
				array( '\\\\\\0', '\\' ),
				$charlist
		);

		$work_horse = '[' . $char_class_inner . ']+';
		$ltrim && $left_pattern = '^' . $work_horse;
		$rtrim && $right_pattern = $work_horse . '$';

		if($both_ends)
		{
				$pattern_middle = $left_pattern . '|' . $right_pattern;
		}
		elseif($ltrim)
		{
				$pattern_middle = $left_pattern;
		}
		else
		{
				$pattern_middle = $right_pattern;
		}

	return preg_replace("/$pattern_middle/usSD", '', $string);
}

date_default_timezone_set("Europe/Prague");
setlocale(LC_ALL, 'cs_CZ.UTF-8');
setlocale(LC_NUMERIC, 'en_US.UTF-8');
?>
