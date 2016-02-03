<group active="0">
	<group eid="zaklad_vzory">
		<img eid="obrazek_sipka" src="/image/sipka.gif" alt="šipka" title="" width="8" height="5" />
		<img eid="obrazek_produktu" alt="" title="" width="150" height="200" />
		<img eid="obrazek_vlastnost_ikona" width="16" height="16" />
		<img eid="obrazek_vlastnost_barva" width="16" height="16" />
		<group eid="vyberova_vlastnost_prirustek_height_popup" specification="30px" />
		<navpoint eid="link_objednat" page="objednávka" popup="height=400px,width=700px,top=100px,left=100px,menubar=no,toolbar=no,resizable=no,scrollbars=no" title="objednat" class="objednat">
			<span>vložit do košíku</span>
		</navpoint>
		<tr eid="ma_vlastnost_vzor">
			<td class="atribut"><text eid="nazev_vlastnosti" />:</td>
			<td class="hodnota"><inline eid="hodnoty_vlastnosti" /></td>
		</tr>
		<tr eid="nema_vlastnost_vzor">
			<td class="atribut"><text eid="nazev_vlastnosti" />:</td>
			<td class="hodnota"><he code="ndash" /></td>
		</tr>
		<div eid="umisteni_vzor" class="lista">
			<flow eid="umisteni_obsah" />
		</div>
	</group>
	<rows eid="radek_struktura">
		<tr><columns eid="produkt" specification="1" /></tr>
		<tr><columns eid="produkt" specification="2" /></tr>
	</rows>
	<group eid="produkt_vzory">
		<columns eid="produkt_vzor" specification="1">
			<td class="td-kat-01">
				<h2><a eid="detail" title=""><text eid="nazev" /></a></h2>
				<div class="akce"><span eid="neni_novinka">Novinka</span><text eid="je_novinka">Novinka</text> | <span eid="je_akce">Akce</span><text eid="neni_akce">Akce</text> | <span eid="je_sleva">Sleva</span><text eid="neni_sleva">Sleva</text></div>
				<flow eid="ma_fotografii">
					<a eid="detail" title=""><inline eid="fotografie" /></a>
				</flow>
				<img eid="nema_fotografii" src="/image/na-catalog.gif" alt="obrázek není k dispozici" title="" width="150" height="200" />
				<flow eid="ma_vlastnost" specification="3"><p><inline eid="vlastnost" specification="3" /></p></flow>
				<flow eid="ma_kratky_popis">
					<p eid="kratky_popis" />
				</flow>
				<p eid="nema_kratky_popis">Tento produkt nemá žádný popis.</p>
			</td>
		</columns>
		<columns eid="produkt_vzor" specification="2">
			<td class="td-kat-02">
				<div class="cena"><span eid="stara_cena_s_dph" class="old" /><span eid="nova_cena_s_dph" class="new" /></div>
				<flow eid="objednani" />
			</td>
		</columns>
		<columns eid="levy_produkt_vzor" specification="1">
			<td class="td-kat-01">
				<h2><a eid="detail" title=""><text eid="nazev" /></a></h2>
				<div class="akce"><span eid="neni_novinka">Novinka</span><text eid="je_novinka">Novinka</text> | <span eid="je_akce">Akce</span><text eid="neni_akce">Akce</text> | <span eid="je_sleva">Sleva</span><text eid="neni_sleva">Sleva</text></div>
				<a eid="detail" title=""><inline eid="fotografie" /></a>
				<flow eid="ma_vlastnost" specification="3"><p><inline eid="vlastnost" specification="3" /></p></flow>
				<flow eid="ma_popis">
					<p eid="popis" />
				</flow>
				<p eid="nema_popis">Tento produkt nemá žádný popis.</p>
			</td>
		</columns>
		<columns eid="levy_produkt_vzor" specification="2">
			<td class="td-kat-02">
				<div class="cena"><span eid="stara_cena_s_dph" class="old" /><span eid="nova_cena_s_dph" class="new" /></div>
				<flow eid="objednani" />
			</td>
		</columns>
		<columns eid="pravy_produkt_vzor" specification="1">
			<td class="td-kat-01">
				<h2><a eid="detail" title=""><text eid="nazev" /></a></h2>
				<div class="akce"><span eid="neni_novinka">Novinka</span><text eid="je_novinka">Novinka</text> | <span eid="je_akce">Akce</span><text eid="neni_akce">Akce</text> | <span eid="je_sleva">Sleva</span><text eid="neni_sleva">Sleva</text></div>
				<a eid="detail" title=""><inline eid="fotografie" /></a>
				<flow eid="ma_vlastnost" specification="3"><p><inline eid="vlastnost" specification="3" /></p></flow>
				<flow eid="ma_popis">
					<p eid="popis" />
				</flow>
				<p eid="nema_popis">Tento produkt nemá žádný popis.</p>
			</td>
		</columns>
		<columns eid="pravy_produkt_vzor" specification="2">
			<td class="td-kat-02">
				<div class="cena"><span eid="stara_cena_s_dph" class="old" /><span eid="nova_cena_s_dph" class="new" /></div>
				<flow eid="objednani" />
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
<p eid="nejsou_produkty">Nenalezeny žádné produkty.</p>
<p eid="prazdna_stranka">Nenalezeny žádné produkty, číslo stránky je příliš vysoké.</p>
<pagination eid="horni_paginace" vid="paginace" case="top" radius="2" />
<flow eid="jsou_produkty">
	<table eid="produkty" id="tab-katalog" cellspacing="0" cellpadding="0">
	</table>
</flow>
<pagination eid="dolni_paginace" vid="paginace" case="bottom" />
