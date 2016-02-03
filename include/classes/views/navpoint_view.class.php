<?php
// todo: current qs updating support
class Navpoint_View extends View_Object
{
	protected $_a;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_a = new HTML_A;

		$this->_attributes->register('page');
		$this->_attributes->register('qs');
		$this->_attributes->register('popup');
		$this->_attributes->register('nobase');
	}

	public function get($name)
	{
		try
		{
			return $this->_a->get($name);
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
			$this->_a->set($name, $value);
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
				$onclick = $this->_a->onclick; // remember the onclick code
				$this->resolveHRef(); // modifies $this->_a->onclick in case popup attribute is used
				$this->_a->render($renderer);
				$this->_a->onclick = $onclick; // reinstate the onclick code
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
	}

	protected function resolveHRef()
	{
		if ($this->page !== null)
		{
			if ($this->page == 'current')
			{
				$page = Server::get('REQUEST_URL');
				$qs = Server::get('REQUEST_QUERY_STRING');
				if ($qs !== '')
					$qs = "?$qs";
			}
			else
			{
				$page = Project_Navigator::getPage($this->page, $this->nobase !== null);
				$qs = $this->qs === null ? '' : "?$this->qs";
			}

			$link = $page . $qs;
			$this->_a->href = $link;

			if ($this->popup !== null)
			{
				$onclick = $this->_a->onclick;
				if ($onclick === null)
					$onclick = '';
				if ($onclick !== '' && substr($onclick, -1) != ';')
					$onclick .= ';';
				if (substr($onclick, -13) == 'return false;')
					$onclick = substr($onclick, 0, -13);
				$onclick .= "window.open('$link','_blank','$this->popup');return false";
				$this->_a->onclick = $onclick;
			}

			if ($this->content !== null)
			{
				$this->_a->clear();
				$this->_a->add($this->content, false);
			}
		}
		else
			throw new Data_Insufficient_Exception('page');
	}
}
?>
