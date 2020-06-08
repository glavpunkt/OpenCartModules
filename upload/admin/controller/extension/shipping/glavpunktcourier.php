<?php

/**
 * Контроллер модуля доставки "Курьерская доставка Главпункт" в панели адмиинистратора
 *
 * Отвчает за вывод и изменение основных настроек модуля
 *
 * Class ControllerExtensionShippingGlavpunktcourier
 * @author SergeChepikov
 */
class ControllerExtensionShippingGlavpunktcourier extends Controller
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
        $this->load_language('extension/shipping/glavpunktcourier');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('shipping_glavpunktcourier', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' .
                $this->session->data['user_token'] . '&type=shipping', $this->isHttps()));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
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
                'extension/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=shipping',
                $this->isHttps()
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'extension/shipping/glavpunktcourier',
                'user_token=' . $this->session->data['user_token'],
                $this->isHttps()
            )
        );

        $data['action'] = HTTPS_SERVER .
            'index.php?route=extension/shipping/glavpunktcourier&user_token=' .
            $this->session->data['user_token'] . '&type=shipping';

        $data['cancel'] = HTTPS_SERVER .
            'index.php?route=extension/shipping&user_token=' .
            $this->session->data['user_token'] . '&type=shipping';


        if (isset($this->request->post['shipping_glavpunktcourier_home_city'])) {
            $data['glavpunktcourier_home_city'] = $this->request->post['shipping_glavpunktcourier_home_city'];
        } else {
            $data['glavpunktcourier_home_city'] = $this->config->get('shipping_glavpunktcourier_home_city');
        }

        if (isset($this->request->post['shipping_glavpunktcourier_weight'])) {
            $data['glavpunktcourier_weight'] = $this->request->post['shipping_glavpunktcourier_weight'];
        } else {
            $data['glavpunktcourier_weight'] = $this->config->get('shipping_glavpunktcourier_weight');
        }


        if (isset($this->request->post['shipping_glavpunktcourier_status'])) {
            $data['glavpunktcourier_status'] = $this->request->post['shipping_glavpunktcourier_status'];
        } else {
            $data['glavpunktcourier_status'] = $this->config->get('shipping_glavpunktcourier_status');
        }

        if (isset($this->request->post['shipping_glavpunktcourier_sort_order'])) {
            $data['glavpunktcourier_sort_order'] = $this->request->post['shipping_glavpunktcourier_sort_order'];
        } else {
            $data['glavpunktcourier_sort_order'] = $this->config->get('shipping_glavpunktcourier_sort_order');
        }
        if (isset($this->request->post['shipping_glavpunktcourier_tarif_edit_code'])) {
            $data['glavpunktcourier_tarif_edit_code'] = $this->request->post['shipping_glavpunktcourier_tarif_edit_code'];
        } else {
            $data['glavpunktcourier_tarif_edit_code'] = $this->config->get('shipping_glavpunktcourier_tarif_edit_code');
        }
        
        if (isset($this->request->post['shipping_glavpunktcourier_days'])) {
            $data['glavpunktcourier_days'] = $this->request->post['shipping_glavpunktcourier_days'];
        } else {
            $data['glavpunktcourier_days'] = $this->config->get('shipping_glavpunktcourier_days');
        }

        if (isset($this->request->post['shipping_glavpunktcourier_hidedate'])) {
            $data['glavpunktcourier_hidedate'] = $this->request->post['shipping_glavpunktcourier_hidedate'];
        } else {
            $data['glavpunktcourier_hidedate'] = $this->config->get('shipping_glavpunktcourier_hidedate');
        }

        if (isset($this->request->post['shipping_glavpunktcourier_price_spb'])) {
            $data['glavpunktcourier_price_spb'] = $this->request->post['shipping_glavpunktcourier_price_spb'];
        } else {
            $data['glavpunktcourier_price_spb'] = $this->config->get('shipping_glavpunktcourier_price_spb');
        }

        if (isset($this->request->post['shipping_glavpunktcourier_price_msk'])) {
            $data['glavpunktcourier_price_msk'] = $this->request->post['shipping_glavpunktcourier_price_msk'];
        } else {
            $data['glavpunktcourier_price_msk'] = $this->config->get('shipping_glavpunktcourier_price_msk');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/glavpunktcourier', $data));

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
