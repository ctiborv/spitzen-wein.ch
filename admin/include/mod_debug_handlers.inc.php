<?php 

if (defined('MODULE_MOD_DEBUG_HANDLERS'))
	user_error('Module mod_debug_handlers is already included', E_USER_ERROR);
else
	define('MODULE_MOD_DEBUG_HANDLERS','1.0');


class PhpError extends exception
{
	protected $php_error_code;
	protected $php_error_code_str;
	protected $php_error_message;
	protected $php_error_file;
	protected $php_error_line;

	public function __construct($errno, $errstr, $file, $line)
	{
		switch ($errno)
		{
			case E_ERROR:           $str_type = 'E_ERROR'; break;
			case E_WARNING:         $str_type = 'E_WARNING'; break;
			case E_PARSE:           $str_type = 'E_PARSE'; break;
			case E_NOTICE:          $str_type = 'E_NOTICE'; break;
			case E_CORE_ERROR:      $str_type = 'E_CORE_ERROR'; break;
			case E_CORE_WARNING:    $str_type = 'E_CORE_WARNING'; break;
			case E_COMPILE_ERROR:   $str_type = 'E_COMPILE_ERROR'; break;
			case E_COMPILE_WARNING: $str_type = 'E_COMPILE_WARNING'; break;
			case E_USER_ERROR:      $str_type = 'E_USER_ERROR'; break;
			case E_USER_WARNING:    $str_type = 'E_USER_WARNING'; break;
			case E_USER_NOTICE:     $str_type = 'E_USER_NOTICE'; break;
			case E_STRICT:          $str_type = 'E_STRICT'; break;
			default:                $str_type = sprintf('E_%d',$errno); break;
		}

		$message = sprintf('%s: %s at [%s:%d]', $str_type, $errstr, $file, $line);
		parent::__construct($message, $errno);

		$this->php_error_code = $errno;
		$this->php_error_code_str = $str_type;
		$this->php_error_message = $errstr;
		$this->php_error_file = $file;
		$this->php_error_line = $line;
	}
}


function __default_handler_echo_row($name, $value)
{
	$name = (string)$name;
	$value = (string)$value;
	printf
	(
		"<tr><th align=\"left\" valign=\"top\">%s</th><td>%s</td></tr>\n",
		$name != '' ? htmlspecialchars($name) : '&nbsp',
		$value != '' ? htmlspecialchars($value) : '&nbsp;'
	);
}


function __default_error_handler($errno, $errstr, $file, $line)
{
	if (error_reporting() == 0)
		return;

	if (!headers_sent())
		header("Content-Type: text/html; charset=UTF-8");

	echo <<<EOT

<br clear="all">
<table align="center" cellspacing="0" cellpadding="3" bgcolor="#FFFFF0" border="1" bordercolor="#0C4EAF">
<tr bgcolor="#FBE342">
	<th align="left" valign="top" colspan="2">PHP error</th>
</tr>

EOT;

	switch ($errno)
	{
		case E_ERROR:           $str_type = 'E_ERROR'; break;
		case E_WARNING:         $str_type = 'E_WARNING'; break;
		case E_PARSE:           $str_type = 'E_PARSE'; break;
		case E_NOTICE:          $str_type = 'E_NOTICE'; break;
		case E_CORE_ERROR:      $str_type = 'E_CORE_ERROR'; break;
		case E_CORE_WARNING:    $str_type = 'E_CORE_WARNING'; break;
		case E_COMPILE_ERROR:   $str_type = 'E_COMPILE_ERROR'; break;
		case E_COMPILE_WARNING: $str_type = 'E_COMPILE_WARNING'; break;
		case E_USER_ERROR:      $str_type = 'E_USER_ERROR'; break;
		case E_USER_WARNING:    $str_type = 'E_USER_WARNING'; break;
		case E_USER_NOTICE:     $str_type = 'E_USER_NOTICE'; break;
		case E_STRICT:          $str_type = 'E_STRICT'; break;
		default:                $str_type = sprintf('E_%d',$errno); break;
	}

	__default_handler_echo_row('Message', $errstr);
	__default_handler_echo_row('Code', $str_type);
	__default_handler_echo_row('File', $file);
	__default_handler_echo_row('Line', $line);
// throw new PhpError($errno, $errstr, $file, $line);

	echo <<<EOT
</table>
<br clear="all">

EOT;
}


function __default_exception_handler($exception)
{
	//set_error_handler(NULL);
	if (!headers_sent())
		header("Content-Type: text/html; charset=UTF-8");

	echo <<<EOT

<table align="center" cellspacing="0" cellpadding="3" bgcolor="#FFFFF0" border="1" bordercolor="#0C4EAF">
<tr bgcolor="#FBE342">
	<th align="left" valign="top" colspan="2">Unhandled exception</th>
</tr>

EOT;

	if (is_object($exception))
	{
		__default_handler_echo_row('Type', 'class ' . get_class($exception));
		if ($exception instanceof Exception)
		{
			__default_handler_echo_row('Message', $exception->getMessage());
			__default_handler_echo_row('Code', $exception->getCode());
			__default_handler_echo_row('File', $exception->getFile());
			__default_handler_echo_row('Line', $exception->getLine());
			$trace_array = $exception->getTrace();
			if (count($trace_array) > 0)
			{
				echo '<tr bgcolor="#FBE342"><th align="left" valign="top" colspan="2">Stack trace</th></tr>'."\n";
				foreach($trace_array as $order => $item)
				{
					$function = isset($item['function']) ? $item['function'] : '(?unknown function?)';
					$file = isset($item['file']) ? $item['file'] : '(?unknown file?)';
					$line = isset($item['line']) ? $item['line'] : '(?unknown line?)';
					__default_handler_echo_row($order, "'$function' at [$file:$line]");
				}
			}
		}
	}
	else
		__default_handler_echo_row('Typ výjimky', gettype($exception));

	echo <<<EOT
</table>
<br clear="all">

EOT;
}


set_error_handler('__default_error_handler');
set_exception_handler('__default_exception_handler');

?>
