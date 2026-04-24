<?php

/**
 * Рекурсивно получает полный путь к разделу инфоблока.
 *
 * @param int $sectionId
 * @return string
 */
function getFullSectionPath($sectionId)
{
    if (!$sectionId) {
        return "";
    }

    $res = CIBlockSection::GetList(
        [],
        ['ID' => $sectionId],
        false,
        ['ID', 'NAME', 'IBLOCK_SECTION_ID']
    );
    if ($arSection = $res->GetNext()) {
        $parentPath = getFullSectionPath($arSection['IBLOCK_SECTION_ID']);
        return ($parentPath ? $parentPath . ' -> ' : '') . $arSection['NAME'];
    }
    return "";
}

/**
 * Обработчик событий OnAfterIBlockElementAdd и OnAfterIBlockElementUpdate.
 *
 * @param array $arFields
 * @return void
 */
function OnAfterIBlockElementLogHandler(&$arFields)
{
    // Получаем ID инфоблока LOG по коду
    $resLogIblock = CIBlock::GetList([], ['CODE' => 'LOG']);
    if (!$arLogIblock = $resLogIblock->Fetch()) {
        return;
    }
    $logIblockId = $arLogIblock['ID'];

    // Если это сам лог – выходим
    if ($arFields['IBLOCK_ID'] == $logIblockId) {
        return;
    }

    // Получаем свежие данные об элементе
    $resElement = CIBlockElement::GetByID($arFields['ID']);
    if (!$arFullElement = $resElement->Fetch()) {
        return;
    }

    // Данные об инфоблоке, который изменили
    $resCurrentIblock = CIBlock::GetByID($arFullElement['IBLOCK_ID']);
    if (!$arCurrentIblock = $resCurrentIblock->Fetch()) {
        return;
    }

    // Ищем или создаём раздел в инфоблоке LOG
    $sectionLogId = false;
    $resSection = CIBlockSection::GetList(
        [],
        [
            'IBLOCK_ID'   => $logIblockId,
            'NAME'        => $arCurrentIblock['NAME'],
            'EXTERNAL_ID' => $arCurrentIblock['CODE']
        ]
    );
    if ($arSect = $resSection->Fetch()) {
        $sectionLogId = $arSect['ID'];
    } else {
        $bs = new CIBlockSection();
        $sectionLogId = $bs->Add([
            "ACTIVE"      => "Y",
            "IBLOCK_ID"   => $logIblockId,
            "NAME"        => $arCurrentIblock['NAME'],
            "CODE"        => $arCurrentIblock['CODE'],
            "EXTERNAL_ID" => $arCurrentIblock['CODE'],
        ]);
    }

    // Собираем цепочку
    $sectionPath = getFullSectionPath($arFullElement['IBLOCK_SECTION_ID']);
    $logText = $arCurrentIblock['NAME'] . ' -> ';
    if ($sectionPath) {
        $logText .= $sectionPath . ' -> ';
    }
    $logText .= $arFullElement['NAME'];

    // Сохраняем запись в лог-инфоблоке
    $el = new CIBlockElement();
    $el->Add([
        "IBLOCK_ID"            => $logIblockId,
        "IBLOCK_SECTION_ID"    => $sectionLogId,
        "NAME"                 => $arFullElement['ID'],
        "ACTIVE_FROM"          => \Bitrix\Main\Type\DateTime::createFromTimestamp(time()),
        "PREVIEW_TEXT"         => $logText,
        "PREVIEW_TEXT_TYPE"    => "text",
    ]);
}

/**
 * Агент для удаления старых записей из лога.
 * Оставляет 10 самых свежих записей.
 *
 * @return string
 */
function CleanOldLogsAgent()
{
    if (!\Bitrix\Main\Loader::includeModule("iblock")) {
        return "CleanOldLogsAgent();";
    }

    // Находим ID инфоблока LOG
    $resLogIblock = CIBlock::GetList([], ['CODE' => 'LOG']);
    if (!$arLogIblock = $resLogIblock->Fetch()) {
        return "CleanOldLogsAgent();";
    }
    $logIblockId = $arLogIblock['ID'];

    // Получаем 10 самых новых элементов (чтобы их НЕ удалять)
    $keepIds = [];
    $res = CIBlockElement::GetList(
        ['ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
        ['IBLOCK_ID' => $logIblockId],
        false,
        ['nTopCount' => 10],
        ['ID']
    );
    while ($item = $res->Fetch()) {
        $keepIds[] = $item['ID'];
    }

    // Если логов меньше 10 – ничего не удаляем
    if (count($keepIds) < 10) {
        return "CleanOldLogsAgent();";
    }

    // Удаляем все элементы, которых нет в сохраняемом списке
    $resToDelete = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        [
            'IBLOCK_ID'        => $logIblockId,
            '!ID'              => $keepIds,
            'CHECK_PERMISSIONS' => 'N',
        ],
        false,
        false,
        ['ID']
    );

    $el = new CIBlockElement();
    while ($item = $resToDelete->Fetch()) {
        $el->Delete($item['ID']);
    }

    return "CleanOldLogsAgent();";
}