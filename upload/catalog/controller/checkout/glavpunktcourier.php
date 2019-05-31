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
            $date = date('Y-m-d',  strtotime(' + 1 day'));
        } else {
            $date = date('Y-m-d', strtotime(' + '.$courierDays.' day'));
        }
        $strDate = "Sun " . $date;
        if ( strftime("%a %Y-%m-%d", strtotime($date)) === $strDate ) {
            $date = date('Y-m-d',  strtotime($date.' + 1 day'));
        }
        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'] =
            $this->request->post['type'] . ' <br>' . $this->request->post['info'] .
            '<br>Дата доставки: ' . $date .
            '<br>Время доставки: ' . "10:00 - 18:00";
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
        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'] =
            $this->request->post['type'] . ' <br>' . $this->request->post['info'] .
            '<br>Дата доставки: ' . date('d.m.Y', strtotime($this->request->post['date'])) .
            '<br>Время доставки: ' . $this->request->post['time'];
        echo $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'];
        exit;
    }
}
