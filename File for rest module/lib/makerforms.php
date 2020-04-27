<?php

namespace Custom\Rest;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Custom\Main\Base\ElementsFields\ElementTable as ElementTableElementsFields;
use Custom\Main\Base\ElementsFields\Property\SimpleTable as SimpleTableElementsFields;
use Custom\Main\Base\ElementsFields\SectionTable as SectionTableElementsFields;
use Custom\Main\Base\Forms\ElementTable as ElementTableForms;
use Custom\Main\Base\Forms\Property\SimpleTable as SimpleTableForms;
use Custom\Main\Base\Forms\SectionTable as SectionTableForms;
use Custom\Main\Base\Malls\ElementTable as ElementTableMalls;
use Custom\Main\Base\Malls\Property\SimpleTable as SimpleTableMalls;

class MakerForms
{
    /**
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function makeForm(): array
    {
        global $CACHE_MANAGER;

        $arPost = Application::getInstance()->getContext()->getRequest()->toArray();

        $lang = (string)$arPost['lang'];
        $formId = (int)$arPost['key'];
        $countryId = (int)$arPost['country'];

        $arResult = [];

        $cache = Cache::createInstance();
        $cacheId = md5(__METHOD__ . $lang . $formId . $countryId);
        $cacheTime = GLOBAL_CACHE_TIME ?: 3600;
        $cacheDir = '/modules/custom.rest/lib/forms/';

        if ($cache->InitCache($cacheTime, $cacheId, $cacheDir)) {
            $arResult = $cache->getVars();
        } else {
            $arLangData = LangHelper::getLanguageDataForForm($lang, $countryId);
            $arSection = self::getSectionArray($arLangData['LANG_FORM'], $formId);

            $arResult = [
                'form' => [
                    'breadCrumb' => $arSection['TITLE'],
                    'legalText' => $arSection['LEGAL_TEXT_SECTION'],
                    'submitButtonText' => $arLangData['BUTTON_TEXT'],
                    'submitUrl' => '/rest/form/submit/',
                    'errorMessages' => $arLangData['ERROR_TEXT'],
                    'groups' => self::getGroupsArray($arLangData['LANG_FORM'], $arSection, $countryId)
                ],
                'js' => Forms::getSrcPath('res/js/scripts.min.js')
            ];

            if ($arSection['TEXT_BEFORE_SECTION']) {
                $arResult['form']['beforeText'] = '<p>' . $arSection['TEXT_BEFORE_SECTION'] . '<p/>';
            }

            if ($arSection['TEXT_AFTER_SECTION']) {
                $arResult['form']['afterText'] = '<p>' . $arSection['TEXT_AFTER_SECTION'] . '<p/>';
            }

            $CACHE_MANAGER->StartTagCache($cacheDir);
            $CACHE_MANAGER->RegisterTag('iblock_id_' . SectionTableForms::getIblockId());
            $CACHE_MANAGER->RegisterTag('iblock_id_' . ElementTableForms::getIblockId());
            $CACHE_MANAGER->RegisterTag('iblock_id_' . ElementTableElementsFields::getIblockId());
            $CACHE_MANAGER->RegisterTag('iblock_id_' . SectionTableElementsFields::getIblockId());
            $CACHE_MANAGER->EndTagCache();
            $cache->startDataCache($cacheTime, $cacheId);
            $cache->EndDataCache($arResult);
        }
        return $arResult;
    }

    /**
     * @param string $langForm
     * @param int $formId
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getSectionArray(string $langForm, int $formId): array
    {
        $obResult = SectionTableForms::getList([
            'runtime' => [
                new ReferenceField(
                    'PARENT_SECTION',
                    SectionTableForms::getEntity(),
                    [
                        '=ref.ID' => 'this.IBLOCK_SECTION_ID'
                    ]
                ),
            ],
            'select' => [
                'ID',
                'CODE',
                'NAME',
                'SORT',
                'IBLOCK_SECTION_ID',
                'SUB_TITLE' => 'UF_' . $langForm . '_SUB_TITLE',
                'PARENT_SECTION_NAME' => 'PARENT_SECTION.NAME',
                'PARENT_SECTION_LANG_NAME' => 'PARENT_SECTION.UF_' . $langForm . '_NAME',
                'PARENT_SECTION_LEGAL' => 'PARENT_SECTION.UF_' . $langForm . '_LEGAL',
                'PARENT_SECTION_TEXT_BEFO' => 'PARENT_SECTION.UF_' . $langForm . '_TEXT_BEFO',
                'PARENT_SECTION_TEXT_AFTE' => 'PARENT_SECTION.UF_' . $langForm . '_TEXT_AFTE',
                'LANG_NAME' => 'UF_' . $langForm . '_NAME',
            ],
            'filter' => [
                'ACTIVE' => 'Y',
                'IBLOCK_SECTION_ID' => $formId,
            ],
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC'
            ],
        ]);
        $arSection = [];
        while ($item = $obResult->fetch()) {
            if (empty($arSection)) {
                $arSection = [
                    'TITLE' => $item['PARENT_SECTION_LANG_NAME'] ?? $item['PARENT_SECTION_NAME'],
                    'LEGAL_TEXT_SECTION' => $item['PARENT_SECTION_LEGAL'],
                    'TEXT_BEFORE_SECTION' => $item['PARENT_SECTION_TEXT_BEFO'],
                    'TEXT_AFTER_SECTION' => $item['PARENT_SECTION_TEXT_AFTE'],
                ];
            }
            $arSection['GROUPS'][$item['ID']] = [
                'ID' => $item['ID'],
                'CODE' => $item['CODE'],
                'SORT' => $item['SORT'],
                'NAME' => $item['LANG_NAME'] ?? $item['NAME'],
                'SUB_TITLE' => $item['SUB_TITLE'],
            ];
            $arSection['LIST_ID'][] = $item['ID'];
        }
        return $arSection;
    }

    /**
     * @param string $langForm
     * @param array $arSection
     * @param int $countryId
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getGroupsArray(string $langForm, array $arSection, int $countryId): array
    {
        $obResult = self::getObjectElementsInputs($langForm, $arSection['LIST_ID']);

        $arGroups = $arSection['GROUPS'];
        while ($item = $obResult->fetch()) {
            $group = 'FIRST_GROUP';
            if ($item['ADDITIONAL_GROUP']) {
                $group = 'SECOND_GROUP';
            }
            $arGroups[$item['IBLOCK_SECTION_ID']][$group][] = $item;
        }
        usort($arGroups, function ($a, $b) {
            return $a['SORT'] <=> $b['SORT'];
        });

        $arResult = [];
        foreach ($arGroups as $group) {
            $arResult[] = [
                'title' => $group['NAME'],
                'accordion' => false,
                'rows' => self::getRowsArray($group['FIRST_GROUP'], $langForm, $countryId),
            ];
            if (!empty($group['SECOND_GROUP'])) {
                $arResult[] = [
                    'subtitle' => $group['SUB_TITLE'],
                    'accordion' => true,
                    'rows' => self::getRowsArray($group['SECOND_GROUP'], $langForm, $countryId),
                ];
            }
        }
        $arResult[] = self::getHiddenFieldAndCaptcha($langForm, $arSection['TITLE']);
        return $arResult;
    }

    /**
     * @param string $langForm
     * @param $listId
     * @return Result
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getObjectElementsInputs(string $langForm, $listId): Result
    {
        return ElementTableForms::getList([
            'runtime' => [
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
                    'YEARS_FOR_TURNOVER_ENUM',
                    PropertyEnumerationTable::getEntity(),
                    ['=this.SIMPLE_PROPERTIES.YEARS_FOR_TURNOVER' => 'ref.ID']
                ),
                new ReferenceField(
                    'REQUIRED_ENUM',
                    PropertyEnumerationTable::getEntity(),
                    ['=this.SIMPLE_PROPERTIES.REQUIRED' => 'ref.ID']
                ),
                new ReferenceField(
                    'SECOND_INPUT_TYPE_ENUM',
                    PropertyEnumerationTable::getEntity(),
                    ['=this.SIMPLE_PROPERTIES.SECOND_INPUT_TYPE' => 'ref.ID']
                ),
                new ReferenceField(
                    'TYPE_LINK_ITEM_ENUM',
                    PropertyEnumerationTable::getEntity(),
                    ['=this.SIMPLE_PROPERTIES.TYPE_LINK_ITEM' => 'ref.ID']
                ),
                new ReferenceField(
                    'LINK_ITEM',
                    ElementTableForms::getEntity(),
                    ['=this.SIMPLE_PROPERTIES.LINK_ITEM' => 'ref.ID']
                ),

            ],
            'select' => [
                'ID',
                'CODE',
                'NAME',
                'SORT',
                'IBLOCK_SECTION_ID',

                'TYPE_INPUT' => 'TYPE_INPUT_ENUM.XML_ID',
                'REQUIRED' => 'REQUIRED_ENUM.XML_ID',
                'YEARS_FOR_TURNOVER' => 'YEARS_FOR_TURNOVER_ENUM.XML_ID',
                'ANSWER_SECTIONS' => 'SIMPLE_PROPERTIES.ANSWER_SECTIONS',
                'ADDITIONAL_GROUP' => 'SIMPLE_PROPERTIES.ADDITIONAL_GROUP',

                'SECOND_INPUT_TYPE' => 'SECOND_INPUT_TYPE_ENUM.XML_ID',

                'NAME_INPUT' => 'SIMPLE_PROPERTIES.' . $langForm . '_NAME',
                'LABEL_TEXT' => 'SIMPLE_PROPERTIES.' . $langForm . '_LABEL_TEXT',
                'TEXT_BEFORE' => 'SIMPLE_PROPERTIES.' . $langForm . '_TEXT_BEFORE',
                'TEXT_AFTER' => 'SIMPLE_PROPERTIES.' . $langForm . '_TEXT_AFTER',
                'TIP' => 'SIMPLE_PROPERTIES.' . $langForm . '_TIP',
                'SECOND_INPUT_TEXT_BEFORE' => 'SIMPLE_PROPERTIES.' . $langForm . '_SECOND_INPUT_TEXT_BEFORE',
                'SECOND_INPUT_TEXT_AFTER' => 'SIMPLE_PROPERTIES.' . $langForm . '_SECOND_INPUT_TEXT_AFTER',
                'SECOND_INPUT_LABEL_TEXT' => 'SIMPLE_PROPERTIES.' . $langForm . '_SECOND_INPUT_LABEL_TEXT',
                'LINK_ITEM_CODE' => 'LINK_ITEM.CODE',
                'LINK_ITEM_ID' => 'LINK_ITEM.ID',
                'TYPE_LINK_ITEM' => 'TYPE_LINK_ITEM_ENUM.XML_ID',
                'LABEL_TEXT_LINK_ITEM' => 'SIMPLE_PROPERTIES.' . $langForm . '_LINK_ITEM',
            ],
            'filter' => [
                'IBLOCK_ID' => ElementTableForms::getIblockId(),
                'IBLOCK_SECTION_ID' => $listId,
                'ACTIVE' => 'Y',
            ],
            'order' => [
                'SORT' => 'ASC'
            ],
        ]);
    }

    /**
     * @param $arElements
     * @param string $langForm
     * @param int $countryId
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getRowsArray($arElements, string $langForm, int $countryId): array
    {
        $rows = [];
        foreach ($arElements as $item) {
            $fields = [];
            $fieldsSecondInput = [];
            switch ($item['TYPE_INPUT']) {
                case 'select':
                    $arField = self::getSelectFields($item, $langForm);
                    $fields[] = $arField['FIRST_INPUT'];

                    if (!empty($arField['SECOND_INPUT'])) {
                        $fieldsSecondInput = $arField['SECOND_INPUT'];
                    }
                    break;

                case 'shopping_center':
                    $fields = self::getShoppingCentersCheckboxFields($countryId, $item['ID'] . '-' . $item['CODE']);
                    break;

                case 'date_years':
                    $fields[] = self::getSelectorYearsList($item);
                    break;

                case 'date_registration':
                    $fields = self::getSelectorDateRegistrationList($item, $langForm);
                    break;

                case 'radio':
                case 'checkbox':
                    $fields = self::getCheckboxOrRadioFields($item, $langForm);
                    break;

                default:
                    $fields[] = self::getDefaultField($item);
            }

            $secondRow = [];
            switch ($item['SECOND_INPUT_TYPE']) {
                case 'number':
                case 'text':
                    $fields[] = self::getDefaultField($item, true);
                    break;

                case 'date_years':
                    $fields[] = self::getSelectorYearsList($item, true);
                    break;
                case  'select_subdir':
                    $secondRow = [
                        'label' => $item['SECOND_INPUT_LABEL_TEXT'],
                        'labelFor' => $fieldsSecondInput['name'],
                        'fields' => [$fieldsSecondInput]
                    ];
            }

            $row = [
                'label' => $item['NAME_INPUT'] ?? $item['NAME'],
                'labelFor' => $item['ID'] . '-' . $item['CODE'],
                'fields' => $fields,
                'hidden' => false,
                'id' => $item['CODE'] . '-' . $item['ID'] . '_row'
            ];

            if ((bool)$item['REQUIRED'] && in_array($item['TYPE_INPUT'], ['checkbox', 'radio', 'shopping_center'])) {
                $row['rowValidation']['atLeastOneCheckbox'] = true;
            }

            if ($item['TIP']) {
                $row['tip'] = $item['TIP'];
            }

            $rows[] = $row;

            if (!empty($secondRow)) {
                $rows[] = $secondRow;
            }
        }
        return $rows;
    }

    /**
     * @param array $item
     * @param string $langForm
     * @return array|array[]
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getSelectFields(array $item, string $langForm): array
    {
        [$options, $optionSets] = self::getOptionsAndOptionsSets($item['ANSWER_SECTIONS'], $langForm);

        $field = [
            'id' => $item['ID'] . '-' . $item['CODE'],
            'name' => $item['ID'] . '-' . $item['CODE'],
            'tag' => 'select',
            'options' => $options,
        ];

        if ($item['TEXT_BEFORE']) {
            $field['textBefore'] = $item['TEXT_BEFORE'];
        }
        if ($item['TEXT_AFTER']) {
            $field['textAfter'] = $item['TEXT_AFTER'];
        }
        if ($item['REQUIRED']) {
            $field['validation'] = ['required' => true];
        }

        if ((bool)$item['LINK_ITEM_CODE'] && $item['TYPE_LINK_ITEM'] === 'VISIBILITY') {
            $field['bind'] = [
                'bindedAttribute' => 'visibility',
                'triggersAndTargets' => [$item['LINK_ITEM_CODE'] . '-' . $item['LINK_ITEM_ID'] . '_row'],
            ];
        }

        $secondField = [];
        if ($item['SECOND_INPUT_TYPE'] === 'select_subdir') {
            $secondField = [
                'id' => $item['ID'] . '-' . $item['CODE'] . '_sub',
                'name' => $item['ID'] . '-' . $item['CODE'] . '_sub',
                'tag' => 'select',
                'validation' => $field['validation'],
                'optionSets' => $optionSets
            ];

            $field['bind'] = [
                'target' => $secondField['name'],
                'bindedAttribute' => 'option'
            ];
        }

        return ['FIRST_INPUT' => $field, 'SECOND_INPUT' => $secondField];
    }

    /**
     * @param $answerSections
     * @param string $langForm
     * @return array|array[]
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getOptionsAndOptionsSets($answerSections, string $langForm): array
    {
        $options = [];
        $optionSets = [];
        $arElementsFields = self::getElementsFieldsData($answerSections, $langForm);

        $elements = count($arElementsFields) === 1 ? $arElementsFields[$answerSections]['ITEMS'] : $arElementsFields;

        foreach ($elements as $element) {
            $options[] = $element['NAME'];

            if (!empty($element['ITEMS'])) {
                $sets = [];
                foreach ($element['ITEMS'] as $value) {
                    $sets[$value['ID']] = $value['NAME'];
                }
                $optionSets[] = $sets;
            }
        }
        return array($options, $optionSets);
    }

    /**
     * @param int $sectionId
     * @param string $langForm
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getElementsFieldsData(int $sectionId, string $langForm): array
    {
        $obResult = ElementTableElementsFields::getList([
            'runtime' => [
                new ReferenceField(
                    'SECTION',
                    SectionTableElementsFields::getEntity(),
                    [
                        '=ref.ID' => 'this.IBLOCK_SECTION_ID'
                    ]
                ),
                new ReferenceField(
                    'PARENT_SECTION',
                    SectionTableElementsFields::getEntity(),
                    [
                        '=ref.ID' => 'this.SECTION.IBLOCK_SECTION_ID'
                    ]
                ),
                new ReferenceField(
                    'SIMPLE_PROPERTIES',
                    SimpleTableElementsFields::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID'
                    ]
                ),
            ],
            'select' => [
                'ID',
                'NAME',
                'SORT',
                'IBLOCK_SECTION_ID',
                'SECTION_NAME' => 'SECTION.NAME',
                'SECTION_SORT' => 'SECTION.SORT',
                'PARENT_SECTION_ID' => 'PARENT_SECTION.ID',
                'CHECKED' => 'SIMPLE_PROPERTIES.CHECKED',
                'LANG_NAME' => 'SIMPLE_PROPERTIES.' . $langForm . '_NAME',
                'SECTION_LANG_NAME' => 'SECTION.UF_' . $langForm . '_NAME',
            ],
            'filter' => [
                'IBLOCK_ID' => ElementTableElementsFields::getIblockId(),
                [
                    'LOGIC' => 'OR',
                    'IBLOCK_SECTION_ID' => $sectionId,
                    'PARENT_SECTION_ID' => $sectionId,
                ]
            ],
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC'
            ],
        ]);

        $arElementsFields = [];

        while ($item = $obResult->fetch()) {
            if (empty($arElementsFields[$item['IBLOCK_SECTION_ID']])) {
                $arElementsFields[$item['IBLOCK_SECTION_ID']] = [
                    'ID' => $item['IBLOCK_SECTION_ID'],
                    'NAME' => $item['SECTION_LANG_NAME'] ?? $item['SECTION_NAME'],
                ];
            }
            $arElementsFields[$item['IBLOCK_SECTION_ID']]['ITEMS'][] = [
                'ID' => $item['ID'],
                'NAME' => $item['LANG_NAME'] ?? $item['NAME'],
                'CHECKED' => (bool)$item['CHECKED']
            ];
        }
        return $arElementsFields;
    }

    /**
     * @param int $countryId
     * @param string $itemCode
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getShoppingCentersCheckboxFields(int $countryId, string $itemCode): array
    {
        $obResult = ElementTableMalls::getList([
            'runtime' => [
                new ReferenceField(
                    'SIMPLE_PROPERTIES',
                    SimpleTableMalls::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID'
                    ]
                ),
            ],
            'select' => [
                'ID',
                'IBLOCK_SECTION_ID',
                'NAME',
                'SORT',
                'URL' => 'SIMPLE_PROPERTIES.URL',
            ],
            'filter' => [
                'IBLOCK_ID' => ElementTableMalls::getIblockId(),
                'IBLOCK_SECTION_ID' => $countryId,
                'ACTIVE' => 'Y',
            ],
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC'
            ],
        ]);

        $fields = [];
        while ($item = $obResult->fetch()) {
            $labelText = $item['NAME'] . " <a href='{$item['URL']}' target='_blank'>🛈</a>";
            $fields[] = [
                'tag' => 'input',
                'type' => 'checkbox',
                'id' => $itemCode . '_' . $item['ID'],
                'name' => $itemCode . '_' . $item['ID'],
                'labelText' => $labelText,
                'value' => $item['NAME'],
            ];
        }
        return $fields;
    }

    /**
     * @param array $item
     * @param bool $forSecondInput
     * @return array
     */
    private static function getSelectorYearsList(array $item, $forSecondInput = false): array
    {

        $textBefore = $forSecondInput ? $item['SECOND_INPUT_TEXT_BEFORE'] : $item['TEXT_BEFORE'];
        $textAfter = $forSecondInput ? $item['SECOND_INPUT_TEXT_AFTER'] : $item['TEXT_AFTER'];
        $code = $forSecondInput ? $item['ID'] . '-' . $item['CODE'] . '_sub' : $item['ID'] . '-' . $item['CODE'];

        $year = date('Y');
        $field = [
            'id' => $code,
            'name' => $code,
            'tag' => 'select',
            'options' => [
                $year => $year,
                $year + 1 => $year + 1,
                $year + 2 => $year + 2,
                $year + 3 => $year + 3,
                $year + 4 => $year + 4,
            ],
            'validation' => ['required' => isset($item['REQUIRED'])]
        ];

        if ($textBefore) {
            $field['textBefore'] = $textBefore;
        }
        if ($textAfter) {
            $field['textAfter'] = $textAfter;
        }
        if ($item['REQUIRED']) {
            $field['validation'] = ['required' => true];
        }

        return $field;
    }

