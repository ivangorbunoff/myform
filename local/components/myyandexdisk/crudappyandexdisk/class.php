<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
require_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

class ClassForm extends CBitrixComponent
{
    function getPathDirecories($strPath)    // функция откидывает имя файла и оставляет только путь
    {
        $pos = strrpos($strPath,'/');
        $strPath = substr($strPath,0,$pos+1);
        return $strPath;
    }

    function getNameFile($strName)  // функция откидывает путь и оставляет только имя файла (для копирования)
    {
        $pos = strrpos($strName,'/');
        $strName = substr($strName,$pos+1);
        return $strName;
    }

    public function executeComponent()
    {
        $linkToken = '<a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=6a71e4a0b7a6449d8ea44176ba676f52">Получить токен</a>';
        // arParams['CHANGE_ACC'] ссылка на случай если нужно изменить аккаунт
        $this->arParams['CHANGE_ACC'] = '<a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=6a71e4a0b7a6449d8ea44176ba676f52">Изменить Яндекс аккаунт</a>';
        try
        {
            $disk = new Arhitector\Yandex\Disk($this->arParams['TOKEN']);
            $disk->toArray();
        }
        catch (Exception $exc)
        {
            echo $exc->getMessage();
            echo '<br/>'.'Получите токен по ссылке и введите в поле в настройках компонента'.'<br/>';
            echo $linkToken;
            die();
        }

        if($_POST['buttondel']) // если нажать - удалить
        {
            foreach($_POST['pathfordelcop'] as$key=>$path)  // удаляем выбранные элементы
            {
                $resource = $disk->getResource($path);  // в $path пути отмеченных элементов
                if(!$resource->has())
                {
                    continue;  // чтобы не падало если обновить после отправки
                }
                $resource->delete();
            }
        }

        $j = 0;
        if($_POST['buttoncopy'])    // если нажать - копировать
        {
            foreach($_POST['pathfordelcop'] as$key=>$path)
            {
                $resource = $disk->getResource($path);  // в $path пути отмеченных элементов

                if(!$resource->has())
                {
                    continue;
                }
//               в строке ниже составляем путь копирования - /путь до папки в которую копируем/название файла который копируем
                $resource->copy($_POST['pathtofolder'].$this->getNameFile($_POST['pathfordelcop'][$j]),true);
                $j++;
            }
        }

        $this->arParams['placeholder'] = 'Введите путь до файла';   // плейсхолдер для строки ввода
        if($_POST['pathtofile'] !== '' && isset($_POST['pathtofile']))
        {
            if(file_exists($_POST['pathtofile']))
            {
                $resource = $disk->getResource($_POST['pathtofile']);
                $resource->upload($_POST['pathtofile'],true);
                $this->arParams['placeholder'] = 'Файл успешно добавлен';
            }
            else
                $this->arParams['placeholder'] = 'Неверный путь до файла';
        }

        if(intval($this->arParams['LIMIT'])!== 0)   //установка показа количества файлов на странице
            $this->arParams['LIMIT'] = intval($this->arParams['LIMIT']);
            else
                $this->arParams['LIMIT'] = 20;

        $collection = $disk->getResources($this->arParams['LIMIT']);	// получаем список файлов
        foreach ($collection as $item)
        {
            $arList[] = $item->getIterator();
        }
        $arPath = [];   // массив для выборки папки для копирования
        $i = 0;
        foreach ($arList as $element)
        {
            $arElements[$i]['path'] = $element['path'];     // формируем массив для arResult
            $arElements[$i]['name'] = $element['name'];
            $arElements[$i]['preview'] = $element['preview'];
            $arElements[$i]['docviewer'] = $element['docviewer'];
            $arPath[$i] = $this->getPathDirecories($arElements[$i]['path']);
            $i++;
        }

        $count = count($arPath);
        for($i = 0; $i < $count-1; $i++)    // удаляем одинаковые папки (для выборки папки для копирования)
            for($j = $i+1; $j < $count; $j++)
                if($arPath[$i] == $arPath[$j])
                    unset($arPath[$j]);

        $this->arParams['SELECT_FOLDER'] = $arPath;// arParams тоже буду передавать в шаблон т.к. в arResult морока с этими ключами)))

        $this->arResult = $arElements;

        $this->includeComponentTemplate();
    }
}