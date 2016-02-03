<?php
require_once 'html_header.class.php';

class Kiwi_HTML_Print_Header extends HTML_Header
{
	public function __construct()
	{
		parent::__construct();
		$this->header_vars['lang'] = 'cs';
		$this->header_vars['charset'] = 'utf-8';
		$this->header_vars['author'] = 'www.artprodesign.com';
		$this->header_vars['copyright'] = 'artprodesign.com';
		$this->nocache = false;
		$this->addCSS('./styles/print.css');
	}
}
?>
