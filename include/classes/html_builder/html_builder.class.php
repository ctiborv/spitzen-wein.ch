<?php
class HTML_Builder
{
	protected static $_tags = array
	(
		'a' => 'A',
		'abbr' => 'Abbr',
		'acronym' => 'Acronym',
		'b' => 'B',
		'base' => 'Base',
		'big' => 'Big',
		'block' => 'Block_Group', // special tag for group of html entities that belong to parametric entity %Block
		'body' => 'Body',
		'br' => 'Br',
		'caption' => 'Caption',
		'cite' => 'Cite',
		'code' => 'Code',
		'col' => 'Col',
		'colgroup' => 'ColGroup',
		'columns' => 'Columns', // special tag for group of table columns
		'dd' => 'DD',
		'dfn' => 'Dfn',
		'div' => 'Div',
		'dl' => 'DL',
		'dt' => 'DT',
		'em' => 'Em',
		'fieldset' => 'FieldSet',
		'flow' => 'Flow_Group', // special tag for group of html entities that belong to parametric entity %Flow
		'form' => 'Form',
		'group' => 'Group', // special tag for group of html entities
		'h1' => 'H1',
		'h2' => 'H2',
		'h3' => 'H3',
		'h4' => 'H4',
		'h5' => 'H5',
		'h6' => 'H6',
		'he' => 'HE', // special tag for rendering html entities without substitution
		'head' => 'Head',
		'hgroup' => 'Head_Group', // special tag for group of html entities that can be contained in the "head" node
		'hr' => 'HR',
		'i' => 'I',
		'img' => 'Img',
		'inline' => 'Inline_Group', // special tag for group of html entities that belong to parametric entity %Inline
		'input' => 'Input',
		'javascript' => 'JavaScript', // special alias tag for script with the type attribute set to "text/javascript"
		'kbd' => 'Kbd',
		'li' => 'LI',
		'link' => 'Link',
		'links' => 'Links', // special tag for group of links
		'list' => 'List_Group', // special tag for group of html entities that can be contained in the "ul" node
		'meta' => 'Meta',
		'newline' => 'NewLine', // special tag for rendering new line
		'noscript' => 'NoScript',
		'ol' => 'OL',
		'optgroup' => 'OptGroup',
		'option' => 'Option',
		'p' => 'P',
		'rows' => 'Rows', // special tag for group of table rows
		'samp' => 'Samp',
		'script' => 'Script',
		'scripts' => 'Scripts', // special tag for group of scripts
		'select' => 'Select',
		'small' => 'Small',
		'span' => 'Span',
		'strong' => 'Strong',
		'style' => 'Style',
		'table' => 'Table',
		'tbody' => 'TBody',
		'td' => 'TD',
		'text' => 'Text_Group', // special tag for text content
		'textarea' => 'TextArea',
		'text_content' => 'Text', // special tag for untagged text content
		'tfoot' => 'TFoot',
		'th' => 'TH',
		'thead' => 'THead',
		'title' => 'Title',
		'tr' => 'TR',
		'tt' => 'TT',
		'ul' => 'UL',
		'var' => 'Var'
	);

	public function __construct()
	{
	}

	public function build($tagName)
	{
		$tag = strtolower($tagName);

		if (array_key_exists($tag, self::$_tags))
		{
			$className = 'HTML_' . self::$_tags[$tag];
			return new $className;
		}
		else
			throw new HTML_No_Such_Tag_Exception($tagName);
	}
}
?>
