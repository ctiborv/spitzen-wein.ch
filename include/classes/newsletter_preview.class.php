<?php
// @TODO otestovat
class Newsletter_Preview
{
	protected $_id;
	protected $_messageBuilder;

	public function __construct($id, Message_Builder $builder)
	{
		$this->_id = (int) $id;
		$this->_messageBuilder = $builder;
	}

	public function render()
	{
		if ($newsletter = Newsletters::get($this->_id))
		{
			$html = $this->_messageBuilder->build(array(
				'newsletter' => $newsletter,
			));
		}
		else
		{
			$html = '';
		}

		echo $html;
	}
}
