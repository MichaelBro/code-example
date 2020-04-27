<?php

namespace Custom\Main\Helpers;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ReferenceField;
use ColorThief\ColorThief;
use Custom\Main\Base\Menu\ElementTable;
use Custom\Main\Base\Menu\Property\MultipleTable;
use Custom\Main\Base\Menu\Property\SimpleTable;
use Bitrix\Main\Data\Cache;
use Custom\Main\Mall;

class Colors
{
    public static $arColors = [
        0 => [
            'COLOR' => 'Light Beige',
            'VALUE' => '#dacdc4',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        1 => [
            'COLOR' => 'Beige',
            'VALUE' => '#c2ad9f',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        2 => [
            'COLOR' => 'Light Ochre',
            'VALUE' => '#edc782',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        3 => [
            'COLOR' => 'Ochre',
            'VALUE' => '#dea224',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        4 => [
            'COLOR' => 'Light Yellow',
            'VALUE' => '#f6e07f',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        5 => [
            'COLOR' => 'Yellow',
            'VALUE' => '#edcd00',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        6 => [
            'COLOR' => 'Light Rose',
            'VALUE' => '#f9c5b4',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        7 => [
            'COLOR' => 'Rose',
            'VALUE' => '#f39983',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        8 => [
            'COLOR' => 'Light Orange',
            'VALUE' => '#f7b082',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        9 => [
            'COLOR' => 'Orange',
            'VALUE' => '#ee7330',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        10 => [
            'COLOR' => 'Light Red',
            'VALUE' => '#ef9e94',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        11 => [
            'COLOR' => 'Red',
            'VALUE' => '#e25054',
            'FLASH' => [
                'SMALL' => '#e25054',
                'BIG' => '#1374a1'
            ]
        ],
        12 => [
            'COLOR' => 'Light Purple',
            'VALUE' => '#ce91aa',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        13 => [
            'COLOR' => 'Purple',
            'VALUE' => '#b14274',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        14 => [
            'COLOR' => 'Light Smoke Blue',
            'VALUE' => '#c5d3e0',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        15 => [
            'COLOR' => 'Smoke Blue',
            'VALUE' => '#99b7cc',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        16 => [
            'COLOR' => 'Light Blue',
            'VALUE' => '#bdd6ed',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        17 => [
            'COLOR' => 'Blue',
            'VALUE' => '#88bbdf',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        18 => [
            'COLOR' => 'Light Green',
            'VALUE' => '#b9ddc9',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        19 => [
            'COLOR' => 'Green',
            'VALUE' => '#7dc4a3',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        20 => [
            'COLOR' => 'Light Irish Green',
            'VALUE' => '#9fbbb3',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
        21 => [
            'COLOR' => 'Irish Green',
            'VALUE' => '#539286',
            'FLASH' => [
                'SMALL' => '#edcd00',
                'BIG' => '#e25054'
            ]
        ],
    ];

    /** этот синоним массив по гайду дизайнеров, для установки цветов неиспользовать*/
    public static $arColorsDark = [
        0 => [
            'COLOR' => 'Dark Beige',
            'VALUE' => '#817060',
            'ALIAS' => '#c2ad9f',
        ],
        1 => [
            'COLOR' => 'Dark Ochre',
            'VALUE' => '#9f791c',
            'ALIAS' => '#dea224',
        ],
        2 => [
            'COLOR' => 'Dark Yellow',
            'VALUE' => '#b39300',
            'ALIAS' => '#edcd00',
        ],
        3 => [
            'COLOR' => 'Dark Rose',
            'VALUE' => '#d77969',
            'ALIAS' => '#f39983',
        ],
        4 => [
            'COLOR' => 'Dark Orange',
            'VALUE' => '#ad4e07',
            'ALIAS' => '#ee7330',
        ],
        5 => [
            'COLOR' => 'Dark Red',
            'VALUE' => '#b81e27',
            'ALIAS' => '#e25054',
        ],
        6 => [
            'COLOR' => 'Dark Purple',
            'VALUE' => '#760b48',
            'ALIAS' => '#b14274',
        ],
        7 => [
            'COLOR' => 'Dark Smoke Blue',
            'VALUE' => '#4583ad',
            'ALIAS' => '#99b7cc',
        ],
        8 => [
            'COLOR' => 'Dark Blue',
            'VALUE' => '#1374a1',
            'ALIAS' => '#88bbdf',
        ],
        9 => [
            'COLOR' => 'Dark Green',
            'VALUE' => '#18a06b',
            'ALIAS' => '#7dc4a3',
        ],
        10 => [
            'COLOR' => 'Dark Irish Green',
            'VALUE' => '#006553',
            'ALIAS' => '#539286',
        ],
    ];

    public static function getRandomColor()
    {
        return self::$arColors[rand(0, 21)]['VALUE'];
    }

    /**
     * Получение массива цвета по hex цвету
     *
     * @param [string] $hexColor
     * @return array
     */
    public static function getArColorByHex($hexColor)
    {
        foreach (self::$arColors as $key => $color) {
            if ($hexColor === $color['VALUE']) {
                return self::$arColors[$key];
            }
        }
        return null;
    }

    /**
     * Получение цвета вспышки для анимации банера
     *
     * @param [string] $hexColor
     * @return array
     */
    public static function getFlashColor($hexColor)
    {
        $arColor = self::getArColorByHex($hexColor);
        return $arColor['FLASH'];
    }

    public static function getMenuColor($link)
    {
        global $CACHE_MANAGER;
        $mallId = Mall::getInstance()->getMallId();
        $cache = Cache::createInstance();
        $cacheId = $link . $mallId;
        $cacheTime = CACHE_TIME ?: 3600;
        $cacheDir = '/modules/custom.main/helpers/menu';
        if ($cache->InitCache($cacheTime, $cacheId, $cacheDir)) {
            $color["COLOR_XML"] = $cache->getVars();
        } else {
            $colorMenu = ElementTable::getList(array(
                'runtime' => array(
                    new ReferenceField(
                        'PROPERTY_SIMPLE',
                        SimpleTable::getEntity(),
                        [
                            '=ref.IBLOCK_ELEMENT_ID' => 'this.ID'
                        ]
                    ),
                    new ReferenceField(
                        'COLOR',
                        PropertyEnumerationTable::getEntity(),
                        ['=this.PROPERTY_SIMPLE.COLOR' => 'ref.ID']
                    ),
                    new ReferenceField(
                        'PROPERTY_MULTIPLE_MALL_ID',
                        MultipleTable::getEntity(),
                        array(
                            '=ref.IBLOCK_ELEMENT_ID' => 'this.ID',
                            '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression(
                                '?i',
                                MultipleTable::getPropertyId('MALL_ID')
                            )
                        )
                    ),
                    new ReferenceField(
                        'MALL',
                        \Custom\Main\Base\Mall\ElementTable::getEntity(),
                        array(
                            '=ref.ID' => 'this.PROPERTY_MULTIPLE_MALL_ID.VALUE'
                        )
                    ),
                ),
                'select' => array(
                    'ID',
                    'NAME',
                    'LINK' => 'PROPERTY_SIMPLE.LINK',
                    'COLOR_XML' => 'COLOR.XML_ID',
                ),
                'filter' => array(
                    '=ACTIVE' => 'Y',
                    '=LINK' => $link,
                    'IBLOCK_ID' => ElementTable::getIblockId(),
                    '=MALL.ID' => \Custom\Main\Mall::getInstance()->getMallId(),
                ),
            ));
            $color = $colorMenu->fetch();
            if (!$color["COLOR_XML"]) $color["COLOR_XML"] = self::getRandomColor();

            $CACHE_MANAGER->StartTagCache($cacheDir);
            $CACHE_MANAGER->RegisterTag('iblock_id_' . ElementTable::getIblockId());
            $CACHE_MANAGER->EndTagCache();
            $cache->startDataCache($cacheTime, $cacheId);
            $cache->EndDataCache($color["COLOR_XML"]);
        }

        return $color["COLOR_XML"];

    }

    public static function getBackgroundColorForImg($imgPath)
    {
        /**
         *  composer require ksubileau/color-thief-php
         *  Need update class ColorThief
         *
         * const SIGBITS = 5;
         * const RSHIFT = 3;
         * const MAX_ITERATIONS = 1500;
         * const FRACT_BY_POPULATIONS = 0.75;
         * const THRESHOLD_ALPHA = 62;
         * const THRESHOLD_WHITE = 245;
         */

        $arColorsHexPrimary = self::$arColors;
        $arColorsRgbPrimary = [];
        foreach ($arColorsHexPrimary as $colorHex) {
            $forPrintColor[] = $colorHex["VALUE"];
            list($r, $g, $b) = sscanf($colorHex["VALUE"], "#%02x%02x%02x");
            $arColorsRgbPrimary[$colorHex["VALUE"]] = [$r, $g, $b];
        }

        $arColorsHexSecondary = self::$arColorsDark;
        $arColorsRgbSecondary = [];
        foreach ($arColorsHexSecondary as $colorHex) {
            $forPrintColor[] = $colorHex["VALUE"];
            list($r, $g, $b) = sscanf($colorHex["VALUE"], "#%02x%02x%02x");
            // $arColorsDark синоним массив поэтому Alias цвета используем
            $arColorsRgbSecondary[$colorHex["ALIAS"]] = [$r, $g, $b];
        }

        $colorBeforeFilter = ColorThief::getColor($imgPath);
        $brightness = 20;
        if (max($colorBeforeFilter) < 70) {
            $brightness = 50;
        } elseif (max($colorBeforeFilter) < 90) {
            $brightness = 40;
        } elseif (max($colorBeforeFilter) < 110) {
            $brightness = 30;
        }

        $colorAfterFilter = self::getFilteredColor($imgPath, $brightness);

        $nearColors = [
            "PRIME_BEFORE_FILTER" => self::GetNearColorDistance($arColorsRgbPrimary, $colorBeforeFilter),
            "SECOND_BEFORE_FILTER" => self::GetNearColorDistance($arColorsRgbSecondary, $colorBeforeFilter),
            "PRIME_AFTER_FILTER" => self::GetNearColorDistance($arColorsRgbPrimary, $colorAfterFilter),
            "SECOND_AFTER_FILTER" => self::GetNearColorDistance($arColorsRgbSecondary, $colorAfterFilter),
        ];
        usort($nearColors, function ($a, $b) {
            return ($a["DISTANCE"] <=> $b["DISTANCE"]);
        });
        return $nearColors[0]['COLOR'];
    }

    private static function GetNearColorDistance($arColorsRgb, $palette)
    {
        $arColorsDistance = [];
        foreach ($arColorsRgb as $key => $color) {
            /** популярное преобразование из RGB в оттенки серого в 30% красного + 59% зеленого + 11% синего. */
            $distance = (30 * ($color[0] - $palette[0])) ** 2 + (59 * ($color[1] - $palette[1])) ** 2 + (11 * ($color[2] - $palette[2])) ** 2;
            $arColorsDistance[$key] = [
                "COLOR" => $key,
                "DISTANCE" => $distance,
            ];
        }
        usort($arColorsDistance, function ($a, $b) {
            return ($a["DISTANCE"] <=> $b["DISTANCE"]);
        });
        return $arColorsDistance[0];
    }

    private static function getFilteredColor($imgPath, $brightness)
    {
        $file = \CFile::MakeFileArray($imgPath);
        $im = '';

        if ($file["type"] === "image/jpeg"){
            $im = imagecreatefromjpeg($imgPath);
        } elseif ($file["type"] === "image/png"){
            $im = imagecreatefrompng($imgPath);
        }

        imagefilter($im, IMG_FILTER_BRIGHTNESS, $brightness);
        imagetruecolortopalette($im, false, 127);
        imagefilter($im, IMG_FILTER_PIXELATE, 25);

        $tmpImgPath = "/upload/tmpImage-" . uniqid() . ".jpg";

        imagejpeg($im, $_SERVER['DOCUMENT_ROOT'] . $tmpImgPath);
        imagedestroy($im);

        $color = ColorThief::getColor($_SERVER['DOCUMENT_ROOT'] . $tmpImgPath);
        unlink($_SERVER['DOCUMENT_ROOT'] . $tmpImgPath);
        return $color;
    }
}
