<?php
/**
 * Created by PhpStorm.
 * @author Karikh Dmitriy <demoriz@gmail.com>
 * @date 23.10.2020
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;

Loader::includeModule('iblock');

/**
 * Class CSiartSimpleForm
 */
class CSiartSimpleForm extends CBitrixComponent
{
    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_ID'] = (int)$arParams['IBLOCK_ID'];
        $arParams['EVENT_NAME'] = (string)$arParams['EVENT_NAME'];
        if (empty($arParams['TITLE'])) $arParams['TITLE'] = 'Заполнена форма';
        $arParams['INCLUDE_URL'] = ($arParams['INCLUDE_URL'] == 'Y');
        $arParams['IS_NEED_RE_CAPTCHA'] = (!empty($arParams['RE_CAPTCHA_SITE_KEY']) && !empty($arParams['RE_CAPTCHA_SECRET_KEY']));
        $arParams['RE_CAPTCHA_SCORE'] = (float)$arParams['RE_CAPTCHA_SCORE'];
        if ($arParams['RE_CAPTCHA_SCORE'] <= 0) $arParams['RE_CAPTCHA_SCORE'] = 0;
        $arParams['IBLOCK_FIELDS'] = array(
            'SORT',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE'
        );

        return $arParams;
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        global $APPLICATION;

        // поле 'HIDDEN' для идентификации компонента
        $this->arResult['HIDDEN'] = md5(implode('', $this->arParams) . __CLASS__);
        $this->arResult['ERROR'] = array();

        // установка кода рекапчи
        if ($this->arParams['IS_NEED_RE_CAPTCHA']) {
            Asset::getInstance()->addString('<script src="https://www.google.com/recaptcha/api.js"></script>', true, AssetLocation::AFTER_CSS);
        }

        $strMode = $this->request->get('mode');
        // проверяем сессию и соответствие поля 'HIDDEN'
        if ($strMode == $this->arResult['HIDDEN'] && check_bitrix_sessid()) {
            $isOk = true;

            // если нужна рекапча, проверяем её
            if ($this->arParams['IS_NEED_RE_CAPTCHA']) {
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret="
                    . $this->arParams['RE_CAPTCHA_SECRET_KEY'] . "&response="
                    . $this->request->get('g-recaptcha-response') . "&remoteip="
                    . $_SERVER['REMOTE_ADDR']);
                $arResponse = json_decode($response, true);

                // рекапча не прошла проверку
                if (!$arResponse['success'] && $arResponse['score'] >= $this->arParams['RE_CAPTCHA_SCORE']) {
                    $isOk = false;
                    $this->arResult['ERROR'][] = 'Ошибка recaptcha';
                }
            }

            if ($isOk) {
                if ($this->arParams['IBLOCK_ID'] > 0) {
                    $this->setIblock();
                }

                if (!empty($this->arParams['EVENT_NAME'])) {
                    $this->sendEmail();
                }

            }

            $APPLICATION->RestartBuffer();
            header("Content-type:application/json");
            $arOut = array(
                'STATUS' => empty($this->arResult['ERROR']),
                'ERROR' => $this->arResult['ERROR']
            );
            echo json_encode($arOut);
            die();
        }

        $this->IncludeComponentTemplate();
    }

    /**
     * Добавляем в инфоблок
     */
    private function setIblock()
    {

        $element = new \CIBlockElement;
        $arFields = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'NAME' => $this->arParams['TITLE'],
            'PROPERTY_VALUES' => array()
        );
        foreach ($this->arParams['FIELDS'] as $strCode) {
            if (in_array($strCode, $this->arParams['IBLOCK_FIELDS'])) {
                $arFields[$strCode] = $this->request->get($strCode);

            } else {
                $arFields['PROPERTY_VALUES'][$strCode] = $this->request->get($strCode);
            }
        }
        if (empty($arFields['NAME'])) {
            $arFields['NAME'] = $this->arParams['TITLE'];
        }
        if ($this->arParams['INCLUDE_URL']) {
            $arFields['PROPERTY_VALUES']['URL'] = $this->request->getRequestUri();
        }
        $result = $element->Add($arFields);

        if ($result === false) {
            $this->arResult['ERROR'][] = $element->LAST_ERROR;
        }
    }

    /**
     * Отправляем письмо
     */
    private function sendEmail()
    {
        $arMailFields = array(
            'EVENT_NAME' => $this->arParams['EVENT_NAME'],
            'LID' => $this->getSiteId(),
            'C_FIELDS' => array()
        );

        foreach ($this->arParams['FIELDS'] as $strCode) {
            $arMailFields['C_FIELDS'][$strCode] = $this->request->get($strCode);
        }
        if ($this->arParams['INCLUDE_URL']) {
            $arMailFields['C_FIELDS']['URL'] = $this->request->getRequestUri();
        }

        $result = Event::send($arMailFields);

        if (!$result->isSuccess()) {
            $this->arResult['ERROR'][] = $result->getErrorMessages();
        }
    }
}