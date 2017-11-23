<?php

class ControllerExtensionShippingCourierglavpunkt extends Controller {
    private $error = array();

    private function load_language($path) {
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

    public function index() {
        $this->load_language('extension/shipping/courierglavpunkt');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('courierglavpunkt', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'], true));
        }

        $data['heading_title']       = $this->language->get('heading_title');

        $data['text_enabled']        = $this->language->get('text_enabled');
        $data['text_disabled']       = $this->language->get('text_disabled');
        $data['text_yes']            = $this->language->get('text_yes');
        $data['text_no']             = $this->language->get('text_no');
        $data['text_edit']           = $this->language->get('text_edit');


        $data['simple_text_enabled'] = $this->language->get('simple_text_enabled');
        $data['simple_text_disabled'] = $this->language->get('simple_text_disabled');


        $data['entry_status']        = $this->language->get('entry_status');
        $data['entry_sort_order']    = $this->language->get('entry_sort_order');
        $data['entry_weight']    = $this->language->get('entry_weight');

        $data['text_titlem']    = $this->language->get('text_titlem');

        $data['button_save']         = $this->language->get('button_save');
        $data['button_cancel']       = $this->language->get('button_cancel');

        $data['tab_general']         = $this->language->get('tab_general');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=shipping', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping/courierglavpunkt', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = HTTPS_SERVER . 'index.php?route=extension/shipping/courierglavpunkt&token=' . $this->session->data['token'];

        $data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/shipping&token=' . $this->session->data['token'];

    
        if (isset($this->request->post['courierglavpunkt_status'])) {
            $data['courierglavpunkt_status'] = $this->request->post['courierglavpunkt_status'];
        } else {
            $data['courierglavpunkt_status'] = $this->config->get('courierglavpunkt_status');
        }

        if (isset($this->request->post['courierglavpunkt_simple_status'])) {
            $data['courierglavpunkt_simple_status'] = $this->request->post['courierglavpunkt_simple_status'];
        } else {
            $data['courierglavpunkt_simple_status'] = $this->config->get('courierglavpunkt_simple_status');
        }

        if (isset($this->request->post['courierglavpunkt_sort_order'])) {
            $data['courierglavpunkt_sort_order'] = $this->request->post['courierglavpunkt_sort_order'];
        } else {
            $data['courierglavpunkt_sort_order'] = $this->config->get('courierglavpunkt_sort_order');
        }

        if (isset($this->request->post['courierglavpunkt_home_city'])) {
            $data['courierglavpunkt_home_city'] = $this->request->post['courierglavpunkt_home_city'];
        } else {
            $data['courierglavpunkt_home_city'] = $this->config->get('courierglavpunkt_home_city');
        }
        if (isset($this->request->post['courierglavpunkt_weight'])) {
            $data['courierglavpunkt_weight'] = $this->request->post['courierglavpunkt_weight'];
        } else {
            $data['courierglavpunkt_weight'] = $this->config->get('courierglavpunkt_weight');
        }
        if (isset($this->request->post['courierglavpunkt_tarif_edit_code'])) {
            $data['courierglavpunkt_tarif_edit_code'] = $this->request->post['courierglavpunkt_tarif_edit_code'];
        } else {
            $data['courierglavpunkt_tarif_edit_code'] = $this->config->get('courierglavpunkt_tarif_edit_code');
        }

        if (isset($this->request->post['courierglavpunkt_widget_data'])) {
            $data['courierglavpunkt_widget_data'] = $this->request->post['courierglavpunkt_widget_data'];
        } else {
            $data['courierglavpunkt_widget_data'] = $this->config->get('courierglavpunkt_widget_data');
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/courierglavpunkt', $data));
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/shipping/courierglavpunkt')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}