    /**
     * @param array $item
     * @param string $langForm
     * @return array
     */
    private static function getSelectorDateRegistrationList(array $item, string $langForm): array
    {
        $yearsList = range(date('Y'), 1980);
        $daysList = range(1, 31);
        $validation = ['required' => (bool)$item['REQUIRED']];

        return [
            0 => [
                'id' => $item['CODE'] . '_day',
                'name' => $item['CODE'] . '_day',
                'tag' => 'select',
                'options' => array_combine($daysList, $daysList),
                'validation' => $validation
            ],
            1 => [
                'id' => $item['CODE'] . '_month',
                'name' => $item['CODE'] . '_month',
                'tag' => 'select',
                'options' => LangHelper::getMonthsList($langForm),
                'validation' => $validation
            ],
            2 => [
                'id' => $item['CODE'] . '_year',
                'name' => $item['CODE'] . '_year',
                'tag' => 'select',
                'options' => array_combine($yearsList, $yearsList),
                'validation' => $validation
            ]
        ];
    }

    /**
     * @param array $item
     * @param string $langForm
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function getCheckboxOrRadioFields(array $item, string $langForm): array
    {
        $arElementsFields = self::getElementsFieldsData($item['ANSWER_SECTIONS'], $langForm);
        $arElements = $arElementsFields[$item['ANSWER_SECTIONS']]['ITEMS'];
        $fields = [];
        foreach ($arElements as $value) {
            $name = $item['ID'] . '-' . $item['CODE'];
            if ($item['TYPE_INPUT'] === 'checkbox') {
                $name = $item['ID'] . '-' . $item['CODE'] . '_' . $value['ID'];
            }
            $fields[] = [
                'tag' => 'input',
                'type' => $item['TYPE_INPUT'],
                'id' => $item['ID'] . '-' . $item['CODE'] . '_' . $value['ID'],
                'name' => $name,
                'labelText' => $value['NAME'],
                'checked' => $value['CHECKED'],
                'value' => $value['NAME'],
            ];
        }
        return $fields;
    }

    /**
     * @param array $item
     * @param bool $forSecondInput
     * @return array
     */
    private static function getDefaultField(array $item, $forSecondInput = false): array
    {
        $type = $forSecondInput ? $item['SECOND_INPUT_TYPE'] : $item['TYPE_INPUT'];
        $code = $forSecondInput ? $item['ID'] . '-' . $item['CODE'] . '_sub' : $item['ID'] . '-' . $item['CODE'];
        $textBefore = $forSecondInput ? $item['SECOND_INPUT_TEXT_BEFORE'] : $item['TEXT_BEFORE'];
        $textAfter = $forSecondInput ? $item['SECOND_INPUT_TEXT_AFTER'] : $item['TEXT_AFTER'];

        $field = [
            'tag' => $type === 'textarea' ? 'textarea' : 'input',
            'id' => $code,
            'name' => $code,
        ];

        if ($type !== 'textarea') {
            $field['type'] = $type;
        }
        if ($item['LABEL_TEXT']) {
            $field['labelText'] = $item['LABEL_TEXT'];
        }
        if ($item['YEARS_FOR_TURNOVER']) {
            $year = $forSecondInput ? date('Y') - 1 : date('Y') - 2;
            $textBefore .= " $year";
        }
        if ($textBefore) {
            $field['textBefore'] = $textBefore;
        }
        if ($textAfter) {
            $field['textAfter'] = $textAfter;
        }
        if ($item['REQUIRED']) {
            $field['validation'] = ['required' => true];
        }
        if ($type === 'file') {
            $field['id'] .= '_file';
            $field['validation']['fileSize'] = 15000;
            $field['validation']['fileTypes'] = '.jpg, .jpeg, .pdf, .png, .doc, .docx, .pages, .ppt, .pptx, .key';
        }

        if ((bool)$item['LINK_ITEM_CODE'] && $item['TYPE_LINK_ITEM'] === 'LINK_VALUE') {
            $field['bind'] = [
                'target' => $item['LINK_ITEM_ID'] . '-' . $item['LINK_ITEM_CODE'],
                'bindedAttribute' => 'value',
                'triggerText' => $item['LABEL_TEXT_LINK_ITEM'],
            ];
        }
        return $field;
    }

