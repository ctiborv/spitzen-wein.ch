<require>
	<javascript src="/js/mailer_form.js" />
</require>
<form render="js" method="post" action="" id="vzkaz">
	<div>
		<input type="hidden" eid="identification" name="mailer" value="1" />
		<autoinput formid="mailer" eid="hidden" id="jsv" name="jsv" type="hidden" />
		<span>Jméno:</span>
		<autoinput formid="mailer" eid="trap" name="fjmeno" type="text" value="" class="iinp2" />
		<autoinput formid="mailer" eid="jmeno" name="jmeno" type="text" value="" class="inp2" /><inline eid="validation" specification="jmeno/missing">... Jméno je povinná položka</inline><inline eid="validation" specification="jmeno/ok">... Jméno vyplněno v pořádku</inline>
		<br /><newline />
		<span>Telefon:</span>
		<autoinput formid="mailer" eid="trap" name="fphone" type="text" value="+420" class="iinp2" />
		<autoinput formid="mailer" eid="telefon" name="phone" type="text" value="+420" class="inp2" /><inline eid="validation" specification="telefon_syntax/invalid">... Neplatné telefonní číslo</inline>
		<br /><newline />
		<span>Email:</span>
		<autoinput formid="mailer" eid="trap" name="femail" type="text" value="@" class="iinp2" />
		<autoinput formid="mailer" eid="email" name="email" type="text" value="@" class="inp2" /><inline eid="validation" specification="email/missing">... Email je povinná položka</inline><inline eid="validation" specification="email/invalid">... Neplatná emailová adresa</inline><inline eid="validation" specification="email/ok">... Emailová adresa vyplněna v pořádku</inline>
		<br /><newline />
		<span>Vzkaz:</span>
		<autotextarea formid="mailer" eid="trap" name="fvzkaz" class="itextarea" rows="0" cols="0" />
		<autotextarea formid="mailer" eid="vzkaz" name="vzkaz" class="textarea" rows="0" cols="0" /><newline /><inline eid="validation" specification="vzkaz/missing">... Vzkaz je povinná položka</inline>
		<autoinput formid="mailer" eid="trap" name="fsubmit" type="submit" value="Odeslat Fake" class="ibut5" onclick="return Mailer_Form.onSubmit('jsv')" /><autoinput formid="mailer" eid="odeslat" name="submit" type="submit" value="Odeslat zprávu" class="but5" onclick="return Mailer_Form.onSubmit('jsv')" /><input type="reset" value="ZRUŠIT" class="but3" />
		<div eid="validation_passed">Kontrola formuláře proběhla úspěšně.</div>
		<flow eid="validation_failed">
			<div>Formulář obsahuje nepovolené vstupní hodnoty. (<inline eid="validation_failures" />)</div>
			<javascript>location.hash = 'vzkaz';</javascript>
		</flow>
	</div>
</form>