@author  glavpunkt.ru
@link    http://glavpunkt.ru/
@date    2017-10-19
@version 1.1
Требования:

Версия опенкарта: 2.1
для использования модуля вы должны быть клиентом Главпункта

Установка:

1) Скопируйте файлы модуля из папки /upload на Ваш сервер в корень сайта
	(Подробнее)
		В папке upload содержатся данные файлы - необходимо разместить их в соотвествующих директориях в корне вашего интренет магазина (./ - Корень сайта)
		./admin/language/ru-ru/extension/shipping/glavpunkt.php
		./admin/language/en-gb/extension/shipping/glavpunkt.php
		./admin/view/template/extension/shipping/glavpunkt.tpl
		./admin/controller/extension/shipping/glavpunkt.php
		./catalog/language/ru-ru/extension/shipping/glavpunkt.php
		./catalog/language/en-gb/extension/shipping/glavpunkt.php
		./catalog/model/extension/shipping/glavpunkt.php
		./catalog/controller/checkout/glavpunkt.php

   или с помощью "Установки расширений" 

2) Установите следующие модули
	Модули / Расширения
	Выбрать "Доствка"
	Активируйте модуль: Главпункт - доставка в пункты самовывоза

3) Настройте модуль в соотвествии с инструкцией размещенной на странице загрузки данного модуля.

4) 	В файл шаблона catalog/view/theme/default/template/checkout/cart.tpl
	и в файл шаблона catalog/view/theme/default/template/checkout/checkout.tpl
   	в конце файлов между "</div> или </script> в случае с файлом checkout.tpl" и "<?php echo $footer; ?>" Добавить строки:

	<script type="text/javascript" src="//glavpunkt.ru/js/punkts-widget/glavpunkt.js"> </script>
	<style> .glavpunkt_container{z-index:2000;}</style>