function Mailer_Form_Class()
{
	this.reEmail = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/
	this.rePhone = /^(\+[0-9]{3} ?)?[0-9]{9}$/

	this.onSubmit = function(hidden)
	{
		var e_hidden = document.getElementById(hidden);

		if (e_hidden)
			e_hidden.value = 3;

		return true;
	}
}

var Mailer_Form = new Mailer_Form_Class();
