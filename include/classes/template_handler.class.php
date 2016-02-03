<?php
class Template_Handler
{
	protected static $vm = null;
	protected static $builder = null;
	protected static $renderer = null;

	protected function __construct()
	{
	}

	public static function getRenderer()
	{
		if (self::$renderer === null)
			self::$renderer = new Text_Renderer('');
		return self::$renderer;
	}

	public static function render($template, $vm_or_builder = null, $renderer = null)
	{
		if ($vm_or_builder === null)
		{
			if (self::$vm === null)
				self::$vm = new View_Manager;

			if (self::$builder === null)
				self::$builder = new HTML_Custom_Builder(self::$vm);

			$view_manager = self::$vm;
			$builder = self::$builder;
		}
		elseif ($vm_or_builder instanceof HTML_Custom_Builder)
		{
			$view_manager = $vm_or_builder->vm;
			$builder = $vm_or_builder;
		}
		elseif ($vm_or_builder instanceof View_Manager)
		{
			$view_manager = $vm_or_builder;
			$builder = new HTML_Custom_Builder($view_manager);
		}
		else
			throw new Invalid_Argument_Type_Exception('vm_or_builder', $vm_or_builder);

		if ($renderer === null)
			$renderer = self::getRenderer();

		try
		{
			$head = Template_Loader::load($template . '.head', $builder);
		}
		catch (File_Not_Found_Exception $e)
		{
			if ($hyppos = strpos($template, '-'))
			{
				try
				{
					$head = Template_Loader::load(substr($template, 0, $hyppos + 1) . 'default.head', $builder);
				}
				catch (File_Not_Found_Exception $e)
				{
					try
					{
						$head = Template_Loader::load('default.head', $builder);
					}
					catch (File_Not_Found_Exception $e)
					{
						throw new Template_Not_Found_Exception("Header doesn't exist for template file: $template");
					}
				}
			}
			else
			{
				try
				{
					$head = Template_Loader::load('default.head', $builder);
				}
				catch (File_Not_Found_Exception $e)
				{
					throw new Template_Not_Found_Exception("Header doesn't exist for template file: $template");
				}					
			}
		}

		try
		{
			$body = Template_Loader::load($template, $builder);
		}
		catch (File_Not_Found_Exception $e)
		{
			throw new Template_Not_Found_Exception("Template file doesn't exist: $template");
		}

		$view_manager->assignHead($head);
		$view_manager->assignBody($body);
		$view_manager->initialize();
		$view_manager->handleInput();
		$view_manager->resolve();
		$view_manager->handleRedirections();

		$renderer->render('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		$renderer->renderNL('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">');
		$renderer->renderNL();
		$view_manager->head->render($renderer);

		$pconf = new Project_Config;
		try
		{
			$prefix = $pconf->html_prefix;
		}
		catch (No_Such_Variable_Exception $e)
		{
			$prefix = "\t";
		}
		$renderer->prefix = $prefix;
		$view_manager->body->render($renderer);
		$renderer->renderNL('</html>');
	}
}
?>