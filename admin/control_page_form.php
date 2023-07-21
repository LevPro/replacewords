<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */

use Bitrix\Catalog;
use Bitrix\Currency;
use Bitrix\Iblock;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;

$moduleId = "levpro.replacewords";

$reuestData = array_merge($_POST, $_GET);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule($moduleId);

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/interface/admin_lib.php");

CJSCore::Init(["jquery"]);

$editItem = isset($reuestData['id']) ? (new CLevproReplacewords)->safeParam($reuestData['id']) : [];

if (!empty($editItem)) {
    $sqlQuery = sprintf("SELECT * FROM `levpro_replacewords` WHERE `ID` = %s", $DB->ForSql($editItem));

    $rsData = $DB->query($sqlQuery);

    if ($row = $rsData->fetch()) {
        $editItem = $row;
    }
}

$removeItem = isset($reuestData['remove']) ? (new CLevproReplacewords)->safeParam($reuestData['remove']) : [];

if (!empty($removeItem)) {
    $sqlQuery = sprintf("DELETE FROM `levpro_replacewords` WHERE `ID` = %s", $DB->ForSql($removeItem));

    $rsData = $DB->query($sqlQuery);

    $_SESSION['result'] = [
        GetMessage("LEVPRO_REPLACEWORDS_REMOVE")
    ];

    header("Location: /bitrix/admin/levpro.replacewords_control_page.php");

    exit();
}

$copyItem = isset($reuestData['copy']) ? (new CLevproReplacewords)->safeParam($reuestData['copy']) : [];

if (!empty($copyItem)) {
    $sqlQuery = sprintf("SELECT * FROM `levpro_replacewords` WHERE `ID` = %s", $DB->ForSql($copyItem));

    $rsData = $DB->query($sqlQuery);

    if ($row = $rsData->fetch()) {
        $editItem = $row;
    }

    $sqlQuery = sprintf("INSERT INTO `levpro_replacewords` (`URL`, `FROM`, `TO`, `QUANTITY`, `GET_PARAMS`) VALUES('%s', '%s', '%s', '%s', '%s')", $DB->ForSql($editItem["URL"]), $DB->ForSql($editItem['FROM']), $DB->ForSql($editItem["TO"]), $DB->ForSql($editItem["QUANTITY"]), $DB->ForSql($editItem["GET_PARAMS"]));

    $rsData = $DB->query($sqlQuery);

    $copyItemId = $DB->LastID();

    $_SESSION['result'] = [
        GetMessage("LEVPRO_REPLACEWORDS_ADD_SUCCESS")
    ];

    header("Location: /bitrix/admin/levpro.replacewords_control_page_form.php?id=$copyItemId");

    exit();
}

