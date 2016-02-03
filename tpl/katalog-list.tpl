<require>
	<link rel="stylesheet" type="text/css" href="/styles/katalog.css" />
	<javascript src="/js/catalog_menu.js" />
</require>
<body id="peak" class="bv0">
<div class="horniBG">
	<div class="horniV">
		<div class="horni span12">
			<div class="horniL span6">
				<article template="article/logo" />
			</div>
			<div class="horniP span6">
			</div>
			<navmenu template="navmenu/hlavni-menu" current="katalog" />
		</div>
	</div>
</div>
<!-- test -->
<div class="teloBG">
	<div class="teloV">
		<div class="telo">
			<div class="teloL span3">
<article template="search/vyhledavani" />
				<div class="katalog-menu">
					<h2>Weinsortiment Katalog</h2>
					<div class="katalog-listV">
						<div class="katalog-list">
<catalog vid="es" case="menu" name="katalog" template="catalog/katalog" width="3" rows="6" images="lightbox" active="1" />
						</div>
					</div>
				</div>
<article mid="6" template="article/teloL-bannery" />
			</div>
			<div class="teloP span9" id="katalog">
<catalog vid="es" />
			</div>
			<br class="clear" />
		</div>
	</div>
</div>
<article template="article/spodni" />
<article template="article/copyright" />
</body>