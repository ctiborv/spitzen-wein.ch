<?php
class HTML_Index_Out_Of_Range_Exception extends HTML_Builder_Exception
{
	public function __construct($index, $rangeA = null, $rangeB = null, $subcode = 10)
	{
		if ($rangeA !== null && $rangeB !== null)
		{
			if ($rangeB > $rangeA)
			{
				$rangeBr = $rangeB - 1;
				$range = " vs [$rangeA, $rangeBr]";
			}
			else
				$range = ' vs empty range';
		}
		else
			$range = '';

		parent::__construct("Index is out of range: $index$range", $subcode);
	}
}
?>