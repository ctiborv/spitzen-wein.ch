<?php
require_once 'page_item.class.php';

class Kiwi_Footer extends Page_Item
{
	protected $artpro_web = 'http://www.artprodesign.com';
	protected $artpro_str = 'artprodesign.com';
	protected $artpro_email_mailto = 'info&#064;artprodesign.com';
	protected $artpro_email_str = 'info&#064;artprodesign.com';

	function __construct()
	{
		parent::__construct();
	}

	function _getHTML()
	{
		$html = <<<EOT
<div id="pata">
	<div id="pataL">&copy; RS Kiwi 2012 - <a href="$this->artpro_web" title="" onclick="window.open('$this->artpro_web','_blank'); return false">$this->artpro_str</a></div>
	<div id="pataP">Support: <a href="mailto:$this->artpro_email_mailto" title="">$this->artpro_email_str</a></div>
</div>

EOT;
		return $html;
	}

	function handleInput($get, $post)
	{
	}
}
?>