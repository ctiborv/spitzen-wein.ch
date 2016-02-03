<group active="0">
	<article vid="ok"><img src="/img/ok.png" alt="ok" /></article>
	<article vid="error"><img src="/img/error.png" alt="error" /></article>
</group>
<require active="0">
	<javascript src="/js/mailer_form.js" />
</require>
	<flow eid="validation_failed">
		<flow active="0">Formulář obsahuje nepovolené vstupní hodnoty. (<inline eid="validation_failures" />)</flow>
		<javascript>location.hash = 'vzkaz';</javascript>
	</flow>
<div class="kontakt-formular" id="vzkaz">
		<flow eid="validation_failed">
			<div class="formular-chyby">Formulář obsahuje (<inline eid="validation_failures" />) chyby. Prosím vyplňte údaje označené červeným vykřičníkem (<strong class="error">!</strong>). Děkujeme.</div>
			<javascript>location.hash = 'vzkaz';</javascript>
		</flow>
	<form render="js" method="post" action="">
		<div>
			<input type="hidden" eid="identification" name="mailer" value="1" />
			<autoinput formid="mailer" eid="hidden" id="jsv" name="jsv" type="hidden" />
			<div class="kontakt-jmeno">
				<span>Jméno: <inline eid="validation" specification="jmeno/missing"><strong class="error">!</strong></inline><inline eid="validation" specification="jmeno/ok"></inline></span>
				<autoinput formid="mailer" eid="trap" name="fjmeno" type="text" value="" class="iinp2" />
				<autoinput formid="mailer" eid="jmeno" name="jmeno" type="text" value="" class="inp2" />
			</div>
			<div class="kontakt-telefon">
				<span>Telefon: <inline eid="validation" specification="telefon_syntax/invalid"><strong class="error">!</strong></inline><inline eid="validation" specification="telefon_syntax/ok"></inline></span>
				<autoinput formid="mailer" eid="trap" name="ftelefon" type="text" value="+420" class="iinp2" />
				<autoinput formid="mailer" eid="telefon" name="telefon" type="text" value="+420" class="inp2" />
			</div>
			<div class="kontakt-email">
				<span>E-mail: <inline eid="validation" specification="email/missing"><strong class="error">!</strong></inline><inline eid="validation" specification="email/invalid"><strong class="error">!</strong></inline><inline eid="validation" specification="email/ok"></inline></span>
				<autoinput formid="mailer" eid="trap" name="femail" type="text" value="@" class="iinp2" />
				<autoinput formid="mailer" eid="email" name="email" type="text" value="@" class="inp2" />
			</div>
			<br class="clear" />
			<div class="kontakt-vzkaz">
				<span>Vzkaz: <inline eid="validation" specification="vzkaz/missing"><strong class="error">!</strong></inline><inline eid="validation" specification="vzkaz/ok"></inline></span>
				<autotextarea formid="mailer" eid="trap" name="fvzkaz" class="itextarea" rows="0" cols="0" />
				<autotextarea formid="mailer" eid="vzkaz" name="vzkaz" class="textarea" rows="0" cols="0" />
			</div>
			<br class="clear" />
			<autoinput formid="mailer" eid="odeslat" name="submit" type="submit" value="Odeslat" class="but5" onclick="return Mailer_Form.onSubmit('jsv')" />
			<autoinput formid="mailer" eid="trap" name="fsubmit" type="submit" value="Odeslat Fake" class="ibut5" onclick="return Mailer_Form.onSubmit('jsv')" />
			<input type="reset" value="ZRUŠIT" class="but3" />
			<div eid="validation_passed"><article vid="ok" />Ok</div>
			<br class="clear" />
		</div>
	</form>
	<br class="clear" />
</div>