<require>
	<link rel="stylesheet" type="text/css" href="/styles/katalog.css" />
</require>
<group active="0">
	<img eid="obrazek_sipka" src="/image/sipka.png" alt="šipka" title="" width="24" height="24" />
	<img eid="obrazek_hlavni" alt="" title="" width="320" height="400" />
	<img eid="obrazek_dalsi" alt="" title="" width="90" height="67" />
	<img eid="obrazek_ilustrativni" alt="" title="" width="90" height="67" />
	<img eid="obrazek_kolekce" alt="" title="" width="90" height="67" />
	<flow eid="priloha_vzor">
		<inline eid="priloha_pripona" specification="pdf"></inline>
		<a eid="priloha_odkaz" target="_blank"><img src="/image/ico-pdf.gif" alt="soubor s PDF"  width="20" height="20" /><span><inline eid="priloha_nazev" /></span></a>
	</flow>
	<img eid="obrazek_vlastnost_ikona" width="30" height="30" />
	<img eid="obrazek_vlastnost_barva" width="16" height="16" />
	<group eid="vyberova_vlastnost_prirustek_height_popup" specification="30px" />
	<navpoint eid="link_objednat" page="objednávka" popup="height=400px,width=700px,top=100px,left=100px,menubar=no,toolbar=no,resizable=no,scrollbars=no" title="objednat" class="objednat2">
		<img src="/image/objednat2.gif" alt="" title="objednat" width="180" height="26" />
	</navpoint>
	<tr eid="ma_vlastnost_vzor">
		<td class="atribut"><text eid="nazev_vlastnosti" />:</td>
		<td class="hodnota"><inline eid="hodnoty_vlastnosti" /></td>
	</tr>
	<rows eid="nema_vlastnost_vzor">
	</rows>
	<div eid="umisteni_vzor" class="navigace">
		<flow eid="umisteni_obsah" />
	</div>
</group>
<table class="tab-detail" cellspacing="0" cellpadding="0">
	<tr><td colspan="2"><flow eid="umisteni" /></td></tr>
	<tr><td class="t-d-nadpis" colspan="2"><h3><text eid="nazev" /></h3></td></tr>
	<tr>
		<td class="t-d-foto" rowspan="2">
			<div class="t-d-foto-hlavni">
				<flow eid="ma_fotografii">
					<a eid="fotografie_odkaz"><inline eid="fotografie" /></a>
				</flow>
				<img eid="nema_fotografii" src="/image/na-detail.gif" alt="Bild nicht verfügbar" title="Bild nicht verfügbar" width="320" height="400" />
			</div>
			<flow eid="ma_vlastnost" specification="9">
				<div class="raritni">
					<strong>Rarität</strong>
				</div>
			</flow>
			<flow eid="nema_vlastnost" specification="9">
			</flow>
			<div class="status-produkt">
				<flow eid="je_novinka">
					<span class="novinka"><strong>Neu</strong></span>
				</flow>
				<flow eid="neni_novinka" />
				<flow eid="je_akce">
					<strong class="akce"><strong>Aktion</strong></strong>
					<flow eid="ma_zmenu_ceny"><div class="procenta-detail"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
				</flow>
				<flow eid="neni_akce" />
				<flow eid="je_sleva">
					<strong class="sleva"><strong>Sale</strong></strong>
					<flow eid="ma_zmenu_ceny"><div class="procenta-detail"><span eid="zmena_ceny_procent" /><strong>%</strong></div></flow>
				</flow>
				<flow eid="neni_sleva"/>
				<div eid="nema_zmenu_ceny" class="new-detail"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div>
				<flow eid="ma_zmenu_ceny"><div class="old-detail"><span eid="stara_cena_s_dph" /><strong>CHF</strong></div><div class="new-detail"><strong>nur</strong><span eid="nova_cena_s_dph" /><strong>CHF</strong></div></flow>
			</div>
			<flow eid="ma_dalsi_fotografie">
				<div class="t-d-foto-dalsi">
					<flow eid="dalsi_fotografie" />
				</div>
			</flow>
			<flow eid="nema_dalsi_fotografie">
			</flow>
			<flow active="0" eid="objednani" />
		</td>
		<td class="t-d-para">
			<div class="tab-detail-V">
				<table cellspacing="0" cellpadding="0">
					<rows eid="vlastnosti" specification="!9" />
					<tr active="0">
						<td class="atribut">Cena bez DPH:</td>
						<td class="hodnota">
							<inline eid="ma_starou_cenu"><span eid="stara_cena_bez_dph" class="old2" /></inline>
							<span eid="nema_starou_cenu" class="old2"><he code="mdash" /></span>
							<inline eid="ma_novou_cenu"><span eid="nova_cena_bez_dph" class="new2" /></inline>
							<span eid="nema_novou_cenu" class="new2"><he code="mdash" /></span>
						</td>
					</tr>
					<tr active="0">
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
				</table><newline />
			</div>
			<div class="detail-prilohy">
				<flow eid="ma_prilohy">
					<h3>Soubory ke stažení</h3>
					<flow eid="prilohy" />
				</flow>
			</div>
			<flow eid="nema_prilohy">
			</flow>
		</td>
	</tr>
</table>
<div eid="ma_popis" class="detail-popis" active="0">
	<text eid="popis" />
</div>
<flow eid="nema_popis">
</flow>
<div eid="ma_ilustrativni_fotografie" class="ilustrativni-foto">
	<h3>Ilustrativní fotografie</h3>
	<flow eid="ilustrativni_fotografie" />
	<br class="clear" />
</div>
<flow eid="nema_ilustrativni_fotografie">
</flow>
<div eid="ma_fotografie_kolekce" class="kolekce-foto">
	<h3>Související produkty</h3>
	<flow eid="fotografie_kolekce" />
	<br class="clear" />
</div>
<flow eid="nema_fotografie_kolekce">
</flow>