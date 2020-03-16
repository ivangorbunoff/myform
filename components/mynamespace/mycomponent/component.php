<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arFilter = [];
if($arParams["CHECK_DATES"]=="Y")
    $arFilter["ACTIVE"] = "Y";

$arFilter["NAME"] = "%".$arParams["NAME_FILTER"]."%";

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"])<=0)
    $arParams["IBLOCK_TYPE"] = "news";

if(!is_array($arParams["FIELD_CODE"]))
    $arParams["FIELD_CODE"] = array();
$this->deleteEmpty($arParams["FIELD_CODE"]);

$arSelect = array_merge($arParams["FIELD_CODE"], array(
    "ID",
    "IBLOCK_ID",
    "IBLOCK_SECTION_ID",
    "NAME",
    "ACTIVE_FROM",
    "TIMESTAMP_X",
    "DETAIL_PAGE_URL",
    "LIST_PAGE_URL",
    "DETAIL_TEXT",
    "DETAIL_TEXT_TYPE",
    "PREVIEW_TEXT",
    "PREVIEW_TEXT_TYPE",
    "PREVIEW_PICTURE",
));
$arResult = [];

$this->setArResult($arParams,$arResult,$arFilter,$arSelect);

$this->setResultFields($arResult["ITEMS"],$arParams["FIELD_CODE"]);

$this->includeComponentTemplate();