<?php

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
        $this->session->data['courierreloaded'] = true;
        $courierDays = intval($this->config->get('glavpunktcourier_days'));
        if (!$courierDays) {
            $date = date('d.m.Y', strtotime(' + 1 day'));
        } else {
            $date = date('d.m.Y', strtotime(' + ' . $courierDays . ' day'));
        }
        $strToDate = strtotime($date);
        if (date('w', $strToDate) == 0) {
            $date = date('d.m.Y', strtotime($date . ' + 1 weekdays'));
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
