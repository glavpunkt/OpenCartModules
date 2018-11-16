<?php


class ControllerExtensionShippingGlavpunkt extends Controller
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

    public function index()
    {
        $this->load_language('extension/shipping/glavpunkt');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('shipping_glavpunkt', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' .
                $this->session->data['user_token'] . '&type=shipping', true));
        }


        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_edit'] = $this->language->get('text_edit');

        // post glavpunktpoints_simple_status
        $data['simple_text_enabled'] = $this->language->get('simple_text_enabled');
        $data['simple_text_disabled'] = $this->language->get('simple_text_disabled');

        // post glavpunktpoints_payment_type
        $data['count_with_rko_text'] = $this->language->get('count_with_rko_text');
        $data['count_without_rko_text'] = $this->language->get('count_without_rko_text');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_weight'] = $this->language->get('entry_weight');

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
            'href' => $this->url->link(
                'common/dashboard',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=shipping' . '&type=shipping',
                true
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'extension/shipping/glavpunkt',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );

        $data['action'] = HTTPS_SERVER .
            'index.php?route=extension/shipping/glavpunkt&user_token=' .
            $this->session->data['user_token'];

        $data['cancel'] = HTTPS_SERVER .
            'index.php?route=extension/shipping&user_token=' .
            $this->session->data['user_token'];


        if (isset($this->request->post['shipping_glavpunkt_status'])) {
            $data['glavpunkt_status'] = $this->request->post['shipping_glavpunkt_status'];
        } else {
            $data['glavpunkt_status'] = $this->config->get('shipping_glavpunkt_status');
        }

        if (isset($this->request->post['shipping_glavpunkt_simple_status'])) {
            $data['glavpunktpoints_simple_status'] = $this->request->post['shipping_glavpunkt_simple_status'];
        } else {
            $data['glavpunktpoints_simple_status'] = $this->config->get('shipping_glavpunkt_simple_status');
        }

        if (isset($this->request->post['shipping_glavpunkt_payment_type'])) {
            $data['glavpunktpoints_payment_type'] = $this->request->post['shipping_glavpunkt_payment_type'];
        } else {
            $data['glavpunktpoints_payment_type'] = $this->config->get('shipping_glavpunkt_payment_type');
        }

        if (isset($this->request->post['shipping_glavpunkt_sort_order'])) {
            $data['glavpunkt_sort_order'] = $this->request->post['shipping_glavpunkt_sort_order'];
        } else {
            $data['glavpunkt_sort_order'] = $this->config->get('shipping_glavpunkt_sort_order');
        }

        if (isset($this->request->post['shipping_glavpunkt_home_city'])) {
            $data['glavpunkt_home_city'] = $this->request->post['shipping_glavpunkt_home_city'];
        } else {
            $data['glavpunkt_home_city'] = $this->config->get('shipping_glavpunkt_home_city');
        }

        if (isset($this->request->post['shipping_glavpunkt_weight'])) {
            $data['glavpunkt_weight'] = $this->request->post['shipping_glavpunkt_weight'];
        } else {
            $data['glavpunkt_weight'] = $this->config->get('shipping_glavpunkt_weight');
        }
        if (isset($this->request->post['shipping_glavpunkt_tarif_edit_code'])) {
            $data['glavpunkt_tarif_edit_code'] = $this->request->post['shipping_glavpunkt_tarif_edit_code'];
        } else {
            $data['glavpunkt_tarif_edit_code'] = $this->config->get('shipping_glavpunkt_tarif_edit_code');
        }

        if (isset($this->request->post['shipping_glavpunkt_widget_data'])) {
            $data['glavpunkt_widget_data'] = $this->request->post['shipping_glavpunkt_widget_data'];
        } else {
            $data['glavpunkt_widget_data'] = $this->config->get('shipping_glavpunkt_widget_data');
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/glavpunkt', $data));
    }

    private function validate()
    {
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}