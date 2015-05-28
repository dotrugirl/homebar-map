<?
// определение города или адреса, который ввел пользователь в форме поиска
if (isset($_REQUEST["q"])) { // форма поиска
    $q = trim($_REQUEST["q"]);
    $arResult["REQUEST"]["QUERY"] = htmlspecialcharsex($q);
    $arResult["ZOOM_LEVEL"] = 10;
} else { // геолокация города
    $arResult["REQUEST"]["QUERY"] = htmlspecialcharsex(trim($arParams["DEFAULT_QUERY"]));
    $arResult["ZOOM_LEVEL"] = 10;
}

// подготовка параметра раздела магазинов, например, медиамаркта, на основе символьного кода
$sectionCode = '';
if (isset($_REQUEST["SECTION_CODE"])) {
    $sectionCode = htmlspecialcharsex(trim($_REQUEST["SECTION_CODE"]));
}


// проводим поиск и формируем массив магазинов вокруг пользователя
if ($arResult["REQUEST"]["QUERY"]) {

// формируем и отправляем запрос к Яндекс.Картам
    $aParams = array(
        'geocode' => $arResult["REQUEST"]["QUERY"],
        'format' => 'json',
        'results' => 1,
        //'key'       => $arParams['YANDEX_KEY']
    );

    $oResponse = json_decode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($aParams, '', '&')));

    if ($oResponse->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
//сохраняем полученные координаты
        $arResult['ELEMENT_COORDS'] = explode(' ', $oResponse->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
    }

//Выбираем элементы из инфоблока "Магазины компании"
    $arFilter = array();
    $arFilter['IBLOCK_ID'] = 21;

    if (!empty($sectionCode)) {
        $arFilter["SECTION_CODE"] = $sectionCode;
    }

    $rElements = CIBlockElement::GetList(array(), $arFilter); //array( "IBLOCK_ID" => 21 ) );

    // объявляем массив с значениями доступных значков
    // не знаю как вытащить их из базы через АПИ
    // поэтому прошиваю жестко здесь
    $availableOptionsAllActive = array(
      'SHOP_AV_SIFON' => 'drs_map_icons_sifon_active',
      'SHOP_AV_SYROP' => 'drs_map_icons_syrop_active',
      'SHOP_AV_BALON' => 'drs_map_icons_balon_active',
      'SHOP_AV_OBMEN' => 'drs_map_icons_obmen_active'
    );

    while ($oElement = $rElements->GetNextElement()) {
        $aElement = $oElement->GetFields();
        $aElement['PROPERTIES'] = $oElement->GetProperties();

//для каждого элемента проверяем расстояние до введенного адреса
        $iDistance = sqrt(($aElement['PROPERTIES']['SHOP_COORD_X']['VALUE'] - $arResult['ELEMENT_COORDS'][0]) * ($aElement['PROPERTIES']['SHOP_COORD_X']['VALUE'] - $arResult['ELEMENT_COORDS'][0]) + ($aElement['PROPERTIES']['SHOP_COORD_Y']['VALUE'] - $arResult['ELEMENT_COORDS'][1]) * ($aElement['PROPERTIES']['SHOP_COORD_Y']['VALUE'] - $arResult['ELEMENT_COORDS'][1]));

// задаем коэффициент радиуса охвата
        if ($arResult["ZOOM_LEVEL"] == 10) {
            $radius = 0.9;
        } else {
            $radius = 0.7;
        }

        if ($iDistance < $radius) { //значение 0.02 было получено экспериментальным путем, оно зависит от размера и масштаба карты
            $avaliableOptions = '';

            //$allOptions =
            //var_dump($aElement['PROPERTIES']['SHOP_AVAILABLE']);
            $availableOptionsAll = array(
                'SHOP_AV_SIFON' => 'drs_map_icons_sifon',
                'SHOP_AV_SYROP' => 'drs_map_icons_syrop',
                'SHOP_AV_BALON' => 'drs_map_icons_balon',
                'SHOP_AV_OBMEN' => 'drs_map_icons_obmen'
            );
            foreach ($aElement['PROPERTIES']['SHOP_AVAILABLE']["VALUE_XML_ID"] as $avXMLValue) {
                if (array_key_exists($avXMLValue, $availableOptionsAllActive))
                {
                    $availableOptionsAll[$avXMLValue] = $availableOptionsAllActive[$avXMLValue];
                }
                //$avaliableOptions .= $avValue . ', ';
            }

            //$avaliableOptions = sprintf('<span class=\"%s\"></span><span class=\"%s\"></span><span class=\"%s\"></span><span class=\"%s\"></span>', $availableOptionsAll['SHOP_AV_SIFON'], $availableOptionsAll['SHOP_AV_SYROP'], $avaliableOptionsAll['SHOP_AV_SYROP'], $avaliableOptionsAll['SHOP_AV_OBMEN']);

            $markerId = '';
            switch ($aElement['PROPERTIES']['SHOP_MARKER_COLOR']['VALUE_XML_ID']) {
                case 'SHOP_MA_CO_BLUE':
                    $markerId = 'Blue';
                    break;
                case 'SHOP_MA_CO_BLUEMH':
                    $markerId = 'BlueMH';
                    break;
                case 'SHOP_MA_CO_PINK':
                    $markerId = 'Pink';
                    break;
                case 'SHOP_MA_CO_REDEX':
                    $markerId = 'RedEX';
                    break;
                case 'SHOP_MA_CO_ORANGEOT':
                    $markerId = 'OrangeOT';
                    break;
                default:
                    $markerId = 'Blue';
            }

            $scheduleString = '';
            if (!empty($aElement['PROPERTIES']['SHOP_SCHEDULE']['VALUE']))
                $scheduleString = 'Режим работы: '.$aElement['PROPERTIES']['SHOP_SCHEDULE']['VALUE'];

            $sLabel = sprintf($arParams['LABEL'],
                                                CFile::GetPath($aElement['PREVIEW_PICTURE']),
                                                $aElement['NAME'],
                                                $aElement['PROPERTIES']['SHOP_ADDRESS']['VALUE'],
                                                $aElement['PROPERTIES']['SHOP_PHONE']['VALUE'],
                                                $scheduleString,
                                                $availableOptionsAll['SHOP_AV_SIFON'],
                                                'Сифоны',
                                                $availableOptionsAll['SHOP_AV_SYROP'],
                                                'Сиропы',
                                                $availableOptionsAll['SHOP_AV_BALON'],
                                                'Баллоны',
                                                $availableOptionsAll['SHOP_AV_OBMEN'],
                                                'Обмен баллонов'
            );
            $aItem = array("MARKER" => $markerId, "LABEL" => $sLabel, "COORDS" => array($aElement['PROPERTIES']['SHOP_COORD_X']['VALUE'], $aElement['PROPERTIES']['SHOP_COORD_Y']['VALUE']));

            $aItems[] = $aItem;
        }
    }
}
$arResult['ITEMS'] = json_encode($aItems);

if (!$arResult['ELEMENT_COORDS']) {
//если адрес не введен или неверный - выводим на карте точку по умолчанию
    $arResult['ELEMENT_COORDS'] = $arParams['DEFAULT_COORDS'];
}

$this->IncludeComponentTemplate();