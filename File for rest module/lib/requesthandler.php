<?php

namespace Custom\Rest;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Custom\Main\Base\ElementsFields\SectionTable as SectionTableElementsFields;
use Custom\Main\Base\Forms\ElementTable as ElementTableForms;
use Custom\Main\Base\Forms\Property\SimpleTable as SimpleTableForms;
use Custom\Main\Base\ResultForms\ElementTable as ElementTableResultForms;

class RequestHandler
{
    /**
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function handleResponseData(): array
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $arPost = $request->getPostList()->toArray();

        $error = [];
        $arFile = [];

        global $APPLICATION;
        if ($APPLICATION->CaptchaCheckCode($arPost['captcha'], $arPost['captchaSid'])) {
            $error['captcha'] = 'captcha';
        } else {
            $arQuestions = self::getQuestionsArray($arPost);
            foreach ($arQuestions as $key => $question) {
                if ($question['TYPE_INPUT'] === 'file') {
                    $arFile = $request->getFile($question['ID'] . '-' . $question['CODE']);
                    $arQuestions[$key]['ANSWERS'][] = self::savePostFile($arFile);
                }
                foreach ($arPost as $codeFromPost => $value) {
                    if (strpos($codeFromPost, $question['ID'] . '-' . $question['CODE']) !== false) {
                        $arQuestions[$key]['ANSWERS'][] = $value;
                    }
                }
            }

            self::addAnswer($arQuestions, $arPost, (array)$arFile);
        }
        $arText = LangHelper::getTextForSubmit($arPost['langForm']);

        if (empty($error)) {
            return [
                'success' => true,
                'message' => $arText['MESSAGE'],
            ];
        }
        return [
            'success' => false,
            'message' => $arText['ERROR'],
            'errors' => $error
        ];
    }

    /**
     * @param $arFile
     * @return string
     */
    private static function savePostFile($arFile): string
    {
        if (!empty($arFile)) {
            $fileId = \CFile::SaveFile($arFile, 'forms');
            if (!empty($fileId)) {
                $arFileSaved = \CFile::GetFileArray($fileId);
                $fileSrc = (\CAllMain::IsHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $arFileSaved['SRC'];
                return "<a href=\"$fileSrc\">{$arFileSaved['ORIGINAL_NAME']}</a>";
            }
        }
        return 'error save file';
    }

    /**
     * @param array $arPost
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getQuestionsArray(array $arPost): array
    {
        global $CACHE_MANAGER;

        $cache = Cache::createInstance();
        $cacheId = md5(__METHOD__ . $arPost['key'] . $arPost['langForm']);
        $cacheTime = GLOBAL_CACHE_TIME ?: 3600;
        $cacheDir = '/modules/custom.rest/lib/forms/';
        $questions = [];
        if ($cache->InitCache($cacheTime, $cacheId, $cacheDir)) {
            $questions = $cache->getVars();
        } else {
            $questions = ElementTableForms::getList([
                'runtime' => [
                    new ReferenceField(
                        'SECTION',
                        SectionTableElementsFields::getEntity(),
                        [
                            '=ref.ID' => 'this.IBLOCK_SECTION_ID'
                        ]
                    ),
                    new ReferenceField(
                        'SIMPLE_PROPERTIES',
                        SimpleTableForms::getEntity(),
                        [
                            '=ref.IBLOCK_ELEMENT_ID' => 'this.ID'
                        ]
                    ),
                    new ReferenceField(
                        'TYPE_INPUT_ENUM',
                        PropertyEnumerationTable::getEntity(),
                        ['=this.SIMPLE_PROPERTIES.TYPE_INPUT' => 'ref.ID']
                    ),
                    new ReferenceField(
                        'SECOND_INPUT_TYPE_ENUM',
                        PropertyEnumerationTable::getEntity(),
                        ['=this.SIMPLE_PROPERTIES.SECOND_INPUT_TYPE' => 'ref.ID']
                    ),
                ],
                'select' => [
                    'ID',
                    'CODE',
                    'NAME',
                    'TYPE_INPUT' => 'TYPE_INPUT_ENUM.XML_ID',
                    'SECOND_INPUT_TYPE' => 'SECOND_INPUT_TYPE_ENUM.XML_ID',
                    'NAME_INPUT' => 'SIMPLE_PROPERTIES.' . $arPost['langForm'] . '_NAME',
                ],
                'filter' => [
                    'IBLOCK_ID' => ElementTableForms::getIblockId(),
                    'SECTION.IBLOCK_SECTION_ID' => $arPost['key'],
                    'ACTIVE' => 'Y'
                ],
                'order' => [
                    'SECTION.SORT' => 'ASC',
                    'SORT' => 'ASC'
                ],
            ])->fetchAll();

            $CACHE_MANAGER->StartTagCache($cacheDir);
            $CACHE_MANAGER->RegisterTag('iblock_id_' . ElementTableForms::getIblockId());
            $CACHE_MANAGER->RegisterTag('iblock_id_' . SectionTableElementsFields::getIblockId());
            $CACHE_MANAGER->EndTagCache();
            $cache->startDataCache($cacheTime, $cacheId);
            $cache->EndDataCache($questions);
        }
        return $questions;
    }

    /**
     * @param array $arQuestions
     * @param array $arPost
     * @param array $arFile
     * @return bool
     * @throws LoaderException
     */
    private static function addAnswer(array $arQuestions, array $arPost, array $arFile): bool
    {
        $resultString = '';
        $countries = Forms::getCountries();
        $currentCountry = $countries[$arPost['country']];

        foreach ($arQuestions as $question) {
            $answer = implode(', ', $question['ANSWERS']);
            $resultString .= "<tr><td><b>{$question['NAME']}</b></td><td>$answer</td></tr>";
        }
        $table = "<table border='1' cellpadding='1' cellspacing='1'><tbody>$resultString</tbody></table>";

        $arFields = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => ElementTableResultForms::getIblockId(),
            'NAME' => $arPost['nameTheme'],
            'DETAIL_TEXT' => "<h2>{$arPost['nameTheme']}</h2><br>$table",
            'PREVIEW_TEXT' => json_encode(['result' => $arQuestions]),
            'PROPERTY_VALUES' => [
                'COUNTRY' => $currentCountry['ID'],
                'FILE' => $arFile
            ]
        ];
        $oElement = new \CIBlockElement();
        if ($idAnswer = $oElement->Add($arFields)) {
            return true;
        }
        return false;
    }

}