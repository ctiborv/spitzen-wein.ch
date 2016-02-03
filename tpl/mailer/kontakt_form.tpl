<group active="0">
	<article vid="ok"><img src="/img/ok.png" alt="ok" /></article>
	<article vid="error"><img src="/img/error.png" alt="error" /></article>
</group>
<require active="0">
	<javascript src="/js/mailer_form.js" />
</require>
<div class="kontakt-formular">
	<h2>KONTAKTIEREN SIE UNS</h2>
		<p class="text">Wir sind bestrebt unseren Kunden den besten Service und viel Freude am Einkaufen zu bieten. Deshalb schätzen wir Ihre Kommentare und Anregungen. Gerne beantworten wir Ihre Fragen.</p>
		<flow eid="validation_failed">
			<flow active="0">Das Formular enthält ungültige Eingabewerte. (<inline eid="validation_failures" />)</flow>
			<javascript>location.hash = 'vzkaz';</javascript>
		</flow>
	<div class="formular" id="vzkaz">
			<flow eid="validation_failed">
				<div class="formular-chyby">Das Formular enthält (<inline eid="validation_failures" />) Fehler. Bitte füllen Sie die Felder mit einem roten ausrufezeichen gekennzeichnet (<strong class="error">!</strong>). Danke.</div>
				<javascript>location.hash = 'vzkaz';</javascript>
			</flow>
		<form render="js" method="post" action="">
			<div>
				<input type="hidden" eid="identification" name="mailer" value="1" />
				<autoinput formid="mailer" eid="hidden" id="jsv" name="jsv" type="hidden" />
				<div class="kontakt-jmeno">
					<span>Name: <inline eid="validation" specification="jmeno/missing"><strong class="error">!</strong></inline><inline eid="validation" specification="jmeno/ok"></inline></span>
					<autoinput formid="mailer" eid="trap" name="fjmeno" type="text" value="" class="iinp2" />
					<autoinput formid="mailer" eid="jmeno" name="jmeno" type="text" value="" class="inp2" />
				</div>
				<div class="kontakt-telefon">
					<span>Telefon: <inline eid="validation" specification="telefon_syntax/invalid"><strong class="error">!</strong></inline><inline eid="validation" specification="telefon_syntax/ok"></inline></span>
					<autoinput formid="mailer" eid="trap" name="ftelefon" type="text" value="" class="iinp2" />
					<autoinput formid="mailer" eid="telefon" name="telefon" type="text" value="" class="inp2" />
				</div>
				<div class="kontakt-email">
					<span>E-mail: <inline eid="validation" specification="email/missing"><strong class="error">!</strong></inline><inline eid="validation" specification="email/invalid"><strong class="error">!</strong></inline><inline eid="validation" specification="email/ok"></inline></span>
					<autoinput formid="mailer" eid="trap" name="femail" type="text" value="@" class="iinp2" />
					<autoinput formid="mailer" eid="email" name="email" type="text" value="@" class="inp2" />
				</div>
				<br class="clear" />
				<div class="kontakt-vzkaz">
					<span>Ihre Nachricht: <inline eid="validation" specification="vzkaz/missing"><strong class="error">!</strong></inline><inline eid="validation" specification="vzkaz/ok"></inline></span>
					<autotextarea formid="mailer" eid="trap" name="fvzkaz" class="itextarea" rows="0" cols="0" />
					<autotextarea formid="mailer" eid="vzkaz" name="vzkaz" class="textarea" rows="0" cols="0" />
				</div>
				<br class="clear" />
				<autoinput formid="mailer" eid="odeslat" name="submit" type="submit" value="Senden" class="but5" onclick="return Mailer_Form.onSubmit('jsv')" />
				<autoinput formid="mailer" eid="trap" name="fsubmit" type="submit" value="Odeslat Fake" class="ibut5" onclick="return Mailer_Form.onSubmit('jsv')" />
				<input type="reset" value="ZRUŠIT" class="but3" />
				<div eid="validation_passed"><article vid="ok" />Ok</div>
				<br class="clear" />
			</div>
		</form>
		<br class="clear" />
	</div>
</div>