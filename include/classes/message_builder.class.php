<?php
abstract class Message_Builder
{
	public function __construct()
	{
	}

	protected function getConfigValue($key)
	{
		$conf = new Project_Config;
		return isset($config->$key) ? $config->$key : NULL;
	}

	public function build(array $data)
	{
		$html = <<<EOT
{$this->getHtmlBegin($data)}
{$this->getHtmlHead($data)}
{$this->getHtmlBody($data)}
{$this->getHtmlEnd($data)}

EOT;
		return $html;
	}

	abstract protected function getHtmlTitle(array $data);

	abstract protected function getBodyHeader(array $data);

	abstract protected function getBodyContent(array $data);

	abstract protected function getBodyFooter(array $data);

	public function getHtmlBody(array $data)
	{
		$html = <<<EOT
{$this->getBodyHeader($data)}
{$this->getBodyContent($data)}
{$this->getBodyFooter($data)}

EOT;
		return $html;
	}

	protected function getHtmlBegin(array $data)
	{
		return <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">

EOT;
	}

	protected function getHtmlHead(array $data)
	{
		if (!isset($data['htmlTitle'])) {
			$title = $this->getHtmlTitle($data);
		}

		return <<<EOT
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="..." lang="cs" />
<meta name="keywords" content="..." lang="cs" />
<meta name="author" content="Antonín Sokola, Tomáš Windsor" />
<meta name="copyright" content="Artprodesign.com" />
<title>$title</title>
</head>

EOT;
	}

	protected function getHtmlEnd(array $data)
	{
		return <<<EOT
</html>

EOT;
	}
}
