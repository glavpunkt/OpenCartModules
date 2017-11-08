<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-auspost" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Настройки</h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-auspost" class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label" >Ваш город:</label>
            <div class="col-sm-10">
              <input type="text" name="glavpunkt_home_city" value="<?php echo $glavpunkt_home_city; ?>" />
            </div>
          <div class="col-sm-10">Введите в данное поле город из котрого будут отправлятся заказы на доставку (Санкт-Петербург или Москва по умолчанию - Санкт-Петербург).</div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label" >Вес по умолчанию:</label>
          <div class="col-sm-10">
            <input type="text" name="glavpunkt_weight" value="<?php echo $glavpunkt_weight; ?>" size="10" />
          </div>
          <div class="col-sm-10">В данное поле введите вес присваеваемый заказу если он не указан в товаре.</div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label" >Номер для сортировки:</label>
          <div class="col-sm-10">
            <input type="text" name="glavpunkt_sort_order" value="<?php echo $glavpunkt_sort_order; ?>" size="1" />
          </div>
          <div class="col-sm-10">В данное поле введите позицию на которой будет показан способ доставки на пункты выдачи Главпункт.</div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label" >Данные для виджета</label>
          <div class="col-sm-10">
              <textarea name="glavpunkt_widget_data" rows="1" cols="100"><?php
                echo $glavpunkt_widget_data; 
              ?></textarea>
              <p>
                В данное поле введите параметры для отображения виджета. <br>
                По умолчанию отображается город выбраный клиентом, но для выбора доступны все города, если вас устраивают настройки по умолчанию - оставьте поле пустым.<br> <br>
                <b>Примеры настроек модуля:</b><br><br>
                Отображать только Москву и СПб, увеличить интервал доставки на 1:<br>
                   <i> 'onlyCities': ['Москва', 'Санкт-Петербург'], 'addToDperiod' : 1 </i><br><br>
                Отображать все города РФ кроме Воронежа и СПб, не отображать интервал доставки:<br> 
                   <i> 'excludeCities': ['Воронеж', 'Санкт-Петербург'], 'disableDperiod' : true </i><br><br>
                <b>Примечание:</b><br>
                  В данном поле можно использовать только ' *** ' (одинарные кавычки). <br>
                  Eсли вы указываете несколько парметров - перечисляйте их через запятую. <br> 
                  Перед началом списка параметров ставить заяптую нельзя! <br>
					<br>
					Больше параметров доступно на <a href="//glavpunkt.ru/punkts-widget-demo#widgets-rf">странице виджета</a>.
              </p>
          </div>

        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label" >Код JavaScript для изменения тарифа:</label>
          <div class="col-sm-10">
             <textarea name="glavpunkt_tarif_edit_code" rows="5" cols="80"><?php
                echo $glavpunkt_tarif_edit_code; 
              ?></textarea>
              <p>
                Итоговая стоимость доставки определяется переменной "tarif". <br>
                <br>
                <b>Примеры использования:</b><br>
                Округление стоимости самовывоза:<br>
                <i> tarif =  Math.round(tarif); </i><br>
                <br>
                Наценка на стоимость доставки при определённых условиях, например при выдаче по СПб:<br>
                <i>if (data.cityTo == "Санкт-Петербург") {<br>
                &nbsp;&nbsp; tarif +=30; <br>
                }</i><br>
                <br>
                Когда общая стоимость заказа больше 1500р - сделать доставку бесплатной:<br>
                <i>if (data.price > 1500) {<br>
                &nbsp;&nbsp; tarif = 0;  <br>
                }</i><br>
                <br>
                Вы можете использовать массив "data". Пример данных:<br>
                <i>
                &nbsp;&nbsp; cityFrom:"Санкт-Петербург"<br>
                &nbsp;&nbsp; cityTo:"Санкт-Петербург"<br>
                &nbsp;&nbsp; paymentType:"cash"<br>
                &nbsp;&nbsp; period:1<br>
                &nbsp;&nbsp; price:123.2<br>
                &nbsp;&nbsp; result:"ok"<br>
                &nbsp;&nbsp; serv:"выдача"<br>
                &nbsp;&nbsp; tarif:91.11<br>
                &nbsp;&nbsp; weight:10<br>
                </i>

              </p>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label" >Статус:</label>
          <div class="col-sm-10">
            <select name="glavpunkt_status">
            <?php if ($glavpunkt_status) { ?>
            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
            <option value="0"><?php echo $text_disabled; ?></option>
            <?php } else { ?>
            <option value="1"><?php echo $text_enabled; ?></option>
            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
