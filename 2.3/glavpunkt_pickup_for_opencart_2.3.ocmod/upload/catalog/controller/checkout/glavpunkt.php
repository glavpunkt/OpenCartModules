<?php
class ControllerCheckoutGlavpunkt extends Controller {
  public function index() {

  }

  public function setprice() {
      $this->language->load('shipping/glavpunkt');


      $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['title'] = $this->request->post['type'] . ' <br>'. $this->request->post['cityTo'] . ' <br>' . $this->request->post['info'] . ' <br>' . $this->request->post['phone'] . ' <br>' . $this->request->post['address']. ' <br>' . $this->request->post['work_time'];

      $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['cost'] = $this->request->post['price'];
      $this->session->data['shipping_methods']['glavpunkt']['quote']['glavpunkt']['text'] = $this->request->post['price'];


      exit;
  }
}
