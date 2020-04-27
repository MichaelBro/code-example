<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Custom\Main\Base\Category\ElementTable as CategoryTable;
use Custom\Main\Base\LookBook\ElementTable as LookBook;
use Custom\Main\Base\LookBook\Property\MultipleTable as LookBookMultipleTable;
use Custom\Main\Base\Mall\Property\SimpleTable as MallProperty;
use Custom\Main\Base\Customcard\ElementTable as CustomcardElementTable;
use Custom\Main\Base\Customcard\Property\SimpleTable as CustomcardSimpleTable;
use Custom\Main\Base\PointType\ElementTable as PointElementTable;
use Custom\Main\Base\Renter\ElementTable;
use Custom\Main\Base\Renter\Property\MultipleTable;
use Custom\Main\Base\Renter\Property\SimpleTable;
use Custom\Main\Base\Renter\SectionTable;
use Custom\Main\Helpers\Counters;
use Custom\Main\Helpers\Highloadblock;
use Custom\Main\Mall;

CBitrixComponent::includeComponentClass("custom:base");

class RenterDetailComponent extends CustomBaseComponent
{
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            Loader::includeModule('iblock');
            Loader::includeModule('custom.main');

            $this->getData();
            $this->getCustomcardData();

            global $CACHE_MANAGER;
            $CACHE_MANAGER->RegisterTag('iblock_id_' . ElementTable::getIblockId());

