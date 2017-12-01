<?php
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

      if (isset($address['city'])) {
        $city = $address['city'];
      } elseif ($this->config->get('glavpunkt_home_city') != null) {
        $city = $this->config->get('glavpunkt_home_city');
      } else {
        $city = 'Санкт-Петербург';
      }

    if ($status) {
      if ($this->config->get('glavpunkt_tarif_edit_code')) {
        $order   = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
        $replace = array(" ", "<", ">", '"', "'");
        $userSettings = str_replace($order, $replace, $this->config->get('glavpunkt_tarif_edit_code'));
      } else {
        $userSettings = '';
      }
             $data_for_widget = "{'defaultCity': '".$city."',".$this->config->get('glavpunkt_widget_data')."}";
            $quote_text = '<a id="glavpunkt_open_map"  href="#" onclick="glavpunkt.openMap(selectPunkt,'.$data_for_widget.'); return false;" style="color: #232323; font-size: 15px; font-weight: 600;" class="custom_style_for_glavpunkt">'.$this->language->get('text_description'). '</a>';

            $quote_text .= '<script type="text/javascript">
              var script = document.createElement(\'script\');

              script.type = \'text/javascript\';
              script.src = \'https://glavpunkt.ru/js/punkts-widget/glavpunkt.js\';
              document.head.appendChild(script);

              var style = document.createElement(\'style\');
              style.type = \'text/css\';
              style.innerHTML = \'.glavpunkt_container{ z-index:2000!important; }\';
              document.head.appendChild(style);glavpunkt_content
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
                //$("input:radio[value=\'glavpunkt.glavpunkt\']").prop("checked", true);
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
                \'price\':\''.$this->cart->getTotal().'\',';

                $quote_text .= "'paymentType':";

                if ($this->config->get('glavpunktpoints_payment_type') == 1) {
                  $quote_text .= "'cash',";
                }else{
                  $quote_text .= "'prepaid',";
                }

               $quote_text .= '}).done(function(data) {
                   if (data.result == \'ok\') {
                    tarif =  data.tarif;
                    '.$userSettings.'
                    $.ajax({
                      url: \''.$this->url->link('checkout/glavpunkt/setprice', '').'\',
                      type: \'post\',
                      data: {price:tarif, type:\'Главпункт - самовывоз\', info:punktInfo.name, address:punktInfo.address, phone:punktInfo.phone, work_time:punktInfo.work_time, city_to:punktInfo.city},
                      dataType: \'html\',
                      success: function(html) {
                        $(\'#glavpunkt_open_map\').css({display: "inline-block", padding:"3px", border: "0"});';
                     
                          if ($this->config->get('glavpunktpoints_simple_status') == 1) {// если установлен симпл, нам потребуется вызов метода reloadAll(); для обновления измененных данных
                            $quote_text .= '
                            reloadAll();
                            ';
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

              $title_text = $this->language->get('text_title');
              $cost = 0;

              if (isset($this->session->data['reloaded']) && $this->session->data['reloaded'] == true) {
                if (isset($this->session->data['shipping_methods'])) {
                  $title_text = $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['title'];
                }

                if (isset($this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['cost'])) {
                  $cost = $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['cost'];
                }
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
}