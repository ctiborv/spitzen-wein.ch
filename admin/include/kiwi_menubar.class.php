<?php
require_once 'page_item.class.php';
require_once 'page_names.inc.php';

class Kiwi_MenuBar extends Page_Item
{
	protected $page;

	protected $menu_links_big = array
	(
/*		array
		(
			'title' => 'FOTOGALERIE',
			'class' => 'fotogalerie',
			'page' => KIWI_PHOTOGALLERIES
		),*/
		array
		(
			'title' => 'WWW',
			'class' => 'www',
			'page' => KIWI_MENU
		),
		array
		(
			'title' => 'KATALOG',
			'class' => 'e-shop',
			'page' => KIWI_ESHOP
		),
		array
		(
			'title' => 'SEO',
			'class' => 'seo',
			'page' => KIWI_PRODUCTS_SEO
		),
		array
		(
			'title' => 'Newsletters',
			'class' => 'newsletters',
			'page' => KIWI_NEWSLETTERS
		)
	);

	protected $menu_links_small = array
	(
/*		'FOTOGALERIE' => array
		(
			array
			(
				'title' => 'Fotogalerie',
				'page' => KIWI_PHOTOGALLERIES
			),
			array
			(
				'title' => 'Obrázky',
				'page' => KIWI_PICTURES
			)
		),*/
		'WWW' => array
		(
			array
			(
				'title' => 'Menüoption',
				'page' => KIWI_MENU
			),
			array
			(
				'title' => 'Module',
				'page' => KIWI_MODULES
			),
			array
			(
				'title' => 'News / Artikel',
				'page' => KIWI_NEWSGROUPS
			)
		),
		'KATALOG' => array
		(
			array
			(
				'title' => 'Gruppen und Serie',
				'page' => KIWI_ESHOP
			),
			array
			(
				'title' => 'Spezial',
				'page' => KIWI_ESHOP_SPECIAL_GROUPS
			),
			array
			(
				'title' => 'Aktione',
				'page' => KIWI_ACTIONGROUPS
			),
			array
			(
				'title' => 'Artikel',
				'page' => KIWI_PRODUCTS
			),
			array
			(
				'title' => 'Eigenschaften',
				'page' => KIWI_PRODUCT_PROPERTIES
			),
/*			array
			(
				'title' => 'Klienten',
				'page' => KIWI_CLIENTS
			),
			array
			(
				'title' => 'Bestellungen',
				'page' => KIWI_ORDERS
			),
			array
			(
				'title' => 'Import/Export',
				'page' => KIWI_IMPORT_EXPORT
			),
*/			array
			(
				'title' => 'Währungskurse',
				'page' => KIWI_CURRENCY_RATES
			)
		),
		'SEO' => array
		(
			array
			(
				'title' => 'Artikel',
				'page' => KIWI_PRODUCTS_SEO
			),
			array
			(
				'title' => 'Gruppen und Serie',
				'page' => KIWI_ESHOP_SEO
			)
		),
		'Newsletters' => array
		(
			array
			(
				'title' => 'Newsletters',
				'page' => KIWI_NEWSLETTERS
			),
			array
			(
				'title' => 'Newsletter subscribers',
				'page' => KIWI_SUBSCRIBERS
			),
		)
	);

	public function __construct($page, &$rights)
	{
		parent::__construct();
		$this->page = $page;

		$self = basename($_SERVER['PHP_SELF']);

		if ($rights->UserID != DEFAULT_USERID)
		{
			if ($self == KIWI_LOGIN) $qs = '?logout';
			else $qs = '?page=' . urlencode($_SERVER['REQUEST_URI']) . '&logout';

			$login_arr = array
			(
				'title' => 'Abmelden',
				'page' => KIWI_LOGIN . $qs
			);
		}
		else
		{
			if ($self == KIWI_LOGIN) $qs = '';
			else $qs = '?page=' . urlencode($_SERVER['REQUEST_URI']);

			$login_arr = array
			(
				'title' => 'Anmelden',
				'page' => KIWI_LOGIN . $qs
			);
		}

		foreach ($this->menu_links_small as &$arr)
			$arr[] = $login_arr;
	}

	public function _getHTML()
	{
		$html =	<<<EOT
<div id="horni">

EOT;

		foreach ($this->menu_links_big as $value)
		{
			$html .= <<<EOT
	<div class="{$value['class']}"><
EOT;
			if ($this->page == $value['title'])
				$html .= <<<EOT
span>{$value['title']}</span
EOT;
			else
				$html .= <<<EOT
a href="{$value['page']}">{$value['title']}</a
EOT;

			$html .= <<<EOT
></div>

EOT;
		}

		$html .= <<<EOT
</div>
<div id="lista">
	<ul>

EOT;
		foreach ($this->menu_links_small[$this->page] as $value)
		{
			$page = htmlspecialchars($value['page']);
			$title = htmlspecialchars($value['title']);
			$html .= <<<EOT
		<li><a href="$page">$title</a></li>

EOT;
		}

		$html .= <<<EOT
	</ul>
</div>

EOT;
		return $html;
	}

	public function handleInput($get, $post)
	{
	}
}
?>