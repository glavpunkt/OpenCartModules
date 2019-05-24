<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-glavpunktorders" data-toggle="tooltip"
                        title="<?php echo $button_save; ?>"
                        class="btn btn-primary">
                    Сохранить и отправить
                    <i class="fa fa-save"></i>
                </button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default">
                    <i class="fa fa-reply"></i>
                </a>
            </div>
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
        <?php foreach ($notifications as $note) { ?>
        <div class="alert alert-<?php echo $note['type']; ?>"><i class="fa fa-exclamation-circle"></i> <?php echo $note['text']; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $order_list_text; ?></h3>
        </div>
        <div class="panel-body">
            <form action="<?php echo $action; ?>"
                  method="post"
                  enctype="multipart/form-data"
                  id="form-glavpunktorders"
            >
                <div class="well">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="glavpunktorders_login">
                                    <?php echo $form_login_title; ?>
                                </label>
                                <input type="text"
                                       name="glavpunktorders_login"
                                       value="<?php echo $form_login_value; ?>"
                                       placeholder="<?php echo $form_login_placeholder; ?>"
                                       id="glavpunktorders_login"
                                       class="form-control"
                                >
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="glavpunktorders_token">
                                    <?php echo $form_token_title; ?>
                                </label>
                                <input type="text"
                                       name="glavpunktorders_token"
                                       value="<?php echo $form_token_value; ?>"
                                       placeholder="<?php echo $form_token_placeholder; ?>"
                                       id="glavpunktorders_token"
                                       class="form-control"
                                       autocomplete="off"
                                >
                                <ul class="dropdown-menu"></ul>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="punkt_id">
                                    <?php echo $form_punkt_id_title; ?>
                                </label>
                                <select name="punkt_id" id="punkt_id" class="form-control">
                                    <?php foreach ($priem_pvz as $point) { ?>
                                    <option value="<?php echo $point['id'] ?>">
                                        <?php echo "{$point['metro']} ({$point['city']})" ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="comments_client">
                                    <?php echo $form_comments_client_title; ?>
                                </label>
                                <textarea
                                        class="form-control"
                                        name="comments_client"
                                        placeholder="<?php echo $form_comments_client_placeholder; ?>"
                                        id="comments_client"
                                >
                                </textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="pickup_needed">
                                    <?php echo $form_pickup_needed_title; ?>
                                </label>
                                <input class="form-control" type="checkbox" id="pickup_needed" name="pickup_needed"
                                       value="1">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="date">
                                    <?php echo $form_date_title; ?>
                                </label>
                                <input class="form-control" type="date" id="date" name="date">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="interval">
                                    <?php echo $form_interval_title; ?>
                                </label>
                                <input class="form-control" type="text" id="interval" name="interval">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="address">
                                    <?php echo $form_address_title; ?>
                                </label>
                                <input class="form-control" type="text" id="address" name="address">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="comment">
                                    <?php echo $form_comment_title; ?>
                                </label>
                                <textarea
                                        class="form-control"
                                        name="comment"
                                        id="comment"
                                >
                                </textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
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
                            <td class="text-center"><?php echo $column_tracking; ?>
                            </td>
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
                            <td class="text-left"><?php echo $order['statusPost']; ?> - <a href="<?php echo $order['trackingUrl']; ?>" target="_blank">трекинг</a></td>
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
                </div>
            </form>
            <div class="row">
                <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                <div class="col-sm-6 text-right"><?php echo $results; ?></div>
            </div>
        </div>
    </div>

</div>
<?php echo $footer; ?>