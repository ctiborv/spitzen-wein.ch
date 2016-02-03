<?php
class Pagination_View extends Template_Based_View
{
	protected $_qs;

	public function __construct(View_Manager $view_manager)
	{
		parent::__construct($view_manager);
		$this->_qs = null;

		$this->_attributes->register('radius', 2); // display radius around current page number
		$this->_attributes->register('total'); // total pages
		$this->_attributes->register('current'); // current page
		$this->_attributes->register('link'); // navigation point for target links
		$this->_attributes->register('qsvar', 'pg'); // query string variable name used for page number in the target links
		// $this->_attributes->register('qs'); // query string to preserve // special variable

		$this->template = 'pagination/default'; // default template
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

	protected function _render(Text_Renderer $renderer, $view_case)
	{
		switch ($view_case)
		{
			case 'top':
			case 'bottom':
				parent::_render($renderer, 'default');
				break;
			default:
				parent::_render($renderer, $view_case);
		}
	}

	protected function _handleInput()
	{
		if ($this->current === null && $this->qsvar === null)
			throw new Data_Insufficient_Exception('current or qsvar');

		if ($this->qsvar !== null && $this->current === null)
		{
			if (array_key_exists($this->qsvar, $_GET))
				$this->current = (int)$_GET[$this->qsvar];
		}
	}

	protected function _updateTemplate()
	{
		if ($this->total === null)
		{
			// if total is not given, don't render anything
			$this->_root->clear();
			return;
		}

		$vars = array
		(
			'obsah',
			'lista',
			'aktualni',
			'celkem'
		);
		foreach ($vars as $varname)
			$$varname = $this->_index->$varname;

		foreach ($aktualni as $elem)
			$elem->add(new HTML_Text($this->current));

		foreach ($celkem as $elem)
			$elem->add(new HTML_Text($this->total));

		if (!empty($lista))
		{
			$pagination_elem = new HTML_Inline_Group;
			$this->generatePagination($pagination_elem);
			foreach ($lista as $elem)
				$elem->add($pagination_elem);
		}

		$exclusive_content = false;
		foreach ($obsah as $elem)
		{
			if ($elem->specification !== null && (int)$elem->specification == (int)$this->total)
			{
				$exclusive_content = true;
				break;
			}
		}

		if ($exclusive_content)
			foreach ($obsah as $elem)
				$elem->active = $elem->specification !== null && (int)$elem->specification == (int)$this->total;
		else
			foreach ($obsah as $elem)
				$elem->active = $elem->specification === null;
	}

	protected function generatePagination(HTML_Inline $node)
	{
		$this->resolveQS();

		$mid = array(max(1, $this->current - $this->radius), min($this->current + $this->radius, $this->total));
		$left = array(1, $mid[0] - 1);
		$right = array($mid[1] + 1, $this->total);

		$this->generateLeftInterval($node, $left);
		$this->generateMidInterval($node, $mid, $this->current);
		$this->generateRightInterval($node, $right);
	}

	protected function generateLeftInterval(HTML_Inline $node, $interval)
	{
		$i = $interval[0];
		$j = $interval[1];
		if ($j - $i > 2)
		{
			$this->generateNavNumber($node, $i);
			$node->add(new HTML_Text(' ... '));
		}
		else
			while ($i <= $j)
			{
				$this->generateNavNumber($node, $i++);
				$node->add(new HTML_Text(' '));
			}
	}

	protected function generateMidInterval(HTML_Inline $node, $interval, $current)
	{
		$i = $interval[0];
		$j = $interval[1];
		$loop = $i <= $j;
		while ($loop)
		{
			if ($i == $current)
				$this->generateCurNumber($node, $i);
			else
				$this->generateNavNumber($node, $i);
			if ($loop = (++$i <= $j))
				$node->add(new HTML_Text(' '));
		}
	}

	protected function generateRightInterval(HTML_Inline $node, $interval)
	{
		$i = $interval[0];
		$j = $interval[1];
		if ($j - $i > 2)
		{
			$node->add(new HTML_Text(' ... '));
			$this->generateNavNumber($node, $j);
		}
		else
			while ($i <= $j)
			{
				$node->add(new HTML_Text(' '));
				$this->generateNavNumber($node, $i++);
			}
	}

	protected function generateNavNumber(HTML_Inline $node, $num)
	{
		$link = new HTML_A;
		$link->href = $this->getHRef($num);
		$link->title = '';
		$link->add(new HTML_Text($num));
		$node->add($link);
	}

	protected function generateCurNumber(HTML_Inline $node, $num)
	{
		$strong = new HTML_Strong;
		$strong->add(new HTML_Text($num));
		$node->add($strong);
	}

	protected function getHRef($page)
	{
		$href = $this->link === null ? Server::get('REQUEST_URL') : Project_Navigator::getPage($this->link);
		$qso = clone $this->_qs;
		if ($page != 1)
			$qso->set($this->qsvar, $page);
		$qs = $qso->__toString();
		return $href . $qs;
	}

	protected function resolveQS()
	{
		if ($this->_qs === null)
		{
			$this->qs = Server::get('REQUEST_QUERY_STRING');
			$this->_qs->remove($this->qsvar);
		}
	}
}
?>
