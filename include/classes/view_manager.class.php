<?php
class View_Manager
{
	protected $_views;
	protected $_pending_wrappers;
	protected $_ai;

	protected $_status;

	protected $head;
	protected $body;

	const NO_FLAGS = 0;
	const INITIALIZED = 1;
	const INPUT_HANDLED = 2;
	const RESOLVED = 4;
	const REDIRECTION_HANDLED = 8;

	public function __construct()
	{
		$this->_views = array();
		$this->_pending_wrappers = array();
		$this->_ai = 1;

		$this->_status = 0;

		$this->head = null;
		$this->body = null;
	}

	public function get($name)
	{
		switch ($name)
		{
			case 'status':
				return $this->_status;
			case 'head':
				return $this->head;
			case 'body':
				return $this->body;
			default:
				throw new No_Such_Variable_Exception($name, __CLASS__);
		}
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	public function assignHead(HTML_Element $element)
	{
		if ($element->tag == 'head')
		{
			$this->head = $element;
			return;
		}
		else
		{
			if ($element instanceof HTML_Container)
			{
				try
				{
					$sub_element = $element->getAt(0);
					if ($sub_element->tag == 'head')
					{
						$this->head = $sub_element;
						return;
					}
				}
				catch (HTML_Index_Out_Of_Range_Exception $e)
				{
				}
			}
		}

		$match = $element->getElementsBy('tag', 'head');
		if (!empty($match))
			$this->head = $match[0];
		else
			throw new Root_Tag_Not_Found_Exception('head');
	}

	public function assignBody(HTML_Element $element)
	{
		if ($element->tag == 'body')
		{
			$this->body = $element;
			return;
		}
		else
		{
			if ($element instanceof HTML_Container)
			{
				try
				{
					$sub_element = $element->getAt(0);
					if ($sub_element->tag == 'body')
					{
						$this->body = $sub_element;
						return;
					}
				}
				catch (HTML_Index_Out_Of_Range_Exception $e)
				{
				}
			}
		}

		$match = $element->getElementsBy('tag', 'body');
		if (!empty($match))
			$this->body = $match[0];
		else
			throw new Root_Tag_Not_Found_Exception('body');
	}

	public function getView($view_class, $view_id)
	{
		if (array_key_exists($view_id, $this->_views))
		{
			if (get_class($this->_views[$view_id]['object']) == $view_class)
				return $this->_views[$view_id]['object'];
			else
				throw new View_Class_Mismatch_Exception($view_id, $view_class, get_class($this->_views[$view_id]['object']));
		}
		else
			throw new Undefined_View_Exception($view_id);
	}

	public function createView($view_class, $view_id = null)
	{
		if ($view_id === null)
			$view_id = 'anonymous_' . $this->_ai++;
		if (array_key_exists($view_id, $this->_views))
			throw new View_Already_Exists_Exception($view_id);
		$this->_views[$view_id] = array
		(
			'object' => new $view_class($this),
			'status' => self::NO_FLAGS
		);
		if ($this->_status & self::INITIALIZED)
		{
			$this->_views[$view_id]['object']->initialize();
			$this->_views[$view_id]['status'] |= self::INITIALIZED;
		}
		if ($this->_status & self::INPUT_HANDLED)
		{
			$this->_views[$view_id]['object']->handleInput();
			$this->_views[$view_id]['status'] |= self::INPUT_HANDLED;
		}
		if ($this->_status & self::RESOLVED)
		{
			$this->_views[$view_id]['object']->resolve();
			$this->_views[$view_id]['status'] |= self::RESOLVED;
		}		
		return $this->_views[$view_id]['object'];
	}

	public function dropView($view_id)
	{
		if (array_key_exists($view_id, $this->_views))
			unset($this->_views[$view_id]);
		else
			throw new Undefined_View_Exception($view_id);
	}

	public function registerWrapper(View_Wrapper $wrapper)
	{
		if (empty($this->_views))
			$this->_pending_wrappers[] = $wrapper;
		else
			$wrapper->loadViewObject();
	}

	public function createViews()
	{
		if (!empty($this->_pending_wrappers))
		{
			foreach ($this->_pending_wrappers as &$wrapper)
				$wrapper->loadViewObject();
			$this->_pending_wrappers = array();
		}
	}

	public function initialize()
	{
		$this->createViews();
		foreach ($this->_views as &$view)
			if (!($view['status'] & self::INITIALIZED))
			{
				$view['object']->initialize();
				$view['status'] |= self::INITIALIZED;
			}
		$this->_status |= self::INITIALIZED;
	}

	public function handleInput()
	{
		foreach ($this->_views as &$view)
			if (($view['status'] & (self::INPUT_HANDLED | self::INITIALIZED)) == self::INITIALIZED)
			{
				$view['object']->handleInput();
				$view['status'] |= self::INPUT_HANDLED;
			}
		$this->_status |= self::INPUT_HANDLED;
	}

	public function resolve()
	{
		foreach ($this->_views as &$view)
			if (($view['status'] & (self::RESOLVED | self::INITIALIZED)) == self::INITIALIZED)
			{
				$view['object']->resolve();
				$view['status'] |= self::RESOLVED;
			}
		$this->_status |= self::RESOLVED;
	}

	public function handleRedirections()
	{
		foreach ($this->_views as &$view)
			if (($view['status'] & (self::REDIRECTION_HANDLED | self::INITIALIZED)) == self::INITIALIZED)
			{
				$view['object']->handleRedirection();
				$view['status'] |= self::REDIRECTION_HANDLED;
			}
	}
}
?>