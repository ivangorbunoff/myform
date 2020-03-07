<?php

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("MyClass", "OnBeforeIBlockElementAddHandler"));

class MyClass
{
    protected static $handlerDisallow = false;
    public static function OnBeforeIBlockElementAddHandler(&$arFields)
    {

        $res = CIBlock::GetList(
            Array(),
            Array(
                "CODE"=>'LOG'
            )
        )->Fetch();  // в $res['ID'] id инфоблока с кодом ЛОГ


        if($res['ID']==$arFields['IBLOCK_ID'])// проверка что добавляем элемент не в ЛОГ
        {
            return;
        }

        $result = CIBlock::GetList(
            Array(),
            Array(
                "ID"=>$arFields['IBLOCK_ID']
            )
        )->Fetch();
       //  $result['NAME'] имя и
       //  $result['CODE'] код инфоблока, изменение которого логируется

        $ITEMS[] = $result['NAME'];// в массиве ITEMS будет лежать строка для анонса


        $rsElement = CIBlockElement::GetList(
            ["ID" => "DESC"],
            ['IBLOCK_ID' => $arFields['IBLOCK_ID']],
            false,
            false,
            ['ID']
        )->Fetch();
        //$rsElement['ID']+2 - id элемента, который бует добавлен (это для имени элемента, который добавим в ЛОГ)

        if(is_numeric($arFields['IBLOCK_SECTION_ID']))// если создаваемый элемент привязан к разделу, создаем или помещаем в существующий раздел ЛОГа элемент
        {

            $nav = CIBlockSection::GetNavChain(false, $arFields['IBLOCK_SECTION_ID']);
            while($arItem = $nav->Fetch())
            {
                $ITEMS[] = $arItem['NAME'];
            }
            $ITEMS[] = $arFields['NAME'];// добавляем разделы в строку для анонса

            $i = 0;// и снова переменная счетчика))
            $db_list = CIBlockSection::GetList([], ['IBLOCK_ID' => $res['ID']]);
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
                $bs = new CIBlockSection;
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
                //-------------------------------
                if (self::$handlerDisallow)
                    return;

                self::$handlerDisallow = true;

                //--------- создаем элемент-------
                $el = new CIBlockElement;
                $arLoadProduct = [
                    "IBLOCK_SECTION_ID" => $ID,
                    "IBLOCK_ID" => $res['ID'],
                    "NAME" => $rsElement['ID']+2,
                    "PREVIEW_TEXT" => implode("->", $ITEMS),
                    "ACTIVE_FROM" => date('d.m.Y')
                ];
                if ($PRODUCT = $el->Add($arLoadProduct))
                {
                    echo "Добавлен элемент в инфоблок LOG в новый раздел : " . $PRODUCT . "<br>";
                }
                else
                {
                    echo "Error: " . $el->LAST_ERROR . '<br>';
                }
                //---------------------------------
                self::$handlerDisallow = false;
            }
            else// совпадения есть, создаем элемент в совпавший раздел
            {
                if (self::$handlerDisallow)
                    return;
                self::$handlerDisallow = true;
                //-----------------------------
                $el = new CIBlockElement;
                $arLoadProduct = [
                    "IBLOCK_SECTION_ID" => $arSect['ID'],
                    "IBLOCK_ID" => $res['ID'],
                    "NAME" => $rsElement['ID']+2,
                    "PREVIEW_TEXT" => implode("->", $ITEMS),
                    "ACTIVE_FROM" => date('d.m.Y')
                ];
                if ($PRODUCT = $el->Add($arLoadProduct))
                {
                    echo "Добавлен элемент в инфоблок LOG в существующий раздел : " . $PRODUCT . "<br>";
                }
                else
                {
                    echo "Error: " . $el->LAST_ERROR . '<br>';
                }
                //--------------------------------
                self::$handlerDisallow = false;
            }
        }
        else// элемент не привязан к разделу, создаем элемент в корне инфоблока ЛОГ
            {
                if (self::$handlerDisallow)
                    return;

                self::$handlerDisallow = true;

                $ITEMS[] = $arFields['NAME'];

                $el = new CIBlockElement;
                $arLoadProduct = [
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID" => $res['ID'],
                    "NAME" => $rsElement['ID']+2,
                    "PREVIEW_TEXT" => implode("->", $ITEMS),
                    "ACTIVE_FROM" => date('d.m.Y')
                ];
                if ($PRODUCT = $el->Add($arLoadProduct))
                {
                    echo "Добавлен элемент в инфоблок LOG в корень : " . $PRODUCT . "<br>";
                }
                else
                {
                    echo "Error: " . $el->LAST_ERROR . '<br>';
                }

                self::$handlerDisallow = false;
            }
    }
}