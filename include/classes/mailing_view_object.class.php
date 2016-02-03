<?php
interface Mailing_View_Object
{
	public function getMailTemplateSource();
	public function updateMailBodyTemplate(HTML_Element $root, HTML_Indexer $index, array $fields);
	public function getMailBodySimpleTable();
	public function getMailBodyFields();
	public function getMailSender();
	public function getMailRecipient();
	public function getMailSubject();
	// also properties: contact, subject
}
?>
