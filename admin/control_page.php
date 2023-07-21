<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */

use Bitrix\Catalog;
use Bitrix\Currency;
use Bitrix\Iblock;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;
//use Bitrix\Main\Grid\Panel\Snippet\Onchange;
//use Bitrix\Main\Grid\Panel\Actions;

$moduleId = "levpro.replacewords";

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule($moduleId);

$postRight = $APPLICATION->GetGroupRight($moduleId);

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/interface/admin_lib.php");

CJSCore::Init(["jquery"]);

$listId = 'levpro_replacewords';

$gridOptions = new GridOptions($listId);

$navParams = $gridOptions->GetNavParams();

$nav = new PageNavigation('request_list');

$nav->allowAllRecords(true)
    ->setRecordCount($DB->query("SELECT COUNT(*) as CNT FROM levpro_replacewords")->fetch()['CNT'])
    ->setPageSize($navParams['nPageSize'])
    ->initFromUri();

$sqlWhere = 'WHERE 1=1';

$sqlOrder = 'ORDER BY `id` ASC';

$sqlLimit = 'LIMIT ' . $nav->getLimit();

$sqlOffset = 'OFFSET ' . $nav->getOffset();

if (($_GET['grid_id'] ?? null) === $listId) {
    if (isset($_GET['grid_action']) and $_GET['grid_action'] === 'sort') {
        $sqlOrder = sprintf("ORDER BY `%s` %s", $DB->ForSql($_GET['by']), $DB->ForSql($_GET['order']));
    }
}

$filterOption = new Bitrix\Main\UI\Filter\Options($listId);

$filterData = $filterOption->getFilter([]);

//var_dump($filterData); exit;

foreach ($filterData as $key => $value) {
    if ($key == 'URL' && !empty($value)) {
        $sqlWhere .= sprintf(" AND `URL` = '%s'", $DB->ForSql($value));
    }
    if ($key == 'FROM' && !empty($value)) {
        $sqlWhere .= sprintf(" AND `FROM` = '%s'", $DB->ForSql($value));
    }
    if ($key == 'TO' && !empty($value)) {
        $sqlWhere .= sprintf(" AND `TO` = '%s'", $DB->ForSql($value));
    }
	if ($key == 'QUANTITY' && !empty($value)) {
        $sqlWhere .= sprintf(" AND `QUANTITY` = '%s'", $DB->ForSql($value));
    }
	if ($key == 'GET_PARAMS' && !empty($value)) {
        $sqlWhere .= sprintf(" AND `GET_PARAMS` = '%s'", $DB->ForSql($value));
    }
}

$sqlQuery = sprintf("SELECT * FROM `levpro_replacewords` %s %s %s %s", $sqlWhere, $sqlOrder, $sqlLimit, $sqlOffset);

//var_dump($sqlQuery); exit;

$rsData = $DB->query($sqlQuery);

$list = [];

while ($row = $rsData->fetch()) {
	$row['GET_PARAMS'] = $row['GET_PARAMS'] == 'Y' ? GetMessage("LEVPRO_REPLACEWORDS_YES") : GetMessage("LEVPRO_REPLACEWORDS_NO");
	
    $itemId = $row['ID'];

    $list[] = [
        'data' => $row,
        'actions' => [
            [
                'text' => GetMessage("LEVPRO_REPLACEWORDS_EDIT"),
                'default' => true,
                'onclick' => "document.location.replace('/bitrix/admin/levpro.replacewords_control_page_form.php?id=$itemId')"
            ],
            [
                'text' => GetMessage("LEVPRO_REPLACEWORDS_DELETE"),
                'default' => true,
                'onclick' => "if(window.confirm('" . GetMessage("LEVPRO_REPLACEWORDS_CONFIRM") . "')){document.location.replace('/bitrix/admin/levpro.replacewords_control_page_form.php?remove=$itemId')}"
            ],
            [
                'text' => GetMessage("LEVPRO_REPLACEWORDS_COPY"),
                'default' => true,
                'onclick' => "document.location.replace('/bitrix/admin/levpro.replacewords_control_page_form.php?copy=$itemId')"
            ]
        ]
    ];
}

$arHeaders = [
    ["id" => "ID", "name" => GetMessage("LEVPRO_REPLACEWORDS_ID"), "sort" => "ID", "align" => "left", "default" => false],
    ["id" => "URL", "name" => GetMessage("LEVPRO_REPLACEWORDS_URL"), "sort" => "URL", "align" => "left", "default" => false],
    ["id" => "FROM", "name" => GetMessage("LEVPRO_REPLACEWORDS_FROM"), "sort" => "FROM", "align" => "left", "default" => true],
    ["id" => "TO", "name" => GetMessage("LEVPRO_REPLACEWORDS_TO"), "sort" => "TO", "align" => "left", "default" => true],
	["id" => "QUANTITY", "name" => GetMessage("LEVPRO_REPLACEWORDS_QUANTITY"), "sort" => "TO", "align" => "left", "default" => false],
	["id" => "GET_PARAMS", "name" => GetMessage("LEVPRO_REPLACEWORDS_GET_PARAMS"), "sort" => "TO", "align" => "left", "default" => false],
];

