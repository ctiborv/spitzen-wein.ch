<?php
//Possible improvement: add support for complex icons (shadows + clickable area definition)
//Known issues: localization seems unavailable as of july 2009
//              lot of other features from version 2 are not being available in googlemaps api v3 as of july 2009
class Googlemap_View extends View_Object
{
	protected $_div;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_div = new HTML_Div;

		$this->_attributes->register('sensor', false);
		$this->_attributes->register('lat'); // lattitude
		$this->_attributes->register('lng'); // longitude
		$this->_attributes->register('zoom', 8);
		$this->_attributes->register('navigationControl', true); // possible values: true, 'SMALL', 'ZOOM_PAN', 'ANDROID', 'DEFAULT'
		$this->_attributes->register('mapTypeControl', true); // possible values: true, 'HORIZONTAL_BAR', 'DROPDOWN_MENU', 'DEFAULT'
		$this->_attributes->register('scaleControl', true);
		$this->_attributes->register('type', 'ROADMAP'); // possible values: 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN'
		$this->_attributes->register('marker'); // ids of elements with info window contents for markers separated by semicolon (use empty string or # if no info window is attached to the marker)
		$this->_attributes->register('mlat'); // lattitudes of markers separated by semicolons
		$this->_attributes->register('mlng'); // longitudes of markers separated by semicolons
		$this->_attributes->register('titles_separator', ';'); // marker titles separator
		$this->_attributes->register('micon'); // icons of markers separated by semicolons
		$this->_attributes->register('mtitle'); // titles of markers separated by titles_separator
		$this->_attributes->register('mopen'); // opened status of info windows attached to markers separated by semicolons
		$this->_attributes->register('setContentByString', false); // set content via string rather than through node (workaround for googlemaps bug)
	}

	public function get($name)
	{
		try
		{
			return $this->_div->get($name);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			return parent::get($name);
		}
	}

	public function set($name, $value)
	{
		try
		{
			$this->_div->set($name, $value);
		}
		catch (HTML_No_Such_Element_Attribute_Exception $e)
		{
			parent::set($name, $value);
		}
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				$this->_div->render($renderer);
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
	}

	protected function _handleInput()
	{
	}

	protected function _resolve()
	{
		$match = $this->_view_manager->head->getElementsBy('tag', 'scripts');
		if (empty($match))
			throw new Template_Invalid_Structure_Exception('scripts tag missing in head template');
		$scripts = $match[0];

		$required = array
		(
			'id',
			'lat',
			'lng'
		);

		foreach ($required as $attr)
			if ($this->$attr === null)
				throw new Data_Insufficient_Exception($attr);

		$markers_js = '';
		if ($this->marker)
		{
			if ($this->mlat === null)
				throw new Data_Insufficient_Exception('mlat');

			if ($this->mlng === null)
				throw new Data_Insufficient_Exception('mlng');

			$marker_ids = explode(';', $this->marker);
			$marker_lats = explode(';', $this->mlat);
			$marker_lngs = explode(';', $this->mlng);
			$marker_icons = explode(';', $this->micon);
			if ($this->mtitle)
				$marker_titles = explode($this->titles_separator, $this->mtitle);
			else
				$marker_titles = array();
			if ($this->mopen)
				$marker_open = explode(';', $this->mopen);
			else
				$marker_open = array();

			$mcount = count($marker_ids);
			if ($mcount > count($marker_lats))
				throw new Data_Insufficient_Exception('mlat');

			if ($mcount > count($marker_lngs))
				throw new Data_Insufficient_Exception('mlng');

			$innerHTML_str = $this->setContentByString ? '.innerHTML' : '';
			for ($mi = 0; $mi < $mcount; ++$mi)
			{
				if ($marker_ids[$mi] !== '' && $marker_ids[$mi] != '#')
					$markers_js .= <<<EOT

		var contentElem$mi = document.getElementById("{$marker_ids[$mi]}").cloneNode(true);
		var infowindow$mi = new google.maps.InfoWindow({content: contentElem$mi$innerHTML_str});
EOT;
				$markers_js .= <<<EOT

		var markerPos$mi = new google.maps.LatLng(${marker_lats[$mi]}, {$marker_lngs[$mi]});
EOT;

				if (array_key_exists($mi, $marker_icons))
					$marker_icon_js = ", icon: '" . str_replace("'", "\\'", $marker_icons[$mi]) . "'";
				else
					$marker_icon_js = '';

				if (array_key_exists($mi, $marker_titles))
					$marker_title_js = ", title: '{$marker_titles[$mi]}'";
				else
					$marker_title_js = '';

				$markers_js .= <<<EOT

		var marker$mi = new google.maps.Marker({position: markerPos$mi, map: map$marker_icon_js$marker_title_js});
EOT;

				if ($marker_ids[$mi] !== '' && $marker_ids[$mi] != '#')
				{
					$markers_js .= <<<EOT

		google.maps.event.addListener(marker$mi, 'click', function() { infowindow$mi.open(map, marker$mi); });
EOT;

					if (array_key_exists($mi, $marker_open) && $marker_open[$mi])
					$markers_js .= <<<EOT

		infowindow$mi.open(map, marker$mi);
EOT;
				}
			}
		}


		if ($this->navigationControl)
		{
			$navigationControl_js = <<<EOT

			navigationControl: true,
EOT;
			if ($this->navigationControl !== true)
			$navigationControl_js .= <<<EOT

			navigationControlOptions: {style: google.maps.NavigationControlStyle.$this->navigationControl},
EOT;
		}
		else
			$navigationControl_js = <<<EOT

			navigationControl: false,
EOT;

		if ($this->mapTypeControl)
		{
			$mapTypeControl_js = <<<EOT

			mapTypeControl: true,
EOT;
			if ($this->mapTypeControl !== true)
			$mapTypeControl_js .= <<<EOT

			mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.$this->mapTypeControl},
EOT;
		}
		else
			$mapTypeControl_js = <<<EOT

			mapTypeControl: false,
EOT;

		if ($this->scaleControl)
			$scaleControl_js = <<<EOT

			scaleControl: true,
EOT;
		else
			$scaleControl_js = <<<EOT

			scaleControl: false,
EOT;

		$init_js_src = <<<EOT

	function initialize_googlemap_$this->id()
	{
		var myLatlng = new google.maps.LatLng($this->lat, $this->lng);
		var myOptions =
		{
			zoom: $this->zoom,
			center: myLatlng,$navigationControl_js$mapTypeControl_js$scaleControl_js
			mapTypeId: google.maps.MapTypeId.$this->type
		}
		var map = new google.maps.Map(document.getElementById("$this->id"), myOptions);$markers_js
	}
	google.setOnLoadCallback(initialize_googlemap_$this->id);

EOT;

		$init_js = new HTML_Javascript;
		$init_js->add(new HTML_Text($init_js_src));

		$sensor = $this->sensor ? 'true' : 'false';
		
		$scripts->addUnique(new HTML_JavaScript("http://www.google.com/jsapi?autoload=" . urlencode("{modules:[{name:\"maps\",version:3,other_params:\"sensor=$sensor\"}]}")));
		$scripts->addUnique($init_js);
	}
}
?>