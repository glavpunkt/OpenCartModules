
</div><?php echo $header; ?><?php echo $column_left; ?>
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
        <h3 class="panel-title">Настройки модуля</h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-auspost" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" >Ваш город:</label>
            <div class="col-sm-10">
              <input type="text" name="glavpunktcourier_home_city" value="<?php echo $glavpunktcourier_home_city; ?>" />
            </div>
      <div class="col-sm-10">Введите в данное поле город из котрого будут отправлятся заказы на доставку(Санкт-Петербург или Москва по умолчанию - Санкт-Петербург).</div>

          <div class="form-group">
            <label class="col-sm-2 control-label" >Цена для Санкт-Петербурга:</label>
            <div class="col-sm-10">
              <input type="text" name="glavpunktcourier_price_spb" value="<?php echo $glavpunktcourier_price_spb; ?>" size="5" />
            </div>
            <div class="col-sm-10">Введите в данное поле цену доставки, которая будет применена для Санкт-Петербурга.</div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" >Цена для Москвы:</label>
            <div class="col-sm-10">
              <input type="text" name="glavpunktcourier_price_msk" value="<?php echo $glavpunktcourier_price_msk; ?>" size="5" />
            </div>
            <div class="col-sm-10">Введите в данное поле цену доставки, которая будет применена для Москвы.</div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" >Отсрочка курьерской доставки:</label>
            <div class="col-sm-10">
              <input type="text" name="glavpunktcourier_days" value="<?php echo $glavpunktcourier_days; ?>" size="5" />
            </div>
            <div class="col-sm-10">В данном поле укажите колличество дней через которое будет доступна курьерская доставка по умолчанию 0</div>
          </div>
      
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" >Вес по умолчанию:</label>
            <div class="col-sm-10">
              <input type="text" name="glavpunktcourier_weight" value="<?php echo $glavpunktcourier_weight; ?>" />
            </div>
      <div class="col-sm-10">В данном поле укажите вес присваеваемый заказу в случае если он не указан.</div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" >Код JavaScript для изменения тарифа:</label>
            <div class="col-sm-10">
              <textarea name="glavpunktcourier_tarif_edit_code" rows="5" cols="80"><?php
                echo $glavpunktcourier_tarif_edit_code; 
              ?></textarea>
              <p>
                Итоговая стоимость доставки определяется переменной "tarif". <br>
                <br>
                <b>Примеры использования:</b><br>
                Наценка на стоимость доставки при определённых условиях, например при выдаче по СПб:<br>
                <i>if (data.cityTo == "Санкт-Петербург") {<br>
                &nbsp;&nbsp;  tarif +=30; <br>
                }</i><br>
                <br>
                Когда общая стоимость заказа больше 1500р - сделать доставку бесплатной:<br>
                <i>if (data.price > 1500) {<br>
                &nbsp;&nbsp;  tarif = 0;  <br>
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
                &nbsp;&nbsp; serv:"курьерская доставка"<br>
                &nbsp;&nbsp; tarif:301.11<br>
                &nbsp;&nbsp; weight:10<br>
                </i>

              </p>
            </div>
        </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" >Номер для сортировки:</label>
            <div class="col-sm-10">
              <input type="text" name="glavpunktcourier_sort_order" value="<?php echo $glavpunktcourier_sort_order; ?>" size="1" />
            </div>
            <div class="col-sm-10">Укажите на каком месте в списке модулей доставки будет находиться курьерская доставка Главпункт.</div>
          </div>
                    <div class="form-group">
            <label class="col-sm-2 control-label" >Статус:</label>
            <div class="col-sm-10">
              <select name="glavpunktcourier_status">
              <?php if ($glavpunktcourier_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select>
            </div>
           <div class="col-sm-10">Включить/Выключить модуль курьерской доставки Главпункт.</div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
