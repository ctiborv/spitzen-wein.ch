<?php
interface HTML_Container
{
	public function __clone();
	public function getAt($index);
	public function getContents();
	public function count();
	public function countActive();
	public function add(HTML_Element $element, $content_check = true);
	public function addElements(Array $elements);
	public function removeByEId($id);
	public function activateByEId($id);
	public function deactivateByEId($id);
}
?>