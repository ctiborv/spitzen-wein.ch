<group active="0">
	<flow eid="novinka_vzor">
		<div class="news">
			<div>
				<datetime eid="datum" template="/datetime/teloL-aktuality" />
				<h3>
					<inline eid="ma_detail"><a eid="detail" title="weiter lesen"><text eid="nadpis" /></a></inline>
					<inline eid="nema_detail"><span><text eid="nadpis" /></span></inline>
				</h3>
				<br class="clear" />
			</div>
			<div class="news-obsah">
				<inline eid="obsah" />
			</div>
			<br class="clear" />
		</div>
	</flow>
</group>
<div class="teloL-news">
<flow eid="zadne_novinky" ></flow>
<flow eid="prazdna_stranka" />
<flow eid="novinky" />
<h2>Neuheiten</h2>
<pagination eid="dolni_paginace" vid="paginace" case="bottom" radius="2" template="pagination/teloL-aktuality" />
</div>