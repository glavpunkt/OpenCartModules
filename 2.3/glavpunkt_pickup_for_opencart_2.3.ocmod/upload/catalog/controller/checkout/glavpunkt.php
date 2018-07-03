<?php
class ControllerCheckoutGlavpunkt extends Controller {
  public function index() {

  }

  public function setprice() {
      $this->language->load('shipping/glavpunkt');
      $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['title'] = $this->request->post['type'] . ' <br>' . $this->request->post['city_to'] . ' <br>' . $this->request->post['info'] . ' <br>' . $this->request->post['phone'] . ' <br>' .$this->request->post['price'].' р.'.' <br>' . $this->request->post['address']. ' <br>' . $this->request->post['work_time']. ' <br>';
      $this->session->data['reloaded'] = true;
      $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['cost'] = $this->request->post['price'];
      $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['text'] = $this->request->post['price'];

      $punkt = $this->request->post['punkt'];

      if ($punkt['cityId'] == "SPB" || $punkt['cityId'] == "MSK") {
          $title = 'Пункт выдачи ' . $punkt['brand'] . ': ' . $punkt['name']  . ', ' . $punkt['city'] . ', ' . ' Адрес: ' . $punkt['address']
              . ' Телефон: ' . $punkt['phone']
              . ' График работы: ' . $punkt['work_time']
              . ' ' . $punkt['deliveryDays'];
      } else {
          $title = 'Пункт выдачи: ' . $punkt['brand'] . ', ' . $punkt['city'] . ', ' . ' Адрес: ' . $punkt['address']
              . ' Телефон: ' . $punkt['phone']
              . ' График работы: ' . $punkt['work_time']
              . ' ' . $punkt['deliveryDays'];
      }

      $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['title'] = $title;

exit;
  }
}
