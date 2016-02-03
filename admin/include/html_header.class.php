<?php
class HTML_Header
{
	protected $header_vars = array();
	protected $css_styles = array();
	protected $js_files = array();
	protected $nocache;
	public $default_css = './styles/style.css';

	function __construct()
	{
		$this->header_vars['lang'] = 'cs';
		$this->header_vars['charset'] = 'utf-8';
		$this->header_vars['author'] = '...';
		$this->header_vars['copyright'] = '...';
		$this->header_vars['description'] = '...';
		$this->header_vars['keywords'] = array();
		$this->nocache = false;
	}

	public function __set($name, $val)
	{
		switch ($name)
		{
			case 'lang':
			case 'title':
			case 'author':
			case 'copyright':
			case 'description':
			case 'keywords':
				$this->header_vars[$name] = $val; break;
			default: throw new Exception('Došlo k pokusu o nastavení neexistující vlastnosti objektu třídy ' . __CLASS__);
		}
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'lang':
			case 'title':
			case 'author':
			case 'copyright':
			case 'description':
			case 'keywords':
				return $this->header_vars[$name];
			default: throw new Exception('Došlo k pokusu o čtení neexistující vlastnosti objektu třídy ' . __CLASS__);
		}
	}

	public function addKeyword($keywords)
	{
		if (is_array($keywords)) $this->header_vars['keywords'] = array_merge($this->header_vars['keywords'], $keywords);
		else
			$this->header_vars['keywords'] = array_merge($this->header_vars['keywords'], explode(' ', $keywords));
	}

	public function addCSS($file)
	{
		if (is_array($file))
			foreach ($file as $f)
				$this->css_styles[] = $f;
		else
			$this->css_styles[] = $file;
	}

	public function addJS($file)
	{
		if (is_array($file))
			foreach ($file as $f)
				$this->js_files[] = $f;
		else
			$this->js_files[] = $file;
	}

	public function send()
	{
		$keywords = (sizeof($this->header_vars['keywords']) > 0) ? implode(',', $this->header_vars['keywords']) : '...';

		header("Content-Type: text/html; charset={$this->header_vars['charset']}");
		if ($this->nocache)
		{
			header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
			header("Pragma: no-cache");                          // HTTP/1.0
		}
		header("keywords: $keywords");

		$html =	"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd\">\n" .
		"<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->header_vars['lang']}\" lang=\"{$this->header_vars['lang']}\">\n" .
		"<head>\n" .
		"<meta http-equiv=\"Content-Type\" content=\"text/html; charset={$this->header_vars['charset']}\" />\n" .
		"<meta name=\"description\" content=\"{$this->header_vars['description']}\" lang=\"{$this->header_vars['lang']}\" />\n" .
		"<meta name=\"keywords\" content=\"$keywords\" lang=\"{$this->header_vars['lang']}\" />\n" .
		"<meta name=\"author\" content=\"{$this->header_vars['author']}\" />\n" .
		"<meta name=\"copyright\" content=\"{$this->header_vars['copyright']}\" />\n" .
		"<title>{$this->header_vars['title']}</title>\n";

		if (sizeof($this->css_styles) > 0)
			foreach ($this->css_styles as $value)
				$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$value\" />\n";
		else $html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$this->default_css\" />\n";

		foreach ($this->js_files as $value)
			$html .= "<script type=\"text/javascript\" src=\"$value\"></script>\n"; // IE doesn't handle <script /> syntax

		$html .= "</head>\n";
		echo $html;
	}
}
?>