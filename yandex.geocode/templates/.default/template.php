<div class="dd-search-form">
    <h1>Карта магазинов</h1>
    <form method="" action="">
        <input class="bx-auth-input" type="text" value="<? if( $arResult["REQUEST"]["QUERY"] ): ?><?=$arResult["REQUEST"]["QUERY"]?><? endif; ?>" placeholder="Введите свой адрес..." class="search-input" name="q">
        <input type="submit" value="Найти ближайшие магазины" class="search-submit btn btn-primary">
    </form>
</div>

<div class="shop-networks">
    <a href="/shops/?<? if( $arResult["REQUEST"]["QUERY"] ): ?>q=<?=$arResult["REQUEST"]["QUERY"]?>&<? endif; ?>SECTION_CODE=mediamarkt" id="show-mediamarkt" title="Показать ближайшие магазины МедиаМаркт, в которых можно приобрести продукцию HomeBar"><img src="<?=SITE_TEMPLATE_PATH?>/images/shop-map-mediamarkt.png" width="196" height="66" alt="Показать ближайшие магазины МедиаМаркт, в которых можно приобрести продукцию HomeBar" /></a>
    <a href="/shops/?<? if( $arResult["REQUEST"]["QUERY"] ): ?>q=<?=$arResult["REQUEST"]["QUERY"]?>&<? endif; ?>SECTION_CODE=elex" id="show-elex" title="Показать ближайшие магазины сети Элекс, в которых можно приобрести продукцию HomeBar"><img src="<?=SITE_TEMPLATE_PATH?>/images/shop-map-elex.png" width="196" height="66" alt="Показать ближайшие магазины сети Элекс, в которых можно приобрести продукцию HomeBar" /></a>
    <a href="/shops/?<? if( $arResult["REQUEST"]["QUERY"] ): ?>q=<?=$arResult["REQUEST"]["QUERY"]?>&<? endif; ?>SECTION_CODE=onlinetrade" id="show-onlinetrde" title="Показать ближайшие магазины сети ОНЛАЙН ТРЕЙД.РУ, в которых можно приобрести продукцию HomeBar"><img src="<?=SITE_TEMPLATE_PATH?>/images/shop-map-onlinetrade.png" width="196" height="66" alt="Показать ближайшие магазины сети ОНЛАЙН ТРЕЙД.РУ, в которых можно приобрести продукцию HomeBar" /></a>
</div>

<script type="text/javascript" src="http://api-maps.yandex.ru/1.1/index.xml"></script>
 
<script type="text/javascript">// <![CDATA[
    var decodeEntities = (function() {
        var element = document.createElement('div');

        function decodeHTMLEntities (str) {
            if(str && typeof str === 'string') {
                str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
                str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
                element.innerHTML = str;
                str = element.textContent;
                element.textContent = '';
            }

            return str;
        }

        return decodeHTMLEntities;
    })();

    window.onload = function () {
        var map = new YMaps.Map(document.getElementById("YMapsID"));
        map.setCenter(new YMaps.GeoPoint(<?=$arResult['ELEMENT_COORDS'][0]?>, <?=$arResult['ELEMENT_COORDS'][1]?>), <?=$arResult["ZOOM_LEVEL"]?>);

        map.addControl(new YMaps.Zoom());
        map.addControl(new YMaps.ToolBar());

        YMaps.Styles.add("constructor#pmlbl1Placemark", {
            iconStyle : {
                href : '<?=SITE_TEMPLATE_PATH?>/images/map_pointer_v2.png',
                size : new YMaps.Point(30, 37),
                offset: new YMaps.Point(-15, -35)
            }
        });

        YMaps.Styles.add("constructor#pmlbl1PlacemarkBlue", {
            iconStyle : {
                href : '<?=SITE_TEMPLATE_PATH?>/images/map_pointer_blue_v2.png',
                size : new YMaps.Point(30, 37),
                offset: new YMaps.Point(-15, -35)
            }
        });

        YMaps.Styles.add("constructor#pmlbl1PlacemarkBlueMH", {
            iconStyle : {
                href : '<?=SITE_TEMPLATE_PATH?>/images/map_pointer_blue_mh_v2.png',
                size : new YMaps.Point(30, 37),
                offset: new YMaps.Point(-15, -35)
            }
        });

        YMaps.Styles.add("constructor#pmlbl1PlacemarkPink", {
            iconStyle : {
                href : '<?=SITE_TEMPLATE_PATH?>/images/map_pointer_pink_v2.png',
                size : new YMaps.Point(30, 37),
                offset: new YMaps.Point(-15, -35)
            }
        });

        YMaps.Styles.add("constructor#pmlbl1PlacemarkRedEX", {
            iconStyle : {
                href : '<?=SITE_TEMPLATE_PATH?>/images/map_pointer_red_elex_v2.png',
                size : new YMaps.Point(30, 37),
                offset: new YMaps.Point(-15, -35)
            }
        });

        YMaps.Styles.add("constructor#pmlbl1PlacemarkOrangeOT", {
            iconStyle : {
                href : '<?=SITE_TEMPLATE_PATH?>/images/map_pointer_orange_onlinetrade_v2.png',
                size : new YMaps.Point(30, 37),
                offset: new YMaps.Point(-15, -35)
            }
        });

        map.addOverlay(
            createObject(
                "Placemark",
                new YMaps.GeoPoint(<?=$arResult['ELEMENT_COORDS'][0]?>, <?=$arResult['ELEMENT_COORDS'][1]?>),
                "constructor#pmlbl1Placemark",
                "Ваш адрес: <?=$arResult["REQUEST"]["QUERY"]?>"
            )
        );

        var oItems = eval('(' + '<?=$arResult['ITEMS']?>' + ')');
        for (i = 0; i < oItems.length; i++) {
            var oItem = oItems[i];
            console.log(oItem);
            map.addOverlay(
                createObject(
                    "Placemark",
                    new YMaps.GeoPoint( oItem.COORDS[0], oItem.COORDS[1] ),
                    "constructor#pmlbl1Placemark" + oItem.MARKER,
                    decodeEntities( oItem.LABEL )
                )
            );
        }

        function createObject (type, point, style, description) {
            var allowObjects = ["Placemark", "Polyline", "Polygon"],
                index = YMaps.jQuery.inArray( type, allowObjects),
                constructor = allowObjects[(index == -1) ? 0 : index];
            description = description || "";

            var object = new YMaps[constructor](point, {style: style, hasBalloon : !!description});
            object.description = description;

            return object;
        }
    }
    // ]]></script>
 
<div id="YMapsID" style="border: 3px solid #ccc; width: 820px; height: 450px;"> </div>