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

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule($moduleId);

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/interface/admin_lib.php");

CJSCore::Init(["jquery"]);

$editItem = isset($_GET['id']) ? (new CLevproReplacewords)->safeParam($_GET['id']) : [];

if (!empty($editItem)) {
    $sqlQuery = sprintf("SELECT * FROM `levpro_replacewords` WHERE `ID` = %s", $DB->ForSql($editItem));

    $rsData = $DB->query($sqlQuery);

    if ($row = $rsData->fetch()) {
        $editItem = $row;
    }
}

$removeItem = isset($_GET['remove']) ? (new CLevproReplacewords)->safeParam($_GET['remove']) : [];

if (!empty($removeItem)) {
    $sqlQuery = sprintf("DELETE FROM `levpro_replacewords` WHERE `ID` = %s", $DB->ForSql($removeItem));

    $rsData = $DB->query($sqlQuery);

    $_SESSION['result'] = [
        GetMessage("LEVPRO_REPLACEWORDS_REMOVE")
    ];

    header("Location: /bitrix/admin/levpro.replacewords_control_page.php");

    exit();
}

$copyItem = isset($_GET['copy']) ? (new CLevproReplacewords)->safeParam($_GET['copy']) : [];

if (!empty($copyItem)) {
    $sqlQuery = sprintf("SELECT * FROM `levpro_replacewords` WHERE `ID` = %s", $DB->ForSql($copyItem));

    $rsData = $DB->query($sqlQuery);

    if ($row = $rsData->fetch()) {
        $editItem = $row;
    }

    $sqlQuery = sprintf("INSERT INTO `levpro_replacewords` (`URL`, `FROM`, `TO`) VALUES('%s', '%s', '%s')", $DB->ForSql($editItem["URL"]), $DB->ForSql($editItem['FROM']), $DB->ForSql($editItem["TO"]));

    $rsData = $DB->query($sqlQuery);

    $copyItemId = $DB->LastID();

    $_SESSION['result'] = [
        GetMessage("LEVPRO_REPLACEWORDS_ADD_SUCCESS")
    ];

    header("Location: /bitrix/admin/levpro.replacewords_control_page_form.php?id=$copyItemId");

    exit();
}

if (isset($_POST['save']) || isset($_POST['apply']) || isset($_POST['dontsave']) || isset($_POST['save_and_add'])) {
    if (isset($_POST['dontsave'])) {
        header("Location: /bitrix/admin/levpro.replacewords_control_page.php");

        exit();
    } else {
        $errors = [];
        $result = [];

        if (!empty($_POST['URL'])) {
            $editItem["URL"] = array_filter($_POST['URL'], function ($item) {
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

        $editItem["FROM"] = (new CLevproReplacewords)->safeParam($_POST['FROM']);
        $editItem["TO"] = (new CLevproReplacewords)->safeParam($_POST['TO']);

        if (empty($editItem['FROM'])) {
            $errors[] = GetMessage("LEVPRO_REPLACEWORDS_FROM_VALIDATE_ERROR");
        }

        if (empty($editItem['TO'])) {
            $errors[] = GetMessage("LEVPRO_REPLACEWORDS_TO_VALIDATE_ERROR");
        }

        if (empty($errors)) {
            if (isset($editItem['ID'])) {
                $sqlQuery = sprintf("UPDATE `levpro_replacewords` SET `URL` = '%s', `FROM` = '%s', `TO` = '%s' WHERE `ID` = %s", $DB->ForSql($editItem["URL"]), $DB->ForSql($editItem['FROM']), $DB->ForSql($editItem["TO"]), $DB->ForSql($editItem['ID']));

                $result[] = GetMessage("LEVPRO_REPLACEWORDS_UPDATE_SUCCESS");
            } else {
                $sqlQuery = sprintf("INSERT INTO `levpro_replacewords` (`URL`, `FROM`, `TO`) VALUES('%s', '%s', '%s')", $DB->ForSql($editItem["URL"]), $DB->ForSql($editItem['FROM']), $DB->ForSql($editItem["TO"]));

                $result[] = GetMessage("LEVPRO_REPLACEWORDS_ADD_SUCCESS");
            }

            $rsData = $DB->query($sqlQuery);
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['result'] = $result;

        if (empty($errors)) {
            if (isset($_POST['save'])) {
                header("Location: /bitrix/admin/levpro.replacewords_control_page.php");

                exit();
            }
            if (isset($_POST['save_and_add'])) {
                header("Location: /bitrix/admin/levpro.replacewords_control_page_form.php");

                exit();
            }
        }
    }
}

$APPLICATION->SetTitle(empty($item["ID"]) ? GetMessage("LEVPRO_REPLACEWORDS_TITLE_ADD") : GetMessage("LEVPRO_REPLACEWORDS_TITLE_EDIT"));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php"); ?>
<?php if (isset($_SESSION['errors'])) { ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <?php foreach ($_SESSION['errors'] as $item) { ?>
            <div class="adm-info-message">
                <div class="adm-info-message-title">Ошибка</div>
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
                <div class="adm-info-message-title">Успешно</div>
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
