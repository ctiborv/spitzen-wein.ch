<?php
require_once 'utils.inc.php';

class Kiwi_LastChange
{
	protected $section;
	protected $items;
	protected $timestamp;
	protected $default_format;

	public function __construct($section, $default_format = 'j.n.Y H:i')
	{
		$this->default_format = $default_format;
		$this->timestamp = null;

		if (is_array($section))
		{
			$this->section = mysql_real_escape_string($section[0]);
			if (is_array($section[1]))
				$this->items = $section[1];
			else
				$this->items = array((int)$section[1]);
		}
		else
		{
			$this->section = mysql_real_escape_string($section);
			$this->items = null;
		}
	}

	public function format($format = '')
	{
		if ($this->timestamp == null)
			throw new Exception('Pokus o naformátování času bez jeho dřívejšího načtení z databáze.');
		return date($format == '' ? $this->default_format : $format, $this->timestamp);
	}

	public function acquire()
	{
		if ($this->items == null)
		{
			$result = mysql_query("SELECT `When` FROM lastchanges WHERE Section='$this->section'");
			$row = mysql_fetch_row($result);
			if ($row == false)
				throw new Exception("Chyba při získávání datumu poslední změny - sekce: $this->section");
		}
		else
		{
			$item = $this->items[0];
			$result = mysql_query("SELECT `When` FROM lastchanges WHERE Section='$this->section' AND Item=$item");
			if (!($row = mysql_fetch_row($result)))
				$row = array("2000-01-01 00:00:00");
		}

		$dt = parseDateTime($row[0]);
		$this->timestamp = $dt['stamp'];
	}

	public function register()
	{
		if ($this->items == null)
		{
			if (!mysql_query("UPDATE lastchanges SET `When`=CURRENT_TIMESTAMP WHERE Section='$this->section'"))
				throw new Exception("Chyba při zapisování datumu poslední změny - sekce: $this->section");
		}
		else
		{
			foreach ($this->items as $item)
			{
				$result =	mysql_query("SELECT COUNT(*) FROM lastchanges WHERE Section='$this->section' AND Item=$item");
				$row = mysql_fetch_row($result);
				if ((int)$row[0])
				{
					if (!mysql_query("UPDATE lastchanges SET `When`=CURRENT_TIMESTAMP WHERE Section='$this->section' AND Item=$item"))
						throw new Exception("Chyba při ukládání datumu poslední změny - sekce: $this->section, položka: $item");
				}
				else
				{
					if (!mysql_query("INSERT INTO lastchanges (Section,Item) VALUES ('$this->section',$item)"))
						throw new Exception("Chyba při ukládání datumu poslední změny - sekce: $this->section, položka: $item");
				}
			}
		}
	}

	public function delete()
	{
		if ($this->items == null)
		{
			if (!mysql_query("DELETE FROM lastchanges WHERE Section='$this->section'"))
				throw new Exception("Chyba při mazání z tabulky lastchanges - sekce: $this->section");
		}
		else
		{
			if (!mysql_query("DELETE FROM lastchanges WHERE Section='$this->section' AND Item IN (" . implode(',', $this->items) . ")"))
				throw new Exception("Chyba při mazání z tabulky lastchanges - sekce: $this->section, položky: " . implode(',', $this->items));
		}
	}
}
?>