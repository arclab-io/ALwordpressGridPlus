<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 2/3/2017
 * Time: 10:23 AM
 */
$grid_id = isset($_GET['grid_id']) ? $_GET['grid_id'] : '';
$grid = get_option(G5PLUS_GRID_OPTION_KEY . '_' . $grid_id, array(
    'id'               => $grid_id,
    'name'             => '',
    'grid_config'      => '',
    'grid_data_source' => '',
    'grid_layout'      => ''
));
$category_color = isset($grid['grid_config']['category_color']) ? $grid['grid_config']['category_color'] : '';
$category_hover_color = isset($grid['grid_config']['category_hover_color']) ? $grid['grid_config']['category_hover_color'] : '';
$background_color = isset($grid['grid_config']['background_color']) ? $grid['grid_config']['background_color'] : '';
$no_image_bg_color = isset($grid['grid_config']['no_image_background_color']) ? $grid['grid_config']['no_image_background_color'] : '';
$icon_color = isset($grid['grid_config']['icon_color']) ? $grid['grid_config']['icon_color'] : '';
$icon_hover_color = isset($grid['grid_config']['icon_hover_color']) ? $grid['grid_config']['icon_hover_color'] : '';
$title_color = isset($grid['grid_config']['title_color']) ? $grid['grid_config']['title_color'] : '';
$title_hover_color = isset($grid['grid_config']['title_hover_color']) ? $grid['grid_config']['title_hover_color'] : '';
$excerpt_color = isset($grid['grid_config']['excerpt_color']) ? $grid['grid_config']['excerpt_color'] : '';
$custom_css = isset($grid['grid_config']['custom_css']) ? $grid['grid_config']['custom_css'] : '';
?>
<div class="grid-plus-container">
    <?php Grid_Plus_Base::gf_get_template('partials/bar/submit-bar'); ?>
    <div class="form-groups" >
        <div class="col-md-6">
            <div class="form-group">
                <label for="layout_category_color" class="col-xs-4 control-label"><?php esc_html_e('Category filter color', 'grid-plus'); ?></label>
                <div class="input-group  col-xs-8">
                    <input type="text" name="cate_filter_color" id="layout_category_color" data-alpha="true"
                           value="<?php echo esc_attr($category_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_category_hover_color" class="col-xs-4 control-label"><?php esc_html_e('Category filter hover color', 'grid-plus'); ?></label>
                <div class="input-group  col-xs-8">
                    <input type="text" name="cate_filter_hover_color" id="layout_category_hover_color" data-alpha="true"
                           value="<?php echo esc_attr($category_hover_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_no_image_bg_color" class="col-xs-4 control-label"><?php esc_html_e('No image background color', 'grid-plus'); ?></label>
                <div class="input-group  col-xs-8">
                    <input type="text" name="no_image_bg_color" id="layout_no_image_bg_color" data-alpha="true"
                           value="<?php echo esc_attr($no_image_bg_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_bg_color" class="col-xs-4 control-label"><?php esc_html_e('Background hover color', 'grid-plus'); ?></label>
                <div class="input-group  col-xs-8">
                    <input type="text" name="bg_hover_color" id="layout_bg_color" data-alpha="true"
                           value="<?php echo esc_attr($background_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_icon_color" class="col-xs-4 control-label"><?php esc_html_e('Icon color', 'grid-plus'); ?></label>
                <div class="input-group col-xs-8">
                    <input type="text" name="icon_color" id="layout_icon_color" data-alpha="true"
                           value="<?php echo esc_attr($icon_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_icon_hover_color" class="col-xs-4 control-label"><?php esc_html_e('Icon hover color', 'grid-plus'); ?></label>
                <div class="input-group  col-xs-8">
                    <input type="text" name="icon_hover_color" id="layout_icon_hover_color" data-alpha="true"
                           value="<?php echo esc_attr($icon_hover_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_title_color" class="col-xs-4 control-label"><?php esc_html_e('Title color', 'grid-plus'); ?></label>
                <div class="input-group  col-xs-8">
                    <input type="text" name="title_color" id="layout_title_color" data-alpha="true"
                           value="<?php echo esc_attr($title_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_title_hover_color" class="col-xs-4 control-label"><?php esc_html_e('Title hover color', 'grid-plus'); ?></label>
                <div class="input-group  col-xs-8">
                    <input type="text" name="title_hover_color" id="layout_title_hover_color" data-alpha="true"
                           value="<?php echo esc_attr($title_hover_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_excerpt_color" class="col-xs-4 control-label"><?php esc_html_e('Excerpt color', 'grid-plus'); ?></label>
                <div class="input-group col-xs-8">
                    <input type="text" name="excerpt_color" id="layout_excerpt_color" data-alpha="true"
                           value="<?php echo esc_attr($excerpt_color) ?>"
                           class="color-picker form-control" />
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="layout_custom_css" class="control-label"><?php esc_html_e('Custom CSS', 'grid-plus'); ?></label>
                <div>
                    <pre id="layout_custom_css" class="ace-editor"><?php echo esc_html($custom_css); ?></pre>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
