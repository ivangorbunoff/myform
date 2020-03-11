<?php

namespace Only\Site\Agents;


class Iblock
{
    public static function clearOldLogs()
    {

            $ibLogID = \CIBlock::GetList(
                Array(),
                Array(
                    "CODE"=>'LOG'
                )
            )->GetNext();

            $sortElement = \CIBlockElement::GetList(
                ["timestamp_x" => "DESC"],
                ['IBLOCK_ID' => $ibLogID['ID']],
                false,
                false,
                ['ID']
            );
            $j = 0;
            while($ob = $sortElement->GetNextElement())
            {
                $j++;
                $arSelectFields = $ob->GetFields();
                if($j > 10)
                {
                    \CIBlockElement::Delete($arSelectFields['ID']);
                }
            }


        return "Only\Site\Agents\Iblock::clearOldLogs();";
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
