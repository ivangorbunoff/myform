<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?if ($arResult["isFormErrors"] == "Y"):?><?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>



<?if ($arResult["isFormNote"] != "Y")// если форма не отправлена - выводим форму
{
?>
    <?php $IDquestion = array();?>
    <?php $i = 0;?>
	<?
	foreach ($arResult["QUESTIONS"] as $Key => $arQuestion)
	{
        $IDquestion[$i] = $Key;// в этот массив получаем символьные идентификаторы вопросов
        $i++;
	}
	?>

    <body>
    <div class="contact-form">
        <?php if ($arResult["isFormDescription"] == "Y" || $arResult["isFormTitle"] == "Y"):?>

            <div class="contact-form__head">
                <div class="contact-form__head-title"><?=$arResult["FORM_TITLE"]?></div><!-- вывод заголовка и описания-->
                <div class="contact-form__head-text"><?=$arResult["FORM_DESCRIPTION"]?></div>
            </div>
        <?endif;?>

        <form name="<?=$arResult["WEB_FORM_NAME"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST" class="contact-form__form" enctype="multipart/form-data">
            <input type="hidden" name="WEB_FORM_ID" value="<?=$arParams["WEB_FORM_ID"]?>" />
            <?=bitrix_sessid_post()?>
            <div class="contact-form__form-inputs">
                <div class="input contact-form__input"><label class="input__label" for="medicine_name">
                        <div class="input__label-text"><?=$arResult["QUESTIONS"]["$IDquestion[0]"]["CAPTION"]?><!-- название вопроса -->
                            <?if ($arResult["QUESTIONS"]["$IDquestion[0]"]["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?></div> <!-- если обязательное поле то добавляем звездочку -->
                        <?=$arResult["QUESTIONS"]["$IDquestion[0]"]["HTML_CODE"]?><!-- стандартное поле ввода т.к. форму не отправляет подругому))) -->
                        <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                    </label></div>
                <div class="input contact-form__input"><label class="input__label" for="medicine_company">
                        <div class="input__label-text"><?=$arResult["QUESTIONS"]["$IDquestion[1]"]["CAPTION"]?>
                            <?if ($arResult["QUESTIONS"]["$IDquestion[1]"]["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?></div>
                        <?=$arResult["QUESTIONS"]["$IDquestion[1]"]["HTML_CODE"]?>
                        <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                    </label></div>
                <div class="input contact-form__input"><label class="input__label" for="medicine_email">
                        <div class="input__label-text"><?=$arResult["QUESTIONS"]["$IDquestion[2]"]["CAPTION"]?>
                            <?if ($arResult["QUESTIONS"]["$IDquestion[2]"]["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?></div>
                        <?=$arResult["QUESTIONS"]["$IDquestion[2]"]["HTML_CODE"]?>
                        <div class="input__notification">Неверный формат почты</div>
                    </label></div>
                <div class="input contact-form__input"><label class="input__label" for="medicine_phone">
                        <div class="input__label-text"><?=$arResult["QUESTIONS"]["$IDquestion[3]"]["CAPTION"]?>
                            <?if ($arResult["QUESTIONS"]["$IDquestion[3]"]["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?></div>
                        <?=$arResult["QUESTIONS"]["$IDquestion[3]"]["HTML_CODE"]?></label></div>
            </div>
            <div class="contact-form__form-message">
                <div class="input"><label class="input__label" for="medicine_message">
                        <div class="input__label-text"><?=$arResult["QUESTIONS"]["$IDquestion[4]"]["CAPTION"]?>
                            <?if ($arResult["QUESTIONS"]["$IDquestion[4]"]["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?></div>
                        <?=$arResult["QUESTIONS"]["$IDquestion[4]"]["HTML_CODE"]?>
                        <div class="input__notification"></div>
                    </label></div>
            </div>
            <div class="contact-form__bottom">
                <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
                    ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных
                    данных&raquo;.
                </div>
                <input class="form-button contact-form__bottom-button" type="submit" name="web_form_submit" value="<?= $arResult["arForm"]["BUTTON"] ?>"/>
            </div>
        </form>
    </div>
    </body>


<?
}
?>

