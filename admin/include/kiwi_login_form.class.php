<?php
require_once 'page_item.class.php';
require_once 'page_names.inc.php';

class Kiwi_Login_Form extends Page_Item
{
	protected $username;
	protected $userid;
	protected $page;

	function __construct(&$rights)
	{
		parent::__construct();
		$this->username = '';
		$this->userid = $rights->UserID;
		$page = null;
	}

	function _getHTML()
	{
		$self = basename($_SERVER['PHP_SELF']);
		if ($this->page != null) $qs = '?page=' . urlencode($this->page);
		else $qs = '';

		$html = <<<EOT
<form action="$self$qs" method="post" class="form0">
	<h2>User Login</h2>
	<div id="login">
		<p>Willkommen bei <b>Kiwi</b></p>
		<p>So verwenden Sie Ihren Benutzernamen und Ihr Passwort ein.</p>
	</div>
	<fieldset>
		<span class="span-form2">Name</span>
		<input type="text" name="Login_Jmeno" id="klfc_username" value="$this->username" class="inpOUT3" onfocus="this.className='inpON3'" onblur="this.className='inpOFF3'" />
		<span class="span-form2">Passwort</span>
		<input type="password" name="Login_Heslo" id="klfc_password" class="inpOUT3" onfocus="this.className='inpON3'" onblur="this.className='inpOFF3'" /></td>
		<input type="submit" name="cmd" value="login" class="but2" onclick="return Kiwi_Login_Form.onSubmit()"/></td>
	</fieldset>
</form>

EOT;

		return $html;
	}

	function handleInput($get, $post)
	{
		if (!empty($get))
		{
			if (isset($get['page']))
				$this->page = $get['page'];

			if (isset($get['logout']))
			{
				$this->logOut();
				return;
			}
		}

		if (!empty($post))
		{
			$username = $password = '';
			if (isset($post['Login_Jmeno']))
				$username = $post['Login_Jmeno'];
			if (isset($post['Login_Heslo']))
				$password = $post['Login_Heslo'];

			if ($username != '' && $password != '')
			{
				$this->logIn($username, $password);
				return;
			}
		}
	}

	protected function logOut()
	{
		if ($this->userid)
		{
			unset($_SESSION['user']);
			if ($this->page != null) $this->redirection = $this->page;
			else $this->redirection = KIWI_DEFAULT;
		}
	}

	protected function logIn($user, $pwd)
	{
		$this->username = $user;

		$accounts = array
		(
			'admin' => '00383286404d871249800b0481aa077e',
			'spitzen' => '00383286404d871249800b0481aa077e', // spitzen1996
			'readonly' => '00383286404d871249800b0481aa077e'
		);

		foreach ($accounts as $username => $password)
		{
			if ($user == $username && md5($pwd) == $password)
			{
				$_SESSION['user'] = $user;
				if ($this->page != null) $this->redirection = $this->page;
				else $this->redirection = KIWI_DEFAULT;
			}
		}
	}
}
?>
