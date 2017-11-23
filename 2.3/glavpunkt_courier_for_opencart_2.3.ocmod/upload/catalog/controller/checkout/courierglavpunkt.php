<?php
class ControllerCheckoutCourierglavpunkt extends Controller {
  public function index() {

  }

  public function setprice() {
      $this->language->load('shipping/courierglavpunkt');
      $this->session->data['shipping_methods']['courierglavpunkt']['quote']['courierglavpunkt']['title'] = $this->request->post['type'] . ' <br>' . $this->request->post['city_to'] . ' <br>' . $this->request->post['info'] . ' <br>' . $this->request->post['phone'] . ' <br>' .$this->request->post['price'].' р.'.' <br>' . $this->request->post['address']. ' <br>' . $this->request->post['work_time']. ' <br>';
      $this->session->data['shipping_methods']['courierglavpunkt']['quote']['courierglavpunkt']['cost'] = $this->request->post['price'];
      $this->session->data['shipping_methods']['courierglavpunkt']['quote']['courierglavpunkt']['text'] = $this->request->post['price'];
      $this->session->data['selected_city'] = $this->request->post['info'];
      $this->session->data['reloaded'] = true;
exit;
  }
}
