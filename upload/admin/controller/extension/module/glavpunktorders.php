<?php

/**
 * Контроллер модуля "Выгрузка заказов в Главпункт"
 *
 * тут мы выводим список заказов, настройки взаимодействия с API Главпункт
 * обработку отправленных данных и вывод номера сформированной накладной
 *
 * Class ControllerModuleGlavpunktorders
 * @author SergeChepikov
 */
class ControllerExtensionModuleGlavpunktorders extends Controller
{
    /** @var array массив возникаемых ошибок */
    private $error = [];
    /** @var array массив переменных выводимых в представлении */
    private $data = [];
    /** @var bool для проверки HTTPS соединения */
    private $isHttps;

    /**
     * Метод исполняемый при вызове данного контроллера
     */
    public function index()
    {
        //Проверка на HTTPS или HTTP
        if (isset($_SERVER['HTTPS'])) {
            $this->isHttps = true;
        } else {
            $this->isHttps = false;
        }

        ini_set('display_errors', 'On');
        error_reporting(E_ALL);

        // Подключение языкового файла
        $this->load->language('extension/module/glavpunktorders');
        $this->load->language('extension/shipping/glavpunkt');
        // Подключение настроек
        $this->load->model('setting/setting');
        // Установка заголовка страницы
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        // Получение списка пунктов выдачи
        $this->data['pvz'] = $this->getPVZ();
        // Получение списка пунктов выдачи с приёмкой
        $this->data['priem_pvz'] = $this->getPriemkaPVZ();
        // При отправке данных псоредством POST запроса, проверяем данные и сохраняем настройки
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            // если есть выбранные заказы для отправки
            if (isset($this->request->post['selected']) && count($this->request->post['selected'])) {
                $this->load->model('sale/order');
                // данный массив будет содержать список всех заказов для передачи в Главпункт
                $orderListToGP = [];
                $this->data['fullListPVZ'] = $this->getPVZfromRussia();
                $this->data['fullListPVZ'] = array_merge($this->data['pvz'], $this->data['fullListPVZ']);
                // получаем детально о каждом заказе
                foreach ($this->request->post['selected'] as $orderId) {
                    $order_info = $this->model_sale_order->getOrder($orderId);
                    $products = $this->model_sale_order->getOrderProducts($orderId);
                    if ($order_info['shipping_code'] === 'glavpunkt.glavpunkt') {
                        // тут выполняется поиск нужного нам пункта выдачи
                        $findId['id,cityId'] = $this->findPoint($order_info['shipping_method']);
                        $orderListToGP[] = $this->ComposeOrder($order_info, $products, $findId['id, cityId']);
                    } else {
                        $orderListToGP[] = $this->ComposeOrder($order_info, $products);
                    }
                }
                // заполняем основную информация
                $invoiceInfo = [
                    // логин интернет-магазина
                    'login' => $this->config->get('module_glavpunktorders_login'),
                    // token для авторизации
                    'token' => $this->config->get('module_glavpunktorders_token'),
                    // список заказов
                    'orders' => $orderListToGP,
                    // Пункт отгрузки заказов, если вы сами привозите их на ПВЗ
                    'punkt_id' => $this->request->post['punkt_id'],
                    // комментарий к накладной
                    'comments_client' => $this->request->post['comments_client'],
                    // Если нужен забор заказов, передайте в этом поле 1 (Отменяет параметр punkt_id!)
                    'pickup_needed' => (isset($this->request->post['pickup_needed']) ? 1 : 0)
                ];
                // если поставлен чекбокс на "нужен забор заказов" то добавляем следующий массив
                if (isset($this->request->post['pickup_needed'])) {
                    $invoiceInfo['pickup_params'] = [
                        // Дата забора "2017-09-22"
                        'date' => $this->request->post['date'],
                        // Интервал забора "10:00-18:00"
                        'interval' => $this->request->post['interval'],
                        // Адрес забора "Алтайская д18, кв99"
                        'address' => $this->request->post['address'],
                        // Рекомендуем указывать здесь контактный телефон и другую полезную информацию
                        'comment' => $this->request->post['comment'],
                        // Город забора. Доступные значения:  SPB/MSK Внимание!
                        // Если город не указан, используется значение по-умолчанию: SPB
                        'city' => 'SPB'
                    ];
                }
                // отправляем данные в Главпункт и получаем идентификатор накладной
                $answer = json_decode($this->CreateInvoice($invoiceInfo), true);
                if ($answer['result'] === 'ok') {
                    $this->session->data['invoice_id'] = $answer['docnum'];
                } elseif ($answer['result'] === 'error') {
                    $this->session->data['error'][] = $answer['message'];
                }
            }
            $this->model_setting_setting->editSetting('module_glavpunktorders', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect(
                $this->url->link('extension/module/glavpunktorders', 'user_token=' . $this->session->data['user_token'], $this->isHttps)
            );
        }
        // Вывод настроек магазина для взаимодействия с Гдавпункт и подписей к форме
        $this->data['form_login_title'] = $this->language->get('form_login_title');
        $this->data['form_login_placeholder'] = $this->language->get('form_login_placeholder');
        $this->data['form_login_value'] = $this->config->get('module_glavpunktorders_login') !== ''
            ? $this->config->get('module_glavpunktorders_login')
            : '';
        $this->data['form_token_title'] = $this->language->get('form_token_title');
        $this->data['form_token_placeholder'] = $this->language->get('form_token_placeholder');
        $this->data['form_token_value'] = $this->config->get('module_glavpunktorders_token') !== ''
            ? $this->config->get('module_glavpunktorders_token')
            : '';
        $this->data['form_punkt_id_title'] = $this->language->get('form_punkt_id_title');
        $this->data['form_comments_client_title'] = $this->language->get('form_comments_client_title');
        $this->data['form_comments_client_placeholder'] = $this->language->get('form_comments_client_placeholder');
        $this->data['form_pickup_needed_title'] = $this->language->get('form_pickup_needed_title');
        $this->data['form_date_title'] = $this->language->get('form_date_title');
        $this->data['form_interval_title'] = $this->language->get('form_interval_title');
        $this->data['form_address_title'] = $this->language->get('form_address_title');
        $this->data['form_comment_title'] = $this->language->get('form_comment_title');
        $this->data['order_list_text'] = $this->language->get('order_list_text');
        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['cancel'] = $this->url->link(
            'extension/module',
            'user_token=' . $this->session->data['user_token'],
            $this->isHttps
        );
        $this->data['action'] = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . '&type=module',
            $this->isHttps
        );
        // Вывод списка заказов
        $this->getOrdersList();
        $this->data['notifications'] = [];
        // вывод сообщения об успешном сохранении модуля
        if (isset($this->session->data['success'])) {
            $this->data['notifications'][] = [
                'text' => $this->session->data['success'],
                'type' => 'info'
            ];
            unset($this->session->data['success']);
        }
        // Вывод номера накладной при его создании
        if (isset($this->session->data['invoice_id'])) {
            $this->data['notifications'][] = [
                'text' => $this->language->get('new_invoice_text') . $this->session->data['invoice_id'],
                'type' => 'info'
            ];
            unset($this->session->data['invoice_id']);
        }
        // при наличии ошибок выводим их
        if (isset($this->session->data['error']) && count($this->session->data['error']) > 0) {
            foreach ($this->session->data['error'] as $error) {
                $this->data['notifications'][] = [
                    'text' => $error,
                    'type' => 'danger'
                ];
            }
            unset($this->session->data['error']);
        }
        // При наличии ошибок добавляем их для вывода
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }
        // Вывод хлебных крошек
        $this->data['breadcrumbs'] = [];
        $this->data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], $this->isHttps)
        ];
        $this->data['breadcrumbs'][] = [
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'] . '&type=module', $this->isHttps)
        ];
        $this->data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/glavpunktorders', 'user_token=' . $this->session->data['user_token'], $this->isHttps)
        ];
        // Подключение обязательных блоков для страницы
        $this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');
        // Вывод модуля на страницу
        $this->response->setOutput($this->load->view('extension/module/glavpunktorders', $this->data));
    }

    /**
     * Функция валидации передаваемых параметров
     *
     * @return bool
     */
    protected function validate()
    {
        return !$this->error;
    }

    /**
     * Функция вывода списка заказов
     *
     * полная копия, за исключением убранных столбцов с модуля sale/order
     * расположен /admin/controller/sale/order.php
     */
    private function getOrdersList()
    {
        // Подключение модели заказа
        $this->load->model('sale/order');
        // Инициализация фильтров для списка заказов
        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = null;
        }
        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = null;
        }
        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = null;
        }
        if (isset($this->request->get['filter_total'])) {
            $filter_total = $this->request->get['filter_total'];
        } else {
            $filter_total = null;
        }
        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = null;
        }
        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = null;
        }
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'o.order_id';
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        // Формирование url данной страницы, исходя из примененных фильтров
        $url = '';
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' .
                urlencode(
                    html_entity_decode(
                        $this->request->get['filter_customer'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
        }
        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }
        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        $this->data['orders'] = array();
        // заполнения массива фильтра
        $filter_data = array(
            'filter_order_id' => $filter_order_id,
            'filter_customer' => $filter_customer,
            'filter_order_status' => $filter_order_status,
            'filter_total' => $filter_total,
            'filter_date_added' => $filter_date_added,
            'filter_date_modified' => $filter_date_modified,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );
        // получение количества заказов по данному фильтру
        $order_total = $this->model_sale_order->getTotalOrders($filter_data);
        // получение списка заказов
        $results = $this->model_sale_order->getOrders($filter_data);
        // обработка списка заказов
        foreach ($results as $result) {
            // по коду доставки определяем записываем строку, которая будет выводится в таблице
            switch ($result['shipping_code']) {
                case "glavpunkt.glavpunkt":
                    $shippingTitle = 'Пункты выдачи Главпункт';
                    break;
                case "glavpunktcourier.glavpunktcourier":
                    $shippingTitle = 'Курьерская доставка Главпункт';
                    break;
                case "glavpunktpochta.glavpunktpochta":
                    $shippingTitle = 'Главпункт доставка Почта РФ';
                    break;
                default:
                    $shippingTitle = 'Доставка не Главпункт';
                    break;
            }
            // переопредение списка заказов по нужным полям
            $this->data['orders'][] = array(
                'order_id' => $result['order_id'],
                'customer' => $result['customer'],
                'status' => $result['order_status'],
                'total' => $this->currency->format(
                    $result['total'],
                    $result['currency_code'],
                    $result['currency_value']
                ),
                'date_added' => date(
                    $this->language->get('date_format_short'),
                    strtotime($result['date_added'])
                ),
                'date_modified' => date(
                    $this->language->get('date_format_short'),
                    strtotime($result['date_modified'])
                ),
                'shipping_code' => $result['shipping_code'],
                'shipping' => $shippingTitle,
                'view' => $this->url->link(
                    'sale/order/info',
                    'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url,
                    $this->isHttps
                ),
                'edit' => $this->url->link(
                    'sale/order/edit',
                    'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url,
                    $this->isHttps
                ),
            );
        }
        // опереденение выделеных заказов, для их повторного выделения при перезагрузке страницы
        // при применении фильтрации
        if (isset($this->request->post['selected'])) {
            $this->data['selected'] = (array)$this->request->post['selected'];
        } else {
            $this->data['selected'] = [];
        }
        // повторное формирование url исходя из выбранных параметров для сортировок
        // P.S. так работает в стандартном выводе
        $url = '';
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' .
                urlencode(
                    html_entity_decode(
                        $this->request->get['filter_customer'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
        }
        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }
        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        // формирование url для кнопок различных сортировок
        $this->data['sort_order'] = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url,
            $this->isHttps
        );
        $this->data['sort_customer'] = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url,
            $this->isHttps
        );
        $this->data['sort_status'] = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url,
            $this->isHttps
        );
        $this->data['sort_total'] = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . '&sort=o.total' . $url,
            $this->isHttps
        );
        $this->data['sort_date_added'] = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . '&sort=o.date_added' . $url,
            $this->isHttps
        );
        $this->data['sort_date_modified'] = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . '&sort=o.date_modified' . $url,
            $this->isHttps
        );
        // формирование url исходя из переданных параметров для кнопок пагинации
        $url = '';
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' .
                urlencode(
                    html_entity_decode(
                        $this->request->get['filter_customer'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
        }
        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }
        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        // инициализация пагинации
        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link(
            'extension/module/glavpunktorders',
            'user_token=' . $this->session->data['user_token'] . $url . '&page={page}',
            $this->isHttps
        );
        // определение пагинации для вывода на страницу
        $this->data['pagination'] = $pagination->render();
        // формирование строки "Показано с 1 по 20 из 61 (всего 4 страниц)"
        $this->data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0,
            (
                (
                    ($page - 1) * $this->config->get('config_limit_admin')
                ) > ($order_total - $this->config->get('config_limit_admin'))
            )
                ? $order_total
                : (
                (
                    ($page - 1) * $this->config->get('config_limit_admin')
                ) + $this->config->get('config_limit_admin')
            ),
            $order_total,
            ceil($order_total / $this->config->get('config_limit_admin'))
        );
        // вывод фильтров на странице
        $this->data['filter_order_id'] = $filter_order_id;
        $this->data['filter_customer'] = $filter_customer;
        $this->data['filter_order_status'] = $filter_order_status;
        $this->data['filter_total'] = $filter_total;
        $this->data['filter_date_added'] = $filter_date_added;
        $this->data['filter_date_modified'] = $filter_date_modified;
        // вывод сортировок на странице
        $this->data['sort'] = $sort;
        $this->data['order'] = $order;
        // вывод названий столбцов
        $this->data['column_order_id'] = $this->language->get('column_order_id');
        $this->data['column_customer'] = $this->language->get('column_customer');
        $this->data['column_status'] = $this->language->get('column_status');
        $this->data['column_total'] = $this->language->get('column_total');
        $this->data['column_date_added'] = $this->language->get('column_date_added');
        $this->data['column_date_modified'] = $this->language->get('column_date_modified');
    }

    /**
     * Получение списка пунктов выдачи посредством cURL запроса по СПб и Москве
     *
     * для выбора пункта отгрузки заказа/заказов
     *
     * @link https://glavpunkt.ru/apidoc/dictionary.html#get-api-punkts-from-spb
     * @return mixed
     */
    private function getPriemkaPVZ()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://glavpunkt.ru/api/punkts/priemka'
        ]);
        $answer = curl_exec($curl);
        curl_close($curl);

        return json_decode($answer, true);
    }

    /**
     * Получение списка пунктов выдачи посредством cURL запроса по СПб и Москве
     *
     * для опредение пункта выдачи по имеющейся информации в заказе
     * (заказ не сохраняет идентификатор выбранного пункта выдачи)
     *
     * @link https://glavpunkt.ru/apidoc/dictionary.html#get-api-punkts-from-spb
     * @return mixed
     */
    private function getPVZ()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://glavpunkt.ru/api/punkts'
        ]);
        $answer = curl_exec($curl);
        curl_close($curl);

        return json_decode($answer, true);
    }

    /**
     * Получение списка пунктов выдачи по России
     *
     * для опредение пункта выдачи по имеющейся информации в заказе
     * (заказ не сохраняет идентификатор выбранного пункта выдачи)
     *
     * @link https://glavpunkt.ru/apidoc/dictionary.html#pvzs-rf
     * @return mixed
     */
    private function getPVZfromRussia()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://glavpunkt.ru/punkts-rf.json'
        ]);
        $answer = curl_exec($curl);
        curl_close($curl);

        return json_decode($answer, true);
    }

    /**
     * Поиск пункта выдачи по переданному тексту
     *
     * заказ не сохраняет идентификатор пункта выдачи, поэтому мы разбиваем имеющееся поле на данные
     * и сравниваем их со списком
     *
     * @param string|bool $text
     */
    private function findPoint($text)
    {
        // разбиваем текст на поля по тегу <br>
        preg_match_all('/([^<br>]+)/', $text, $params);
        // если количество полей недостаточно, значит информация не полноценна
        // и данное поле мы рассматривать не можем
        if (count($params[0]) < 6) {
            return false;
        }
        // поле выбранного города
        $city = trim($params[0][1]);
        // если это СПб или МСК, то есть поле метро, которое немного сбивает порядок
        if ($city === 'Санкт-Петербург' || $city === 'Москва') {
            $phone = trim($params[0][3]);
            $address = trim($params[0][5]);
        } else {
            $phone = trim($params[0][2]);
            $address = trim($params[0][4]);
        }
        // далее мы идём по полному списку пунктов выдачи (по СПб,МСК и России) и находим подходящее
        foreach ($this->data['fullListPVZ'] as $point) {
            // @todo определить кол-во символов в адресе и сравить по кол-ву
            $addressLength = strlen($address);
            if (
                $city === trim($point['city']) &&
                $phone === trim($point['phone']) &&
                substr($address, 0, $addressLength) === substr(trim($point['address']), 0, $addressLength)
                // данное поле сравнивается по количеству символов в адресе хранимом в заказе,
                // т.к. в заказе есть возможность обрезания данной строки
                // и мы сравниваем исключительно по имеющемуся
            ) {
                return $punktAndCity = ['id' => $point['id'],
                    'cityId' => $point['cityId']];
            }
        }

        // если в ходе перебора найти не удалось, то возвращаем false
        return false;
    }

    /**
     * Компоновка массива заказа под вид вгрузки в Главпункт
     *
     * @link https://glavpunkt.ru/apidoc/takepkgs.html#id2
     * @param $info
     * @param $items
     * @param int $punktId
     * @return array
     */
    private function ComposeOrder($info, $items, $punktId = null)
    {
        $parts = [];
        // получаем номенклатуру заказа
        foreach ($items as $item) {
            $parts[] = [
                'name' => $item['name'] . " " . $item['model'],
                'price' => $item['total'],
                'barcode' => '',
                'num' => $item['quantity']

            ];
        }
        // получаем общие параметры заказа
        $thisOrder = [
            'sku' => $info['order_id'],
            'price' => $info['total'],
            'client_delivery_price' => "",
            'comment' => $info['comment'],
            'buyer_fio' => $info['shipping_firstname'] . " " . $info['shipping_lastname'],
            'buyer_phone' => $info['telephone'],
            'is_prepaid' => 0,
            'weight' => 1,
            'parts' => $parts
        ];
        // тут исходя из кода доставки мы заполняем нужные поля
        if ($info['shipping_code'] === 'glavpunktcourier.glavpunktcourier') {
            $delivery_date = '';
            $delivery_time = '';
            // Выполнение условия если выбрана доставка "Курьерская доставка"
            if (strlen($info['comment']) > 0) {
                // если у нас стоит модуль Simple то у нас идёт добавление данных через комментарии
                preg_match_all(
                    '/Дата доставки: (\d{2}.\d{2}.\d{4})/',
                    $info['comment'],
                    $dateMatch
                );
                preg_match_all(
                    '/Время доставки: (\d{2}:\d{2} - \d{2}:\d{2})/',
                    $info['comment'],
                    $timeMatch
                );
                if (isset($dateMatch[1][0])) {
                    $delivery_date = $dateMatch[1][0];
                }
                if (isset($timeMatch[1][0])) {
                    $delivery_time = $timeMatch[1][0];
                }
            } else {
                // иначе смотрим по полю комментарии
                preg_match_all(
                    '/Дата доставки: (\d{2}.\d{2}.\d{4})/',
                    $info['shipping_method'],
                    $dateMatch
                );
                preg_match_all(
                    '/Время доставки: (\d{2}:\d{2} - \d{2}:\d{2})/',
                    $info['shipping_method'],
                    $timeMatch
                );
                if (isset($dateMatch[1][0])) {
                    $delivery_date = $dateMatch[1][0];
                }
                if (isset($timeMatch[1][0])) {
                    $delivery_time = $timeMatch[1][0];
                }
            }
            $thisOrder['serv'] = 'курьерская доставка';
            $thisOrder['delivery'] = [
                'city' => $info['shipping_city'],
                'date' => $delivery_date, // delivery_date
                'time' => $delivery_time, // delivery_from_hour , delivery_to_hour
                'address' => $info['shipping_zone_code'] . " " . $info['shipping_address_1'] . " " . $info['shipping_address_2']
            ];
        }
        if ($info['shipping_code'] === 'glavpunktpochta.glavpunktpochta') {
            // Выполнение условия если выбрана доставка "Почта РФ"
            $thisOrder['serv'] = 'почта';
            $thisOrder['pochta'] = [
                'address' => $info['shipping_zone'] . " Россия," . $info['shipping_address_1'] . " " . $info['shipping_address_2']
            ];
        }
        if ($info['shipping_code'] === 'glavpunkt.glavpunkt' && $punktId['id'] !== null) {

            if ($punktId['cityId'] !== "SPB" && $punktId['cityId'] !== "MSK") {
                // Выполнение условия если выбрана доставка "выдача по РФ"
                $thisOrder['serv'] = 'выдача по РФ';
                $thisOrder['delivery_rf'] = ['pvz_id' => $punktId['id'],
                    'city_id' => $punktId['cityId']];
            } else {
                // Выполнение условия если выбрана доставка "выдача"
                $thisOrder['serv'] = 'выдача';
                $thisOrder['dst_punkt_id'] = $punktId['id'];

            }
        }


        // возвращаем массив с параметрами данного заказа для вгрузки в Главпункт
        return $thisOrder;
    }

    /**
     * Мы отправляем заказы в главпункт и получаем либо ошибку, либо идентификатор созданной накладной
     *
     * @param array $request
     * @return mixed
     */
    private function CreateInvoice($request)
    {
        $requestJson = json_encode($request);
        $curl = curl_init('http://glavpunkt.ru/api/take_pkgs');
        curl_setopt($curl, CURLOPT_USERAGENT, "opencart-3");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestJson);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($requestJson))
        );
        $answer = curl_exec($curl);
        curl_close($curl);

        return $answer;
    }
}
