<?php
IncludeModuleLangFile(__FILE__);
class levpro_replacewords extends CModule
{
	const MODULE_ID = 'levpro.replacewords';
	var $MODULE_ID = 'levpro.replacewords';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = [];
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("levpro.replacewords_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("levpro.replacewords_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("levpro.replacewords_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("levpro.replacewords_PARTNER_URI");
	}

	function InstallDB($arParams = [])
	{
		global $DB;
		$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/db/install.sql');
		if (!empty($errors)) {
			return false;
		}
		RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CLevproReplacewords', 'OnBuildGlobalMenu');
		return true;
	}

	function UnInstallDB($arParams = [])
	{
		global $DB;
		$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/db/unInstall.sql');
		if (!empty($errors)) {
			return false;
		}
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CLevproReplacewords', 'OnBuildGlobalMenu');
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = [])
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface')) {
            $filename = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/init.php';
            if (file_exists($filename) && ((int)filesize($filename) !== 0)) {
                $heandle = fopen($filename, 'rb');
                $buffer = fread($heandle, filesize($filename));
                $openTag = $openTag + substr_count($buffer, '<?');
                $closeTag = $closeTag + substr_count($buffer, '?>');
                fclose($heandle);
                if ($openTag !== $closeTag) {
                    file_put_contents(
                        $filename,
                        PHP_EOL . 'require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/events/OnEndBufferContent.php");' . PHP_EOL,
                        FILE_APPEND
                    );
                } else {
                    file_put_contents(
                        $filename,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/events/OnEndBufferContent.php");',
                        FILE_APPEND
                    );
                }
            } else {
                file_put_contents(
                    $filename,
                    '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/events/OnEndBufferContent.php");?' . '>'
                );
            }
        }
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function UnInstallFiles()
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php')) {
			$phpInterface = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php');
			$phpInterface = str_replace(
			    'require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/events/OnEndBufferContent.php");',
			    '',
			    $phpInterface
			);
			file_put_contents(
			    $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php',
			    $phpInterface
			);
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
