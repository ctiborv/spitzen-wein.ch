<require>
	<javascript src="/js/newslettersubscriber_form.js" />
</require>
<form render="js" method="post" action="" id="prihlaseni_k_odberu">
	<div>
		<input type="hidden" eid="identification" name="newslettersubscriber" value="1" />
		<autoinput formid="newslettersubscriber" eid="hidden" id="jsv" name="jsv" type="hidden" />
		<span>Email:</span>
		<autoinput formid="newslettersubscriber" eid="trap" name="femail" type="text" value="@" class="iinp2" />
		<autoinput formid="newslettersubscriber" eid="email" name="email" type="text" value="@" class="inp2" /><inline eid="validation" specification="email/missing">... Email je povinná položka</inline><inline eid="validation" specification="email/invalid">... Neplatná emailová adresa</inline><inline eid="validation" specification="email/ok">... Emailová adresa vyplněna v pořádku</inline>
		<br /><newline />
		<autoinput formid="newslettersubscriber" eid="trap" name="fsubmit" type="submit" value="Přihlásit k odběru" class="ibut5" onclick="return Newslettersubscriber_Form.onSubmit('jsv')" /><autoinput formid="newslettersubscriber" eid="odeslat" name="submit" type="submit" value="Přihlásit k odběru" class="but5" onclick="return Newslettersubscriber_Form.onSubmit('jsv')" />
		<flow eid="validation_failed">
			<javascript>location.hash = 'prihlaseni_k_odberu';</javascript>
		</flow>
	</div>
</form>