<?php
if (CModule::IncludeModule("levpro.replacewords")) {
    AddEventHandler('main', 'OnEndBufferContent', 'OnEndBufferContent');

    function OnEndBufferContent(&$content)
    {
        global $DB;

        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode('?', $uri);
        $uri = $uri[0];
        $uri = rtrim($uri, '/');

        $rsData = $DB->query("SELECT `URL`, `FROM`, `TO` FROM `levpro_replacewords`");

        while ($row = $rsData->fetch()) {
            if (!empty($row['URL'])) {
                $rowUri = explode(',', $row['URL']);

                foreach ($rowUri as $rowUriItem) {
                    if ($uri == rtrim($rowUriItem, '/')) {
                        $content = str_replace($row['FROM'], $row['TO'], $content);
                    }
                }
            } else {
                $content = str_replace($row['FROM'], $row['TO'], $content);
            }
        }
    }
}