if (isset($reuestData['save']) || isset($reuestData['apply']) || isset($reuestData['dontsave']) || isset($reuestData['save_and_add'])) {
    if (isset($reuestData['dontsave'])) {
        header("Location: /bitrix/admin/levpro.replacewords_control_page.php");

        exit();
    } else {
        $errors = [];
        $result = [];

        if (!empty($reuestData['URL'])) {
            $editItem["URL"] = array_filter($reuestData['URL'], function ($item) {
                return !empty($item);
            });

            foreach ($editItem["URL"] as $key => $url) {
                if (strpos($url, 'http') !== false) {
                    $errors[] = GetMessage("LEVPRO_REPLACEWORDS_URL_VALIDATE_ERROR");

                    break;
                }

                $editItem["URL"][$key] = (new CLevproReplacewords)->safeParam($url);
            }

            $editItem["URL"] = implode(', ', $editItem["URL"]);
        }

        $editItem["FROM"] = (new CLevproReplacewords)->safeParam($reuestData['FROM']);
        $editItem["TO"] = (new CLevproReplacewords)->safeParam($reuestData['TO']);
		$editItem["QUANTITY"] = (new CLevproReplacewords)->safeParam($reuestData['QUANTITY']);
        $editItem["GET_PARAMS"] = (new CLevproReplacewords)->safeParam($reuestData['GET_PARAMS']);

        if (empty($editItem['FROM'])) {
            $errors[] = GetMessage("LEVPRO_REPLACEWORDS_FROM_VALIDATE_ERROR");
        }

        if (empty($editItem['TO'])) {
            $errors[] = GetMessage("LEVPRO_REPLACEWORDS_TO_VALIDATE_ERROR");
        }
		
		if (!empty($editItem['QUANTITY']) && (int) $editItem['QUANTITY'] < 1) {
            $errors[] = GetMessage("LEVPRO_REPLACEWORDS_QUANTITY_VALIDATE_ERROR");
        }

        if (empty($errors)) {
            if (isset($editItem['ID'])) {
                $sqlQuery = sprintf("UPDATE `levpro_replacewords` SET `URL` = '%s', `FROM` = '%s', `TO` = '%s', `QUANTITY` = '%s', `GET_PARAMS` = '%s' WHERE `ID` = %s", $DB->ForSql($editItem["URL"]), $DB->ForSql($editItem['FROM']), $DB->ForSql($editItem["TO"]), $DB->ForSql($editItem["QUANTITY"]), $DB->ForSql($editItem["GET_PARAMS"]), $DB->ForSql($editItem['ID']));

                $result[] = GetMessage("LEVPRO_REPLACEWORDS_UPDATE_SUCCESS");
            } else {
                $sqlQuery = sprintf("INSERT INTO `levpro_replacewords` (`URL`, `FROM`, `TO`, `QUANTITY`, `GET_PARAMS`) VALUES('%s', '%s', '%s', '%s', '%s')", $DB->ForSql($editItem["URL"]), $DB->ForSql($editItem['FROM']), $DB->ForSql($editItem["TO"]), $DB->ForSql($editItem["QUANTITY"]), $DB->ForSql($editItem["GET_PARAMS"]));

                $result[] = GetMessage("LEVPRO_REPLACEWORDS_ADD_SUCCESS");
            }

            $rsData = $DB->query($sqlQuery);
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['result'] = $result;

        if (empty($errors)) {
            if (isset($reuestData['save'])) {
                header("Location: /bitrix/admin/levpro.replacewords_control_page.php");

                exit();
            }
            if (isset($reuestData['save_and_add'])) {
                header("Location: /bitrix/admin/levpro.replacewords_control_page_form.php");

                exit();
            }
        }
    }
}

$APPLICATION->SetTitle(empty($editItem["ID"]) ? GetMessage("LEVPRO_REPLACEWORDS_TITLE_ADD") : GetMessage("LEVPRO_REPLACEWORDS_TITLE_EDIT"));

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
    <form method="POST" action="<?php echo $_SERVER["REQUEST_URI"] ?>">
        <div class="adm-detail-block">
            <div class="adm-detail-content-wrap">
                <div class="adm-detail-content">
                    <div class="adm-detail-title"><?php echo GetMessage("LEVPRO_REPLACEWORDS_TITLE_MAIN_DATA") ?></div>
                    <div class="adm-detail-content-item-block">
                        <table class="adm-detail-content-table edit-table">
                            <tbody>
                            <?php if (!empty($editItem["ID"])) { ?>
                                <tr>
                                    <td width="20%"
                                        class="adm-detail-content-cell-l"><?php echo GetMessage("LEVPRO_REPLACEWORDS_ID") ?>
                                        :
                                    </td>
                                    <td width="80%" class="adm-detail-content-cell-r"><?php echo $editItem["ID"] ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td width="20%"
                                    class="adm-detail-valign-top adm-detail-content-cell-l"><?php echo GetMessage("LEVPRO_REPLACEWORDS_PAGES") ?>
                                    :
                                </td>
                                <td width="80%" class="adm-detail-content-cell-r" id="URL_fields">
                                    <?php if (!empty($editItem["URL"])) {
                                        foreach (explode(", ", $editItem["URL"]) as $url) { ?>
                                            <input type="text" size="70" name="URL[]" value="<?php echo $url ?>">
                                        <?php }
                                    } ?>
                                    <input type="text" size="70" name="URL[]">
                                    <br>
                                    <input type="button"
                                           value="<?php echo GetMessage("LEVPRO_REPLACEWORDS_ADD_BUTTON") ?>">
                                </td>
                            </tr>
                            <tr>
                                <td width="20%" class="adm-detail-content-cell-l">
                                    <span
                                        class="adm-required-field"><?php echo GetMessage("LEVPRO_REPLACEWORDS_FROM") ?>:</span>
                                </td>
                                <td width="80%" class="adm-detail-content-cell-r">
                                    <input type="text" size="70" name="FROM"
                                           value="<?php echo empty($editItem["FROM"]) ? "" : $editItem["FROM"] ?>"
                                           required="required">
                                </td>
                            </tr>
                            <tr>
                                <td width="20%" class="adm-detail-content-cell-l">
                                    <span
                                        class="adm-required-field"><?php echo GetMessage("LEVPRO_REPLACEWORDS_TO") ?>:</span>
                                </td>
                                <td width="80%" class="adm-detail-content-cell-r">
                                    <input type="text" size="70" name="TO"
                                           value="<?php echo empty($editItem["TO"]) ? "" : $editItem["TO"] ?>"
                                           required="required">
                                </td>
                            </tr>
							<tr>
                                <td width="20%" class="adm-detail-content-cell-l"><?php echo GetMessage("LEVPRO_REPLACEWORDS_QUANTITY") ?>:</td>
                                <td width="80%" class="adm-detail-content-cell-r">
                                    <input type="text" size="70" name="QUANTITY"
                                           value="<?php echo empty($editItem["QUANTITY"]) ? "" : $editItem["QUANTITY"] ?>" pattern="[0-9]+">
                                </td>
                            </tr>
							<tr>
                                <td width="20%" class="adm-detail-content-cell-l"><?php echo GetMessage("LEVPRO_REPLACEWORDS_GET_PARAMS") ?>:</td>
                                <td width="80%" class="adm-detail-content-cell-r">
                                    <input type="checkbox" name="GET_PARAMS" value="Y" <?php echo $editItem["GET_PARAMS"] == "Y" ? 'checked="checked"' : "" ?> id="GET_PARAMS" class="adm-designed-checkbox">
									<label class="adm-designed-checkbox-label" for="GET_PARAMS"></label>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="adm-detail-content-btns-wrap">
                    <div class="adm-detail-content-btns">
                        <input type="submit" class="adm-btn-save" name="save" id="save"
                               value="<?php echo GetMessage("LEVPRO_REPLACEWORDS_SAVE") ?>">
                        <input type="submit" class="button" name="apply" id="apply"
                               value="<?php echo GetMessage("LEVPRO_REPLACEWORDS_APPLY") ?>">
                        <input type="submit" class="button" name="dontsave" id="dontsave"
                               value="<?php echo GetMessage("LEVPRO_REPLACEWORDS_CANCEL") ?>">
                        <input type="submit" class="adm-btn-add" name="save_and_add" id="save_and_add"
                               value="<?php echo GetMessage("LEVPRO_REPLACEWORDS_SAVE_AND_ADD") ?>">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <style>
        .adm-detail-content-wrap {
            border-radius: 4px;
            border-top: 1px solid #ced7d8;
        }

        .adm-detail-content-btns-wrap {
            background: transparent;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('#URL_fields input[type="button"]').on('click', function (evt) {
                evt.preventDefault();

                const button = $('#URL_fields input:last-child');

                button.before('<input type="text" size="70" name="URL[]"><br>');
            })
        });
    </script>
<?php
if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}
if (isset($_SESSION['result'])) {
    unset($_SESSION['result']);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>
