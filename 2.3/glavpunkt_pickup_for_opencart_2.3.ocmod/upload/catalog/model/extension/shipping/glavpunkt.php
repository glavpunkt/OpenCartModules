<?php
class ModelExtensionShippingGlavpunkt extends Model {
  function getQuote($address) {

    $this->language->load('extension/shipping/glavpunkt');

    if ($this->config->get('glavpunkt_status') == 1) {
      $status = true;
    } else {
      $status = false;
    }

    $method_data = array();
      $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), 'kilogramm');
      $home_city = $this->config->get('glavpunkt_home_city');

      if ($weight == 0) $weight = $this->config->get('glavpunktcourier_weight');
      if ($weight == 0) $weight = 1;
      if ($home_city == null) $home_city = 'Санкт-Петербург';

      $city = $address['city'];
      $zone = $address['zone'];
      if ($city == '' && $zone != '') $city = $zone;
      if ($city == 'Moscow') $city = 'Москва';
      if ($city == 'St. Peterburg') $city =  'Санкт-Петербург';

    if ($status) {
      if ($this->config->get('glavpunkt_tarif_edit_code')) {
        $order   = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
        $replace = array(" ", "<", ">", '"', "'");
        $userSettings = str_replace($order, $replace, $this->config->get('glavpunkt_tarif_edit_code'));
      }
      $quote_data = array();
          $title_text = $this->language->get('text_title');
          $data_for_widget = "{'defaultCity': '".$city."',".$this->config->get('glavpunkt_widget_data')."}";
          $quote_text =  ' <a id="glavpunkt_open_map"  href="#" onclick="glavpunkt.openMap(selectPunkt,'.$data_for_widget.'); return false;">'.$this->language->get('text_description'). '</a>'.
          '<script type="text/javascript"> 
            $(\'#button-shipping-method\').on(\'click\', function(e){
              if ($("input:radio[value=\'glavpunkt.glavpunkt\']").is(\':checked\')){
                if ($(\'#glavpunkt_content\').html() == \'\'){
                  $(\'#glavpunkt_open_map\').css({display: "inline-block", padding:"3px", border: "2px solid red"});
                   return false;
                }
              }
            });
            function selectPunkt(punktInfo) { 
                
                console.log(punktInfo.city);
                
              $("input:radio[value=\'glavpunkt.glavpunkt\']").prop("checked", true);
              var name = punktInfo.name;
              var tarif =0;
              if (name != punktInfo.address) {
                name += \', \' + punktInfo.address;
              }
              $(\'#selectedPunkt\').text(name);
               $.getJSON( "//glavpunkt.ru/api/get_tarif", {
              \'serv\': \'выдача\',
              \'cityFrom\': \''.$this->config->get('glavpunkt_home_city').'\',
              \'cityTo\': punktInfo.city,
              \'punktId\': punktInfo.id,
              \'weight\': \''.$weight.'\',
              \'price\':\''.$this->cart->getTotal().'\',
              \'paymentType\': \'cash\'
            }).done(function(data) {
                 if (data.result == \'ok\') {
                  tarif =  data.tarif;
                  '.$userSettings.'
                  $.ajax({
                    url: \''.$this->url->link('checkout/glavpunkt/setprice', '').'\',
                    type: \'post\',
                    data: {price:tarif, type:\'Самовывоз Главпункт\', info:punktInfo.name, address:punktInfo.address, phone:punktInfo.phone, work_time:punktInfo.work_time, cityTo:punktInfo.city},
                    dataType: \'html\',
                    success: function(html) {
                            document.getElementById(\'glavpunkt_content\').innerHTML = punktInfo.city +\'<br>\';
                            document.getElementById(\'glavpunkt_content\').innerHTML += \'<b>\'+ punktInfo.name +\'</b><br>\';
                            document.getElementById(\'glavpunkt_content\').innerHTML += \''. $this->language->get('text_phone') .'\'+ punktInfo.phone +\'<br>\';
                            document.getElementById(\'glavpunkt_content\').innerHTML += \''. $this->language->get('text_price') .'\'+ tarif + \' р.\'+\'<br>\';
                            document.getElementById(\'glavpunkt_content\').innerHTML += \''. $this->language->get('text_address') .'\'+ punktInfo.address + \' р.\'+\'<br>\';
                            document.getElementById(\'glavpunkt_content\').innerHTML += \''. $this->language->get('text_work_time') .'\'+ punktInfo.work_time +\'<br>\';
                            $(\'#glavpunkt_open_map\').css({display: "inline-block", padding:"3px", border: "0"});
                    }
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
           <br><span id="glavpunkt_content"></span>
           ';

          $quote_data['glavpunkt'] = array(
            'code'         => 'glavpunkt.glavpunkt',
            'title'        => $title_text,
            'text'         => $quote_text,
            'cost'         => 0,
            'tax_class_id' => 0,
          );

          $method_data = array(
            'code'       => 'glavpunkt',
            'title'      => $this->language->get('text_title'),
            'quote'      => $quote_data,
            'sort_order' => $this->config->get('glavpunkt_sort_order'),
            'error'      => false
          );
    }

    return $method_data;
  }
}