    /**
     * @param string $langForm
     * @param string $nameTheme
     * @return array
     */
    private static function getHiddenFieldAndCaptcha(string $langForm, string $nameTheme): array
    {
        global $APPLICATION;
        $captchaSid = $APPLICATION->CaptchaGetCode();
        $captchaImg = (\CAllMain::IsHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/bitrix/tools/captcha.php?captcha_sid=' . $captchaSid;
        return [
            'accordion' => false,
            'rows' => [
                0 => [
                    'hidden' => true,
                    'fields' => [
                        0 => [
                            'tag' => 'input',
                            'name' => 'langForm',
                            'type' => 'text',
                            'value' => $langForm
                        ],
                        1 => [
                            'tag' => 'input',
                            'name' => 'nameTheme',
                            'type' => 'text',
                            'value' => $nameTheme
                        ],
                        2 => [
                            'tag' => 'input',
                            'name' => 'captchaSid',
                            'type' => 'text',
                            'value' => $captchaSid
                        ]
                    ]
                ],
                1 => [
                    'label' => LangHelper::getCaptchaLabel($langForm),
                    'labelFor' => 'captcha',
                    'fields' => [
                        [
                            'id' => 'captcha',
                            'name' => 'captcha',
                            'tag' => 'input',
                            'type' => 'text',
                            'captchaImage' => $captchaImg,
                            'validation' => [
                                'required' => true,
                                'captcha' => true,
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}