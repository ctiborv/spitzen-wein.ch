function Kiwi_EShopMail_Form_Class()
{
	this.reEmail = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/

	this.onSubmit = function()
	{
		sendto = document.getElementById('kemf_sendto');
		if (sendto.value == '')
		{
			alert('E-Mail-Adresse ist obligatorisch!');
			sendto.focus();
			return false;
		}

		if (!this.reEmail.test(sendto.value.toLowerCase()))
		{
			alert('E-Mail-Adresse ist nicht korrekt!');
			sendto.focus();
			return false;			
		}

		subject = document.getElementById('kemf_subject');
		if (subject.value == '')
		{
			alert('Der Gegenstand ist obligatorisch!');
			subject.focus();
			return false;
		}

		message = document.getElementById('kemf_message');
		if (message.value == '')
		{
			alert('Nachrichtentext ist obligatorisch!');
			message.focus();
			return false;
		}

		return true;
	}
}

var Kiwi_EShopMail_Form = new Kiwi_EShopMail_Form_Class();

