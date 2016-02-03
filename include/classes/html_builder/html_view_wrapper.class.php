<?php
class HTML_View_Wrapper extends HTML_Entity implements HTML_Head_Content, HTML_Block, HTML_Inline, HTML_List_Content, HTML_DL_Content, HTML_Table_Content, HTML_TR_Content, View_Wrapper
{
	protected $_view_manager;

	protected $_view_class;
	protected $_view_case;

	protected $_content;

	protected $_view_object;

	public function __construct(View_Manager $view_manager, $view_class, $view_case = 'default')
	{
		$this->_tag = $view_class;
		parent::__construct();

		$this->_view_manager = $view_manager;

		$this->_view_class = $view_class;
		$this->_view_case = $view_case;

		$this->_content = null;

		$this->_view_object = null;

		$this->_attributes->register('vid');

		$this->_view_manager->registerWrapper($this);
	}

	public function __clone()
	{
		parent::__clone();
		$this->_view_object = null;
		$this->_view_manager->registerWrapper($this);
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'case':
			case 'view':
				return $this->_view_case;
			case 'vo':
				return $this->_view_object;
			case 'content':
				return $this->_content;
			default:
				try
				{
					return parent::get($name);
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
					return null;
				}
		}
	}

	public function set($name, $value)
	{
		switch ($name)
		{
			case 'case':
			case 'view':
				$this->_view_case = $value;
				break;
			case 'vo':
				throw new Readonly_Variable_Exception($name);		
			case 'content':
				$this->_content = $value;
				break;
			default:
				try
				{
					parent::set($name, $value);
				}
				catch (HTML_No_Such_Element_Attribute_Exception $e)
				{
					$this->_attributes->register($name, $value);
				}
		}
	}

	public function add(HTML_Element $element, $content_check = true)
	{
		if ($this->_content === null)
			$this->_content = new HTML_Group;
		$this->_content->add($element, $content_check);
	}

	protected function loadView()
	{
		$v_id = $this->_attributes->vid;
		
		if ($v_id !== null)
		{
			try
			{
				$this->acquireViewObject($this->_view_class, $v_id);
			}
			catch (Undefined_View_Exception $e)
			{
				$this->createViewObject($this->_view_class, $v_id);
			}
		}
		else
		{
			$this->createViewObject($this->_view_class, $v_id);
		}
	}

	protected function acquireViewObject($view_class, $view_id)
	{
		$this->_view_object = $this->_view_manager->getView($this->_view_class, $view_id);
		$attribs = $this->_attributes->toArray();
		foreach ($attribs as $attrib => $value)
			if ($value !== null && $attrib != 'eid' && $attrib != 'vid')
				throw new View_Data_Redefinition_Exception($view_id, $attrib);
		$events = $this->_events->toArray();
		foreach ($events as $event => $value)
			if ($value !== null)
				throw new View_Data_Redefinition_Exception($view_id, $event);
		if ($this->_content !== null)
			throw new View_Data_Redefinition_Exception($view_id, 'content');
	}

	protected function createViewObject($view_class, $view_id)
	{
		$this->_view_object = $this->_view_manager->createView($view_class, $view_id);
		$attribs = $this->_attributes->toArray();
		foreach ($attribs as $attrib => $value)
			if ($value !== null && $attrib != 'eid')
				$this->_view_object->set($attrib, $value);
		$events = $this->_events->toArray();
		foreach ($events as $event => $value)
			if ($value !== null)
				$this->_view_object->set($event, $value);
		if ($this->_content !== null)
			$this->_view_object->content = $this->_content;
	}

	public function loadViewObject()
	{
		if ($this->_view_object === null)
			$this->loadView();
	}

	public function getViewObject()
	{
		if ($this->_view_object === null)
			$this->loadView();
		return $this->_view_object;
	}

	protected function renderINL(Text_Renderer $renderer)
	{
	}

	protected function renderBegin(Text_Renderer $renderer)
	{
	}

	protected function renderContent(Text_Renderer $renderer)
	{
		$this->getViewObject()->render($renderer, $this->_view_case);
	}

	protected function renderEnd(Text_Renderer $renderer)
	{
	}
}
?>