$filterList = [
    [
        "id" => "ID",
        'type' => 'number',
        "name" => GetMessage("LEVPRO_REPLACEWORDS_ID"),
        "default" => false,
    ],
    [
        "id" => "URL",
        'type' => 'url',
        "name" => GetMessage("LEVPRO_REPLACEWORDS_URL"),
        "default" => false
    ],
    [
        "id" => "FROM",
        'type' => 'text',
        "name" => GetMessage("LEVPRO_REPLACEWORDS_FROM"),
        "default" => true
    ],
    [
        "id" => "TO",
        'type' => 'text',
        "name" => GetMessage("LEVPRO_REPLACEWORDS_TO"),
        "default" => true
    ],
    [
        "id" => "QUANTITY",
        'type' => 'text',
        "name" => GetMessage("LEVPRO_REPLACEWORDS_QUANTITY"),
        "default" => false
    ],
    [
        "id" => "GET_PARAMS",
        'type' => 'list',
        "name" => GetMessage("LEVPRO_REPLACEWORDS_GET_PARAMS"),
        "default" => false,
		'items' => [
		    '' => GetMessage("LEVPRO_REPLACEWORDS_ALL"), 
			'Y' => GetMessage("LEVPRO_REPLACEWORDS_YES"), 
			'N' => GetMessage("LEVPRO_REPLACEWORDS_NO")
		], 
		'params' => [
		    'multiple' => 'Y'
		]
    ]
];

//$onchange = new Onchange();
//$onchange->addAction(
//    [
//        'ACTION' => Actions::CALLBACK,
//        'CONFIRM' => true,
//        'CONFIRM_APPLY_BUTTON' => 'Подтвердить',
//        'DATA' => [
//            ['JS' => 'Grid.removeSelected()']
//        ]
//    ]
//);

$APPLICATION->SetTitle(GetMessage("LEVPRO_REPLACEWORDS_TITLE"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php"); ?>
<?php if (isset($_SESSION['errors'])) { ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <?php foreach ($_SESSION['errors'] as $item) { ?>
            <div class="adm-info-message">
                <div class="adm-info-message-title"><?php echo GetMessage("LEVPRO_REPLACEWORDS_ERROR") ?></div>
                <?php echo $item ?>
                <br>
                <div class="adm-info-message-icon"></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<?php if (isset($_SESSION['result'])) { ?>
    <div class="adm-info-message-wrap adm-info-message-green">
        <?php foreach ($_SESSION['result'] as $item) { ?>
            <div class="adm-info-message">
                <div class="adm-info-message-title"><?php echo GetMessage("LEVPRO_REPLACEWORDS_SUCCESS") ?></div>
                <?php echo $item ?>
                <br>
                <div class="adm-info-message-icon"></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<div class="adm-toolbar-panel-container">
    <div class="adm-toolbar-panel-flexible-space">
        <?php $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
            'FILTER_ID' => $listId,
            'GRID_ID' => $listId,
            'FILTER' => $filterList,
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL' => true
        ]); ?>
    </div>
    <div class="adm-toolbar-panel-align-right">
        <div class="ui-btn-primary">
            <a href="/bitrix/admin/levpro.replacewords_control_page_form.php"
               class="ui-btn-main"><?php echo GetMessage("LEVPRO_REPLACEWORDS_ADD") ?></a>
        </div>
    </div>
</div>
<?php
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $listId,
    'COLUMNS' => $arHeaders,
    'ROWS' => $list,
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $nav,
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '5', 'VALUE' => '5'],
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP' => 'Y',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => true,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => true,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => true,
    'AJAX_OPTION_HISTORY' => 'Y',
    'TOTAL_ROWS_COUNT_HTML' => '<span class="main-grid-panel-content-title">' . GetMessage("LEVPRO_REPLACEWORDS_TOTAL") . ':</span> <span class="main-grid-panel-content-text">' . $nav->getRecordCount() . '</span>',
//    'ACTION_PANEL' => [
//        'GROUPS' => [
//            'TYPE' => [
//                'ITEMS' => [
//                    [
//                        'ID' => 'edit',
//                        'TYPE' => 'BUTTON',
//                        'TEXT' => 'Редактировать',
//                        'CLASS' => 'icon edit',
//                        'ONCHANGE' => ''
//                    ],
//                    [
//                        'ID' => 'delete',
//                        'TYPE' => 'BUTTON',
//                        'TEXT' => 'Удалить',
//                        'CLASS' => 'icon remove',
//                        'ONCHANGE' => $onchange->toArray()
//                    ],
//                ],
//            ]
//        ],
//    ],
]);

if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}
if (isset($_SESSION['result'])) {
    unset($_SESSION['result']);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
