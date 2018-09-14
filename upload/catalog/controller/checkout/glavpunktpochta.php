<?php

/**
 * Контроллер службы доставки "Главпункт почта РФ" на странице оформления заказа
 *
 * Class ControllerCheckoutGlavpunktPochta
 */
class ControllerCheckoutGlavpunktPochta extends Controller
{
    public function index()
    {
    }

    public function setprice()
    {
        $this->language->load('shipping/glavpunktpochta');
        $this->session->data['shipping_methods']['glavpunktpochta']['quote']['glavpunktpochta']['title'] = $this->request->post['type']
            . ' <br>' . $this->request->post['info'];
        $this->session->data['shipping_methods']['glavpunktpochta']['quote']['glavpunktpochta']['cost'] = $this->request->post['price'];
        $this->session->data['shipping_methods']['glavpunktpochta']['quote']['glavpunktpochta']['text'] = $this->request->post['price'];
        $this->session->data['gppochtarfreloaded'] = true;
        exit;
    }
}