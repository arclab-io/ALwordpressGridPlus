<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/19/2016
 * Time: 4:24 PM
 */
?>
<div class="layout-header">
    <span class="layout-name"><?php esc_html_e('Layout Builder','grid-plus');?></span>
    <div class="action-groups" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')) ?>">
        <a class="change-ratio" href="javascript:;"><i class="fa fa-qrcode"></i><?php esc_html_e('Change image ratio','grid-plus');?> </a>
        <a class="generate-layout" href="javascript:;"><i class="fa fa-cogs"></i><?php esc_html_e('Generate Layout','grid-plus');?></a>
        <a class="add-item" href="javascript:;"><i class="fa fa-plus"></i> <?php esc_html_e('Add Item','grid-plus');?></a>
        <a class="change-item-style" href="javascript:;"><i class="fa fa-plus"></i><?php esc_html_e('Change style','grid-plus');?></a>
        <a class="save-layout" href="javascript:;"><i class="fa fa-floppy-o"></i><?php esc_html_e('Save Layout','grid-plus');?> </a>
    </div>
</div>
