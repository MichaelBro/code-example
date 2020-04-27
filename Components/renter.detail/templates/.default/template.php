<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$mall_code = Custom\Main\Mall::getInstance()->getMallCode();?>
<section class="storeHero <?$APPLICATION->ShowProperty("EventsNotFound")?>">
    <div class="storeHeroHolder js-slider txSlider-variable js-slider-desktop">
        <div class="storeInfo">
            <div class="storeInfoImageHolder" style="background-image: url('<?= $arResult['COVER_BIG'] ?>')">
                <img src="<?= $arResult['COVER_BIG'] ?>" class="storeInfoImage" alt="">
            </div>
            <img src="<?= $arResult['LOGO'] ?>" class="storeInfoLogo" alt="">
            <article class="storeInfoDetails">
                <div class="storeInfoDetailsHolder">
                    <h1 class="storeInfoTitle"><?= $arResult['NAME'] ?></h1>
                    <? if (!empty($arResult['SITE']['URL'])):?>
                        <a href="<?= $arResult['SITE']['URL'] ?>" class="storeInfoLink"><?= $arResult['SITE']['TEXT'] ?></a>
                    <?endif;?>
                    <dl class="storeInfoContacts">
                        <? if (is_array($arResult['PHONES'])) : ?>
                            <?if(sizeof($arResult['PHONES'])>0):?>
                                <dt class="storeInfoContactTitle">Телефон</dt>
                                <? foreach ($arResult['PHONES'] as $phone): ?>
                                    <dd class="storeInfoContactOption"><a href="tel:<?= $phone ?>" class="storeInfoPhone"><?= $phone ?></a></dd>
                                <? endforeach; ?>
                            <?endif;?>
                        <? else: ?>
                            <dd class="storeInfoContactOption"><a href="tel:<?= $arResult['PHONES'] ?>" class="storeInfoPhone"><?= $arResult['PHONES'] ?></a></dd>
                        <? endif; ?>
                        <? if (is_array($arResult['WORK_TIME'])) : ?>
                            <?if(sizeof($arResult['WORK_TIME'])>0):?>
                                <dt class="storeInfoContactTitle">Время работы</dt>
                                <? foreach ($arResult['WORK_TIME'] as $time): ?>
                                    <dd class="storeInfoContactOption"><?= $time ?></dd>
                                <? endforeach; ?>
                            <?endif;?>
                        <? else: ?>
                            <dt class="storeInfoContactTitle">Время работы</dt>
                            <dd class="storeInfoContactOption"><?= $arResult['WORK_TIME'] ?></dd>
                        <? endif; ?>
                    </dl>
                    <? foreach ($arResult['LINKS'] as $link): ?>
                        <? if (strpos($link['URL'], 'vk')): ?>
                            <a href="<?= $link['URL'] ?>" class="storeInfoSocial">
                                VK
                                <svg class="storeInfoSocialIcon storeInfoSocialIcon-VK">
                                    <use xlink:href="#spt-socialVK"/>
                                </svg>
                            </a>
                        <? endif; ?>
                        <? if (strpos($link['URL'], 'facebook')): ?>
                            <a href="<?= $link['URL'] ?>" class="storeInfoSocial">
                                Facebook
                                <svg class="storeInfoSocialIcon storeInfoSocialIcon-FB">
                                    <use xlink:href="#spt-socialFB"/>
                                </svg>
                            </a>
                        <? endif; ?>
                        <? if (strpos($link['URL'], 'instagram')): ?>
                            <a href="<?= $link['URL'] ?>" class="storeInfoSocial">
                                Instagram
                                <svg class="storeInfoSocialIcon storeInfoSocialIcon-IG">
                                    <use xlink:href="#spt-socialIG"/>
                                </svg>
                            </a>
                        <? endif; ?>
                        <? if (strpos($link['URL'], 'twitter')): ?>
                            <a href="<?= $link['URL'] ?>" class="storeInfoSocial">
                                Twitter
                                <svg class="storeInfoSocialIcon storeInfoSocialIcon-TW">
                                    <use xlink:href="#spt-socialTW"/>
                                </svg>
                            </a>
                        <? endif; ?>
                        <? if (strpos($link['URL'], 'youtube')): ?>
                            <a href="<?= $link['URL'] ?>" class="storeInfoSocial">
                                Youtube
                                <svg class="storeInfoSocialIcon storeInfoSocialIcon-YT">
                                    <use xlink:href="#spt-socialYT"/>
                                </svg>
                            </a>
                        <? endif; ?>
                    <? endforeach; ?>
                    <a href="/scheme/<?=$mall_code?>/?point=<?=$arResult["ID"]?>" class="storeInfoScheme">Смотреть на схеме</a>
                    <ul class="storeInfoTags">
                        <? foreach ($arResult['CATEGORIES'] as $item): ?>
                            <li class="storeInfoTag">
                                <a href="/<?=$arParams["CATEGORY_CODE"]?>/<?=$item["CODE"]?>/<?=$mall_code?>/" class="storeInfoTagLink"><?= $item['NAME'] ?></a>
                            </li>
                        <? endforeach; ?>
                    </ul>
                    <ul class="storeParticipation">
                    <? if ($arResult['CUSTOMCARD']['ACCEPT']):?>
                        <li class="storeParticipationItem">
                            <div class="cardCustomcard">CUSTOMCARD</div>
                        </li>
                    <?endif;?>
                    <? if ($arResult['CUSTOMFRIENDS']):?>
                        <li class="storeParticipationItem">
                            <div class="cardCustomcard">CUSTOMFRIENDS</div>
                        </li>
                    <?endif;?>
                    </ul>
                 
                    <div class="storeAbout">
                        <p><?= $arResult['TEXT'] ?></p>
                    </div>
            </article>
            <a href="<?=$arResult['BACK_LINK']?>" class="storeInfoBack" data-count="<?=$arResult['COUNT_RENTERS']?>">
                <?=$arResult["TEXT_BACK_LINK"]?>
                <svg width="40" height="46" viewBox="0 0 40 46" class="storeInfoBackIcon">
                    <g mask="url(#mg-arrowMask)">
                        <use xlink:href="#mg-arrowTail" class="mg-arrowTail-solid"/>
                        <use xlink:href="#mg-arrowHead-top" class="mg-arrowHeadTop-solid mg-arrowHead-solid"/>
                        <use xlink:href="#mg-arrowHead-bottom" class="mg-arrowHeadBottom-solid mg-arrowHead-solid"/>
                    </g>
                </svg>
            </a>
        </div>
        <? $countEvents = $APPLICATION->IncludeComponent("custom:renter.offers", "", array(
            "CACHE_TIME" => $arParams['CACHE_TIME'],
            "CACHE_TYPE" => $arParams['CACHE_TYPE'],
            "RENTER_SECTION_ID" => $arResult['IBLOCK_SECTION_ID'],
            "COUNT_OFFERS" => 7,
            "MALL_ID" => $arResult["MALL_ID"],
            "DETAIL_OFFERS" => "/offers/#YEAR#/#ELEMENT_ID#/",
            "HTML_TRUCK" => "Y" // данный параметр используется для детальной страницы арендатора
        ));
        if(!$countEvents) {
            $APPLICATION->SetPageProperty("EventsNotFound", "storeHero-is-single");
        }?>
</section>
<section class="storeAbout">
    <p>
        <?= $arResult['TEXT'] ?>
    </p>
</section>
<? if (count($arResult["GALLERY"])>0): ?>
    <section class="gallery">
        <div class="slider slider-gallery">
            <div class="sliderContent js-slider js-sliderPagination">
                <? foreach ($arResult["GALLERY"] as $item): ?>
                    <div class="slide">
                        <img data-src="<?= $item ?>" alt="" class="slideContent">
                    </div>
                <? endforeach ?>
            </div>
            <a href="#" class="sliderPrev js-sliderPrev js-cursor"></a>
            <a href="#" class="sliderNext js-sliderNext js-cursor"></a>
        </div>
    </section>
<? endif ?>