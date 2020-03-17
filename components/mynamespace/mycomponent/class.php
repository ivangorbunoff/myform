<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class Cl extends CBitrixComponent
{
    public function deleteEmpty(&$par)
    {
        foreach($par as $key=>$val)
            if (!$val)
                unset($par[$key]);
    }

    public function setArResult(&$par,&$res,&$filt,&$sel)
    {
        $iblockList = CIBlock::GetList(Array(), Array('TYPE'=>$par['IBLOCK_TYPE']));
        while($iBlock = $iblockList->Fetch())
        {
            if(empty($filt['IBLOCK_ID']))
            {
                $filt['IBLOCK_ID'] = $iBlock['ID'];
                $elemList = CIBlockElement::GetList(Array('iblock_id'=>'ASC'), $filt,false,false,$sel);
                while($ob = $elemList->GetNextElement())
                {
                    $arItem = $ob->GetFields();
                    $arItem["FIELDS"] = array();
                    $res['ITEMS'][$iBlock['ID']][] = $arItem;
                }
                unset($filt['IBLOCK_ID']);
            }
            else
            {
                $elemList = CIBlockElement::GetList(Array('iblock_id'=>'ASC'), $filt,false,false,$sel);
                while($ob = $elemList->GetNextElement())
                {
                    $arItem = $ob->GetFields();
                    $arItem["FIELDS"] = array();
                    $res['ITEMS'][$iBlock['ID']][] = $arItem;
                }
                break;
            }

        }
    }

    public function setResultFields(&$resItems,&$parField)
    {
        foreach ($resItems as $key1=>&$arItem)
            foreach ($arItem as $key2=>&$value)
                foreach($parField as $code)
                    if (array_key_exists($code, $value))
                        $value["FIELDS"][$code] = $value[$code];
    }

    public function executeComponent()
    {
        $db_iblock_type = CIBlockType::GetList();
        while($ar_iblock_type = $db_iblock_type->Fetch())
        {
            if($arIBType = CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG))
            {
                $arIBlockType[$ar_iblock_type['ID']] = $arIBType["~NAME"];
            }
        }
        if (!array_key_exists($this->arParams["IBLOCK_TYPE"], $arIBlockType))
        {
            ShowError('Некорректный ввод типа инфоблока');
            return;
        }

        $this->arParams["IBLOCK_ID"] = trim($this->arParams["IBLOCK_ID"]);
        $this->arParams["IBLOCK_TYPE"] = trim($this->arParams["IBLOCK_TYPE"]);
        if(strlen($this->arParams["IBLOCK_TYPE"])<=0)
            $this->arParams["IBLOCK_TYPE"] = "news";

        if(!is_array($this->arParams["FIELD_CODE"]))
            $this->arParams["FIELD_CODE"] = array();

        $arFilter = [];
        if($this->arParams["CHECK_DATES"]=="Y")
            $arFilter["ACTIVE"] = "Y";
        $arFilter["NAME"] = "%".$this->arParams["NAME_FILTER"]."%";
        $arFilter["IBLOCK_ID"] = $this->arParams["IBLOCK_ID"];

        $arSelect = array_merge($this->arParams["FIELD_CODE"], array(
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

        $this->deleteEmpty($this->arParams["FIELD_CODE"]);
        $this->setArResult($this->arParams,$this->arResult,$arFilter,$arSelect);
        $this->setResultFields($this->arResult["ITEMS"],$this->arParams["FIELD_CODE"]);
        $this->includeComponentTemplate();
    }

}