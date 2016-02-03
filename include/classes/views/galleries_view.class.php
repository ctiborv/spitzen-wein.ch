<?php
class Galleries_View extends Template_Based_View
{
	protected $_kiwi_module;
	protected $_galleries;
	protected $_total;
	protected $_qs;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_kiwi_module = null;
		$this->_galleries = null;
		$this->_total = null;
		$this->_qs = null;

		$this->_attributes->register('mid'); // module id in database
		$this->_attributes->register('name'); // module name in database
		$this->_attributes->register('mactive'); // module activity status
		$this->_attributes->register('ggid'); // gallerygroup id in database
		$this->_attributes->register('ggroup'); // gallerygroup name in database
		$this->_attributes->register('ggshortdesc'); // gallerygroup short description
		$this->_attributes->register('gglongdesc'); // gallerygroup long description
		$this->_attributes->register('ggauthor'); // gallerygroup author
		$this->_attributes->register('ggwhen'); // gallerygroup date
		$this->_attributes->register('ggsize'); // number of galleries per page to display (when displaying list)
		$this->_attributes->register('gggsize'); // number of pictures per gallery (when displaying list)
		$this->_attributes->register('gsize'); // number of pictures per page to display (when displaying detail)
		$this->_attributes->register('pagination'); // pagination display toggle ("", "0", "top", "bottom"; other means "on")
		$this->_attributes->register('detailpage'); // navigation point for displaying detailed galleries
		$this->_attributes->register('gid'); // id of gallery to display in detail (dont set if you want list)
		$this->_attributes->register('ggpage'); // page to display (when displaying list)
		$this->_attributes->register('gpage'); // page to display (when displaying detail)
		$this->_attributes->register('ns'); // namespace (query string variables prefix)
		$this->_attributes->register('nsd'); // detail namespace (query string variables prefix) (if not defined, falls back to ns)
		// $this->_attributes->register('qs'); // query string to preserve // special variable
		$this->_attributes->register('images'); // attribute which says if images should be rendered in special way
		$this->_attributes->register('thumbs'); // size and quality of thumbnails, e.g.: 100x100@75

