<?php

/**
 * Модель модуля доставки Главпункт
 *
 * @author SergeChepikov
 */
class ModelShippingGlavpunkt extends Model
{
    /**
     * Получение вариантов расчётов доставки
     *
     * @param array $address Информация о заказе для расчёта
     * [
     *  [firstname] =>
     *  [lastname] =>
     *  [company] =>
     *  [address_1] =>
     *  [address_2] =>
     *  [postcode] => 456789
     *  [city] =>
     *  [zone_id] => 2788
     *  [zone] => Тамбовская область
     *  [zone_code] => TAM
     *  [country_id] => 176
     *  [country] => Российская Федерация
     *  [iso_code_2] => RU
     *  [iso_code_3] => RUS
     *  [address_format] =>
     * ]
     * @return array Массив доступных способов доставки
     * [
     *  [code] => glavpunkt
     *  [title] => Главпункт
     *  [quote] => [
     *      [post] => [
     *          [code] => glavpunkt.post
     *          [title] => Почта РФ
     *          [cost] => 409
     *          [tax_class_id] => 1
     *          [text] => $409.00
     *      ]
     *  ]
     *  [sort_order] =>
     *  [error] =>
     * ]
     */

    /** Проверка на HTTPS */
    private function isHttps()
    {
        if (isset($_SERVER['HTTPS'])) {
            return true;
        }
        return false;
    }

    function getQuote($address)
    {
        // Подключение языкового файла
        $this->language->load('shipping/glavpunkt');

        // Подгрузка настроек
        $this->load->model('setting/setting');

        $quote_data = array();

        // Проверяем включение "самовывоз" и доступность данной услуги из этого города
        if ($this->config->get('glavpunkt_pickup_status') == 1 && $address['city'] !== '') {
            // и добавляем расчёт по данному виду доставки к общему списку
            $calc = $this->pickupCalculation($address);
            if (count($calc) > 0) {
                $quote_data['pickup'] = $calc;
            }
        }

        // Проверяем включение "Почта РФ" и доступность данной услуги из этого города
        if (
            $this->config->get('glavpunkt_post_status') == 1
            && (
                $this->config->get('glavpunkt_cityFrom') === 'Санкт-Петербург'
                || $this->config->get('glavpunkt_cityFrom') === 'Москва'
            )
        ) {
            // и добавляем расчёт по данному виду доставки к общему списку
            $calc = $this->postCalculation($address);
            if (count($calc) > 0) {
                $quote_data['post'] = $calc;
            }
        }

        /** Проверяем включение "Курьерская доставка" и наличие заполненного города доставки */
        if ($this->config->get('glavpunkt_courier_status') == 1 && $address['city'] !== '') {
            // и добавляем расчёт по данному виду доставки к общему списку
            $calc = $this->courierCalculation($address);
            if (count($calc) > 0) {
                $quote_data['courier'] = $calc;
            }
        }

        // проверяем на количество доступных методов доставки
        if (count($quote_data) > 0) {
            // и выводим их
            return array(
                'code' => 'glavpunkt',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => '',
                'error' => ''
            );
        } else {
            // или же возвращаем пустое значение
            return array();
        }
    }

