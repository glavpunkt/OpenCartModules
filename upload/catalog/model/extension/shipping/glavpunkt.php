<?php

/**
 * Модель службы доставки "Пункты выдачи Главпункт" на странице оформления заказа
 *
 * Class ModelExtensionShippingGlavpunkt
 */
class ModelExtensionShippingGlavpunkt extends Model
{
    function getQuote($address)
    {

        $this->language->load('extension/shipping/glavpunkt');

        if ($this->config->get('glavpunkt_status') == 1) {
            $status = true;
        } else {
            $status = false;
        }

        if ($this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm') > 0) {
            $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm');
        } elseif ($this->config->get('glavpunktcourier_weight') > 0) {
            $weight = $this->config->get('glavpunktcourier_weight');
        } else {
            $weight = 1;
        }

        if (isset($address['city']) && $address['city'] != '') {
            $city = $address['city'];
        } elseif (isset($address['zone']) && $address['zone'] != '') {
            $city = $address['zone'];
        } elseif ($this->config->get('glavpunkt_home_city') != null) {
            $city = $this->config->get('glavpunkt_home_city');
        } else {
            $city = 'Санкт-Петербург';
        }

        if ($status) {
            // расчёт на бэке
            if ($city == 'Санкт-Петербург') {
                $deliveryType = 'выдача';
                $punktId = 'Moskovskaya-A16';// пункт по-умолчанию
                $priceUp = 60;
            } elseif ($city == 'Москва') {
                $deliveryType = 'выдача';
                $punktId = 'Msk-Novokuzneckaja-18S4';// пункт по-умолчанию
                $priceUp = 20;
            } else {
                $deliveryType = 'выдача по РФ';
                $punktId = '';
                $priceUp = 0;
            }

            if ($this->config->get('glavpunktpoints_payment_type') == 1) {
                $paymentType = 'cash';
            } else {
                $paymentType = 'prepaid';
            }

            $paramsDelivery = array(
                'serv' => $deliveryType,// тип доставки
                'cityFrom' => 'Санкт-Петербург',// город отправки заказа
                'cityTo' => $city,// город доставки заказа
                'weight' => $weight,// вес заказа
                'price' => $this->cart->getTotal(),// стоимость заказа
                'punktId' => $punktId,// id пункта получения
                'paymentType' => $paymentType// тип оплаты (из настроек службы доставки)
            );

            $res = $this->getTarif($paramsDelivery);

            if ($res['result'] == 'ok') {
                // стоимость доставки
                $cost = $res['tarif'] + $priceUp;
            } else {
                $cost = 0;
            }
            // end расчёт на бэке


            if ($this->config->get('glavpunkt_tarif_edit_code')) {
                $order = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
                $replace = array(" ", "<", ">", '"', "'");
                $userSettings = str_replace($order, $replace, $this->config->get('glavpunkt_tarif_edit_code'));
            } else {
                $userSettings = '';
            }
            $data_for_widget = "{'defaultCity': '" . $city . "'," . $this->config->get('glavpunkt_widget_data') . "}";
            $quote_text = '<a id="glavpunkt_open_map"  href="#" onclick="glavpunkt.openMap(selectPunkt,' . $data_for_widget . '); return false;" style="color: #232323; font-size: 15px; font-weight: 600;" class="custom_style_for_glavpunkt">' . $this->language->get('text_description') . '</a>';

            $quote_text .= '<script type="text/javascript">
              var script = document.createElement(\'script\');

              script.type = \'text/javascript\';
              script.src = \'https://glavpunkt.ru/js/punkts-widget/glavpunkt.js\';
              document.head.appendChild(script);

              var style = document.createElement(\'style\');
              style.type = \'text/css\';
              style.innerHTML = \'.glavpunkt_container{ z-index:2000!important; }\';
              document.head.appendChild(style);
              </script>';

            $quote_text .= '<script type="text/javascript">
            $(\'#button-shipping-method\').on(\'click\', function(e){
                if ($("input:radio[value=\'glavpunkt.glavpunkt\']").is(\':checked\')){
                  if ($(\'#glavpunkt_content\').html() == \'\'){
                    $(\'#glavpunkt_open_map\').css({display: "inline-block", padding:"3px", border: "2px solid red"});
                     return false;
                  }
                }
              }
            );

              function selectPunkt(punktInfo) { 
                $("input:radio[value=\'glavpunkt.glavpunkt\']").prop("checked", true);
                var name = punktInfo.name;
                var tarif =0;
                if (name != punktInfo.address) {
                  name += \', \' + punktInfo.address;
                }
                $(\'#selectedPunkt\').text(name);
              
                 $.getJSON( "//glavpunkt.ru/api/get_tarif", {
                \'serv\': \'выдача\',
                \'cityFrom\': \'' . $this->config->get('glavpunkt_home_city') . '\',
                \'cityTo\': punktInfo.city,
                \'punktId\': punktInfo.id,
                \'weight\': \'' . $weight . '\',
                \'price\':\'' . $this->cart->getTotal() . '\',';

            $quote_text .= "'paymentType':";

            if ($this->config->get('glavpunktpoints_payment_type') == 1) {
                $quote_text .= "'cash',";
            } else {
                $quote_text .= "'prepaid',";
            }

            $quote_text .= '}).done(function(data) {
                   if (data.result == \'ok\') {
                    tarif =  data.tarif;
                    ' . $userSettings . '
                    $.ajax({
                      url: \'' . $this->url->link('checkout/glavpunkt/setprice', '') . '\',
                      type: \'post\',
                      data: {price:tarif, type:\'Главпункт - самовывоз\', info:punktInfo.name, address:punktInfo.address, phone:punktInfo.phone, work_time:punktInfo.work_time, city_to:punktInfo.city, punkt:punktInfo},
                      dataType: \'html\',
                      success: function(html) {
                        $(\'#glavpunkt_open_map\').css({display: "inline-block", padding:"3px", border: "0"});';

            if ($this->config->get('glavpunktpoints_simple_status') == 1) {// если установлен симпл, нам потребуется вызов метода reloadAll(); для обновления измененных данных
                $quote_text .= '
                            reloadAll();
                            ';
            }else{
                $quote_text .= "
                            $('#glavpunkt_content').html(html);
                            ";
            }
            $quote_text .= '}
                    });
                  } else if (data.result == \'error\') {
                    console.log(\'Ошибка подсчета тарифа\', data.message);
                  } else {
                    console.log(\'Ошибка подсчета тарифа (неверный ответ от сервера)\', data);
                  }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus);
                });
              }
             </script> 

             <br><span id="glavpunkt_content"></span>';


            if (isset($this->session->data['pointsreloaded']) && $this->session->data['pointsreloaded'] == true) {
                if (isset($this->session->data['shipping_methods'])) {
                    $title_text = $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['title'];
                }

                if (isset($this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['cost'])) {
                    $cost = $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['cost'];
                }
            } else {
                $title_text = $this->language->get('text_title');
            }

            if (!isset($title_text)) {
                $title_text = '';
            }

            $quote_data['glavpunkt'] = array(
                'code' => 'glavpunkt.glavpunkt',
                'title' => $title_text,
                'text' => $quote_text,
                'description' => '',
                'cost' => $cost,
                'tax_class_id' => 0,
            );

            $method_data = array(
                'code' => 'glavpunkt',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => $this->config->get('glavpunkt_sort_order'),
                'error' => false
            );
        }

        return $method_data;
    }

    /**
     * Формируем запрос на стоимость расчёта доставки к API Главпункта, ответ - json-массив
     *
     * @param array $params массив с переданными параметрами из метода calculateConcrete()
     *
     * @return array ['result' => корректен ли ответ на запрос, 'serv' => тип доставки, 'tarif' => тариф за доставку,
     * 'cityFrom' => город отправки заказа, 'cityTo' => город доставки заказа, 'price' => стоимость заказа,
     * 'weight' => вес заказа, 'paymentType' => тип оплаты, 'period' => срок доставки груза]
     *
     */
    private function getTarif($params)
    {
        $get = '?' . http_build_query($params, '', '&');// строка запроса с переданными параметрами
        // строка запроса с замененными пробелами (после http_build_query = '+') на %20, для версий php < 5.3
        $query = str_replace('+', '%20', $get);
        $url = 'https://glavpunkt.ru/api/get_tarif' . $query;// ссылка для запроса
        $answer = $this->request($url);// получаем содержимое страницы запроса

        return $answer;
    }

    /**
     * Проверяем подключен ли Curl, отключен ли file_get_contents
     *
     * @param string urlPage урл страницы
     *
     */
    private function request($urlPage)
    {
        if (ini_get('allow_url_fopen')) {
            return $this->requestByFileGetContents($urlPage); // отправка запроса через file_get_contents
        } elseif (function_exists('curl_version')) {
            return $this->requestByCurl($urlPage);// отправка запроса курлом
        } else {
            // не удалось получить тариф, следует проверить настройки расширения curl
            // или разрешить file_get_contents с помощью директивы allow_url_fopen = 1
            return 'DELIVERY_ERROR_CURL_FGC';
        }
    }

    /**
     * Получаем страницу ответа API с помощью file_get_contents
     *
     * @param string urlPagebyFGC урл страницы
     *
     */
    private function requestByFileGetContents($urlPageByFGC)
    {
        $page = json_decode(file_get_contents($urlPageByFGC), true);

        return $page;
    }

    /**
     * Получаем страницу ответа API с помощью Curl
     *
     * @param string urlPagebyCurl урл страницы
     *
     */
    private function requestByCurl($urlPagebyCurl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlPagebyCurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $responce = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            return false;
        } else {
            $pageCurl = json_decode($responce, true);

            return $pageCurl;
        }
    }
}
