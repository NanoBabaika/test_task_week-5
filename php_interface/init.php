<?php

if (\Bitrix\Main\Loader::includeModule("my.task")) {
    AddEventHandler("iblock", "OnAfterIBlockElementAdd", "OnAfterIBlockElementLogHandler");
    AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "OnAfterIBlockElementLogHandler");
}