<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 1/14/2017
 * Time: 2:21 PM
 */
$grid_setting_url = menu_page_url('grid_plus_setting', false);
?>
<script type="text/html" id="tmpl-bg-processing-template">
    <div class="bg-processing">
        <div class="loading">
            <i class="{{{data.ico}}}"></i><span>{{{data.text}}}</span>
        </div>
    </div>
</script>
<script type="text/html" id="tmpl-bg-alert-template">
    <div class="bg-alert-popup">
        <div class="content-popup">
            <div class="message">
                <i class="{{{data.ico}}}"></i><span>{{{data.text}}}</span>
            </div>
            <div class="btn-group">
                <a href="javascript:;" class="btn-close">Close</a>
            </div>
        </div>

    </div>
</script>
<script type="text/html" id="tmpl-bg-confirm-dialog">
    <div class="bg-dialog-popup" id="grid-confirm-dialog">
        <div class="content-popup">
            <div class="message">
                <i class="{{{data.ico}}}"></i><span>{{{data.message}}}</span>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="tmpl-bg-prompt-dialog">
    <div class="bg-dialog-popup" id="grid-prompt-dialog">
        <div class="content-popup">
            <div class="message">
                <input type="text" id="grid_name">
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-bg-prompt-change-height-dialog">
    <div class="bg-popup-change-height">
        <div class="content-popup">
            <a href="javascript:;" class="close-popup"><i class="fa fa-times"></i></a>
            <div>
                <label><?php echo esc_attr('Width','grid-plus'); ?></label>
                <input type="number" min="1" max="5" value="{{data.width_ratio}}" class="txt_width" step="1">
            </div>
            <div>
                <label><?php echo esc_attr('Height','grid-plus'); ?></label>
                <input type="number" min="1" max="5" value="{{data.height_ratio}}" class="txt_height" step="1">
            </div>
            <div>
                <a href="javascript:;" class="apply-change-height">Apply change</a>
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-list-grid-template">
    <# _.each(data, function(item, index){ #>
        <tr>
            <td class="grid-plus-id">{{{index+1}}}</td>
            <td class="grid-plus-name">{{{item.name}}}</td>
            <td class="grid-plus-shortcode">
                <span id="layout_shortcode_{{{item.id}}}"> [grid_plus name="{{{item.name}}}"] </span>
                <a class="copy-clipboard" href="javascript:;" data-clipboard-target="#layout_shortcode_{{{item.id}}}"><i class="fa fa-clipboard"></i></a>
            </td>
            <td class="grid-plus-shortcode" >
                <div class="actions" data-id="{{{item.id}}}"
                     data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" >
                    <a href="<?php echo sprintf('%s&grid_id={{{item.id}}}',$grid_setting_url); ?>" class="edit-grid" ><i class="fa fa-pencil"></i><?php esc_html_e('Edit','grid-plus'); ?></a>
                    <a href="javascript:;" class="clone-grid"><i class="fa fa-clone"></i><?php esc_html_e('Clone','grid-plus'); ?></a>
                    <a href="javascript:;" class="delete-grid"><i class="fa fa-trash-o"></i><?php esc_html_e('Delete','grid-plus'); ?></a>
                </div>
            </td>
        </tr>
        <# }) #>
</script>
