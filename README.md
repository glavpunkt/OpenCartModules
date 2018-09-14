# OpenCard

Шаблон отображения службы доставки самовывоз находится по пути public_html => catalog => view => theme => default => template => checkout => simplecheckout_shipping.tpl
 
Производится вставка JS кода в html, через php, в результате символ "<" был заменен на "&lt;",
в model glavpunkt.php, добавлена проверка ввода в поле, администрирование => дополнения => доставка => бесплатная доставка => "Код JavaScript для изменения тарифа".

<?php
if (isset($shipping_method['quote'])) {
    foreach ($shipping_method['quote'] as $key => $value) {
        if (isset($value['description'])) {
            $order   = array("&nbsp;", "&lt;", "&gt;", "&amp;", "&quot;", "&apos;");
            $replace = array(" ", "<", ">", '"', "'");
            $newstr = str_replace($order, $replace, $value['description']);
            $shipping_method['quote'][$key]['description'] = $newstr;
        }
    }
}
?>

В файле public_html => catalog => model => extension => shipping => glavpunkt.php хранится класс модели, в котором происходит передача значения введенного пользователем кода (в настройках модуля), в $quote_data['glavpunkt'] и $method_data, дальше происходит вывод в template (shipping_method.tpl)

описание модификаторов в OpenCart
https://opencart2x.ru/blog/vqmod-to-ocmod
