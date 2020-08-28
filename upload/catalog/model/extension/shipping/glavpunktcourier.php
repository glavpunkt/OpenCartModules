<?php

/**
 * Модель модуля доставки "Курьерская доставка Главпункт" на странице оформления заказа
 *
 * Отвчает за вывод всех полей и получении цены
 *
 * Class ControllerCheckoutGlavpunktCourier
 * @author SergeChepikov
 */
class ModelExtensionShippingGlavpunktcourier extends Model
{
    function getQuote($address)
    {
        $this->language->load('extension/shipping/glavpunktcourier');

        if ($this->config->get('shipping_glavpunktcourier_status') == 1) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $quote_data = array();

            $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm');
            $cityFrom = $this->config->get('shipping_glavpunktcourier_home_city');

            if ($weight <= 0) {
                if ($this->config->get('shipping_glavpunktcourier_weight')) {
                    $weight = $this->config->get('shipping_glavpunktcourier_weight');
                } else {
                    $weight = 1;
                }
            } elseif ($weight > 20) {
                //exit("превышен допустимый вес для данной службы доставки");
                return false;
            }

            $courierDays = intval($this->config->get('shipping_glavpunktcourier_days'));
            if (!$courierDays) {
                $date = date('Y-m-d', strtotime(' + 1 day'));
            } else {
                $date = date('Y-m-d', strtotime(' + ' . $courierDays . ' day'));
            }

            $strToDate = strtotime($date);
            if (date('w', $strToDate) == 0) {
                $date = date('Y-m-d', strtotime($date . ' + 1 weekdays'));
            }

            if (isset($this->session->data['selected_city'])) {
                $cityTo = $this->session->data['selected_city'];
            } else if ($address['city'] != '') {
                $cityTo = $address['city'];// из профиля юзера
            } else {
                $cityTo = 'Санкт-Петербург';
            }

            if (null !== $this->config->get('shipping_glavpunktcourier_tarif_edit_code')) {
                $order = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
                $replace = array(" ", "<", ">", '"', "'");
                $userSettingsCourier = str_replace($order, $replace, $this->config->get('shipping_glavpunktcourier_tarif_edit_code'));
            }

            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, 'https://glavpunkt.ru/api/get_courier_cities');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $out = curl_exec($curl);
                curl_close($curl);
                $answerGetCities = json_decode($out, true);
            } else {
                $answerGetCities = json_decode(file_get_contents('https://glavpunkt.ru/api/get_courier_cities'), true);
            }

            $selectCities = '<select style="padding: 4px 5px;" class="glavpunkt-courier" id="courierDeliveryGlavpunkt">';

            foreach ($answerGetCities as $key => $value) {
                if (mb_strtolower($value['name']) == mb_strtolower($cityTo)) {
                    $selectCities .= '<option value="' . $value['name'] . '" selected data-price="">' . $value['name'] . '</option>';
                } else {
                    $selectCities .= '<option value="' . $value['name'] . '" data-price="">' . $value['name'] . '</option>';
                }
            }
            $selectCities .= '</select>';

            if (isset($this->session->data['reloaded']) && $this->session->data['reloaded'] == true) {
                $selectCities .= '<script> $(function(){$(\'#courierDeliveryGlavpunkt\').siblings("input").prop( "checked", true);});</script>';
            }
            $selectCities .= '<script>
        function serCourierPriceWithFix(price, city){
            var data = {
                "Санкт-Петербург": "' . $this->config->get('shipping_glavpunktcourier_price_spb') . '",                    
                "Москва": "' . $this->config->get('shipping_glavpunktcourier_price_msk') . '"
            };
            if ( data[city] ){
                return data[city];
            }else{
                return price;
            }
        }

        $(function(){
          $(\'.glavpunkt-courier\').on(\'change\', function(e, firstload){
            var itemsPrice = ' . $this->cart->getTotal() . ';
            var city_obl = $(e.currentTarget); 
            var cityFrom = "' . $cityFrom . '";
            var weight = ' . $weight . ';
            var selectedCity = $(".glavpunkt-courier option:checked").html();            
             $.ajax({
              url: "https://glavpunkt.ru/api/get_tarif",
              type: "GET",
              data: {serv:"курьерская доставка", cityFrom:cityFrom, cityTo:selectedCity, weight:weight, price:itemsPrice, cms: "opencart-3"},
              dataType: "json",
              success: function(data){             
              var tarif = serCourierPriceWithFix(data["tarif"], selectedCity);

                ' . $userSettingsCourier . '
                //$("#glavpunktcourier_price").html(tarif + " р.");
                
                let priceCont = document.querySelectorAll(".glavpunktcourier_price");
                
                for (let i = 0; i < priceCont.length; i++) {
                    priceCont[i].innerHTML = tarif + " р.";
                }
                
                  $.ajax({
                    url: "' . $this->url->link("checkout/glavpunktcourier/setprice", '') . '",
                    type: "post",
                    data: {price:tarif, type:\'Курьерская доставка Главпункт\', info:city_obl.val()},
                    dataType: \'html\',
                    success: function(html) {
                     if (true !== firstload) {
                       if (typeof reloadAll === "function") {
                         // Проверка, что reloadAll существует (режим модуля simple)
                         reloadAll();
                       }
                     }
                    }
                  });
                }
              });
          }).trigger("change", [true]);
          
        });
        </script>';

            if ($this->config->get('shipping_glavpunktcourier_simple_status') == 1) {
                // если установлен модуль Simple то мы не будем выводить дополнительные поля
                // т.к. они замечательно выводятся в самом модуле
                $inputs = '';
            } elseif ((bool)$this->config->get('shipping_glavpunktcourier_hidedate')) {
                // Если дата доставки скрыта в настройках
                $inputs = '';
            } else {
                $inputs = <<<EOD
                <br><br>
                <label for="glavpunktcourier_date">Дата доставки</label>
                <input type="date" class="datetimeinputs" name="glavpunktcourier_date" id="glavpunktcourier_date"  value="$date" min="$date"><br><br>
                 <label for="glavpunktcourier_time">Интервал доставки</label>
                <input type="text" class="datetimeinputs" name="glavpunktcourier_time" id="glavpunktcourier_time" value="10:00 - 18:00">         
EOD;
                $linkForAjax = $this->url->link("checkout/glavpunktcourier/setcomment", '');
                $inputs .= <<<EOD
                <script>            
                    $(function(){
                        //Прибавляет день к выбранной дате  
                        function addDays(date) {
                          var now = new Date(date);
                          now.setDate(now.getDate() + 1);
                          var day = ("0" + now.getDate()).slice(-2);
                          var month = ("0" + (now.getMonth() + 1)).slice(-2);
                          var result = now.getFullYear()+"-"+(month)+"-"+(day);
                          return result;
                        } 
                        function setGetTime()
                        {
                            $.ajax({
                                url: '$linkForAjax',
                                type: 'post',
                                data: {
                                    date: $('#glavpunktcourier_date').val(),
                                    time: $('#glavpunktcourier_time').val(),
                                    type: 'Главпункт Курьерская доставка по РФ',
                                    info: $('#courierDeliveryGlavpunkt').val()
                                },
                                dataType: 'html',
                                success: function(html) {
                                    console.log(html);
                                }                    
                            }); 
                        }
                        $('.datetimeinputs').change(function() {
                            setGetTime();
                        });
                        $('.glavpunkt-courier').change(function() {
                            setGetTime();
                        });  
                        //При изменение даты курьерской доставки, проверяет день недели,  
                        //если воскресенье, то прибавлет день
                        $('#glavpunktcourier_date').change(function() { 
                          var check = new Date($('#glavpunktcourier_date').val());
                          if (check != 'Invalid Date') {
                              if (check.getDay() == 0) {          
                                $("#glavpunktcourier_date").val(addDays(check));
                                alert("Дата доставки не может быть в воскресенье");
                              }
                          }   
                        });  
                    });                   
                </script>
EOD;
            }

            if (isset($cityTo)) {
                $title_text = $this->language->get('text_description') . ' <br>' . $cityTo;
            } else {
                $title_text = $this->language->get('text_description');
            }

            if (isset($this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['cost'])) {
                $tarif = $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['cost'];
            } else {
                // Устанавливаем тариф по умолчанию
                $tarif = 90;
            }

            $quote_data['glavpunktcourier'] = array(
                'code' => 'glavpunktcourier.glavpunktcourier',
                'title' => $title_text,
                'cost' => $tarif,
                'tax_class_id' => 0,
                'text' => $selectCities . '<span class="glavpunktcourier_price">' .
                    $tarif . ' ' . $this->session->data['currency'] . '</span>' .
                    $inputs
            );

            $method_data = array(
                'code' => 'glavpunktcourier',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => $this->config->get('glavpunktcourier_sort_order'),
                'error' => false
            );
        }
        return $method_data;
    }

    private function sendRequest($dataToRequest)
    {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, 'https://glavpunkt.ru/api/get_tarif?');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'serv=' . $dataToRequest['serv'] .
                '&cityFrom=' . $dataToRequest['cityFrom'] .
                '&cityTo=' . $dataToRequest['cityTo'] .
                '&weight=' . $dataToRequest['weight'] .
                '&price=' . $dataToRequest['price']);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Opera 10.00');
            $res = curl_exec($curl);
            curl_close($curl);
            $result_of_request = json_decode($res, true);
        } else {
            $url = 'https://glavpunkt.ru/api/get_tarif?serv=курьерская доставка&cityFrom=' .
                $dataToRequest['cityFrom'] . '&cityTo=' . $dataToRequest['cityTo'] . '&weight=' . $dataToRequest['weight'] . '&price=' . $dataToRequest['price'];
            $result_of_request = json_decode(file_get_contents($url, true));
        }

        return $result_of_request;
    }
}
