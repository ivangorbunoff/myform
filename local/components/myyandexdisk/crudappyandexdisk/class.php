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


        if($_POST['buttondel']) // если нажать удалить
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


        if($_POST['buttoncopy'])    // если нажать копировать
        {
            foreach($_POST['pathfordelcop'] as$key=>$path)
            {
                $resource = $disk->getResource($path);  // в $path пути отмеченных элементов
                if(!$resource->has() || $_POST['pathtofolder'] == $this->getPathDirecories($_POST['pathfordelcop'][$key]))
                {
                    continue;
                }
//               в строке ниже составляем путь копирования - /путь до папки в которую копируем/название файла который копируем
                $resource->copy($_POST['pathtofolder'].$this->getNameFile($_POST['pathfordelcop'][$key]),true);
            }
        }


        if ($_FILES && $_FILES['pathtofile']['error']== UPLOAD_ERR_OK)
        {
            $name = $_FILES['pathtofile']['name'];
            move_uploaded_file($_FILES['pathtofile']['tmp_name'], $_SERVER["DOCUMENT_ROOT"].'/'.$name);//загружаем файл на сервер
            $resource = $disk->getResource($name);
            $resource->upload($_SERVER["DOCUMENT_ROOT"].'/'.$name,true);
            unlink($_SERVER["DOCUMENT_ROOT"].'/'.$name); // удаляем файл с сервера
        }


        if(intval($this->arParams['LIMIT'])!== 0)   //установка показа количества файлов на странице
            $this->arParams['LIMIT'] = intval($this->arParams['LIMIT']);
            else
                $this->arParams['LIMIT'] = 20;


        $absolutepath = $_SERVER['REQUEST_SCHEME'] . '://'.$_SERVER['HTTP_HOST'].'/YDisk_images/';//для атрибута src тега img
        $folder = $_SERVER['DOCUMENT_ROOT'] . '/YDisk_images/';
        mkdir($folder); // создаем папку для хранения файлов
        $collection = $disk->getResources($this->arParams['LIMIT']);
        foreach ($collection as $item)
        {
            $arList[] = $item->getIterator();
        }
        $arPath = [];   // массив для выборки папки для копирования
        $forClear = []; // массив с именами файлов для очистки папки YDisk_images
        $i = 0;
        foreach ($arList as $element)
        {
            $arElements[$i]['path'] = $element['path'];     // формируем массив для arResult
            $arElements[$i]['name'] = $element['name'];
            if($element['media_type'] === 'image')
            {
                if(!file_exists($folder.$element['name'])) // если такого файла нет то скачиваем
                    file_put_contents($folder.$element['name'], file_get_contents($element['file']));
                $arElements[$i]['preview'] = $absolutepath.$element['name'];
                $forClear[] = $element['name'];
            }
            $arElements[$i]['docviewer'] = $element['docviewer'];
            $arPath[$i] = $this->getPathDirecories($arElements[$i]['path']);
            $i++;
        }

        $YDisk_images = scandir($folder);
        foreach ($YDisk_images as $img)
            if (!in_array($img, $forClear))// если в папке YDisk_images есть файлы, которых уже нет на яндекс диске, удаляем их также и из YDisk_images
            {
                unlink($folder.$img);
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