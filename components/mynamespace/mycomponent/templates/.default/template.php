<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(true);
?>

<div class="news-list">
    <?foreach($arResult["ITEMS"] as$key1=>$arItem):?>
        <?foreach($arItem as$key2=>$value):?>
                <p class="news-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                    <b><?echo $arItem[$key2]["NAME"]?></b><br/>
                    <?echo $arItem[$key2]["PREVIEW_TEXT"];?>
                    <?foreach($arItem[$key2]["FIELDS"] as $code=>$value1):?>
                        <small>
                            <?=GetMessage("IBLOCK_FIELD_".$code)?>:&nbsp;<?=$value1;?>
                        </small><br />
                    <?endforeach;?>
                </p>
        <?endforeach;?>
    <?endforeach;?>
</div>
