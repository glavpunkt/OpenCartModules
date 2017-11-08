<?php
class ControllerCheckoutGlavpunktCourier extends Controller {
  public function index() {

  }

  public function setprice() {
    $this->language->load('shipping/glavpunktcourier');

    $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['title'] = $this->request->post['type'] . ' <br>' . $this->request->post['info'];
    $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['cost'] = $this->request->post['price'];
    $this->session->data['shipping_methods']['glavpunktcourier']['quote']['glavpunktcourier']['text'] = $this->request->post['price'];
    $this->session->data['selected_city'] = $this->request->post['info'];
    $this->session->data['reloaded'] = true;

    exit;
  }
}