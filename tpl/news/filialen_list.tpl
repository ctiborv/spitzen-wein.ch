<group active="0">
	<flow eid="novinka_vzor">
		<div class="prodejna">
			<div>
				<datetime active="0" eid="datum" template="/datetime/teloL-aktuality" />
				<h3 class="prodejna-nadpis">
					<inline eid="ma_detail"><a eid="detail" title="weiter zum Detail"><text eid="nadpis" /><br /><span>weiter zum Detail</span></a></inline>
					<inline eid="nema_detail"><text eid="nadpis" /></inline>
				</h3>
				<br class="clear" />
			</div>
			<div class="prodejna-obsah">
				<inline eid="obsah" />
			</div>
			<br class="clear" />
		</div>
	</flow>
</group>
<div class="telo-prodejny">
<h2>Unsere Filialen</h2>
<flow eid="zadne_novinky" />
<flow eid="prazdna_stranka" />
<flow eid="novinky" />
<pagination eid="dolni_paginace" vid="paginace" case="bottom" radius="2" template="pagination/teloL-aktuality" />
</div>