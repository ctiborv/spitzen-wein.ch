<?php
// supported JS libraries: lightbox, greybox

class Images_JS_Support
{
	const UNKNOWN = 0;
	const LIGHTBOX = 1;
	const GREYBOX = 2;

	const LIGHTBOX_PLUGIN_VERSION = 0.5;

	public static function identify($type)
	{
		if (substr($type, 0, 8) == 'lightbox')
			return self::LIGHTBOX;
		elseif (substr($type, 0, 7) == 'greybox')
			return self::GREYBOX;
		else
			return self::UNKNOWN;
	}

	public static function updateHead($type, HTML_Head $head)
	{
		$itype = self::identify($type);
		switch ($itype)
		{
			case self::LIGHTBOX:
				$nav = new Project_Navigator;
				try
				{
					$lightbox_css_dir = $nav->lightbox_css_dir;
				}
				catch (Navigator_No_Such_Location_Exception $e)
				{
					$lightbox_css_dir = $nav->css_dir;
				}
				try
				{
					$lightbox_js_dir = $nav->lightbox_js_dir;
				}
				catch (Navigator_No_Such_Location_Exception $e)
				{
					$lightbox_js_dir = $nav->js_dir;
				}
				$links = self::getLinks($head);
				$scripts = self::getScripts($head);

				$links->addUnique(new HTML_Link('stylesheet', 'text/css', $lightbox_css_dir . 'jquery.lightbox-' . self::LIGHTBOX_PLUGIN_VERSION . '.css'));
				$lightbox_scripts = array
				(
					'jquery.js' => $nav->js_dir,
					'jquery.lightbox-' . self::LIGHTBOX_PLUGIN_VERSION . '.js'  => $lightbox_js_dir,
					'jquery.lightbox.ready.js'  => $lightbox_js_dir
				);
				foreach ($lightbox_scripts as $script => $dir)
					$scripts->addUnique(new HTML_JavaScript($dir . $script));
				break;
			case self::GREYBOX:
				$nav = new Project_Navigator;
				try
				{
					$greybox_css_dir = $nav->greybox_css_dir;
				}
				catch (Navigator_No_Such_Location_Exception $e)
				{
					$greybox_css_dir = $nav->css_dir;
				}
				try
				{
					$greybox_js_dir = $nav->greybox_js_dir;
				}
				catch (Navigator_No_Such_Location_Exception $e)
				{
					$greybox_js_dir = $nav->js_dir;
				}
				$links = self::getLinks($head);
				$scripts = self::getScripts($head);

				$links->addUnique(new HTML_Link('stylesheet', 'text/css', "{$greybox_css_dir}gb_styles.css"));
				$greybox_scripts = array
				(
					'AJS.js',
					'AJS_fx.js',
					'gb_scripts.js'
				);
				foreach ($greybox_scripts as $script)
					$scripts->addUnique(new HTML_JavaScript($greybox_js_dir . $script));
				break;
		}
	}

	public static function updateElement($type, HTML_Element $elem)
	{
		$itype = self::identify($type);
		switch ($itype)
		{
			case self::LIGHTBOX:
			case self::GREYBOX:
				$elem->rel = $type;
				break;
		}
	}

	public static function updateElements($type, array $elements)
	{
		$itype = self::identify($type);
		switch ($itype)
		{
			case self::LIGHTBOX:
			case self::GREYBOX:
				foreach ($elements as $elem)
					$elem->rel = $type;
				break;
		}
	}

	public static function updateHTML($type, HTML_Element $root)
	{
		$itype = self::identify($type);
		switch ($itype)
		{
			case self::LIGHTBOX:
			case self::GREYBOX:
				$imgs = $root->getElementsBy('tag', 'img');
				self::updateElements($type, $imgs);
				break;
		}
	}

	public static function updateHTMLRaw($type, $html)
	{
		$itype = self::identify($type);
		switch ($itype)
		{
			case self::LIGHTBOX:
			case self::GREYBOX:
				$html = self::adRelRaw($type, $html);
				break;
		}
		return $html;
	}

	protected static function adRelRaw($type, $html)
	{
		$pattern = '#(<a ([^<]+ )?href=[^<]+(jpg|png)"[^<]*)(><img[^<]+</a>)#i';
		$replacement = "$1 rel=\"$type\"$4";
		return preg_replace($pattern, $replacement, $html);
	}

	protected static function getLinks(HTML_Head $head)
	{
		$match = $head->getElementsBy('tag', 'links');
		if (empty($match))
			throw new Template_Invalid_Structure_Exception('links tag missing in head template');
		return $match[0];
	}

	protected static function getScripts(HTML_Head $head)
	{
		$match = $head->getElementsBy('tag', 'scripts');
		if (empty($match))
			throw new Template_Invalid_Structure_Exception('scripts tag missing in head template');
		return $match[0];
	}
}
?>
