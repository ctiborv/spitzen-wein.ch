<?php
class Template_Loader
{
	protected function __construct()
	{
	}

	public static function load($template, $builder = null)
	{
		$pnav = new Project_Navigator;
		$dir = $pnav->getRelative('tpl_dir');
		$cdir = $pnav->getRelative('tpl.php_dir');

		$pconf = new Project_Config;
		$ext = $pconf->templates['extension'];
		$compile = $pconf->templates['compile'];
		$cext = $pconf->templates['compiled_extension'];

		$t_file = $dir . $template . $ext;
		$ct_file = $cdir . $template . $cext;

		if ($t_file_exists = file_exists($t_file))
			$tf_time = filemtime($t_file);

		if ($ct_file_exists = file_exists($ct_file))
			$ctf_time = filemtime($ct_file);

		if ($builder === null)
			$builder = new HTML_Builder;

		$ct_file_available = $ct_file_exists && (!$t_file_exists || $ctf_time >= $tf_time);

		if ($ct_file_available)
			return self::loadCompiled($ct_file, $builder);
		elseif ($t_file_exists)
		{
			if ($compile)
			{
				self::compile($t_file, $ct_file);
				return self::loadCompiled($ct_file, $builder);
			}
			else
				return self::build($t_file, $builder);
		}
		else
			throw new File_Not_Found_Exception($t_file);
	}

	protected static function loadCompiled($filename, HTML_Builder $_b_)
	{
		$_e_ = array();
		require $filename;
		return $_e_[0];
	}

	protected static function compile($source, $destination)
	{
		$pconf = new Project_Config;
		if (isset($pconf->templates['remote_parser']) && $pconf->templates['remote_parser'])
			$parser = new Remote_HTML_Parser($pconf->templates['remote_parser']);
		else
			$parser = new HTML_Parser;
		if (array_key_exists('dtd', $pconf->templates))
			$parser->dtd = $pconf->templates['dtd'];
		$parser->parse($source);
		$code = $parser->compile();
		if ($code !== '' && substr($code, 0, 4) == '$_e_')
			$code = "<?php\n" . $code . "\n?>";
		else
		{
			if ($_e_pos = mb_strpos($code, '$_e_', 0, 'UTF-8'))
				$code = mb_substr($code, 0, $_e_pos, 'UTF-8');
		}
		file_put_contents($destination, $code);
		@chmod($destination, 0666);
	}

	protected static function build($filename, HTML_Builder $builder)
	{
		$pconf = new Project_Config;
		if (isset($pconf->templates['remote_parser']) && $pconf->templates['remote_parser'])
			$parser = new Remote_HTML_Parser($pconf->templates['remote_parser'], $builder);
		else
			$parser = new HTML_Parser($builder);
		$parser->parse($filename);
		return $parser->build();
	}
}
?>