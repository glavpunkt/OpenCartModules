<?php

class ModelShippingGlavpunktpochta extends Model
{
    function getQuote($address)
    {
        $this->language->load('shipping/glavpunktpochta');

        if ($this->config->get('glavpunktpochta_status') == 1) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $quote_data = array();

            if (strlen($this->config->get('glavpunktpochta_home_city')) > 0) {
                $cityFrom = $this->config->get('glavpunktpochta_home_city');
            } else {
                $cityFrom = 'Санкт-Петербург';
            }

            $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm');

            if ($weight <= 0) {
                if ($this->config->get('glavpunktpochta_weight')) {
                    $weight = $this->config->get('glavpunktpochta_weight');
                } else {
                    $weight = 1;
                }
            } elseif ($weight > 20) {
                //exit("превышен допустимый вес для данной службы доставки");
                return false;
            }

            if (isset($address['city'])) {
                $cityTo = $address['city'];
            } else {
                $cityTo = 'Санкт-Петербург';
            }

            $fullAddress = $address['country'] . ', ' . $address['city'] . ', ' . $address['address_1'];

            if ($this->config->get('glavpunktpochta_tarif_edit_code') !== null) {
                $order = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
                $replace = array(" ", "<", ">", '"', "'");
                $userSettingsPochta = str_replace($order, $replace, $this->config->get('glavpunktpochta_tarif_edit_code'));
            } else {
                $userSettingsPochta = "";
            }

            // массив параметров
            $params = array(
                'address' => $fullAddress,
                'weight' => $weight,
                'price' => $this->cart->getTotal(),
            );

            // строка запроса с переданными параметрами
            $get = '?' . http_build_query($params, '', '&');
            $url = 'https://glavpunkt.ru/api/get_pochta_tarif' . $get;

            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $out = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($out, true);
            } else {
                $result = json_decode(file_get_contents($url), true);
            }

            $title_text = $this->language->get('text_title');
            $cost = 0;
            $text = '';

            if ($result['result'] == 'ok') {

                $jsCode = '<script>
              $(document).ready(function(){
                var data = [];
                  data["result"] = "' . $result["result"] . '";
                  data["cityFrom"] = "' . $cityFrom . '";
                  data["cityTo"] = "' . $cityTo . '";
                  data["paymentType"] = "cash";
                  data["price"] = ' . $this->cart->getTotal() . ';
                  data["tarif"] = ' . $result["tarifTotal"] . ';
                  data["weight"] = ' . $weight . ';
                ';

                $jsCode .= 'var tarif = data["tarif"];
                ' . $userSettingsPochta . '
                ';
                $jsCode .= '
                    $.ajax({
                        url: "' . $this->url->link("checkout/glavpunktpochta/setprice", '') . '",
                        type: "post",
                        data: {price:tarif, type:"Главпункт Почта РФ", info:"' . $fullAddress . '"},
                        dataType: "html",
                        success: function(html) {
                            $("#glavpunktpochta_price").html(tarif + " р.");
                    ';

                // если установлен симпл, нам потребуется вызов метода reloadAll(); для обновления измененных данных
                if ($this->config->get('glavpunktpochta_simple_status') == 1) {
                    $jsCode .= 'if (!firstCall) {
                                reloadAll();
                            }';
                }

                $jsCode .= '}
                    });
                    ';

                $jsCode .= '})';

                // если установлен симпл, нам потребуется передать параметр, т.к 1 вызов trigger выполнится при загрузке страницы,
                // нас будет интересовать второй, для вызова reloadAll();, иначе получим бесконечную перезагрузку
                if ($this->config->get('glavpunktpochta_simple_status') == 1) {
                    $jsCode .= '.trigger("change", ["true"]);';
                } else {
                    $jsCode .= '.trigger("change");';
                }

                $jsCode .= '
                        </script>';

                $text = $fullAddress . '</br><span id="glavpunktpochta_price">' . $cost . ' ' .
                    " р." . '</span>' . $jsCode;

                if (isset($this->session->data['gppochtarfreloaded']) && $this->session->data['gppochtarfreloaded'] == true) {
                    if (isset($this->session->data['shipping_methods'])) {
                        $title_text = $this->session->data['shipping_methods']['glavpunktpochta']['quote']['glavpunktpochta']['title'];
                        $text = '<span id="glavpunktpochta_price">' . $cost . ' ' .
                            " р." . '</span>' . $jsCode;

                        if (isset($this->session->data['shipping_methods']['glavpunktpochta']['quote']['glavpunktpochta']['cost'])) {
                            $cost = $this->session->data['shipping_methods']['glavpunktpochta']['quote']['glavpunktpochta']['cost'];
                        }

                    }
                }
            } else {

                $text = '<span class="glavpunkt_pochta_error" style="font-size:15px; color:red;">' . $result['message'] . '</span>';

            }

            $quote_data['glavpunktpochta'] = array(
                'code' => 'glavpunktpochta.glavpunktpochta',
                'title' => $title_text,
                'cost' => $cost,
                'tax_class_id' => 0,
                'text' => $text
            );

            $method_data = array(
                'code' => 'glavpunktpochta',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => $this->config->get('glavpunktpochta_sort_order'),
                'error' => false
            );
        }
        return $method_data;
    }
}