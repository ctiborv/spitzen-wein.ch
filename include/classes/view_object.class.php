<?php
abstract class View_Object implements Renderable
{
	protected $_view_manager;

	protected $_redirection;
	protected $_initialized;

	protected $_attributes;

	public function __construct(View_Manager $view_manager)
	{
		$this->_view_manager = $view_manager;

		$this->_redirection = null;
		$this->_initialized = false;

		$this->_attributes = new Typed_Var_Pool('VO/' . get_class($this));

		$this->_attributes->register('active', true);
		$this->_attributes->register('vid');
		$this->_attributes->register('content');
		$this->_attributes->register('ifmodactive'); // render only if given module is active
	}

	public function __clone()
	{
		throw new Class_Not_Clonable_Exception(get_class($this));
	}

	public function get($name)
	{
		return $this->_attributes->get($name);
	}

	public function set($name, $value)
	{
		$this->_attributes->set($name, $value);
	}

	final public function __get($name)
	{
		return $this->get($name);
	}

	final public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	protected function _render(Text_Renderer $renderer, $view)
	{
		throw new Undefined_View_Case_Exception($view);
	}

	public function render(Text_Renderer $renderer, $view = 'default')
	{
		if ($this->active)
		{
			if ($this->_initialized)
				$this->_render($renderer, $view);
			else
				throw new View_Not_Initialized_Exception(get_class($this), $this->vid);
		}
	}

	public function getHTML($prefix = '', $view = 'default', $step_length = 1, $newline = "\n")
	{
		$plen = strlen($prefix);
		$level = $plen / $step_length;
		$pref = substr($prefix, 0, $step_length);
		$renderer = new Text_Renderer($pref, $newline);
		$renderer->level = $level;
		$this->render($renderer, $view);
		return $renderer->text;
	}

	abstract protected function _initialize();

	public function initialize()
	{
		if ($this->_initialized)
			throw new View_Already_Initialized_Exception(__CLASS__, $this->vid);
		else
		{
			$this->_initialize();
			$this->_initialized = true;
			if ($this->ifmodactive !== NULL && $this->active)
				$this->active = Kiwi_Module::getModuleActiveStatus($this->ifmodactive);
		}
	}

	abstract protected function _handleInput();

	public function handleInput()
	{
		if (!$this->_initialized)
			throw new View_Not_Initialized_Exception(get_class($this), $this->vid);
		elseif ($this->active)
			$this->_handleInput();
	}

	abstract protected function _resolve();

	public function resolve()
	{
		if (!$this->_initialized)
			throw new View_Not_Initialized_Exception(get_class($this), $this->vid);
		elseif ($this->active)
			$this->_resolve();
	}

	protected function _handleRedirection()
	{
		if ($this->_redirection !== null)
			Redirection::redirectNavPoint($this->_redirection);
	}

	public function handleRedirection()
	{
		if (!$this->_initialized)
			throw new View_Not_Initialized_Exception(get_class($this), $this->vid);
		elseif ($this->active)
			$this->_handleRedirection();
	}
}
?>