<?php

/**
 * Контроллер изменений службы доставки в оформлении заказа
 *
 * Class ControllerCheckoutGlavpunkt
 * @author SergeChepikov
 */
class ControllerCheckoutGlavpunkt extends Controller
{
    /**
     * Установка цены
     */
    public function setprice()
    {
        // Подключаем языковой файл
        $this->language->load('shipping/glavpunkt');

        // Проверяем на соответствие полученного адреса и информации о пункте выдачи
        // и формируем title который будет характеризовать пункт выдачи
        if ($this->request->post['info'] == $this->request->post['address']) {
            $title = $this->request->post['type'] .
                ' <br>' . $this->request->post['city_to'] .
                ' <br>' . $this->request->post['phone'] .
                ' <br>' . $this->request->post['address'] .
                ' <br>' . $this->request->post['work_time'] . ' <br>';
        } else {
            $title = $this->request->post['type'] .
                ' <br>' . $this->request->post['city_to'] .
                ' <br>' . $this->request->post['info'] .
                ' <br>' . $this->request->post['phone'] .
                ' <br>' . $this->request->post['address'] .
                ' <br>' . $this->request->post['work_time'] . ' <br>';
        }

        // получаем данные пункта, если СПб или Москва, то выводим более развёрнутую информацию
        $punkt = $this->request->post['punkt'];
        if ($punkt['cityId'] == "SPB" || $punkt['cityId'] == "MSK") {
            $title = 'Пункт выдачи ' . $punkt['brand'] . ': ' . $punkt['name'] . ', ' . ' <br>' .
                $punkt['city'] . ' <br>' .
                ' Телефон: ' . $punkt['phone'] . ' <br>' .
                $this->request->post['price'] . ' р.' . ' <br>' .
                ' Адрес: ' . $punkt['address'] . ' <br>' .
                ' График работы: ' . $punkt['work_time'] . ' <br>' . $punkt['deliveryDays'];
        }

        // сохраняем данную информация для сохранения в заказе
        $this->session->data['shipping_methods']['glavpunkt']['quote']['pickup']['title'] = $title;
        $this->session->data['pointsreloaded'] = true;
        $this->session->data['shipping_methods']['glavpunkt']['quote']['pickup']['cost'] = $this->request->post['price'];
        $this->session->data['shipping_methods']['glavpunkt']['quote']['pickup']['text'] = $this->request->post['price'];

        // Выводим полное название, для отображения пользователю
        echo $title;

        // не знаю на что тут он влияет, но в примере был - оставляю
        exit;
    }

    /**
     * Установка параметров для курьерской доставки
     */
    public function setcourier()
    {
        // Подключаем языковой файл
        $this->language->load('shipping/glavpunkt');

        $this->session->data['shipping_methods']['glavpunkt']['quote']['courier']['title'] =
            $this->request->post['type'] . ' <br>' . $this->request->post['info'] .
            '<br>Дата доставки: ' . date('d.m.Y', strtotime($this->request->post['date'])) .
            '<br>Время доставки: ' . $this->request->post['time'];

        exit;
    }
}