<group active="0">
	<flow eid="novinka_vzor">
		<div class="news">
			<div>
				<datetime eid="datum" template="/datetime/teloL-aktuality" />
				<h3>
					<inline eid="ma_detail"><a eid="detail" title="celý článek"><text eid="nadpis" /></a></inline>
					<inline eid="nema_detail"><span><text eid="nadpis" /></span></inline>
				</h3>
				<br class="clear" />
			</div>
			<div class="news-obsah">
				<inline eid="obsah" />
				<br class="clear" />
			</div>
		</div>
		<div class="oznameni-vse"><navpoint page="oznameni">Zobrazit všechny oznámení</navpoint></div>
	</flow>
</group>
<div class="newsG">
<pagination eid="horni_paginace" vid="paginace" case="top" radius="2" template="pagination/teloL-aktuality" />
	<div class="news-obsah" eid="zadne_novinky"></div>
	<div class="news-obsah" eid="prazdna_stranka"></div>
	<flow eid="novinky" />
<pagination eid="dolni_paginace" vid="paginace" case="bottom" />
</div>