<?php

class ControllerCheckoutGlavpunkt extends Controller
{
    public function index()
    {
    }

    public function setprice()
    {
        $this->language->load('shipping/glavpunkt');
        if ($this->request->post['info'] == $this->request->post['address']) {
            $title = $this->request->post['type']
                . ' <br>' . $this->request->post['city_to'] . ' <br>'
                . $this->request->post['phone'] . ' <br>' . $this->request->post['price'] . ' р.' . ' <br>'
                . $this->request->post['address'] . ' <br>' . $this->request->post['work_time'] . ' <br>';
        } else {
            $title = $this->request->post['type']
                . ' <br>' . $this->request->post['city_to'] . ' <br>' . $this->request->post['info'] . ' <br>'
                . $this->request->post['phone'] . ' <br>' . $this->request->post['price'] . ' р.' . ' <br>'
                . $this->request->post['address'] . ' <br>' . $this->request->post['work_time'] . ' <br>';
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

        $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['title'] = $title;
        $this->session->data['pointsreloaded'] = true;
        $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['cost'] = $this->request->post['price'];
        $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['text'] = $this->request->post['price'];

        echo($this->request->post['city_to'] . ' <br>' . $this->request->post['info'] . ' <br>' . $this->request->post['phone'] . ' <br>' . $this->request->post['price'] . ' р.' . ' <br>' . $this->request->post['address'] . ' <br>' . $this->request->post['work_time'] . ' <br>');

        exit;
    }
}
