<?php

namespace Only\Site\Handlers;

function myFunc($n,$arr,&$array)// рекурсивная функция поиска имен разделов
{
    if($n=='')// если раздел корневой - return
    {
        return;
    }
    for($i = 0;$i < count($arr);$i++)
    {
        if($arr[$i]['ID']==$n)
        {
            array_unshift($array, $arr[$i]['NAME']);
            $n = $arr[$i]['IBLOCK_SECTION_ID'];
            myFunc($n,$arr,$array);
        }
    }
}
class Iblock
{
    public function addLog(&$arFields)
    {
        $el = new \CIBlockElement;

        $res = \CIBlock::GetList(
            Array(),
            Array(
                "CODE"=>'LOG'
            )
        )->Fetch();  // в $res['ID'] id инфоблока с кодом ЛОГ


        if($res['ID']==$arFields['IBLOCK_ID'])// проверка что добавляем элемент не в ЛОГ
        {
            return;
        }

        $result = \CIBlock::GetList(
            Array(),
            Array(
                "ID"=>$arFields['IBLOCK_ID']
            )
        )->Fetch();
        //  $result['NAME'] имя и
        //  $result['CODE'] код инфоблока, изменение которого логируется

        $list = \CIBlockSection::GetList(
            [],
            ['IBLOCK_ID' => $arFields['IBLOCK_ID']],
            false,
            ['ID','IBLOCK_SECTION_ID','NAME']
        );
        while ($arSections = $list->GetNext())
        {
            $mas[] = $arSections;// массив с разделами исходного инфоблока
        }

        $ITEMS = [];// массив для анонса
        myFunc($arFields['IBLOCK_SECTION'][0],$mas,$ITEMS);
        array_unshift($ITEMS, $result['NAME']);
        $ITEMS[] = $arFields['NAME'];


        $i = 0;// и снова переменная счетчика))
        $db_list = \CIBlockSection::GetList([], ['IBLOCK_ID' => $res['ID']]);
        while ($arSect = $db_list->GetNext())
        {
            if(trim($arSect['NAME'])=== trim($result['NAME']))// проверяем, нет ли такого раздела в ЛОГ
            {
                $i++;
                break;
            }
        }
        if($i == 0)// совпадений нет, создаем раздел и в нем элемент
        {
            //-------создаем раздел--------
            $bs = new \CIBlockSection;
            $arSection = Array(
                "IBLOCK_ID" => $res['ID'],
                "NAME" => $result['NAME'],
                "CODE" => $result['CODE']
            );
            if($ID = $bs->Add($arSection))
            {
                echo "Добавлен раздел в инфоблок LOG : " . $ID . "<br>";
            }
            else
            {
                echo "Error: " . $bs->LAST_ERROR . '<br>';
            }
        }
        else// совпадения есть
        {
            $ID = $arSect['ID'];
        }

        $getElement = \CIBlockElement::GetList(
            ["SORT" => "ASC"],
            ['IBLOCK_ID' => $res['ID']],
            false,
            false,
            ['ID','NAME']
        );
        $i = 0;
        while($idElement = $getElement->GetNextElement())
        {
            $arID = $idElement->GetFields();
            if(trim($arID['NAME'])=== trim($arFields['ID']))// проверяем, есть ли уже такой элемент
            {
                $i++;
                break;
            }
        }
        if($i == 0)// если нету то добавляем
        {
            //--------- создаем элемент-------
            $arLoadProduct = [
                "IBLOCK_SECTION_ID" => $ID,
                "IBLOCK_ID" => $res['ID'],
                "NAME" => $arFields['ID'],
                "PREVIEW_TEXT" => implode("->", $ITEMS),
                "ACTIVE_FROM" => date('d.m.Y')
            ];
            if ($PRODUCT = $el->Add($arLoadProduct))
            {
                echo " в ЛОГ добавлен элемент с именем : " . $arFields['ID'] . "<br>";
            }
            else
            {
                echo "Error: " . $el->LAST_ERROR . '<br>';
            }

        }
        else// иначе изменяем уже существующий
        {
            $arLoadProduct = [
                "NAME" => $arFields['ID'],
                "PREVIEW_TEXT" => implode("->", $ITEMS),
            ];
            if ($PRODUCT = $el->Update($arID['ID'],$arLoadProduct))
            {
                echo "Изменен элемент с ID : " . $arID['ID'] . "<br>";
            }
            else
            {
                echo "Error: " . $el->LAST_ERROR . '<br>';
            }

        }

    }

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }

}
