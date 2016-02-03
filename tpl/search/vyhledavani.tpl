<form render="js" method="get" action="/katalog">
	<div class="search">
		<h2>Suche Begriff eingeben</h2>
		<input eid="vyhledavani" name="s" value="Suchen" class="inp16" onfocus="wakeInput(this,'Suchen')" onblur="sleepInput(this,'Suchen')" />
		<autoinput formid="vyhledavani" eid="odeslat" type="submit" value="" class="but16" onclick="return Mailer_Form.onSubmit('jsv')"  />
	</div>
</form>