<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a
                href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <?php foreach ($notifications as $note) { ?>
    <div class="<?php echo $note['type']; ?>"><?php echo $note['text']; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/module.png" alt=""/> <?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button">Сохранить и отправить</a>
                <a href="<?php echo $cancel; ?>" class="button">Отменить и вернуться</a>
            </div>
        </div>
    </div>
    <div class="content">
        <form action="<?php echo $action; ?>"
              method="post"
              enctype="multipart/form-data"
              id="form"
        >
            <table id="module" class="list">
                <tr>
                    <td><?php echo $form_login_title; ?></td>
                    <td>
                        <input type="text"
                               name="glavpunktorders_login"
                               value="<?php echo $form_login_value; ?>"
                               placeholder="<?php echo $form_login_placeholder; ?>"
                               id="glavpunktorders_login"
                               class="form-control"
                        >
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_token_title; ?></td>
                    <td>
                        <input type="text"
                               name="glavpunktorders_token"
                               value="<?php echo $form_token_value; ?>"
                               placeholder="<?php echo $form_token_placeholder; ?>"
                               id="glavpunktorders_token"
                               class="form-control"
                               autocomplete="off"
                        >
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_punkt_id_title; ?></td>
                    <td>
                        <select name="punkt_id" id="punkt_id" class="form-control">
                            <?php foreach ($priem_pvz as $point) { ?>
                            <option value="<?php echo $point['id'] ?>">
                                <?php echo "{$point['metro']} ({$point['city']})" ?>
                            </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_comments_client_title; ?></td>
                    <td>
                        <textarea
                                class="form-control"
                                name="comments_client"
                                placeholder="<?php echo $form_comments_client_placeholder; ?>"
                                id="comments_client"
                        >
                        </textarea>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_pickup_needed_title; ?></td>
                    <td>
                        <input class="form-control" type="checkbox" id="pickup_needed" name="pickup_needed"
                               value="1">
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_date_title; ?></td>
                    <td>
                        <input class="form-control" type="date" id="date" name="date">
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_interval_title; ?></td>
                    <td>
                        <input class="form-control" type="text" id="interval" name="interval">
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_address_title; ?></td>
                    <td>
                        <input class="form-control" type="text" id="address" name="address">
                    </td>
                </tr>
                <tr>
                    <td><?php echo $form_comment_title; ?></td>
                    <td>
                        <textarea
                                class="form-control"
                                name="comment"
                                id="comment"
                        >
                        </textarea>
                    </td>
                </tr>
            </table>
            <table class="list table table-bordered table-hover">
                <thead>
                <tr>
                    <td style="width: 1px;" class="text-center">
                        <input type="checkbox"
                               onclick="$('input[name*=\'selected\']').prop('checked', this.checked);"/>
                    </td>
                    <td class="text-right"><?php if ($sort == 'o.order_id') { ?>
                        <a href="<?php echo $sort_order; ?>"
                           class="<?php echo strtolower($order); ?>"><?php echo $column_order_id; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_order; ?>"><?php echo $column_order_id; ?></a>
                        <?php } ?></td>
                    <td class="text-left"><?php if ($sort == 'customer') { ?>
                        <a href="<?php echo $sort_customer; ?>"
                           class="<?php echo strtolower($order); ?>"><?php echo $column_customer; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_customer; ?>"><?php echo $column_customer; ?></a>
                        <?php } ?></td>
                    <td class="text-left"><?php if ($sort == 'status') { ?>
                        <a href="<?php echo $sort_status; ?>"
                           class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                        <?php } ?></td>
                    <td class="text-right"><?php if ($sort == 'o.total') { ?>
                        <a href="<?php echo $sort_total; ?>"
                           class="<?php echo strtolower($order); ?>"><?php echo $column_total; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_total; ?>"><?php echo $column_total; ?></a>
                        <?php } ?></td>
                    <td class="text-left"><?php if ($sort == 'o.date_added') { ?>
                        <a href="<?php echo $sort_date_added; ?>"
                           class="<?php echo strtolower($order); ?>"><?php echo $column_date_added; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?></a>
                        <?php } ?></td>
                    <td class="text-left"><?php if ($sort == 'o.date_modified') { ?>
                        <a href="<?php echo $sort_date_modified; ?>"
                           class="<?php echo strtolower($order); ?>"><?php echo $column_date_modified; ?></a>
                        <?php } else { ?>
                        <a href="<?php echo $sort_date_modified; ?>"><?php echo $column_date_modified; ?></a>
                        <?php } ?></td>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                <?php if ($orders) { ?>
                <?php foreach ($orders as $order) { ?>
                <tr>
                    <td class="text-center"><?php if (in_array($order['order_id'], $selected)) { ?>
                        <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>"
                               checked="checked"/>
                        <?php } else { ?>
                        <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>"/>
                        <?php } ?>
                    </td>
                    <td class="text-right"><?php echo $order['order_id']; ?></td>
                    <td class="text-left"><?php echo $order['customer']; ?></td>
                    <td class="text-left"><?php echo $order['status']; ?></td>
                    <td class="text-right"><?php echo $order['total']; ?></td>
                    <td class="text-left"><?php echo $order['date_added']; ?></td>
                    <td class="text-left"><?php echo $order['date_modified']; ?></td>
                    <td class="text-right"><?php echo $order['shipping']; ?></td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                    <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
            </div>
        </form>
    </div>
</div>
<?php echo $footer; ?>