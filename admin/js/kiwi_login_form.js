function Kiwi_Login_Form_Class()
{
	this.onSubmit = function()
	{
		username = document.getElementById('klfc_username');
		if (username.value == '')
		{
			alert("Posten 'Name' ist obligatorisch!");
			username.focus();
			return false;
		}
		password = document.getElementById('klfc_password');
		if (password.value == '')
		{
			alert("Posten 'Passwort' ist obligatorisch!");
			password.focus();
			return false;
		}
		return true;
	}
}

var Kiwi_Login_Form = new Kiwi_Login_Form_Class();