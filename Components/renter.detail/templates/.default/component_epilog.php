<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$APPLICATION->AddChainItem($arResult['NAME']);

if ($arResult['TYPE_CODE'] == 'store' || $arResult['TYPE_CODE'] == 'shop') {
    $title = "Магазин {$arResult['NAME']} в Custom {$arParams['MALL_NAME']}";
    $description = "Магазин {$arResult['NAME']} в Custom {$arParams['MALL_NAME']}. График работы, описание и действующие акции.";
} elseif ($arResult['TYPE_CODE'] == 'cafe') {
    $arCategories = [];
    foreach ($arResult['CATEGORIES'] as $item) {
        $arCategories[] = $item["NAME"];
    }
    $categories = implode(', ', $arCategories);
    $title = "$categories {$arResult['NAME']} в Custom {$arParams['MALL_NAME']}";
    $description = "$categories {$arResult['NAME']}. График работы и телефон. Расположение на схеме Custom {$arParams['MALL_NAME']}";
}

$APPLICATION->SetTitle($title ?? $arParams["TITLE"]);
$APPLICATION->SetPageProperty('description', $description ?? $arParams["DESCRIPTION"]);
