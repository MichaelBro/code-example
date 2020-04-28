<?php

namespace Custom\Rest;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Custom\Main\Base\Forms\SectionTable as SectionTableForms;
use Custom\Main\Base\Legal\ElementTable as ElementTableLegal;
use Custom\Main\Base\Malls\SectionTable as SectionTableMalls;

class Forms
{
    public const RUSSIAN_FEDERATION_ID = 40;
    public const CHINA_ID = 35;

    /**
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getStart(): array
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $arText = LangHelper::getTextForStart($request->getPost('lang'));

        $html = "<form id='startForm' class='startForm' action='/rest/form/main/' data-button_url='/rest/form/button/'>";
        $html .= "<select name='country' id='country#0'>  <option disabled selected>{$arText['SELECT_TEXT']}</option>";

        foreach (self::getCountriesArray() as $country) {
            $html .= "<option value='" . $country['ID'] . "'>" . $country['NAME'] . "</option>";
        }

        $html .= "</select><div class='buttonHolder'></div></form>";

        return [
            'html' => $html,
            'styles' => self::getSrcPath('res/css/styles.min.css'),
            'breadCrumb' => $arText['BREADCRUMB']
        ];
    }

    /**
     * @return array|string[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getButton(): array
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $countryId = $request->getPost('country');
        $lang = $request->getPost('lang');
        $arForms = self::getFormsArray($countryId, $lang);

        $html = '';
        foreach ($arForms as $id => $name) {
            $html .= "<button type='button' data-key='" . $id . "'>" . $name . "</button>";
        }

        return [
            'html' => $html,
        ];
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getCountriesArray(): array
    {
        global $CACHE_MANAGER;

        $countries = [];

        $cache = Cache::createInstance();
        $cacheId = md5(__METHOD__);
        $cacheTime = GLOBAL_CACHE_TIME ?: 3600;
        $cacheDir = '/modules/custom.rest/lib/forms/';

        if ($cache->InitCache($cacheTime, $cacheId, $cacheDir)) {
            $countries = $cache->getVars();
        } else {
            $sectionElements = SectionTableMalls::getList(
                [
                    'select' => ['ID', 'NAME', 'UF_EMAIL'],
                    'filter' => [
                        'IBLOCK_ID' => SectionTableMalls::getIblockId(),
                        'ACTIVE' => 'Y'
                    ],
                    'order' => [
                        'SORT' => 'ASC',
                        'NAME' => 'ASC'
                    ]
                ]
            );

            while ($element = $sectionElements->fetch()) {
                $countries[$element['ID']] = $element;
            }

            $CACHE_MANAGER->StartTagCache($cacheDir);
            $CACHE_MANAGER->RegisterTag('iblock_id_' . SectionTableMalls::getIblockId());
            $CACHE_MANAGER->EndTagCache();
            $cache->startDataCache($cacheTime, $cacheId);
            $cache->EndDataCache($countries);
        }

        return $countries;
    }

    /**
     * @param int $countryId
     * @param string $lang
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getFormsArray(int $countryId, string $lang): array
    {
        global $CACHE_MANAGER;

        $forms = [];

        $cache = Cache::createInstance();
        $cacheId = md5(__METHOD__ . $countryId . $lang);
        $cacheTime = GLOBAL_CACHE_TIME ?: 3600;
        $cacheDir = '/modules/custom.rest/lib/forms/';

        if ($cache->InitCache($cacheTime, $cacheId, $cacheDir)) {
            $forms = $cache->getVars();
        } else {
            $arLangData = LangHelper::getLangDataForFormsList($countryId, $lang);
            $sectionElements = SectionTableForms::getList(
                [
                    'select' => [
                        'ID',
                        'NAME',
                        'LANG_NAME' => 'UF_' . $arLangData['LANG'] . '_NAME',
                    ],
                    'filter' => [
                        'IBLOCK_ID' => SectionTableForms::getIblockId(),
                        'ACTIVE' => 'Y',
                        'IBLOCK_SECTION_ID' => $arLangData['IBLOCK_SECTION_ID'],
                    ],
                    'order' => [
                        'SORT' => 'ASC',
                        'NAME' => 'ASC'
                    ]
                ]
            );

            while ($element = $sectionElements->fetch()) {
                $forms[$element['ID']] = $element['LANG_NAME'] ?? $element['NAME'];
            }

            $CACHE_MANAGER->StartTagCache($cacheDir);
            $CACHE_MANAGER->RegisterTag('iblock_id_' . SectionTableForms::getIblockId());
            $CACHE_MANAGER->EndTagCache();
            $cache->startDataCache($cacheTime, $cacheId);
            $cache->EndDataCache($forms);
        }

        return $forms;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function main(): array
    {
        return MakerForms::makeForm();
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function legal(): array
    {
        $id = Application::getInstance()->getContext()->getRequest()->get('id');
        $arResult = ElementTableLegal::getList(
            [
                'select' => ['DETAIL_TEXT'],
                'filter' => [
                    'ID' => $id,
                    'IBLOCK_ID' => ElementTableLegal::getIblockId(),
                ],
            ]
        )->fetch();
        return ['html' => $arResult['DETAIL_TEXT']];
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function submit(): array
    {
        return RequestHandler::handleResponseData();
    }

    /**
     * @param $relativePath
     * @return string
     */
    public static function getSrcPath($relativePath): string
    {
        $src = MARKUP_PATH . $relativePath;
        $src .= '?' . filemtime($_SERVER['DOCUMENT_ROOT'] . $src);
        return (\CAllMain::IsHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $src;
    }
}
