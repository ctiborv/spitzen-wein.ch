<?php
class HTML_Custom_Builder extends HTML_Builder
{
	protected $_view_manager;

	public function __construct(View_Manager $view_manager = null)
	{
		parent::__construct();
		if ($view_manager === null)
			$this->_view_manager = new View_Manager;
		else
			$this->_view_manager = $view_manager;
	}

	public function __get($name)
	{
		if ($name == 'vm' || $name == 'view_manager')
			return $this->_view_manager;
		else
			throw new No_Such_Variable_Exception($name, __CLASS__);
	}

	public function build($tagName)
	{
		$instance = null;

		try
		{
			$instance = parent::build($tagName);
		}
		catch (HTML_No_Such_Tag_Exception $e)
		{
			$className = $this->resolveClassName($tagName);
			$instance = new HTML_View_Wrapper($this->_view_manager, $className);
		}

		return $instance;
	}

	protected function resolveClassName($tagName)
	{
		$tag_parts = explode('_', $tagName);
		foreach ($tag_parts as &$part)
			$part = strtoupper(substr($part, 0, 1)) . substr($part, 1);
		$result = implode('_', $tag_parts) . '_View';
		return $result;
	}
}
?>