		$this->template = 'galleries/default'; // default template
	}

	public function get($name)
	{
		if ($name == 'qs')
		{
			if ($this->_qs !== null)
				return $this->_qs->__toString();
			else
				return null;
		}
		else
			return parent::get($name);
	}

	public function set($name, $value)
	{
		if ($name == 'qs')
		{
			if ($value !== null)
				$this->_qs = new Query_String($value);
			else
				$this->_qs = null;
		}
		else
			parent::set($name, $value);
	}

	protected function _initialize()
	{
		//$this->_kiwi_module = new Kiwi_Galleries_Module($this->mid, $this->name, $this->ggid, $this->ggroup, $this->ggsize, $this->gggsize, $this->gsize, $this->pagination, $this->detailpage);
		$this->_kiwi_module = Kiwi_Object_Manager::load
		(
			'Kiwi_Galleries_Module', 
			array
			(
				$this->mid,
				$this->name,
				$this->ggid,
				$this->ggroup,
				$this->ggsize,
				$this->gggsize,
				$this->gsize,
				$this->pagination,
				$this->detailpage
			)
		); // allows caching

		// non-overridable attributes (module attributes have priority)
		$attr_no = array
		(
			'mid' => 'id',
			'name' => 'name',
			'ggid' => 'ggid',
			'mactive' => 'active'
		);
		foreach ($attr_no as $attr => $mattr)
			if ($this->_kiwi_module->$mattr !== null)
				$this->$attr = $this->_kiwi_module->$mattr;

		// overridable attributes (tag attributes have priority)
		$attr_o = array
		(
			'ggsize' => 'ggsize',
			'gggsize' => 'gggsize',
			'gsize' => 'gsize',
			'pagination' => 'pagination',
			'detailpage' => 'detailpage',
			'ggroup' => 'ggroup',
			'ggshortdesc' => 'ggshortdesc',
			'gglongdesc' => 'gglongdesc',
			'ggauthor' => 'ggauthor',
			'ggwhen' => 'ggwhen'
		);
		foreach ($attr_o as $attr => $mattr)
			if ($this->$attr === null)
				$this->$attr = $this->_kiwi_module->$mattr;

		// default values of attributes
		$defaults = array
		(
			'ggsize' => 4,
			'gggsize' => 4,
			'gsize' => 4,
			'pagination' => 'both',
			'ggpage' => 1,
			'gpage' => 1,
			'ns' => '',
			'images' => ''
		);
		foreach ($defaults as $attr => $val)
			if ($this->$attr === null)
				$this->$attr = $val;

		// default for nsd is value of ns
		if ($this->nsd === null)
			$this->nsd = $this->ns;

		if ($this->ggid === null && $this->ggroup === null)
			throw new Data_Insufficient_Exception('ggid or ggroup');

		if ($this->thumbs === null)
			throw new Data_Insufficient_Exception('thumbs');

		parent::_initialize();
	}

	protected function _handleInput()
	{
		if ($this->ggid === null)
		{
			$qsvar = $this->ns . 'gg';
			if (array_key_exists($qsvar, $_GET))
				$this->ggid = $_GET[$qsvar];
		}

		if ($this->gid === null)
		{
			$qsvar = $this->ns . 'g';
			if (array_key_exists($qsvar, $_GET))
				$this->gid = $_GET[$qsvar];
		}

		$qsvar = $this->ns . 'ggpg';
		if (array_key_exists($qsvar, $_GET))
			$this->ggpage = $_GET[$qsvar];

		$qsvar = $this->ns . 'gpg';
		if (array_key_exists($qsvar, $_GET))
			$this->gpage = $_GET[$qsvar];
	}

	protected function resolveTemplateSource()
	{
		if (!$this->mactive)
			$suffix = 'disabled';
		else
			$suffix = $this->gid === null ? 'list' : 'detail';
		$base = $this->template;
		if ($base === null)
			throw new Data_Insufficient_Exception('template');
		$this->template = $base . '_' . $suffix;
	}

	protected function _updateTemplate()
	{
		$this->loadData();
		if (!$this->mactive)
			$this->updateGalleriesDisabledTemplate();
		elseif ($this->gid === null)
			$this->updateGalleriesListTemplate();
		else
			$this->updateGalleriesDetailTemplate();
	}

	protected function updateHead()
	{
		Images_JS_Support::updateHead($this->images, $this->_view_manager->head);
	}

	protected function updateGalleriesDisabledTemplate()
	{
	}

	protected function updateGalleriesListTemplate()
	{
		$vars = array
		(
			'nadpis_skupiny',
			'kratky_popis_skupiny',
			'dlouhy_popis_skupiny',
			'autor_skupiny',
			'autor_skupiny_jako_link',
			'datum_skupiny',
			'ma_galerie',
			'zadne_galerie',
			'prazdna_stranka',
			'horni_paginace',
			'galerie',
			'dolni_paginace',
			'galerie_vzor',
			'obrazek_vzor',
			'nahradni_obrazek_vzor'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		if (!$galerie_vzor)
			throw new Template_Element_Missing_Exception('galerie_vzor');

		if (count($galerie_vzor) != 1)
			throw new Template_Invalid_Structure_Exception('The "galerie_vzor" element duplicity');

		if (!$obrazek_vzor)
			throw new Template_Element_Missing_Exception('obrazek_vzor');

		if (!$nahradni_obrazek_vzor)
			throw new Template_Element_Missing_Exception('nahradni_obrazek_vzor');

		if (count($obrazek_vzor) != 1)
			throw new Template_Invalid_Structure_Exception('The "obrazek_vzor" element duplicity');

		if (count($nahradni_obrazek_vzor) != 1)
			throw new Template_Invalid_Structure_Exception('The "nahradni_obrazek_vzor" element duplicity');

		if ($horni_paginace && count($horni_paginace) != 1)
			throw new Template_Invalid_Structure_Exception('The "horni_paginace" element duplicity');

		if ($dolni_paginace && count($dolni_paginace) != 1)
			throw new Template_Invalid_Structure_Exception('The "dolni_paginace" element duplicity');

		$galerie_vzor = $galerie_vzor[0];
		if ($horni_paginace)
			$horni_paginace = $horni_paginace[0];
		if ($dolni_paginace)
			$dolni_paginace = $dolni_paginace[0];

		// nadpis skupiny
		foreach ($nadpis_skupiny as $elem)
			$elem->text = $this->ggroup;

		// kratky popis skupiny
		foreach ($kratky_popis_skupiny as $elem)
			$elem->text = $this->ggshortdesc;

		// dlouhy popis skupiny
		$content_elem = new HTML_Text($this->gglongdesc);
		$content_elem->raw = true;
		foreach ($dlouhy_popis_skupiny as $elem)	
			$elem->add($content_elem);

		// autor skupiny
		foreach ($autor_skupiny as $elem)
			$elem->text = $this->ggauthor;

		// autor skupiny jako link
		foreach ($autor_skupiny_jako_link as $elem)
		{
			$elem->href = 'http://' . $this->ggauthor;
			$elem->clear();
			$elem->add(new HTML_Text($this->ggauthor));
		}

		// datum skupiny
		foreach ($datum_skupiny as $elem)
		{
			try
			{
				$elem->timestamp = $this->ggwhen; // nikoli view_object, protoze se to bude jeste klonovat
			}
			catch (HTML_No_Such_Element_Attribute_Exception $e)
			{
				$datum_spec = null;
				try
				{
					$datum_spec = $elem->specification;
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
				}

				$datetime = new Date_Time($this->ggwhen);
				$elem->add(new HTML_Text($datetime->format($datum_spec === null ? 'j.n. Y' : $datum_spec)));
			}		
		}

		// galerie
		if (!empty($galerie))
		{
			foreach ($this->_galleries as &$gallery)
			{
				$this->updateTemplate_Gallery($gallery, $this->gggsize);
				foreach ($galerie as $elem)
					$elem->add(clone $galerie_vzor);
			}
		}

		foreach ($ma_galerie as $elem)
			$elem->active = !empty($this->_galleries);

		foreach ($zadne_galerie as $elem)
			$elem->active = empty($this->_galleries) && $this->_total == 0;

		foreach ($prazdna_stranka as $elem)
			$elem->active = empty($this->_galleries) && $this->_total > 0;

		$pages = (int)($this->_total / $this->ggsize);
		if ($pages * $this->ggsize < $this->_total) $pages++;

		if ($horni_paginace)
		{
			$horni_paginace->vo->total = $pages;
			$horni_paginace->vo->current = $this->ggpage;
			$horni_paginace->vo->qsvar = $this->ns . 'ggpg';
			$horni_paginace->active = $this->pagination && $this->pagination != 'bottom';
		}

		if ($dolni_paginace)
		{
			$dolni_paginace->vo->total = $pages;
			$dolni_paginace->vo->current = $this->ggpage;
			$dolni_paginace->vo->qsvar = $this->ns . 'ggpg';
			$dolni_paginace->active = $this->pagination && $this->pagination != 'top';
		}
	}

	protected function updateGalleriesDetailTemplate()
	{
		$je_galerie = $this->_index->je_galerie;
		$neni_galerie = $this->_index->neni_galerie;

		foreach ($je_galerie as $elem)
			$elem->active = !empty($this->_galleries);

		foreach ($neni_galerie as $elem)
			$elem->active = empty($this->_galleries);

		if (!empty($this->_galleries))
			$this->updateTemplate_Gallery();
	}

/* update template helper methods: begin */

	protected function updateTemplate_Gallery(Var_Pool $gallery = null, $gsize = null)
	{
		if ($gallery === null)
			$gallery = reset($this->_galleries);

		if ($gsize === null)
			$gsize = $this->gsize;

		$this->updateTemplate_DetailLink($gallery);
		$this->updateTemplate_GalleryTitles($gallery);
		$this->updateTemplate_GalleryDescriptions($gallery);
		$this->updateTemplate_GalleryAuthors($gallery);
		$this->updateTemplate_GalleryDate($gallery);
		$this->updateTemplate_GalleryPictures($gallery, $gsize);
	}

	protected function updateTemplate_DetailLink(Var_Pool $gallery)
	{
		$detail = $this->_index->detail;

		if (!empty($detail))
		{
			$detailpage = $this->detailpage ? Project_Navigator::getPage($this->detailpage) : '';
			$this->resolveQS();
			$qso = clone $this->_qs;
			$qso->set($this->nsd . 'g', $gallery->GID);
			$qso->set($this->nsd . 'gg', $this->ggid);
			if ($this->ggpage > 1)
				$qso->set($this->nsd . 'ggpg', $this->ggpage);
			$detail_link = $detailpage . $qso->__toString();

			foreach ($detail as $elem)
				$elem->href = $detail_link;
		}
	}

	protected function updateTemplate_GalleryTitles(Var_Pool $gallery)
	{
		$vars = array
		(
			'nadpis_galerie',
			'puvodni_nadpis_galerie'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$nadpis_galerie_val = $gallery->GBTitle !== '' ? $gallery->GBTitle : $gallery->Title;
		foreach ($nadpis_galerie as $elem)
			$elem->text = $nadpis_galerie_val;

		foreach ($puvodni_nadpis_galerie as $elem)
			$elem->text = $gallery->Title;
	}

	protected function updateTemplate_GalleryDescriptions(Var_Pool $gallery)
	{
		$vars = array
		(
			'kratky_popis_galerie',
			'puvodni_kratky_popis_galerie',
			'dlouhy_popis_galerie',
			'puvodni_dlouhy_popis_galerie'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$kratky_popis_galerie_val = $gallery->GBShortDesc !== '' ? $gallery->GBShortDesc : $gallery->ShortDesc;
		foreach ($kratky_popis_galerie as $elem)
			$elem->text = $kratky_popis_galerie_val;

		foreach ($puvodni_kratky_popis_galerie as $elem)
			$elem->text = $gallery->ShortDesc;

		$dlouhy_popis_galerie_val = $gallery->GBLongDesc !== '' ? $gallery->GBLongDesc : $gallery->LongDesc;
		$content_elem = new HTML_Text($dlouhy_popis_galerie_val);
		$content_elem->raw = true;
		foreach ($dlouhy_popis_galerie as $elem)
			$elem->add($content_elem);

		$content_elem = new HTML_Text($gallery->LongDesc);
		$content_elem->raw = true;
		foreach ($puvodni_dlouhy_popis_galerie as $elem)
			$elem->add($content_elem);
	}

	protected function updateTemplate_GalleryAuthors(Var_Pool $gallery)
	{
		$vars = array
		(
			'autor_galerie',
			'puvodni_autor_galerie',
			'autor_galerie_jako_link',
			'puvodni_autor_galerie_jako_link'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$autor_galerie_val = $gallery->GBAuthor !== '' ? $gallery->GBAuthor : $gallery->Author;
		foreach ($autor_galerie as $elem)
			$elem->text = $autor_galerie_val;

		foreach ($puvodni_autor_galerie as $elem)
			$elem->text = $gallery->Author;

		foreach ($autor_galerie_jako_link as $elem)
		{
			$elem->href = 'http://' . $autor_galerie_val;
			$elem->clear();
			$elem->add(new HTML_Text($autor_galerie_val));
		}

		foreach ($puvodni_autor_galerie_jako_link as $elem)
		{
			$elem->href = 'http://' . $gallery->Author;
			$elem->clear();
			$elem->add(new HTML_Text($gallery->Author));
		}
	}

	protected function updateTemplate_GalleryDate(Var_Pool $gallery)
	{
		$datum_galerie = $this->_index->datum_galerie;

		foreach ($datum_galerie as $elem)
		{
			try
			{
				$elem->timestamp = $elem->vo->timestamp = $gallery->When; // nikoli pouze view_object, protoze se to muze jeste klonovat
			}
			catch (HTML_No_Such_Element_Attribute_Exception $e)
			{
				$datum_spec = null;
				try
				{
					$datum_spec = $elem->specification;
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
				}

				$datetime = new Date_Time($gallery->When);
				$elem->add(new HTML_Text($datetime->format($datum_spec === null ? 'j.n. Y' : $datum_spec)));
			}		
		}
	}

	protected function updateTemplate_GalleryPictures(Var_Pool $gallery, $gsize)
	{
		$vars = array
		(
			'ma_obrazky',
			'zadne_obrazky',
			'prazdna_stranka_galerie',
			'obrazek_vzor',
			'obrazky',
			'horni_paginace_galerie',
			'dolni_paginace_galerie'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$obrazek_vzor = $obrazek_vzor[0];

		foreach ($ma_obrazky as $elem)
			$elem->active = $gallery->Pictures !== null;

		foreach ($zadne_obrazky as $elem)
			$elem->active = $gallery->Pictures !== null && $gallery->PicturesTotal == 0;

		foreach ($prazdna_stranka_galerie as $elem)
			$elem->active = $gallery->Pictures !== null && $gallery->PicturesTotal > 0;

		$pages = (int)($gallery->PicturesTotal / $gsize);
		if ($pages * $gsize < $gallery->PicturesTotal) $pages++;

		if (!empty($horni_paginace_galerie))
			$horni_paginace_galerie = $horni_paginace_galerie[0];

		if (!empty($dolni_paginace_galerie))
			$dolni_paginace_galerie = $dolni_paginace_galerie[0];

		if ($horni_paginace_galerie)
		{
			$horni_paginace_galerie->vo->total = $pages;
			$horni_paginace_galerie->vo->current = $this->gpage;
			$horni_paginace_galerie->vo->qsvar = $this->ns . 'gpg';
			$horni_paginace_galerie->active = $this->pagination && $this->pagination != 'bottom';
		}

		if ($dolni_paginace_galerie)
		{
			$dolni_paginace_galerie->vo->total = $pages;
			$dolni_paginace_galerie->vo->current = $this->gpage;
			$dolni_paginace_galerie->vo->qsvar = $this->ns . 'gpg';
			$dolni_paginace_galerie->active = $this->pagination && $this->pagination != 'top';
		}

		foreach ($obrazky as $elem)
			$elem->clear();

		foreach ($gallery->Pictures as $picture)
		{
			$this->updateTemplate_Picture($picture, $gallery->Directory);
			$content_elem = clone $obrazek_vzor;
			foreach ($obrazky as $elem)
				$elem->add($content_elem);
		}
	}

	protected function updateTemplate_Picture(Var_Pool $picture, $directory = '')
	{
		$vars = array
		(
			'ma_vlastni_obrazek',
			'nema_vlastni_obrazek',
			'vlastni_obrazek',
			'vlastni_obrazek_jako_link',
			'puvodni_vlastni_obrazek',
			'puvodni_vlastni_obrazek_jako_link',
			'nahradni_obrazek_vzor',
			'nahradni_obrazek',
			'tag_obrazku'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$vlastni_obrazek_val = $picture->GPFileName !== '' ? "$directory/$picture->GPFileName" : $picture->FileName;
		$puvodni_vlastni_obrazek_val = $picture->FileName;
		$nadpis_val = $picture->GPTitle !== '' ? $picture->GPTitle : $picture->Title;
		$puvodni_nadpis_val = $picture->Title;

		foreach ($ma_vlastni_obrazek as $elem)
			$elem->active = $vlastni_obrazek_val !== '';

		foreach ($nema_vlastni_obrazek as $elem)
			$elem->active = $vlastni_obrazek_val === '';

		$pnav = new Project_Navigator;
		$galleries_directory = $pnav->images_dir . $pnav->get('galleries_dir', true);

		if ($vlastni_obrazek_val === '')
		{
			$img = clone $nahradni_obrazek_vzor[0];
			$thumb = $this->getThumbnailFileName($img->src, $this->thumbs);
			$img->src = $galleries_directory . $thumb;
			$img->alt = basename($thumb);
			foreach ($nahradni_obrazek as $elem)
				$elem->add($img);
		}
		elseif (!empty($vlastni_obrazek_jako_link) || !empty($vlastni_obrazek))
		{
			if (!empty($obrazek_img_vzor))
				$img = clone $obrazek_img_vzor[0];
			else
				$img = new HTML_Img;
			$thumb = $this->getThumbnailFileName($vlastni_obrazek_val, $this->thumbs);
			$img->src = $galleries_directory . $thumb;
			$img->alt = basename($thumb);
			if (!empty($obrazek_a_vzor))
			{
				$a = clone $obrazek_a_vzor[0];
				$a->clear();
			}
			else
				$a = new HTML_A;
			$a->href = $galleries_directory . $vlastni_obrazek_val;
			$a->title = $nadpis_val;
			Images_JS_Support::updateElement($this->images, $a);
			$a->add($img);

			foreach ($vlastni_obrazek_jako_link as $elem)
			{
				$elem->clear();
				$elem->add($a);
			}

			foreach ($vlastni_obrazek as $elem)
			{
				$elem->clear();
				$elem->add($img);
			}
		}

		if ($puvodni_vlastni_obrazek_val !== '' && (!empty($puvodni_vlastni_obrazek_jako_link) || !empty($puvodni_vlastni_obrazek)))
		{
			$img = new HTML_Img;
			$thumb = $this->getThumbnailFileName($puvodni_vlastni_obrazek_val, $this->thumbs);
			$img->src = $galleries_directory . $thumb;
			$img->alt = basename($thumb);
			$a = new HTML_A;
			$a->href = $galleries_directory . $puvodni_vlastni_obrazek_val;
			$a->title = $puvodni_nadpis_val;
			Images_JS_Support::updateElement($this->images, $a);
			$a->add($img);
		}

		foreach ($puvodni_vlastni_obrazek_jako_link as $elem)
			$elem->add($a);

		foreach ($puvodni_vlastni_obrazek_jako_link as $elem)
			$elem->add($img);

		$this->updateTemplate_PictureTitles($picture);
		$this->updateTemplate_PictureDescriptions($picture);
		$this->updateTemplate_PictureAuthors($picture);
		$this->updateTemplate_PictureDate($picture);		
	}

	protected function updateTemplate_PictureTitles(Var_Pool $picture)
	{
		$vars = array
		(
			'nadpis_obrazku',
			'puvodni_nadpis_obrazku'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$nadpis_obrazku_val = $picture->GPTitle !== '' ? $picture->GPTitle : $picture->Title;
		foreach ($nadpis_obrazku as $elem)
			$elem->text = $nadpis_obrazku_val;

		foreach ($puvodni_nadpis_obrazku as $elem)
			$elem->text = $picture->Title;
	}

	protected function updateTemplate_PictureDescriptions(Var_Pool $picture)
	{
		$vars = array
		(
			'kratky_popis_obrazku',
			'puvodni_kratky_popis_obrazku',
			'dlouhy_popis_obrazku',
			'puvodni_dlouhy_popis_obrazku'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$kratky_popis_obrazku_val = $picture->GPShortDesc !== '' ? $picture->GPShortDesc : $picture->ShortDesc;
		foreach ($kratky_popis_obrazku as $elem)
			$elem->text = $kratky_popis_obrazku_val;

		foreach ($puvodni_kratky_popis_obrazku as $elem)
			$elem->text = $picture->ShortDesc;

		$dlouhy_popis_obrazku_val = $picture->GPLongDesc !== '' ? $picture->GPLongDesc : $picture->LongDesc;
		$content_elem = new HTML_Text($dlouhy_popis_obrazku_val);
		$content_elem->raw = true;
		foreach ($dlouhy_popis_obrazku as $elem)
			$elem->add($content_elem);

		$content_elem = new HTML_Text($picture->LongDesc);
		$content_elem->raw = true;
		foreach ($puvodni_dlouhy_popis_obrazku as $elem)
			$elem->add($content_elem);
	}

	protected function updateTemplate_PictureAuthors(Var_Pool $picture)
	{
		$vars = array
		(
			'autor_obrazku',
			'puvodni_autor_obrazku',
			'autor_obrazku_jako_link',
			'puvodni_autor_obrazku_jako_link'
		);

		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		$autor_obrazku_val = $picture->GPAuthor !== '' ? $picture->GPAuthor : $picture->Author;
		foreach ($autor_obrazku as $elem)
			$elem->text = $autor_obrazku_val;

		foreach ($puvodni_autor_obrazku as $elem)
			$elem->text = $picture->Author;

		foreach ($autor_obrazku_jako_link as $elem)
		{
			$elem->href = 'http://' . $autor_obrazku_val;
			$elem->clear();
			$elem->add(new HTML_Text($autor_obrazku_val));
		}

		foreach ($puvodni_autor_obrazku_jako_link as $elem)
		{
			$elem->href = 'http://' . $picture->Author;
			$elem->clear();
			$elem->add(new HTML_Text($picture->Author));
		}
	}

	protected function updateTemplate_PictureDate(Var_Pool $picture)
	{
		$datum_obrazku = $this->_index->datum_obrazku;

		foreach ($datum_obrazku as $elem)
		{
			try
			{
				$elem->timestamp = $picture->When; // nikoli view_object, protoze se to bude jeste klonovat
			}
			catch (HTML_No_Such_Element_Attribute_Exception $e)
			{
				$datum_spec = null;
				try
				{
					$datum_spec = $elem->specification;
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
				}

				$datetime = new Date_Time($picture->When);
				$elem->add(new HTML_Text($datetime->format($datum_spec === null ? 'j.n. Y' : $datum_spec)));
			}		
		}
	}

/* update template helper methods: end */

	protected function loadData()
	{
		if ($this->_galleries === null)
		{
			if ($this->gid === null)
			{
				$gsize = (int)$this->gggsize;
				$ggpage = $this->ggpage === null ? 1 : $this->ggpage;
				$ggsize = (int)$this->ggsize;
				$this->_kiwi_module->loadGalleriesList($this->_galleries, $this->_total, $ggpage, $ggsize, $gsize);
			}
			else
			{
				$gsize = (int)$this->gsize;
				$gpage = $this->gpage === null ? 1 : $this->gpage;
				$this->_kiwi_module->loadGallery($this->_galleries, $this->gid, $gpage, $gsize);
			}
		}
	}

	protected function getThumbnailFileName($image, $thumb)
	{
		if ($extpos = strrpos($image, '.'))
		{
			$extension = substr($image, $extpos); // including .
			$base = substr($image, 0, $extpos + 1); // including .
			return $base . $thumb . $extension;
		}
		else
			return false;
	}

	protected function resolveQS()
	{
		if ($this->_qs === null)
		{
			$this->qs = Server::get('REQUEST_QUERY_STRING');
			$this->_qs->remove($this->ns . 'g');
			$this->_qs->remove($this->ns . 'gg');
			$this->_qs->remove($this->ns . 'ggpg');
			$this->_qs->remove($this->ns . 'gpg');
		}
	}
}
?>
