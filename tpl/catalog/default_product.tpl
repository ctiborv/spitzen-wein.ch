<group active="0">
	<img eid="obrazek_sipka" src="/image/sipka.gif" alt="šipka" title="" width="8" height="5" />
	<img eid="obrazek_hlavni" alt="" title="" width="300" height="225" />
	<img eid="obrazek_dalsi" alt="" title="" width="91" height="68" />
	<img eid="obrazek_ilustrativni" alt="" title="" width="91" height="68" />
	<img eid="obrazek_kolekce" alt="" title="" width="91" height="68" />
	<flow eid="priloha_vzor">
		<inline eid="eid="priloha_pripona" specification="pdf"><img src="/image/file_pdf.gif" /></inline>
		<a eid="priloha_odkaz"><inline eid="priloha_nazev" /></a>
	</flow>
	<img eid="obrazek_vlastnost_ikona" width="16" height="16" />
	<img eid="obrazek_vlastnost_barva" width="16" height="16" />
	<group eid="vyberova_vlastnost_prirustek_height_popup" specification="30px" />
	<navpoint eid="link_objednat" page="objednávka" popup="height=400px,width=700px,top=100px,left=100px,menubar=no,toolbar=no,resizable=no,scrollbars=no" title="objednat" class="objednat2">
		<img src="/image/objednat2.gif" alt="" title="objednat" width="180" height="26" />
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
<flow eid="umisteni" />
<table id="tab-detail" cellspacing="0" cellpadding="0">
	<tr><td class="t-d-nadpis" colspan="2"><h3><text eid="nazev" /></h3></td></tr>
	<tr>
		<td class="t-d-foto" rowspan="2">
			<flow eid="ma_fotografii">
				<a eid="fotografie_odkaz"><inline eid="fotografie" /></a>
			</flow>
			<img eid="nema_fotografii" src="/image/na-detail.gif" alt="obrázek není k dispozici" title="" width="300" height="225" />
			
			<flow eid="ma_vlastnost" specification="3">
				<div id="barvy">
					<p><span>Barvy: </span><inline eid="vlastnost" specification="3" /></p>				
				</div>
			</flow>
			<flow eid="ma_dalsi_fotografie">
				<flow eid="dalsi_fotografie" />
			</flow>
			<flow eid="nema_dalsi_fotografie">
				<p>Žádné další fotografie nejsou k dispozici.</p>
			</flow>
			<flow eid="ma_prilohy>
				<flow eid="prilohy" />
			</flow>
			<flow eid="nema_prilohy">
				<p>Žádné přílohy produktu nejsou k dispozici.</p>			
			</flow>
			<flow eid="objednani" />
		</td>
		<td class="t-d-akce"><span eid="je_novinka">Novinka</span><text eid="neni_novinka">Novinka</text> | <span eid="je_akce">Akce</span><text eid="neni_akce">Akce</text> | <span eid="je_sleva">Sleva</span><text eid="neni_sleva">Sleva</text> | <inline eid="vlastnost" specification="1" /></td>
	</tr>
	<tr>
		<td class="t-d-para">
			<table id="tab-detail-V" cellspacing="0" cellpadding="0">
				<rows eid="vlastnosti" specification="!1,3" />
				<tr>
					<td class="atribut">Cena bez DPH:</td>
					<td class="hodnota">
						<inline eid="ma_starou_cenu"><span eid="stara_cena_bez_dph" class="old2" /></inline>
						<span eid="nema_starou_cenu" class="old2"><he code="mdash" /></span>
						<inline eid="ma_novou_cenu"><span eid="nova_cena_bez_dph" class="new2" /></inline>
						<span eid="nema_novou_cenu" class="new2"><he code="mdash" /></span>
					</td>
				</tr>
				<tr>
					<td class="atribut"><b>Cena s DPH:</b></td>
					<td class="hodnota">
						<inline eid="ma_starou_cenu"><span eid="stara_cena_s_dph" class="old3" /></inline>
						<span eid="nema_starou_cenu" class="old3"><he code="mdash" /></span>
						<inline eid="ma_novou_cenu"><span eid="nova_cena_s_dph" class="new3" /></inline>
						<span eid="nema_novou_cenu" class="new3"><he code="mdash" /></span>
					</td>
				</tr>
				<tr active="0">
					<td class="atribut"><b>DPH:</b></td>
					<td class="hodnota">
						<inline eid="ma_starou_cenu"><span eid="stara_cena_dph" class="old4" /></inline>
						<span eid="nema_starou_cenu" class="old4"><he code="mdash" /></span>
						<inline eid="ma_novou_cenu"><span eid="nova_cena_dph" class="new4" /></inline>
						<span eid="nema_novou_cenu" class="new4"><he code="mdash" /></span>
					</td>
				</tr>
				<tr eid="ma_zmenu_ceny">
					<td class="atribut"><b>Sleva:</b></td>
					<td class="hodnota">
						<span><text eid="zmena_ceny" /> (<text eid="zmena_ceny_procent" />%)</span>
					</td>
				</tr>
				<rows eid="nema_zmenu_ceny" />
			</table><newline />
		</td>
	</tr>
</table>
<div eid="ma_popis" class="detail-popis">
	<h3>Popis produktu</h3>
	<text eid="popis" />
</div>
<div eid="nema_popis" class="detail-popis">
	Tento produkt nemá žádný popis
</div>
<div eid="ma_ilustrativni_fotografie" class="ilustrativni-foto">
	<h3>Ilustrativní fotografie</h3>
	<flow eid="ilustrativni_fotografie" />
	<br class="clear" />
</div>
<div eid="nema_ilustrativni_fotografie" class="ilustrativni-foto">
	<h3>Ilustrativní fotografie</h3>
	Žádné ilustrativní fotografie.
	<br class="clear" />
</div>
<div eid="ma_fotografie_kolekce" class="kolekce-foto">
	<h3>Fotografie z kolekce</h3>
	<flow eid="fotografie_kolekce" />
	<br class="clear" />
</div>
<div eid="nema_fotografie_kolekce" class="kolekce-foto">
	<h3>Fotografie z kolekce</h3>
	Žádné fotografie kolekce.
	<br class="clear" />
</div>
