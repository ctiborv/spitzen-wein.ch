<require>
	<link rel="stylesheet" type="text/css" href="/styles/katalog.css" />
</require>
<group active="0">
	<group eid="zaklad_vzory">
		<img eid="obrazek_sipka" src="/image/sipka.gif" alt="šipka" title="" width="8" height="5" />
		<img eid="obrazek_produktu" alt="" title="" width="200" height="250" />
		<img eid="obrazek_vlastnost_ikona" width="44" height="44" />
		<img eid="obrazek_vlastnost_barva" width="44" height="44" />
		<group eid="vyberova_vlastnost_prirustek_height_popup" specification="30px" />
			<navpoint eid="link_objednat" page="objednávka" popup="height=400px,width=700px,top=100px,left=100px,menubar=no,toolbar=no,resizable=no,scrollbars=no" title="objednat" class="objednat">
				<span>vložit do košíku</span>
			</navpoint>
			<flow eid="ma_vlastnost_vzor">
				<span class="atribut"><text eid="nazev_vlastnosti" />:</span>
				<span class="hodnota"><inline eid="hodnoty_vlastnosti" /></span>
			</flow>
			<flow eid="nema_vlastnost_vzor">
				<span class="atribut"><text eid="nazev_vlastnosti" />:</span>
				<span class="hodnota"><he code="ndash" /></span>
			</flow>
			<div eid="umisteni_vzor" class="lista">
				<flow eid="umisteni_obsah" />
			</div>
		</group>
	<rows eid="radek_struktura">
		<tr class="tr-kat-01"><columns eid="produkt" specification="1" /></tr>
		<tr class="tr-kat-01"><columns eid="produkt" specification="2" /></tr>
		<tr class="tr-kat-white"><td colspan="3"><hr /></td></tr>
	</rows>
	<group eid="produkt_vzory">
		<columns eid="produkt_vzor" specification="1">
			<td class="td-kat-01n">
				<h2><a eid="detail"><text eid="nazev" /></a></h2>
				<div class="kat-produkt">
					<div class="status-produkt">
						<flow eid="je_novinka">
							<span class="novinka"><strong>Neu</strong></span>
						</flow>
						<flow eid="neni_novinka" />
						<flow eid="je_akce">
							<strong class="akce"><strong>Aktion</strong></strong>
							<flow eid="ma_zmenu_ceny"><div class="procenta"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
						</flow>
						<flow eid="neni_akce" />
						<flow eid="je_sleva">
							<strong class="sleva"><strong>Sale</strong></strong>
							<flow eid="ma_zmenu_ceny"><div class="procenta"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
						</flow>
						<flow eid="neni_sleva"/>
					</div>
					<flow eid="ma_vlastnost" specification="9"><div class="raritni"><strong>Rarität</strong></div></flow><flow eid="nema_vlastnost" specification="9"></flow>
					<flow eid="ma_fotografii">
						<a eid="detail"><inline eid="fotografie" /></a>
					</flow>
					<flow eid="nema_fotografii">
						<a eid="detail"><img src="/image/na-catalog.gif" alt="obrázek není k dispozici" width="200" height="250" /></a>
					</flow>
					<flow eid="ma_kratky_popis">
						<p eid="kratky_popis" />
					</flow>
					<flow eid="nema_kratky_popis"></flow>
					<div eid="nema_zmenu_ceny" class="new"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div>
					<flow eid="ma_zmenu_ceny"><div class="old"><span eid="stara_cena_s_dph" /><strong>CHF</strong></div><div class="new"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div></flow>
				</div>
			</td>
		</columns>
		<columns eid="produkt_vzor" specification="2">
			<td class="td-kat-02n">
				<div class="detail"><a eid="detail">Zur Detailkarte</a></div>
			</td>
		</columns>
		<columns eid="levy_produkt_vzor" specification="1">
			<td class="td-kat-01f">
				<h2><a eid="detail"><text eid="nazev" /></a></h2>
				<div class="kat-produkt">
					<div class="status-produkt">
						<flow eid="je_novinka">
							<span class="novinka"><strong>Neu</strong></span>
						</flow>
						<flow eid="neni_novinka" />
						<flow eid="je_akce">
							<strong class="akce"><strong>Aktion</strong></strong>
							<flow eid="ma_zmenu_ceny"><div class="procenta"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
						</flow>
						<flow eid="neni_akce" />
						<flow eid="je_sleva">
							<strong class="sleva"><strong>Sale</strong></strong>
							<flow eid="ma_zmenu_ceny"><div class="procenta"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
						</flow>
						<flow eid="neni_sleva"/>
					</div>
					<flow eid="ma_vlastnost" specification="9"><div class="raritni"><strong>Rarität</strong></div></flow><flow eid="nema_vlastnost" specification="9"></flow>
					<flow eid="ma_fotografii">
						<a eid="detail"><inline eid="fotografie" /></a>
					</flow>
					<flow eid="nema_fotografii">
						<a eid="detail"><img src="/image/na-catalog.gif" alt="obrázek není k dispozici" width="200" height="250" /></a>
					</flow>
					<flow eid="ma_kratky_popis">
						<p eid="kratky_popis" />
					</flow>
					<flow eid="nema_kratky_popis"></flow>
					<div eid="nema_zmenu_ceny" class="new"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div>
					<flow eid="ma_zmenu_ceny"><div class="old"><span eid="stara_cena_s_dph" /><strong>CHF</strong></div><div class="new"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div></flow>
				</div>
			</td>
		</columns>
		<columns eid="levy_produkt_vzor" specification="2">
			<td class="td-kat-02f">
				<div class="detail"><a eid="detail">Zur Detailkarte</a></div>
			</td>
		</columns>
		<columns eid="pravy_produkt_vzor" specification="1">
			<td class="td-kat-01n">
				<h2><a eid="detail"><text eid="nazev" /></a></h2>
				<div class="kat-produkt">
					<div class="status-produkt">
						<flow eid="je_novinka">
							<span class="novinka"><strong>Neu</strong></span>
						</flow>
						<flow eid="neni_novinka" />
						<flow eid="je_akce">
							<strong class="akce"><strong>Aktion</strong></strong>
							<flow eid="ma_zmenu_ceny"><div class="procenta"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
						</flow>
						<flow eid="neni_akce" />
						<flow eid="je_sleva">
							<strong class="sleva"><strong>Sale</strong></strong>
							<flow eid="ma_zmenu_ceny"><div class="procenta"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
						</flow>
						<flow eid="neni_sleva"/>
					</div>
					<flow eid="ma_vlastnost" specification="9"><div class="raritni"><strong>Rarität</strong></div></flow><flow eid="nema_vlastnost" specification="9"></flow>
					<flow eid="ma_fotografii">
						<a eid="detail"><inline eid="fotografie" /></a>
					</flow>
					<flow eid="nema_fotografii">
						<a eid="detail"><img src="/image/na-catalog.gif" alt="obrázek není k dispozici" width="200" height="250" /></a>
					</flow>
					<flow eid="ma_kratky_popis">
						<p eid="kratky_popis" />
					</flow>
					<flow eid="nema_kratky_popis"></flow>
					<div eid="nema_zmenu_ceny" class="new"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div>
					<flow eid="ma_zmenu_ceny"><div class="old"><span eid="stara_cena_s_dph" /><strong>CHF</strong></div><div class="new"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div></flow>
				</div>
			</td>
		</columns>
		<columns eid="pravy_produkt_vzor" specification="2">
			<td class="td-kat-02n">
				<div class="detail"><a eid="detail">Zur Detailkarte</a></div>
			</td>
		</columns>
		<columns eid="neni_produkt_vzor" specification="1">
			<td class="white"><he code="nbsp" /></td>
		</columns>
		<columns eid="neni_produkt_vzor" specification="2">
			<td class="white"><he code="nbsp" /></td>
		</columns>
		<columns eid="levy_neni_produkt_vzor" specification="1">
			<td class="white"><he code="nbsp" /></td>
		</columns>
		<columns eid="levy_neni_produkt_vzor" specification="2">
			<td class="white"><he code="nbsp" /></td>
		</columns>
		<columns eid="pravy_neni_produkt_vzor" specification="1">
			<td class="white"><he code="nbsp" /></td>
		</columns>
		<columns eid="pravy_neni_produkt_vzor" specification="2">
			<td class="white"><he code="nbsp" /></td>
		</columns>
	</group>
</group>
<flow eid="nejsou_produkty"></flow>
<flow eid="prazdna_stranka"></flow>
<flow eid="jsou_produkty">
	<table eid="produkty" id="tab-katalog" cellspacing="0" cellpadding="0">
	</table>
</flow>