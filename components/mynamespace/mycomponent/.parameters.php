<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
    return;

$db_iblock_type = CIBlockType::GetList();
while($ar_iblock_type = $db_iblock_type->Fetch())
{
    if($arIBType = CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG))
    {
        $arIBlockType[$ar_iblock_type['ID']] = $arIBType["~NAME"];
    }
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ),
        "FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "DATA_SOURCE"),
        "CHECK_DATES" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("T_IBLOCK_DESC_CHECK_DATES"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "NAME_FILTER" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("FILTER_MASK_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
    ),
);
