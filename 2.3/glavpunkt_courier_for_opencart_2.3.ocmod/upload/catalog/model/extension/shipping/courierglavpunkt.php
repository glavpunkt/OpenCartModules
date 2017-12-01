<?php
class ModelExtensionShippingCourierglavpunkt extends Model
{

  function getQuote($address)
  {
      $this->language->load('extension/shipping/courierglavpunkt');

      if ($this->config->get('courierglavpunkt_status') == 1) {
        $status = true;
      } else {
        $status = false;
      }

      $cityFrom = $this->config->get('courierglavpunkt_home_city');

      if ($this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm') > 0) {
        $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm');
      } elseif ($this->config->get('courierglavpunkt_weight') > 0) {
        $weight = $this->config->get('courierglavpunkt_weight');
      } else {
        $weight = 1;
      }

     if (isset($this->session->data['selected_city'])) {
        $cityTo = $this->session->data['selected_city'];
      } else if ($address['city'] != '') {
        $cityTo = $address['city'];// из профиля юзера
      } else {
        $cityTo = 'Санкт-Петербург';
      }

    if ($status) {
      if ($this->config->get('courierglavpunkt_tarif_edit_code')) {
        $order   = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
        $replace = array(" ", "<", ">", '"', "'");
        $userSettingsCourier = str_replace($order, $replace, $this->config->get('courierglavpunkt_tarif_edit_code'));
      } else {
        $userSettingsCourier = '';
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
          $selectCities .= '<option value="' . $value['name'] . '" selected data-price="">' . $value['name'] . '</option>';
        } else {
          $selectCities .= '<option value="' . $value['name'] . '" data-price="">' . $value['name'] . '</option>';
        }
      }
      $selectCities .= '</select>';

      if (isset($this->session->data['reloaded']) && $this->session->data['reloaded'] == true) {
        $selectCities .= '<script> $(function(){$(\'#courierglavpunkt.courierglavpunkt\').siblings("input").prop( "checked", true);});</script>';
      }

      if ($this->config->get('glavpunktcourier_payment_type') == 1) {
        $payment_type = "cash";
      }else{
        $payment_type = "prepaid";
      }

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
              data: {
                serv:"курьерская доставка",
                cityFrom:cityFrom,
                cityTo:selectedCity,
                weight:weight,
                price:itemsPrice, 
                paymentType:"'.$payment_type.'"
              },
              dataType: "json",
              success: function(data){
              var tarif = Math.round(data["tarif"]);
                '.$userSettingsCourier.'
                $("#courierglavpunkt_price").html(tarif + " р.");
                  $.ajax({
                    url: "'.$this->url->link("checkout/courierglavpunkt/setprice", '').'",
                    type: "post",
                    data: {price:tarif, type:\'Курьерская доставка Главпункт\', info:city_obl.val()},
                    dataType: \'html\',
                    success: function(html) {';
                      if ($this->config->get('courierglavpunkt_simple_status') == 1) {// если установлен симпл, нам потребуется вызов метода reloadAll(); для обновления измененных данных
                        $selectCities .= 'if (!firstCall) {
                          reloadAll();
                        }';
                      }
                    $selectCities .= '}
                  });
                }
              });
          })';
          if ($this->config->get('courierglavpunkt_simple_status') == 1) {
              $selectCities .= '.trigger("change", ["true"]);';// если установлен симпл, нам потребуется передать параметр, т.к 1 вызов trigger выполнится при загрузке страницы, нас будет интересовать второй, для вызова reloadAll();, иначе получим бесконечную перезагрузку
          } else {
            $selectCities .= '.trigger("change");';
          }
       $selectCities .= '});
        </script>';

      $this->session->data['reloaded'] = false;

      if (isset($cityTo)) {
        $title_text = $this->language->get('text_description') . ' <br>' . $cityTo;
      } else {
        $title_text = $this->language->get('text_description');
      }

      if (isset($this->session->data['shipping_methods']['courierglavpunkt']['quote']['courierglavpunkt']['cost'])) {
        $tarif = $this->session->data['shipping_methods']['courierglavpunkt']['quote']['courierglavpunkt']['cost'];
      } else {
        $tarif = 0;
      }

      $quote_data['courierglavpunkt'] = array(
        'code' => 'courierglavpunkt.courierglavpunkt',
        'title' => $title_text,
        'cost' => $tarif,
        'tax_class_id' => 0,
        'text' => $selectCities.'<span id="courierglavpunkt_price">'.$tarif.' '.$this->session->data['currency'].'</span>'
      );

      $method_data = array(
        'code' => 'courierglavpunkt',
        'title' => $this->language->get('text_title'),
        'quote' => $quote_data,
        'sort_order' => $this->config->get('courierglavpunkt_sort_order'),
        'error' => false
      );

    }

    return $method_data;
  }
}