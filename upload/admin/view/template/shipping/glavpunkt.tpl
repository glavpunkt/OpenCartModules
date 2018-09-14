<?php echo $header; ?>
<div id="content">
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/shipping.png" alt=""/> <?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a>
                <a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form">
                    <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td>
                            <select name="glavpunkt_status">
                                <?php if ($glavpunkt_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_city; ?></td>
                        <td><input type="text" value="<?php echo $cityFrom?>" name="glavpunkt_cityFrom"></td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_post; ?></td>
                        <td>
                            <select name="glavpunkt_post_status">
                                <?php if ($post_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_courier; ?></td>
                        <td>
                            <select name="glavpunkt_courier_status">
                                <?php if ($courier_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_pickup; ?></td>
                        <td>
                            <select name="glavpunkt_pickup_status">
                                <?php if ($pickup_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_paymentType; ?></td>
                        <td>
                            <select name="glavpunkt_paymentType">
                                <option value="prepaid"
                                <?php echo ($paymentType === 'prepaid'
                                        ? "selected=\"selected\""
                                        : "") ?>
                                >
                                <?php echo $entry_paymentType_prepaid; ?>
                                </option>
                                <option value="cash"
                                <?php echo ($paymentType === 'cash'
                                        ? "selected=\"selected\""
                                        : "") ?>
                                >
                                <?php echo $entry_paymentType_cash; ?>
                                </option>
                                <option value="credit"
                                <?php echo ($paymentType === 'credit'
                                        ? "selected=\"selected\""
                                        : "") ?>
                                >
                                <?php echo $entry_paymentType_credit; ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $simple_text; ?></td>
                        <td>
                            <select name="glavpunkt_simple_status">
                                <?php if ($simple_status) { ?>
                                <option value="1" selected="selected"><?=$simple_text_enabled?></option>
                                <option value="0"><?=$simple_text_disabled?></option>
                                <?php } else { ?>
                                <option value="1"><?=$simple_text_enabled?></option>
                                <option value="0" selected="selected"><?=$simple_text_disabled?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>