            $this->endResultCache();
        }
        $this->includeComponentTemplate($this->arResult['TEMPLATE']);
        $this->addInteraction();
    }

    private function getData()
    {
        $this->checkURL();

        $obResult = ElementTable::getList([
            'runtime' => [
                new ReferenceField(
                    'SECTION',
                    SectionTable::getEntity(),
                    [
                        '=ref.ID' => 'this.IBLOCK_SECTION_ID'
                    ]
                ),
                new ReferenceField(
                    'SIMPLE_PROPERTIES',
                    SimpleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID'
                    ]
                ),
                new ReferenceField(
                    'MALL_PROPERTIES',
                    MallProperty::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.SIMPLE_PROPERTIES.MALL_ID'
                    ]
                ),
                new ReferenceField(
                    'RENTER_PROPERTY_MULTIPLE_WORK_TIME',
                    MultipleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                        '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression(
                            '?i',
                            MultipleTable::getPropertyId('WORK_TIME')
                        )
                    ]
                ),
                new ReferenceField(
                    'RENTER_PROPERTY_MULTIPLE_PHONE',
                    MultipleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                        '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression(
                            '?i',
                            MultipleTable::getPropertyId('PHONE')
                        )
                    ]
                ),
                new ReferenceField(
                    'MULTIPLE_TABLE_BRANDS',
                    MultipleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                        '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression(
                            '?i',
                            MultipleTable::getPropertyId('BRAND_ID')
                        )
                    ]
                ),
                new ExpressionField(
                    'BRANDS',
                    'GROUP_CONCAT(DISTINCT CONCAT_WS("", %s))',
                    array(
                        'MULTIPLE_TABLE_BRANDS.VALUE'
                    )
                ),
                new ReferenceField(
                    'MULTIPLE_TABLE_SOCIAL_LINKS',
                    MultipleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                        '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression(
                            '?i',
                            MultipleTable::getPropertyId('SOCIAL_LINKS')
                        )
                    ]
                ),
                new ExpressionField(
                    'RENTER_SOCIAL_LINKS',
                    'GROUP_CONCAT(DISTINCT CONCAT_WS("", %s))',
                    array(
                        'MULTIPLE_TABLE_SOCIAL_LINKS.VALUE'
                    )
                ),
                new ReferenceField(
                    'RENTER_PROPERTY_MULTIPLE_CATEGORYS_ID',
                    MultipleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                        '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression(
                            '?i',
                            MultipleTable::getPropertyId('CATEGORYS_ID')
                        )
                    ]
                ),
                new ReferenceField(
                    'TEMPLATE',
                    PropertyEnumerationTable::getEntity(),
                    ['=this.SIMPLE_PROPERTIES.TEMPLATE' => 'ref.ID']
                ),
                new ReferenceField(
                    'CATEGORY',
                    SectionTable::getEntity(),
                    [
                        '=ref.ID' => 'this.RENTER_PROPERTY_MULTIPLE_CATEGORYS_ID.VALUE',
                        '=ref.IBLOCK_ID' => new SqlExpression(
                            '?i',
                            CategoryTable::getIblockId()
                        ),
                        '=ref.ACTIVE' => new SqlExpression(
                            '?s',
                            'Y'
                        )
                    ]
                ),
                new ExpressionField(
                    'WORK_TIME',
                    'GROUP_CONCAT(DISTINCT CONCAT_WS("",%s) SEPARATOR "#")', // SEPARATOR — позволяет разделить данные через нужный делитель, в данном случае поделю через #, так как по умолчанию "," ломает данные если график работы "Пн,Вт,Ср"
                    [
                        'RENTER_PROPERTY_MULTIPLE_WORK_TIME.VALUE',
                    ]
                ),
                new ExpressionField(
                    'PROP_PHONES',
                    'GROUP_CONCAT(DISTINCT %s)',
                    [
                        'RENTER_PROPERTY_MULTIPLE_PHONE.VALUE',
                    ]
                ),
                new ExpressionField(
                    'CATEGORIES',
                    'GROUP_CONCAT(DISTINCT CONCAT_WS(":", %s, %s, %s, %s))',
                    [
                        'CATEGORY.ID',
                        'CATEGORY.NAME',
                        'CATEGORY.CODE',
                        'CATEGORY.ACTIVE',
                    ]
                ),
                new ReferenceField(
                    'TYPE',
                    PointElementTable::getEntity(),
                    [
                        '=ref.ID' => 'this.SIMPLE_PROPERTIES.TYPE_ID'
                    ]
                ),

            ],
            'filter' => [
                'IBLOCK_ID' => ElementTable::getIblockId(),
                'ACTIVE' => 'Y',
                'XML_ID' => $this->arParams['XML_ID'],
            ],
            'select' => [
                'ID',
                'XML_ID',
                'NAME',
                'PREVIEW_PICTURE',
                'DETAIL_TEXT',
                'DETAIL_PICTURE',
                'IBLOCK_SECTION_ID',
                'IBLOCK_ID',
                'WORK_TIME',
                'CATEGORIES',
                'MALL_ID' => 'SIMPLE_PROPERTIES.MALL_ID',
                'GALLERY_ID' => 'SIMPLE_PROPERTIES.GALLERY_ID',
                'TEMPLATE_XML' => 'TEMPLATE.XML_ID',
                'DESCRIPTION' => 'SECTION.DESCRIPTION',
                'SECTION_PICTURE' => 'SECTION.PICTURE',
                'SECTION_DETAIL_PICTURE' => 'SECTION.DETAIL_PICTURE',
                'SECTION_NAME' => 'SECTION.NAME',
                'PHONES' => 'SECTION.UF_PHONES',
                'PROP_PHONES',
                'SOCIAL_LINKS' => 'SECTION.UF_SOCIAL_LINKS',
                'SITE' => 'SECTION.UF_SITE',
                'RENTER_SITE' => 'SIMPLE_PROPERTIES.URL_SITE',
                'LOGO_SVG' => 'SECTION.UF_LOGO_SVG',
                'TYPE_ID' => 'SECTION.UF_TYPE_ID',
                'SECTION_LOOK_BOOK_ID' => 'SECTION.UF_LOOK_BOOK_ID',
                'TAGS_ID' => 'SECTION.UF_TAGS_ID',
                'MALL_PHONE' => 'MALL_PROPERTIES.PHONE',
                'MALL_WORK_TIME' => 'MALL_PROPERTIES.WORK_TIME',
                'BRANDS',
                'RENTER_SOCIAL_LINKS',
                'TYPE_CODE' => 'TYPE.CODE',
            ],
        ]);
        if ($arItem = $obResult->fetch()) {
            if ($arItem['PREVIEW_PICTURE']) {
                $logo = $arItem['PREVIEW_PICTURE'];
            } elseif ($arItem['LOGO_SVG']) {
                $logo = $arItem['LOGO_SVG'];
            } else {
                $logo = $arItem['SECTION_PICTURE'];
            }

            if ($logo) {
                $logo = CFile::ResizeImageGet(
                    $logo,
                    ["width" => 200, "height" => 200],
                    BX_RESIZE_IMAGE_EXACT,
                    true
                );
            } else {
                $logo['src'] = CFile::GetPath(Option::get('custom.main', 'IMG_SHOPS_LOGO'));
            }

            if ($arItem['DETAIL_PICTURE']) {
                $imgOriginal = $arItem['DETAIL_PICTURE'];
            } else {
                $imgOriginal = $arItem['SECTION_DETAIL_PICTURE'];
            }

            if ($imgOriginal) {
                $cover = CFile::ResizeImageGet(
                    $imgOriginal,
                    ["width" => 1850, "height" => 700],
                    BX_RESIZE_IMAGE_EXACT,
                    true
                );

                $coverBig = CFile::ResizeImageGet(
                    $imgOriginal,
                    ["width" => 1850, "height" => 700],
                    BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                    true
                );

                $coverMiddle = CFile::ResizeImageGet(
                    $imgOriginal,
                    ["width" => 1850, "height" => 700],
                    BX_RESIZE_IMAGE_EXACT,
                    true
                );
            } else {
                $imgOriginal['src'] = CFile::GetPath(Option::get('custom.main', 'IMG_SHOPS_PREVIEW'));
            }

            $text = $arItem['DETAIL_TEXT'] ? $arItem['DETAIL_TEXT'] : $arItem['DESCRIPTION'];
            $name = $arItem['NAME'] ? $arItem['NAME'] : $arItem['SECTION_NAME'];
            $site = CustomBaseComponent::getUrlData($arItem["RENTER_SITE"] ?? $arItem['SITE']);
            if($arItem['PROP_PHONES']){
                $phones = explode(",", $arItem['PROP_PHONES']);
            } else {
                $phones = $arItem['PHONES'];
            }
            // если данные о времени работы арендателя есть, прогоним через функцию разделитель
            if($arItem['WORK_TIME']){
                $strWorkTime = $this->makeWorkTimeRenter($arItem['WORK_TIME']);
            }
            // получаю итоговое время, если время арендателя нет, тогда запишу время работы ТЦ
            $workTime = $strWorkTime ?? $arItem['MALL_WORK_TIME'];

            $link = [];
            if($arItem["RENTER_SOCIAL_LINKS"] !== ''){
                // соц. сети из арендателя
                $arItem["RENTER_SOCIAL_LINKS"] = explode(',', $arItem["RENTER_SOCIAL_LINKS"]);
                foreach ($arItem['RENTER_SOCIAL_LINKS'] as $item) {
                    $link[] = CustomBaseComponent::getUrlData($item);
                }
            } else {
                // соц. сети из раздела
                foreach ($arItem['SOCIAL_LINKS'] as $item) {
                    $link[] = CustomBaseComponent::getUrlData($item);
                }
            }

            if ($arItem['TAGS_ID']) {
                $tagClass = Highloadblock::getInstance('InteractionsTags')->getDataClass();
                $tagsResult = $tagClass::getList([
                    'filter' => [
                        'ID' => $arItem['TAGS_ID']
                    ]
                ]);
                $tags = $tagsResult->fetchAll();
            }
            
            $customfiends = false;
            $categories = array_map(function ($item) use (&$customfiends){
                $result = [];
                list(
                    $result['ID'],
                    $result['NAME'],
                    $result['CODE'],
                    $result['ACTIVE']) = explode(':', $item);
                $result["CODE"] = preg_replace("/\~.+/", "", $result["CODE"]);
                if ($result['CODE'] == 'member-of-bank-loyalty~' || $result['CODE'] == 'customfriends-receive~') {
                    $customfiends = true;
                }
                if(strpos($result['NAME'], '##') !== false) {
                    unset($result);
                }
                return $result;
            },
                explode(',', $arItem['CATEGORIES']));

            $count = '';
            $backLink = '';
            $arHfsType = ['pick-up', 'drop-off', 'pick-and-drop'];
            $textBackLink = $this->arParams["TITLE_FOR_FILTER"];

            if ($arItem['TYPE_CODE'] == 'store' || $arItem['TYPE_CODE'] == 'shop'){
                $count = Counters::getShopsCount($this->arParams["MALL_ID"]);
                $backLink = '/shops/';
            }elseif ($arItem['TYPE_CODE'] == 'cafe'){
                $count = Counters::getFoodCount($this->arParams["MALL_ID"]);
                $backLink = '/food/';
            }elseif ($arItem['TYPE_CODE'] == 'entertainment' || $arItem['TYPE_CODE'] == 'service'){
                $count = Counters::getServiceCount($this->arParams["MALL_ID"]);
                $backLink = '/service/';
            }elseif (in_array($arItem['TYPE_CODE'], $arHfsType)){
                $backLink ="/scheme/{$this->arParams['MALL_CODE']}/?point={$arItem["ID"]}";
                $textBackLink = 'Смотреть на схеме';
            }

            if(!$coverBig['src']) // если нет обложки арендатора (а так же обложки раздела), запишу внутрь заглушку
            {
                $coverBig['src'] = $imgOriginal['src'];
            }
            $lookBooksId = $arItem['GALLERY_ID'] ? $arItem['GALLERY_ID'] : $arItem['SECTION_LOOK_BOOK_ID'];

            $this->arResult = [
                'ID' => $arItem['ID'],
                'XML_ID' => $arItem['XML_ID'],
                'MALL_ID' => $arItem['MALL_ID'],
                'TYPE_ID' => $arItem['TYPE_ID'],
                'TYPE_CODE' => $arItem['TYPE_CODE'],
                'IBLOCK_SECTION_ID' => $arItem['IBLOCK_SECTION_ID'],
                'NAME' => $name,
                'TEXT' => $text,
                'LOGO' => $logo['src'],
                'COVER' => $cover['src'],
                'COVER_BIG' => $coverBig['src'],
                'COVER_MIDDLE' => $coverMiddle['src'],
                'PHONES' => $phones,
                'SITE' => $site,
                'WORK_TIME' => $workTime,
                'LINKS' => $link,
                'BACK_LINK' => $backLink,
                'TEXT_BACK_LINK' => $textBackLink,
                'COUNT_RENTERS' => $count,
                'CATEGORIES' => $categories,
                'GALLERY' => $this->getLookBook($lookBooksId),
                'TAGS' => $tags,
                'TEMPLATE' => $arItem['TEMPLATE_XML'],
                'BRANDS' => (!empty($arItem['BRANDS'])) ? explode(',', $arItem['BRANDS']) : false,
                'SECTION_DETAIL_PICTURE_BIG' => CFile::ResizeImageGet(
                    $arItem['SECTION_DETAIL_PICTURE'],
                    ["width" => 1850, "height" => 740],
                    BX_RESIZE_IMAGE_EXACT,
                    true
                ),
                'SECTION_DETAIL_PICTURE_MIDDLE' => CFile::ResizeImageGet(
                    $arItem['SECTION_DETAIL_PICTURE'],
                    ["width" => 1000, "height" => 400],
                    BX_RESIZE_IMAGE_EXACT,
                    true
                ),
                'CUSTOMFRIENDS' => $customfiends,
            ];
        } else {
            LocalRedirect($this->arParams['SEF_FOLDER'] . Mall::getInstance()->getMallCode() . '/');
        }
    }

    public function getCustomcardData(){
        $listCard = CustomcardElementTable::getList(array(
            'runtime' => array(
                new ReferenceField(
                    'PROPERTY_SIMPLE',
                    CustomcardSimpleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID'
                    ]
                ),
                new ReferenceField(
                    'RENTER',
                    ElementTable::getEntity(),
                    array(
                        '=ref.ID' => 'this.PROPERTY_SIMPLE.RENTER_ID'
                    )
                ),
                new ReferenceField(
                    'SECTION',
                    SectionTable::getEntity(),
                    [
                        '=ref.ID' => 'this.RENTER.IBLOCK_SECTION_ID'
                    ]
                ),
            ),
            'select' => array(
                'ID',
                'NAME',
                'RENTER_ID' => 'PROPERTY_SIMPLE.RENTER_ID',
                'ACCEPT' => 'PROPERTY_SIMPLE.ACCEPT',
                'CHARGE' => 'PROPERTY_SIMPLE.CHARGE',
                'DEBIT_RATE' => 'PROPERTY_SIMPLE.DEBIT_RATE',
                'CREDIT_RATE' => 'PROPERTY_SIMPLE.CREDIT_RATE',
                'SECTION_ID' => 'SECTION.ID'
            ),
            'filter' => array(
                '=ACTIVE' => 'Y',
                'IBLOCK_ID' => CustomcardElementTable::getIblockId(),
                '=RENTER_ID' => $this->arResult['ID'],
            ),
        ));
        if ($card = $listCard->fetch()) {
            if ($card['ACCEPT'] > 0) {
                $this->arResult['CUSTOMCARD']['ACCEPT'] = true;
            }
            if ($card['CHARGE'] > 0) {
                $this->arResult['CUSTOMCARD']['ACCRUE'] = true;
            }
            if ($card['DEBIT_RATE'] > 1) {
                $this->arResult['CUSTOMCARD']['DEBIT'] = true;
            }
            if ($card['CREDIT_RATE'] > 1) {
                $this->arResult['CUSTOMCARD']['CREDIT'] = true;
            }
        }
    }

    private function makeWorkTimeRenter($string)
    {
        $workTime = array_map(function ($item) {
            list($result) = explode('@', $item); // ставлю тут @ чтобы не ломать логику если данные прийдут в таком виде: "Пн, Вт, Чт, Суб"
            return $result;
        }, explode('#', $string)); // разделяю с помощью #, так как с помощью символа "," логика ломается
        $strWorkTime = "";
        foreach ($workTime as $time) {
            $value = unserialize($time);
            if (strlen($strWorkTime) > 0) {
                $strWorkTime .= ";";
            }
            if (strlen($value["NAME"]) > 0) {
                $strWorkTime .= $value["NAME"];
            }
            if (strlen($value["WORK_FROM"]) > 0 || strlen($value["WORK_TO"]) > 0) {
                // если есть название ставим двоеточие
                if($value["NAME"]){
                    $strWorkTime .= ": " .
                        ((strlen($value["WORK_FROM"]) > 0) ? ($value["WORK_FROM"]) : "") .
                        ((strlen($value["WORK_TO"]) > 0) ? ("-" . $value["WORK_TO"]) : "");
                } else {
                    // если названия нет, а только время, выводим просто время.
                    $strWorkTime .=
                        ((strlen($value["WORK_FROM"]) > 0) ? ($value["WORK_FROM"]) : "") .
                        ((strlen($value["WORK_TO"]) > 0) ? ("-" . $value["WORK_TO"]) : "");
                }
            }
        }
        return explode(';', $strWorkTime);
    }

    private function getLookBook($lookBooksId)
    {
        $obLookBooks = LookBook::getList([
            'runtime' => [
                new ReferenceField(
                    'PROPERTY_MULTIPLE_IMAGES',
                    LookBookMultipleTable::getEntity(),
                    [
                        '=ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                        '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression(
                            '?i',
                            LookBookMultipleTable::getPropertyId('IMAGES')
                        )
                    ]
                ),
                new ExpressionField(
                    'IMAGES',
                    'GROUP_CONCAT(DISTINCT %s)',
                    [
                        'PROPERTY_MULTIPLE_IMAGES.VALUE',
                    ]
                ),
            ],
            'filter' => [
                'IBLOCK_ID' => LookBook::getIblockId(),
                'ID' => $lookBooksId,
                'ACTIVE' => 'Y',
            ],
            'select' => [
                'ID',
                'NAME',
                'IMAGES',
            ]
        ]);
        $lookBook = [];
        while ($arLookBook = $obLookBooks->fetch()) {
            if (count($arLookBook['IMAGES']) > 0) {
                $arImages = explode(',', $arLookBook['IMAGES']);

                foreach ($arImages as $picture) {
                    $pictureResize = CFile::ResizeImageGet(
                        $picture,
                        ["width" => 1000, "height" => 1000],
                        BX_RESIZE_IMAGE_PROPORTIONAL,
                        true);
                    $lookBook[] = $pictureResize['src'] ? $pictureResize['src'] : $picture;
                }
            }
        }

        shuffle($lookBook);
        $lookBook = array_slice($lookBook, 0, 10);

        return $lookBook;
    }

    private function getSectionElement()
    {
        $elements = ElementTable::getList([
            'runtime' => [
                new ReferenceField(
                    'RENTERS',
                    ElementTable::getEntity(),
                    [
                        '=ref.IBLOCK_SECTION_ID' => 'this.IBLOCK_SECTION_ID'
                    ]
                ),
                new ReferenceField(
                    'SIMPLE_PROPERTIES_RENTERS',
                    SimpleTable::getEntity(),
                    ['=ref.IBLOCK_ELEMENT_ID' => 'this.RENTERS.ID']
                ),
            ],
            'select' => [
                'IBLOCK_SECTION_ID',
                'RENTER_ID' => 'RENTERS.ID',
                'RENTER_XML_ID' => 'RENTERS.XML_ID',
                'RENTER_MALL_ID' => 'SIMPLE_PROPERTIES_RENTERS.MALL_ID',
            ],
            'filter' => [
                'IBLOCK_ID' => ElementTable::getIblockId(),
                'XML_ID' => $this->arParams['XML_ID'],
                '=RENTERS.ACTIVE' => 'Y',
            ]
        ])->fetchAll();
        $arElements = [];
        foreach ($elements as $element) {
            $arElements[$element['RENTER_XML_ID']] = $element;
        }
        return $arElements;
    }

    private function checkUrl()
    {
        if ($this->arParams['SEF_FOLDER'] == '/Customcard/') {
            return;
        }
        $mallCode = $this->arParams['MALL_CODE'];
        $arMalls = Mall::getMalls();
        $sectionElements = $this->getSectionElement();
        $point = $sectionElements[$this->arParams['XML_ID']];
        $arSkip = ['1406', '1320'];
        if (!in_array($point['IBLOCK_SECTION_ID'], $arSkip)) {
            foreach ($sectionElements as $element) {
                if ($element['RENTER_MALL_ID'] == $this->arParams['MALL_ID']) {
                    $xmlId[$element['RENTER_XML_ID']] = $element['RENTER_XML_ID'];
                }
            }
        }
        if(array_key_exists($this->arParams['XML_ID'],$xmlId)){
            $xmlId = $this->arParams['XML_ID'];
        } else {
            $xmlId = array_shift($xmlId);
        }
        if ($xmlId != 0 && $this->arParams['XML_ID'] != $xmlId) {
            LocalRedirect($this->arParams['SEF_FOLDER'] . $xmlId . '/' . $mallCode . '/');
        } elseif (!$this->arParams['MALL_CODE']) {
            LocalRedirect($this->arParams['SEF_FOLDER'] . $this->arParams['XML_ID'] . '/' . $arMalls[$point['MALL_ID']]['CODE'] . '/');
        } elseif ($xmlId == 0) {
            if ($this->arParams['MALL_ID'] != $point['MALL_ID']) {
                LocalRedirect($this->arParams['SEF_FOLDER'] . $mallCode . '/');
            }
            @define('ERROR_404', 'Y');
            return;
        }
        if(strripos($this->request->getRequestUri(), $mallCode) === false) {
            LocalRedirect($this->request->getRequestUri() . $mallCode . '/');
        }
    }

    private function addInteraction()
    {
        $excludeTypes = [
            7268,
            7270,
            7931,
        ];
        if ($this->arResult['ID'] && !in_array($this->arResult['TYPE_ID'],$excludeTypes) ) {

            if (count($this->arResult['TAGS']) > 0) {
                foreach ($this->arResult['TAGS'] as $item) {
                    $tags[] = ['TagName' => $item['UF_NAME']];
                }
            }

            new \Custom\Main\Hybris\Interactions\TenantPageVisit(
                $this->arResult['ID'],
                $tags
            );
        }
    }
}