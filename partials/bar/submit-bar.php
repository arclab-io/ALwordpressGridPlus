<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/19/2016
 * Time: 4:23 PM
 */
?>
<div class="layout-header">
    <span class="layout-name"><?php esc_html_e('Layout Builder','grid-plus');?></span>
    <div class="action-groups" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" >
        <a class="save-layout" href="javascript:;"><i class="fa fa-floppy-o"></i><?php esc_html_e('Save Layout','grid-plus');?></a>
    </div>
</div>
