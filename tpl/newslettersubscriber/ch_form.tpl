<require>
	<javascript src="/js/newslettersubscriber_form.js" />
</require>
<form render="js" method="post" action="" id="prihlaseni_k_odberu">
	<div class="newsletter">
		<input type="hidden" eid="identification" name="newslettersubscriber" value="1" />
		<autoinput formid="newslettersubscriber" eid="hidden" id="jsv" name="jsv" type="hidden" />
		<h2>Zum Newsletter anmelden</h2>
		<span>Newsletter abonnieren - neue Angebote und aktuelle Rabatte nicht verpassen!</span>
		<div class="newsletter-form">
			<autoinput formid="newslettersubscriber" eid="trap" name="femail" type="text" value="@" class="iinp-nwsletter" />
			<autoinput formid="newslettersubscriber" eid="email" name="email" type="text" value="@" class="inp-nwsletter" /><inline eid="validation" specification="email/missing">... Email je povinná položka</inline><inline eid="validation" specification="email/invalid">... Neplatná emailová adresa</inline><inline eid="validation" specification="email/ok">... Emailová adresa vyplněna v pořádku</inline>
			<autoinput formid="newslettersubscriber" eid="trap" name="fsubmit" type="submit" value="Senden" class="ibut5" onclick="return Newslettersubscriber_Form.onSubmit('jsv')" /><autoinput formid="newslettersubscriber" eid="odeslat" name="submit" type="submit" value="Senden" class="btn-nwsletter" onclick="return Newslettersubscriber_Form.onSubmit('jsv')" />
			<flow eid="validation_failed">
				<javascript>location.hash = 'prihlaseni_k_odberu';</javascript>
			</flow>
		</div>
	</div>
</form>