    /**
     * Расчёт доставки с пунктов выдачи
     *
     * @param $address
     * @return array
     */
    private function pickupCalculation($address)
    {
        // подключаем языковой файл
        $title = $this->language->get('pickup_title');

        // проверяем в сессии сохраннённую цену доставки или же считаем до выбранного города
        $cost = isset($this->session->data['cost'])
            ? $this->session->data['cost']
            : $this->standartPrice($address);

        // достаём город доставки или же ставим город магазина
        $cityTo = $address['city'] !== ''
            ? $address['city']
            : $this->config->get('glavpunkt_cityFrom');

        // город отправки - город выбранный в настройках
        $cityFrom = $this->config->get('glavpunkt_cityFrom');

        // URL для Ajax запроса на сохранение данных по выбранному пункту выдачи
        $ajaxURL = $this->url->link('checkout/glavpunkt/setprice', '',  $this->isHttps());

        // получение веса заказа
        $weigth = $this->cart->getWeight();

        // получение цены заказа
        $price = $this->cart->getTotal();

        // получение тип оплаты для расчёта доставки
        $paymentType = $this->config->get('glavpunkt_paymentType');

        // если установлен модуль Симпл
        if ($this->config->get('glavpunkt_simple_status') == 1) {
            $resultScript = 'reloadAll();';
        } else {
            $resultScript = '$(\'#checked-point\').html(html);';
        }

        // тут начинается дичь :)
        // вывод html кода, который будет выводится внутри label в способах доставки
        // тут мы выводим выбранный пункт доставки или пустоту, если ничего не выбрали
        // а также выводим скрипт карты с пунктами выдачи
        // и скрипт который будет отправлять запрос на обновление блока доставки
        $script = <<<EOD
            <a href="javascript:void(0)" onclick="glavpunkt.openMap(selectPunkt,{'defaultCity': '$cityTo'});">
                $title
            </a>       
            <span id="checked-point"></span>            
            <style type="text/css">
                /* Стили, чтобы вспылвающие окна были поверх всех */
                .glavpunkt_overlay{ z-index:10000!important; }
                .glavpunkt_container{ z-index:10001!important; }
            </style>            
            <script type="text/javascript">
                /* Скрипт добавляющий в head виджет Главпункта с пунктами выдачи */
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = 'https://glavpunkt.ru/js/punkts-widget/glavpunkt.js';
                document.head.appendChild(script);
            </script>
            <script type="text/javascript">
                /* Функция, которая срабатывает на выбор пункта выдачи на карте */
                function selectPunkt(punktInfo) {
                    /* Отправляем запрос на расчёт стоимости доставки */
                    $.getJSON( "//glavpunkt.ru/api/get_tarif", {
                        'serv': 'выдача',
                        'cityFrom': '$cityFrom',
                        'cityTo': punktInfo.city,
                        'punktId': punktInfo.id,
                        'weight': $weigth,
                        'price': $price,
                        'paymentType': '$paymentType',
                        'cms': 'opencart-1.5'
                    }).done(function(data) {
                        if (data.result == 'ok') {    
                            /* При положительном ответе выполняем следующее */
                            /* Переключаем радио-кнопку, что мы выбрали самовывоз */
                            $("input:radio[value='glavpunkt.pickup']").prop("checked", true);
                            
                            /* Отправляем запрос, чтобы сохранить данный пункт выдачи в сессии */
                            /* Передаём кучу данных, связанных с пунктом выдачи */
                            $.ajax({
                                url: '$ajaxURL',
                                type: 'post',
                                data: {
                                    price: data.tarif,
                                    type: '$title',
                                    info: punktInfo.name,
                                    address: punktInfo.address,
                                    phone: punktInfo.phone,
                                    work_time: punktInfo.work_time,
                                    city_to: punktInfo.city,
                                    punkt:punktInfo
                                },
                                dataType: 'html',
                                success: function(html) {
                                    $resultScript                                                               
                                }
                            });  
                        } else if (data.result == 'error') {
                            /* Вывод ошибок, если что-то не так в запросе */
                            console.log('Ошибка подсчета тарифа', data.message);
                        } else {
                            /* Вывод ошибки, если запроса не состоялось */
                            console.log('Ошибка подсчета тарифа (неверный ответ от сервера)', data);
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        /* Вывод ошибки, если запроса не состоялось */
                        console.log(textStatus);
                    });
                }
            </script>            
EOD;

        $pointAddress = '<span id="checked-point">Пункт выдачи не выбран</span>';
        if (isset($this->session->data['pointsreloaded']) && $this->session->data['pointsreloaded'] == true) {
            if (isset($this->session->data['shipping_methods'])) {
                $pointAddress = $this->session->data['shipping_methods']['glavpunkt']['quote']['pickup']['title'];
            }
            if (isset($this->session->data['shipping_methods']['glavpunkt']['quote']['pickup']['cost'])) {
                $cost = $this->session->data['shipping_methods']['glavpunkt']['quote']['pickup']['cost'];
            }
        }

        // возвращаем массив с доступным методом доставки
        return array(
            'code' => 'glavpunkt.pickup',
            'title' => $pointAddress,
            'cost' => $cost,
            'tax_class_id' => 1,
            'text' => $script.$this->currency->format(
            // Конвертация рублей в текущую валюту.
                $this->currency->convert($cost, 'RUB', $this->currency->getCode())
            )
        );
    }

    /**
     * Расчёт курьерской доставки
     *
     * @param $address
     * @return array
     */
    private function courierCalculation($address)
    {
        /** @var string $url URL запроса к Главпункт API */
        $url = 'https://glavpunkt.ru/api/get_tarif' .
            '?serv=' . 'курьерская доставка' .
            '&cityFrom=' . $this->config->get('glavpunkt_cityFrom') .
            '&cityTo=' . $address['city'] .
            '&weight=' . $this->cart->getWeight() .
            '&price=' . $this->cart->getTotal() .
            '&cms=' . 'opencart-1.5' .
            '&paymentType=' . $this->config->get('glavpunkt_paymentType');

        $answer = $this->request($url);

        // Добавление полей "Дата доставки" и "Интервал доставки"
        // а также скрипта, который будет проверять правильность заполненных данных при нажатии на продолжить
        // при условии, что не включен модуль Симпл
        if ($this->config->get('glavpunkt_simple_status') == 1) {
            $inputs = '';
        } else {
            // URL для Ajax запроса на сохранение данных по заполненым полям
            $ajaxURL = $this->url->link('checkout/glavpunkt/setcourier', '',  $this->isHttps());
            $city = $address['city'];
            $inputs = <<<EOD
                <br><br>
                <label for="glavpunktcourier_date">Дата доставки</label>
                <input type="date" class="datetimeinputs" name="glavpunktcourier_date" id="glavpunktcourier_date">
                <br><br>
                <label for="glavpunktcourier_time">Интервал доставки</label>
                <input 
                    type="text" 
                    class="datetimeinputs" 
                    name="glavpunktcourier_time" 
                    id="glavpunktcourier_time" 
                    value="10:00-18:00"
                    placeholder="В формате '10:00-18:00'"
                >                
                <script type="text/javascript">
                     $('#button-shipping-method').on('click', function(e){
                        if ($("input:radio[value='glavpunkt.courier']").is(':checked')){
                            var regExp = /\d{2}:\d{2}-\d{2}:\d{2}/;
                            var date = $('#glavpunktcourier_date').val();
                            var time = $('#glavpunktcourier_time').val();
                            if (date === '' || regExp.test(time) === false){
                                // вывод ошибки
                                alert('Заполните дату и интервал доставки');
                                return false;
                            }else{
                                // отправляем заполненные данные в контроллер
                                $.ajax({
                                    url: '$ajaxURL',
                                    type: 'post',                                    
                                    data: {
                                        date: date,
                                        time: time,
                                        type: 'Главпункт Курьерская доставка по РФ',
                                        info: '$city'
                                    },
                                    dataType: 'html',
                                    success: function(html) {
                                        return true;                                                               
                                    }
                                });
                            }
                        }
                    });
                </script>
EOD;
        }

        if ($answer['result'] === 'ok') {
            return array(
                'code' => 'glavpunkt.courier',
                'title' => $this->language->get('courier_title') . $inputs,
                'cost' => $answer['tarif'],
                'tax_class_id' => 1,
                'text' => $this->currency->format(
                /** Конвертация рублей в текущую валюту. */
                    $this->currency->convert($answer['tarif'], 'RUB', $this->currency->getCode())
                )
            );
        } else {
            return array();
        }
    }

    /**
     * Расчёт доставки "Почта РФ"
     *
     * @param $address
     * @return array
     */
    private function postCalculation($address)
    {
        /** @var string $url URL запроса к Главпункт API */
        $url = 'https://glavpunkt.ru/api/get_pochta_tarif?' .
            'cityFrom=' . ($address['city'] === 'Москва' ? "MSK" : "SPB") .
            '&address=' . $address['postcode'] . " " .
            $address['zone'] . " " .
            $address['address_1'] . " " .
            $address['address_2'] . " " .
            '&weight=' . $this->cart->getWeight() .
            '&price=' . $this->cart->getTotal() .
            '&index=' . $address['postcode'] .
            '&cms=' . 'opencart-1.5' .
            '&paymentType=' . $this->config->get('glavpunkt_paymentType');

        $answer = $this->request($url);

        if ($answer['result'] === 'ok') {
            return array(
                'code' => 'glavpunkt.post',
                'title' => $this->language->get('post_title'),
                'cost' => $answer['tarifTotal'],
                'tax_class_id' => 1,
                'text' => $this->currency->format(
                /** Конвертация рублей в текущую валюту. */
                    $this->currency->convert($answer['tarifTotal'], 'RUB', $this->currency->getCode())
                )
            );
        } else {
            return array();
        }
    }

    /**
     * Получение цены для доставке по выдаче для выбранного города
     *
     * Это получение цены, необходимо для первоначальном выводе доступный способов доставки
     * и чтобы ноль не выводился, а просто расчитывалась цена для выбранного города
     *
     * @param $address
     * @return mixed
     */
    private function standartPrice($address)
    {
        /** @var string $url URL запроса к Главпункт API */
        $url = 'https://glavpunkt.ru/api/get_tarif' .
            '?serv=' . 'выдача' .
            '&cityFrom=' . $this->config->get('glavpunkt_cityFrom') .
            '&cityTo=' . $address['city'] .
            '&weight=' . $this->cart->getWeight() .
            '&price=' . $this->cart->getTotal() .
            '&cms=' . 'opencart-1.5' .
            '&paymentType=' . $this->config->get('glavpunkt_paymentType');
        $calculation = $this->request($url);

        return $calculation['tarif'];
    }

    /**
     * Инициализация и отправка cURL запроса
     *
     * @param $url
     * @return array
     */
    private function request($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ));
        $answer = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $answer;
    }
}