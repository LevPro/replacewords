<?php
if (CModule::IncludeModule("levpro.replacewords")) {
    AddEventHandler('main', 'OnEndBufferContent', 'OnEndBufferContent');

    function OnEndBufferContent(&$content)
    {
        $uri = $_SERVER['REQUEST_URI'];
		
		if (strpos($uri, 'bitrix/admin') !== false) {
		    return false;
		}
		
		global $DB;
		
		$uri = rtrim($uri, '/');
        $cleanUri = explode('?', $uri);
        $cleanUri = $cleanUri[0];
		$cleanUri = rtrim($cleanUri, '/');

        $rsData = $DB->query("SELECT `URL`, `FROM`, `TO`, `QUANTITY`, `GET_PARAMS` FROM `levpro_replacewords`");

        while ($row = $rsData->fetch()) {
			$rowCurrentUri = $row['GET_PARAMS'] == 'Y' ? $uri : $cleanUri;
			
            if (!empty($row['URL'])) {
                $rowUri = explode(',', $row['URL']);

                foreach ($rowUri as $rowUriItem) {
                    if ($rowCurrentUri == rtrim($rowUriItem, '/')) {
                        $content = str_replace($row['FROM'], $row['TO'], $content, $row['QUANTITY']);
                    }
                }
            } else {
                $content = str_replace($row['FROM'], $row['TO'], $content, $row['QUANTITY']);
            }
        }
    }
}
