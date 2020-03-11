<?php
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules/dev.site/lib/Handlers/Iblock.php"))
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/dev.site/lib/Handlers/Iblock.php");
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules/dev.site/lib/Agents/Iblock.php"))
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/dev.site/lib/Agents/Iblock.php");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("Only\Site\Handlers\Iblock", "addLog"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("Only\Site\Handlers\Iblock", "addLog"));

