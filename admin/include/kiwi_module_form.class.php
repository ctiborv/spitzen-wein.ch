<?php
require_once 'utils.inc.php';
require_once 'page_names.inc.php';
require_once 'page_item.class.php';
require_once 'kiwi_lastchange.class.php';
require_once 'kiwi_modules.inc.php';

abstract class Kiwi_Module_Form extends Page_Item
{
	protected $id;
	protected $name;
	protected $lastchange;
	protected $read_only;
	protected $menu_item;
	protected $s_menu_item; // pro obnovení query stringu po editaci ze seznamu modulů

	function __construct(&$rights)
	{
		parent::__construct();

		$mrights = $rights->WWW;
		if (is_array($mrights))
		{
			if (!array_key_exists('EditTextModule', $mrights)) $mrights['EditTextModule'] = false;
			$this->read_only = !($mrights['Write'] || $mrights['EditTextModule']);
		}
		else $this->read_only = !$mrights;
		$this->id = 0;
		$this->name = null;
		$this->lastchange = null;
		$this->menu_item = false;
		$this->s_menu_item = false;
	}

	function handleInput($get, $post)
	{
		if (!empty($get))
		{
			if (isset($get['mi']))
			{
				if (($mi = (int)$get['mi']) < 1)
					throw new Exception("Neplatná hodnota parametru \"mi\": $mi");

				$this->menu_item = $mi;
			}

			if (isset($get['smi']))
			{
				if ($this->menu_item)
					throw new Exception("Souběžné použití parametrů \"mi\" a \"smi\"");

				if (($smi = (int)$get['smi']) < 1)
					throw new Exception("Neplatná hodnota parametru \"smi\": $smi");

				$this->s_menu_item = $smi;
			}

			if (isset($get['m']))
			{
				if (($m = (int)$get['m']) < 1)
					throw new Exception("Neplatná hodnota parametru \"m\": $m");

				$this->id = $m;
			}
		}
	}

	protected function redirectLevelUp()
	{
		if ($this->menu_item)
			$this->redirection = KIWI_EDIT_MENUITEM . "?mi=$this->menu_item";
		elseif ($this->s_menu_item)
			$this->redirection = KIWI_MODULES . "?mi=$this->s_menu_item";
		else
			$this->redirection = KIWI_MODULES;
	}
}
?>