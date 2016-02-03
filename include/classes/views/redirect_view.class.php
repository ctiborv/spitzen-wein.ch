<?php
class Redirect_View extends View_Object
{
	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);

		$this->_attributes->register('code', 303);
		$this->_attributes->register('schema');
		$this->_attributes->register('host');
		$this->_attributes->register('target');
		$this->_attributes->register('navpoint');
		$this->_attributes->register('qs');
	}

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'default':
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _initialize()
	{
		if ($this->code === null)
			throw new Data_Insufficient_Exception('code');

		if (($this->target === null || $this->target === '') && ($this->navpoint === null || $this->navpoint === ''))
			throw new Data_Insufficient_Exception('target or navpoint');

		if (($this->target !== null && $this->target !== '') && ($this->navpoint !== null && $this->navpoint !== ''))
			throw new Data_Superfluous_Exception('target', 'navpoint');
	}

	protected function _handleInput()
	{
	}

	protected function _resolve()
	{
		$this->_redirection = true;
	}

	protected function _handleRedirection()
	{
		if ($this->_redirection !== null)
		{
			if ($this->navpoint !== null && $this->navpoint !== '')
			{
				$navigator = new Project_Navigator;
				$to = $navigator->get($this->navpoint);
			}
			else
				$to = $this->target;

			if ($this->qs !== null)
			{
				if ($this->qs[0] !== '?')
					$to .= '?';
				$to .= $this->qs;
			}

			Redirection::redirectPage($to, $this->code, $this->schema, $this->host);
		}
	}
}
?>
