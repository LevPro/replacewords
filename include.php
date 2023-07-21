<?php
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

$MODULE_ID = basename(dirname(__FILE__));

IncludeModuleLangFile(__FILE__);

Loader::registerAutoLoadClasses(
	$MODULE_ID,
	[
		"CLevproReplacewords" => __FILE__
	]
);

Class CLevproReplacewords
{
	static function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu): void
	{
		$MODULE_ID = basename(dirname(__FILE__));

		if($GLOBALS['APPLICATION']->GetGroupRight("main") < "R")
			return;

		$aMenu = array(
			"parent_menu" => "global_menu_services",
			//"parent_menu" => "global_menu_settings",
			"section" => GetMessage("LEVPRO_REPLACEWORDS_TITLE"),
			"sort" => 50,
			"text" => GetMessage("LEVPRO_REPLACEWORDS_TITLE"),
			"title" => GetMessage("LEVPRO_REPLACEWORDS_TITLE"),
//			"url" => "partner_modules.php?module=".$MODULE_ID,
			"icon" => "",
			"page_icon" => "",
			"items_id" => $MODULE_ID."_items",
			"more_url" => array(),
			"items" => array()
		);

		if (file_exists($path = dirname(__FILE__).'/admin'))
		{
			if ($dir = opendir($path))
			{
				$arFiles = array();

				while(false !== $item = readdir($dir))
				{
					if (in_array($item,array('.','..','menu.php')))
						continue;

					if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$MODULE_ID.'_'.$item))
						file_put_contents($file,'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
			}
		}

		$aMenu['items'][] = array(
		    'text' => GetMessage("LEVPRO_REPLACEWORDS_LIST"),
		    'url' => $MODULE_ID.'_control_page.php',
		    'module_id' => $MODULE_ID,
		    "title" => GetMessage("LEVPRO_REPLACEWORDS_LIST"),
		);

		$aMenu['items'][] = array(
		    'text' => GetMessage("LEVPRO_REPLACEWORDS_ADD"),
		    'url' => $MODULE_ID.'_control_page_form.php',
		    'module_id' => $MODULE_ID,
		    "title" => GetMessage("LEVPRO_REPLACEWORDS_ADD"),
		);

		$aModuleMenu[] = $aMenu;
	}

    function safeParam($value): string
	{
        $value = stripslashes($value);
        $value = htmlspecialchars($value);

		return $value;
	}
}
?>
