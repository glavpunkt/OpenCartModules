<?php

/**
 * Контроллер модуля доставки "Курьерская доставка Главпункт" на странице оформления заказа
 *
 * Отвчает за изменение цены и комментария к заказу при выборе города доставки,
 * и при заполнении полей "дата и время доставки"
 *
 * Class ControllerCheckoutGlavpunktCourier
 * @author SergeChepikov
 */
class ControllerCheckoutGlavpunktCourier extends Controller
{
    public function index()
    {

    }

    public function setprice()
    {
        $this->language->load('shipping/glavpunktcourier');


        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'] = $this->request->post['type'] . ' <br>' . $this->request->post['info'];
        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['cost'] = $this->request->post['price'];
        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['text'] = $this->request->post['price'];
        $this->session->data['selected_city'] = $this->request->post['info'];
        $this->session->data['reloaded'] = true;
        $courierDays = intval($this->config->get('shipping_glavpunktcourier_days'));
        if (!$courierDays) {
            $date = date('d.m.Y', strtotime(' + 1 day'));
        } else {
            $date = date('d.m.Y', strtotime(' + ' . $courierDays . ' day'));
        }
        $strToDate = strtotime($date);
        if (date('w', $strToDate) == 0) {
            $date = date('d.m.Y', strtotime($date . ' + 1 weekdays'));
        }

        $courDateTime =
            '<br>Дата доставки: ' . $date .
            '<br>Время доставки: 10:00 - 18:00';
        if ((bool)$this->config->get('shipping_glavpunktcourier_hidedate')) {
            $courDateTime = '';
        }

        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'] =
            $this->request->post['type'] . ' <br>' . $this->request->post['info'] . $courDateTime;
        echo $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'];

        exit;
    }

    /**
     * Функция добавления комментария к выбранной доставке
     *
     * комментарий это дата и интервал доставки
     */
    public function setcomment()
    {
        $this->language->load('shipping/glavpunktcourier');

        if ((bool)$this->config->get('shipping_glavpunktcourier_hidedate')) {
            $courDateTime = '';
        } else {
            $courDateTime =
                '<br>Дата доставки: ' . date('d.m.Y', strtotime($this->request->post['date'])) .
                '<br>Время доставки: ' . $this->request->post['time'];
        }

        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'] =
            $this->request->post['type'] . ' <br>' . $this->request->post['info'] . $courDateTime;
        echo $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'];
        exit;
    }
}
