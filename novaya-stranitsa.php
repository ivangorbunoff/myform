<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
\Bitrix\Main\Loader::includeModule('iblock');

$el = new CIBlockElement;
$ibpenum = new CIBlockPropertyEnum;
$IBLOCK_ID = 3;

$rsProp = CIBlockPropertyEnum::GetList(
    [ "ID" => "ASC"],
    ['IBLOCK_ID' => $IBLOCK_ID]
);
while ($arProp = $rsProp->Fetch())
{
    $key = trim($arProp['VALUE']);
    $arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
}

$rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);
while ($element = $rsElements->GetNext())
{
    CIBlockElement::Delete($element['ID']);
}

if (($handle = fopen("vacancy.csv", "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        if ($data[0] == '№ п/п')
        {
            continue;
        }
        //---------------Блок для типа зарплаты--------------------------
        if(preg_match('/^по/',$data[7]))
        {
            $PROP['SALARY_VALUE'] = '';
            $PROP['SALARY_TYPE'] = 'Договорная';
        }
        elseif (preg_match('/^\d/',$data[7]))
        {
            $PROP['SALARY_VALUE'] = $data[7];
            $PROP['SALARY_TYPE'] = '=';
        }
        elseif (preg_match('/^от/',$data[7]))
        {
            $PROP['SALARY_VALUE'] = substr($data[7],3);
            $PROP['SALARY_TYPE'] = 'ОТ';
        }
        elseif (preg_match('/^до/',$data[7]))
        {
            $PROP['SALARY_VALUE'] = substr($data[7],3);
            $PROP['SALARY_TYPE'] = 'ДО';
        }
        else
        {
            $PROP['SALARY_VALUE'] = '';
            $PROP['SALARY_TYPE'] = 'Не указано';
        }
        //---------------------------------------------------------
        $PROP['ACTIVITY'] = trim($data[9]);
        $PROP['FIELD'] = trim($data[11]);
        $PROP['OFFICE'] = trim($data[1]);
        $PROP['LOCATION'] = trim($data[2]);
        $PROP['TYPE'] = trim($data[8]);
        $PROP['SCHEDULE'] = trim($data[10]);

        foreach ($PROP as $key => &$value)
        {
            $i = 0;// переменная для подсчета итераций
            foreach ($arProps[$key] as $propKey => $propVal)
            {
                if (stripos($propKey, $value) !== false)
                {
                    $value = $propVal;
                    break;
                }
                else
                {
                    $i++;
                    if($i==count($arProps[$key]))// если итерация последняя и небыло совпадений, добавляем значение свойства
                    {
                        $property = CIBlockProperty::GetByID($key, $IBLOCK_ID)->GetNext();// для получения id свойства
                        if ($PropID = $ibpenum->Add(Array('PROPERTY_ID' => $property['ID'], 'VALUE' => $value)))
                        {
                            echo 'Добавлено значение в свойство ' . $key.' '.$value.'<br/>';
                            $arProps[$key][$value] = $PropID;
                            $value = $PropID;
                        }
                    }
                }
            }
        }
        $PROP['REQUIRE'] = explode('•', $data[4]);
        $PROP['DUTY'] = explode('•', $data[5]);
        $PROP['CONDITIONS'] = explode('•', $data[6]);
        $PROP['DATE'] = date('d.m.Y');
        $PROP['EMAIL'] = $data[12];

        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $data[3],
            "ACTIVE" => end($data) ? 'Y' : 'N',
        ];
        if ($PRODUCT_ID = $el->Add($arLoadProductArray))
        {
            echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
        }
        else
        {
            echo "Error: " . $el->LAST_ERROR . '<br>';
        }
    }
    fclose($handle);
}

