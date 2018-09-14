<?php

/**
 * Контроллер службы доставки "Курьерская доставка Главпункт" на странице оформления заказа
 *
 * Class ControllerCheckoutGlavpunktCourier
 */
class ControllerCheckoutGlavpunktCourier extends Controller
{
    public function index()
    {

    }

    public function setprice()
    {
        $this->language->load('shipping/glavpunktcourier');


        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'] = $this->request->post['type'] .
            ' <br>' . $this->request->post['info'];
        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['cost'] = $this->request->post['price'];
        $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['text'] = $this->request->post['price'];
        $this->session->data['selected_city'] = $this->request->post['info'];
        $this->session->data['reloaded'] = true;

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
