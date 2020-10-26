<?php
/**
 * Created by PhpStorm.
 * @author Karikh Dmitriy <demoriz@gmail.com>
 * @date 28.09.2020
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */

/** @var CBitrixComponent $component */

$this->setFrameMode(true);
?>
<div id="modal_callback" class="modal">
    <div class="modal--callback-wrap modal--wrap">
        <h2 class="modal--title">Заказать звонок</h2>
        <form id="callback_form">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="mode" value="<?= $arResult['HIDDEN'] ?>">
            <div class="input--wrap">
                <div class="input--holder">
                    <div class="flex flex-center-vertical">
                        <span class="input__placeholder">Имя</span>
                    </div>
                    <label for="callback_name">Имя</label>
                    <input class="iText validate" type="text"
                           name="NAME"
                           data-parsley-required-message="Поле обязательно для заполнения" required
                           id="callback_name" placeholder="">
                </div>
            </div>
            <div class="input--wrap">
                <div class="input--holder">
                    <div class="flex flex-center-vertical">
                        <span class="input__placeholder">Телефон</span>
                        <span class="phone__holder">+7 (___) ___-__-__</span>
                    </div>
                    <label for="callback_phone">Телефон</label>
                    <input class="iText pmask validate" data-parsley-required-message="Поле обязательно для заполнения"
                           name="PHONE"
                           data-parsley-pattern="/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){11,14}(\s*)?$/" required
                           type="text" id="callback_phone" placeholder="">
                </div>
            </div>
            <div class="callback--bottom flex flex-column flex-center-vertical flex-center-horizontal">
                <button
                        id="modal_callback-btn"
                        role="button"
                        data-sitekey="<?= $arParams['RE_CAPTCHA_SITE_KEY'] ?>"
                        data-callback="onCallbackSubmit"
                        class="button--submit btn_transparent flex flex-center-vertical flex-center-horizontal g-recaptcha">
                    <span>Отправить</span>
                </button>
                <div class="terms--check">
                    <label class="inpCheck">
                        <input type="checkbox" required name="callback__terms" value="Y"
                               data-parsley-required-message="">
                        <span>Я согласен на обработку <a target="_blank" href="#">персональных данных</a></span>
                    </label>
                </div>
            </div>
        </form>
    </div>


    <div class="modal--success">
        <div class="modal_ok flex flex-column flex-center-vertical flex-center-horizontal">
            <span class="main_h2">Спасибо!</span>
            <p>Мы скоро с вами свяжемся.</p>
        </div>
    </div>
</div>
