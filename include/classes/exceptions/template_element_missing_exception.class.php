<?php
class Template_Element_Missing_Exception extends Template_Exception
{
	public function __construct($eid, $subcode = 1)
	{
		parent::__construct("Required template element missing: $eid", $subcode);
	}
}
?>