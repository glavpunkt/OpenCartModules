<?php

/**
 * Контролер слуюбы доставки "Главпункт Служба РФ" в администраторской панели
 *
 * Class ControllerExtensionShippingGlavpunktpochta
 */
class ControllerExtensionShippingGlavpunktpochta extends Controller
{
    private $error = array();

    private function load_language($path)
    {
        $language = $this->language;
        if (isset($language) && method_exists($language, 'load')) {
            $this->language->load($path);
            unset($language);

            return;
        }
        $load = $this->load;
        if (isset($load) && method_exists($load, 'language')) {
            $this->load->language($path);
            unset($load);

            return;
        }
    }
    private function isHttps()
    {
        if (isset($_SERVER['HTTPS'])) {
            return true;
        }
        return false;
    }

    public function index()
    {
        $this->load_language('extension/shipping/glavpunktpochta');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('shipping_glavpunktpochta', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' .
                $this->session->data['user_token'] . '&type=shipping', $this->isHttps()));
        }
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['simple_text_enabled'] = $this->language->get('simple_text_enabled');
        $data['simple_text_disabled'] = $this->language->get('simple_text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['entry_from_city_code_id'] = $this->language->get('entry_from_city_code_id');
        $data['entry_weight'] = $this->language->get('entry_weight');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['text_titlem'] = $this->language->get('text_titlem');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['tab_general'] = $this->language->get('tab_general');
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], $this->isHttps())
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'extension/shipping',
                'user_token=' . $this->session->data['user_token'] . '&type=shipping',
                $this->isHttps()
            )
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping/glavpunktpochta' . '&type=shipping', 'user_token=' . $this->session->data['user_token'], $this->isHttps())
        );
        $data['action'] = HTTPS_SERVER .
            'index.php?route=extension/shipping/glavpunktpochta&user_token=' .
            $this->session->data['user_token'] . '&type=shipping';
        $data['cancel'] = HTTPS_SERVER .
            'index.php?route=extension/shipping&user_token=' .
            $this->session->data['user_token'];
        if (isset($this->request->post['shipping_glavpunktpochta_home_city'])) {
            $data['glavpunktpochta_home_city'] = $this->request->post['shipping_glavpunktpochta_home_city'];
        } else {
            $data['glavpunktpochta_home_city'] = $this->config->get('shipping_glavpunktpochta_home_city');
        }
        if (isset($this->request->post['shipping_glavpunktpochta_weight'])) {
            $data['glavpunktpochta_weight'] = $this->request->post['shipping_glavpunktpochta_weight'];
        } else {
            $data['glavpunktpochta_weight'] = $this->config->get('shipping_glavpunktpochta_weight');
        }
        if (isset($this->request->post['shipping_glavpunktpochta_status'])) {
            $data['glavpunktpochta_status'] = $this->request->post['shipping_glavpunktpochta_status'];
        } else {
            $data['glavpunktpochta_status'] = $this->config->get('shipping_glavpunktpochta_status');
        }
        if (isset($this->request->post['shipping_glavpunktpochta_simple_status'])) {
            $data['glavpunktpochta_simple_status'] = $this->request->post['shipping_glavpunktpochta_simple_status'];
        } else {
            $data['glavpunktpochta_simple_status'] = $this->config->get('shipping_glavpunktpochta_simple_status');
        }
        if (isset($this->request->post['shipping_glavpunktpochta_sort_order'])) {
            $data['glavpunktpochta_sort_order'] = $this->request->post['shipping_glavpunktpochta_sort_order'];
        } else {
            $data['glavpunktpochta_sort_order'] = $this->config->get('shipping_glavpunktpochta_sort_order');
        }
        if (isset($this->request->post['shipping_glavpunktpochta_tarif_edit_code'])) {
            $data['glavpunktpochta_tarif_edit_code'] = $this->request->post['shipping_glavpunktpochta_tarif_edit_code'];
        } else {
            $data['glavpunktpochta_tarif_edit_code'] = $this->config->get('shipping_glavpunktpochta_tarif_edit_code');
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/shipping/glavpunktpochta', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/shipping/glavpunktcourier')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}