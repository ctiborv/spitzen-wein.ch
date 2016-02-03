<?php
class Query_String
{
	protected $vars;

	public function __construct($str = '')
	{
		$this->vars = new Var_Pool;

		if (is_string($str))
		{
			if ($str !== '')
			{
				if (substr($str, 0, 1) == '?')
					$ar = explode('&', substr($str, 1));
				else
					$ar = explode('&', $str);

				foreach ($ar as $def)
				{
					$dfa = explode('=', $def, 2);
					$var = $dfa[0];
					if ($var === '') continue;
					$val = (array_key_exists(1, $dfa)) ? $dfa[1] : null;
					$this->vars->register($dfa[0], $val, false);
				}
			}
		}
	}

	public function __clone()
	{
		$this->vars = clone $this->vars;
	}

	public function get($name)
	{
		return $this->vars->get($name);
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	public function set($name, $value)
	{
		$this->vars->register($name, $value, false);
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	public function remove($name)
	{
		$this->vars->unregister($name, false);
	}

	public function __toString()
	{
		$ar = $this->vars->toArray();
		$qsa = array();
		foreach ($ar as $key => $val)
		{
			if ($val !== null)
				$qsa[] = $key . '=' . $val;
			else
				$qsa[] = $key;
		}
		
		if (empty($qsa)) return '';
		return '?' . implode('&', $qsa);
	}
}
?>