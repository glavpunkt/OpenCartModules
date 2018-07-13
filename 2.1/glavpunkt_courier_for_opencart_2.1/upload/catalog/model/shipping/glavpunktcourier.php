<?php
class ModelShippingGlavpunktcourier extends Model {
    function getQuote($address) {
        $this->language->load('shipping/glavpunktcourier');

        if ($this->config->get('glavpunktcourier_status') == 1) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $quote_data = array();
            $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm');
            $cityFrom = $this->config->get('glavpunktcourier_home_city');

            if ($weight <= 0) {
                if ($this->config->get('glavpunktcourier_weight')) {
                    $weight = $this->config->get('glavpunktcourier_weight');
                } else {
                    $weight = 1;
                }
            }elseif($weight > 20) {
                //exit("превышен допустимый вес для данной службы доставки");
                return false;
            }

            if (isset($this->session->data['selected_city'])) {
                $cityTo = $this->session->data['selected_city'];
            } else if ($address['city'] != '') {
                $cityTo = $address['city'];// из профиля юзера
            } else {
                $cityTo = 'Санкт-Петербург';
            }

            if (null !== $this->config->get('glavpunktcourier_tarif_edit_code')) {
                $order   = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
                $replace = array(" ", "<", ">", '"', "'");
                $userSettingsCourier = str_replace($order, $replace, $this->config->get('glavpunktcourier_tarif_edit_code'));
            }

            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, 'https://glavpunkt.ru/api/get_courier_cities');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                $out = curl_exec($curl);
                curl_close($curl);
                $answerGetCities = json_decode($out,true);
            } else {
                $answerGetCities = json_decode(file_get_contents('https://glavpunkt.ru/api/get_courier_cities'),true);
            }

            $selectCities = '<select style="padding: 4px 5px;" class="glavpunkt-courier" id="courierDeliveryGlavpunkt">';

            foreach ($answerGetCities as $key => $value) {
                if (mb_strtolower($value['name']) == mb_strtolower($cityTo)) {
                    $selectCities .= '<option value="'.$value['name'].'" selected data-price="">'.$value['name'].'</option>';
                } else {
                    $selectCities .= '<option value="' . $value['name'] . '" data-price="">' . $value['name'] . '</option>';
                }
            }
            $selectCities .= '</select>';

            $selectCities .= '<script>
        $(function(){
          $(\'.glavpunkt-courier\').change(function(e, firstCall){
            var itemsPrice = '.$this->cart->getTotal().';
            var city_obl = $(e.currentTarget);            
            var cityFrom = "'.$cityFrom.'";
            var weight = '.$weight.';
            var selectedCity = $(".glavpunkt-courier option:checked").html();            
             $.ajax({
              url: "https://glavpunkt.ru/api/get_tarif",
              type: "GET",
              data: {serv:"курьерская доставка", cityFrom:cityFrom, cityTo:selectedCity, weight:weight, price:itemsPrice},
              dataType: "json",
              success: function(data){
              var tarif = data["tarif"];
                '.$userSettingsCourier.'
                $("#glavpunktcourier_price").html(tarif + " р.");
                  $.ajax({
                    url: "'.$this->url->link("checkout/glavpunktcourier/setprice", '').'",
                    type: "post",
                    data: {price:tarif, type:\'Главпункт Курьерская доставка по РФ\', info:city_obl.val()},
                    dataType: \'html\',
                    success: function(html) {';
            if ($this->config->get('glavpunktcourier_simple_status') == 1) {// если установлен симпл, нам потребуется вызов метода reloadAll(); для обновления измененных данных
                $selectCities .= 'if (!firstCall) {
                          $("input[value=\'glavpunktcourier.glavpunktcourier\']").click();
                          reloadAll();
                        }';
            }
            $selectCities .= '}
                  });
                }
              });
          })';
            if ($this->config->get('glavpunktcourier_simple_status') == 1) {
                $selectCities .= '.trigger("change", ["true"]);';// если установлен симпл, нам потребуется передать параметр, т.к 1 вызов trigger выполнится при загрузке страницы, нас будет интересовать второй, для вызова reloadAll();, иначе получим бесконечную перезагрузку
            } else {
                $selectCities .= '.trigger("change");';
            }
            $selectCities .= '});
        </script>';

            $this->session->data['courierreloaded'] = false;

            if (isset($cityTo)) {
                $title_text = $this->language->get('text_description'). ' <br>' .$cityTo;
            } else {
                $title_text = $this->language->get('text_description');
            }

            if (isset($this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['cost'])) {
                $tarif = $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['cost'];
            } else {
                $tarif = 0;
            }

            $quote_data['glavpunktcourier'] = array(
                'code' => 'glavpunktcourier.glavpunktcourier',
                'title' => $title_text,
                'cost' => $tarif,
                'tax_class_id' => 0,
                'text' => $selectCities.'<span id="glavpunktcourier_price">'.$tarif.' '.$this->session->data['currency'].'</span>'
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
}