<?php
class Article_View extends Template_Based_View
{
	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_attributes->register('mid'); // module id in database
		$this->_attributes->register('name'); // module name in database
		$this->_attributes->register('mactive'); // module activity status
		$this->_attributes->register('title'); // title to be displayed if template supports it
		$this->_attributes->register('images'); // attribute which says if images should be rendered in special way

		$this->template = 'article/default'; // default template
	}

	protected function _initialize()
	{
		//$module = new Kiwi_Text_Module($this->mid, $this->name);
		$module = Kiwi_Object_Manager::load('Kiwi_Text_Module', array($this->mid, $this->name)); // allows caching

		// non-overridable attributes (module attributes have priority)
		$attr_no = array
		(
			'mid' => 'id',
			'name' => 'name',
			'mactive' => 'active'
		);
		foreach ($attr_no as $attr => $mattr)
			if ($module->$mattr !== null)
				$this->$attr = $module->$mattr;

		// overridable attributes (tag attributes have priority)
		$attr_o = array
		(
			'title' => 'name',
			'content' => 'content'
		);
		foreach ($attr_o as $attr => $mattr)
			if ($this->$attr === null)
				$this->$attr = $module->$mattr;

		parent::_initialize();
	}

	protected function _handleInput()
	{
	}

	protected function _updateTemplate()
	{
		$vars = array
		(
			'nadpis',
			'obsah',
			'ma_obsah',
			'nema_obsah',
			'neni_aktivni'
		);
		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		foreach ($nadpis as $elem)
			$elem->text = $this->title;

		$neni_aktivni_val = !$this->mactive;
		$ma_obsah_val = $this->mactive && $this->content !== ''; // direct content is always considered non-empty
		$nema_obsah_val = $this->mactive && $this->content === '';

		foreach ($ma_obsah as $elem)
			$elem->active = $ma_obsah_val;

		foreach ($nema_obsah as $elem)
			$elem->active = $nema_obsah_val;

		foreach ($neni_aktivni as $elem)
			$elem->active = $neni_aktivni_val;

		if (!empty($obsah))
		{
			if ($this->content instanceof HTML_Element)
			{
				Images_JS_Support::updateHTML($this->images, $this->content);
				foreach ($obsah as $elem)
					$elem->add($this->content, false);
			}
			else
			{
				/*
				 * NOTE:
				 * This could be realized using HTML_Builder library, but only if html contents of individual modules is precompiled
				 * in Administrative application during the save operation. (compilation just in time would not be efficient, althought
				 * even that is possible with change detection just in time to avoid repetitive compilations)
				 *
				 * NOTE:
				 * However such solution could lead to exceptions caused by non-valid xhtml content in these modules, and that would
				 * have to be resolved somehow. Probably by an alternative way of filtering these modules, such as current way using
				 * regexp replacement.
				 */

				$content_elem = new HTML_Text(Images_JS_Support::updateHTMLRaw($this->images, $this->content));
				$content_elem->raw = true;
				foreach ($obsah as $elem)
					$elem->add($content_elem);
			}
		}
	}

	protected function updateHead()
	{
		Images_JS_Support::updateHead($this->images, $this->_view_manager->head);
	}
}
?>