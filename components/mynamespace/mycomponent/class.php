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
            $filt['IBLOCK_ID'] = $iBlock['ID'];
            $elemList = CIBlockElement::GetList(Array('iblock_id'=>'ASC'), $filt,false,false,$sel);
            while($ob = $elemList->GetNextElement())
            {
                $arItem = $ob->GetFields();
                $arItem["FIELDS"] = array();
                $res['ITEMS'][] = $arItem;
            }
            unset($filt['IBLOCK_ID']);
        }
    }

    public function setResultFields(&$resItems,&$parField)
    {
        foreach ($resItems as &$arItem)
        {
            foreach($parField as $code)
                if (array_key_exists($code, $arItem))
                    $arItem["FIELDS"][$code] = $arItem[$code];
        }
    }
}