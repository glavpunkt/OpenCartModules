<?php

/**
 * Контроллер администраторской панели доставки Главпункт
 *
 * @author SergeChepikov
 */
class ControllerShippingGlavpunkt extends Controller
{
    /** @var array Ошибки */
    private $error = array();

    /** Вывод страницы настроек модуля доставки */
    public function index()
    {
        /** Загрузка языкового файла */
        $this->language->load('shipping/glavpunkt');

        /** Установка заголовка страницы */
        $this->document->setTitle($this->language->get('heading_title'));

        /** Подгрузка настроек */
        $this->load->model('setting/setting');

        /** Обработка запроса при сохранении формы */
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            /** Сохранение настроек к БД */
            $this->model_setting_setting->editSetting('glavpunkt', $this->request->post);

            /** Вывод уведомления об удачном сохранении */
            $this->session->data['success'] = $this->language->get('text_success');

            /** Редирект на страницу списка модулей доставки */
            $this->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
        }

        /** Установка перенных для вывода на странице модуля */
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_city'] = $this->language->get('entry_city');
        $this->data['entry_post'] = $this->language->get('entry_post');
        $this->data['entry_courier'] = $this->language->get('entry_courier');
        $this->data['entry_pickup'] = $this->language->get('entry_pickup');
        $this->data['entry_paymentType'] = $this->language->get('entry_paymentType');
        $this->data['entry_paymentType_prepaid'] = $this->language->get('entry_paymentType_prepaid');
        $this->data['entry_paymentType_cash'] = $this->language->get('entry_paymentType_cash');
        $this->data['entry_paymentType_credit'] = $this->language->get('entry_paymentType_credit');
        $this->data['simple_text_enabled'] = $this->language->get('simple_text_enabled');
        $this->data['simple_text_disabled'] = $this->language->get('simple_text_disabled');
        $this->data['simple_text'] = $this->language->get('simple_text');

        /** Название модуля */
        $this->data['heading_title'] = $this->language->get('heading_title');

        /** Статус модуля (включени/выключен) */
        if (isset($this->request->post['glavpunkt_status'])) {
            $this->data['glavpunkt_status'] = $this->request->post['glavpunkt_status'];
        } else {
            $this->data['glavpunkt_status'] = $this->config->get('glavpunkt_status');
        }

        /** Статус доставки "Почта РФ"  */
        $this->data['post_status'] = isset($this->request->post['glavpunkt_post_status'])
            ? $this->request->post['glavpunkt_post_status']
            : $this->config->get('glavpunkt_post_status');

        /** Статус доставки "Курьерская доставка"  */
        $this->data['courier_status'] = isset($this->request->post['glavpunkt_courier_status'])
            ? $this->request->post['glavpunkt_courier_status']
            : $this->config->get('glavpunkt_courier_status');

        /** Статус доставки "Пункты выдачи Главпункт"  */
        $this->data['pickup_status'] = isset($this->request->post['glavpunkt_pickup_status'])
            ? $this->request->post['glavpunkt_pickup_status']
            : $this->config->get('glavpunkt_pickup_status');

        /** Настройка "город отправки" */
        $this->data['cityFrom'] = $this->config->get('glavpunkt_cityFrom')
            ? $this->config->get('glavpunkt_cityFrom')
            : '';

        /** Настройка "стандартный способ оплаты для расчёта" */
        $this->data['paymentType'] = $this->config->get('glavpunkt_paymentType')
            ? $this->config->get('glavpunkt_paymentType')
            : 'cash';

        // Настройка наличия модуля Simple
        $this->data['simple_status'] = isset($this->request->post['glavpunkt_simple_status'])
            ? $this->request->post['glavpunkt_simple_status']
            : $this->config->get('glavpunkt_simple_status');

        /** Вывод предупрждений */
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        /** URL обработки формы */
        $this->data['action'] = $this->url->link('shipping/glavpunkt', 'token=' . $this->session->data['token'], 'SSL');

        /** URL кнопки отмены */
        $this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

        /** @var string template Подключение шаблона страницы модуля */
        $this->template = 'shipping/glavpunkt.tpl';

        /** @var array children Подключение дополнительных модулей и расширений */
        $this->children = array(
            'common/header',
            'common/footer'
        );

        /** Вывод страницы модуля */
        $this->response->setOutput($this->render());
    }

    /**
     * Валидация полученных данных и прав доступа
     *
     * @return bool
     */
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'shipping/glavpunkt')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}