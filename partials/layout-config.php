<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/15/2016
 * Time: 9:01 AM
 */

$grid_id = isset($_GET['grid_id']) ? $_GET['grid_id'] : '';
$clone = isset($_GET['clone']) ? $_GET['clone'] : '';
$grid_name_clone = isset($_GET['grid_name']) ? $_GET['grid_name'] : '';


$grid = get_option(G5PLUS_GRID_OPTION_KEY . '_' . $grid_id, array(
    'id'               => $grid_id,
    'name'             => '',
    'grid_config'      => array(
    		'use_image' => 'full'
    ),
    'grid_data_source' => '',
    'grid_layout'      => ''
));
$layout_type = array(
    'grid'    => esc_html__('Grid', 'grid-plus'),
    'masonry' => esc_html__('Masonry', 'grid-plus'),
    'carousel'   => esc_html__('Carousel', 'grid-plus')
);
$cols = array(
    '2' => esc_html__('2 Columns', 'grid-plus'),
    '3' => esc_html__('3 Columns', 'grid-plus'),
    '4' => esc_html__('4 Columns', 'grid-plus'),
    '5' => esc_html__('5 Columns', 'grid-plus'),
    '6' => esc_html__('6 Columns', 'grid-plus')
);
$gutter = array(
    '0'  => esc_html__('None', 'grid-plus'),
    '5'  => esc_html__('5 pixel', 'grid-plus'),
    '10' => esc_html__('10 pixel', 'grid-plus'),
    '15' => esc_html__('15 pixel', 'grid-plus'),
    '20' => esc_html__('20 pixel', 'grid-plus'),
    '30' => esc_html__('30 pixel', 'grid-plus')
);

$layout_use_image = array(
    'thumbnail'  => esc_html__('Thumbnail', 'grid-plus'),
    'full' => esc_html__('Fullsize', 'grid-plus')
);

$layout_id = $clone == 'true' ? '' : $grid_id;
$grid_name = $clone == 'true' ? $grid_name_clone : $grid['name'];
$grid_name = $grid_name != '' ? $grid_name : esc_html__('New Grid', 'grid-plus');

?>
<div class="grid-plus-container">
    <?php Grid_Plus_Base::gf_get_template('partials/bar/submit-bar'); ?>
    <div class="form-groups" id="form_layout_info">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label col-xs-4"><?php esc_html_e('Grid ID', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <input type="text" readonly id="layout_id" value="<?php echo esc_attr($layout_id); ?>">
                </div>
            </div>
            <div class="form-group  ">
                <label class="control-label col-xs-4"><?php esc_html_e('Grid Shortcode', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <div>
                        <span id="layout_shortcode"><?php echo '[grid_plus name="' . $grid_name . '"]'; ?></span>
                        <a class="copy-clipboard" href="javascript:;"
                           title="<?php esc_attr_e('Copy to clipboard', 'grid-plus'); ?>"
                           data-clipboard-target="#layout_shortcode"><i
                                class="fa fa-clipboard"></i></a>
                    </div>
                    <span
                        class="description"><?php esc_html_e('Click icon clipboard to copy and paste shortcode to page or anywhere', 'grid-plus'); ?></span>
                </div>
            </div>
            <div class="form-group  ">
                <label class="control-label col-xs-4"
                       for="layout_name"><?php esc_html_e('Grid name', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <input class="form-control" id="layout_name" value="<?php echo esc_attr($grid_name); ?>"
                           type="text" required>
                    <span class="description"><?php esc_html_e('Grid name is identity', 'grid-plus'); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label for="layout_type"
                       class="col-xs-4 control-label"><?php esc_html_e('Grid type', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <select id="layout_type" class="form-control ">
                        <?php foreach ($layout_type as $key => $val) { ?>
                            <option
                                value="<?php echo esc_attr($key); ?>" <?php if (isset($grid['grid_config']['type']) && $grid['grid_config']['type'] == $key) {
                                echo 'selected';
                            }; ?> >
                                <?php echo esc_html($val); ?>
                            </option>
                        <?php }; ?>
                    </select>
                    <span
                        class="description"><?php esc_html_e('Please click generate layout after change layout type to update item width', 'grid-plus'); ?></span>
                </div>
            </div>
            <div class="form-group" data-depend-control="layout_type" data-depend-value="grid,masonry,metro">
                <label for="layout_col"
                       class="col-xs-4 control-label"><?php esc_html_e('Grid columns', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <select id="layout_col" class="form-control ">
                        <?php foreach ($cols as $key => $val) { ?>
                            <option
                                value="<?php echo esc_attr($key); ?>" <?php if (isset($grid['grid_config']['columns']) && $grid['grid_config']['columns'] == $key) {
                                echo 'selected';
                            }; ?> >
                                <?php echo esc_html($val); ?>
                            </option>
                        <?php }; ?>
                    </select>
                    <span
                        class="description"><?php esc_html_e('Please click generate layout after change layout type to update grid column', 'grid-plus'); ?></span>
                </div>
            </div>

            <div class="form-group" data-depend-control="layout_type" data-depend-value="grid,masonry,metro,carousel">
                <label for="layout_gutter"
                       class="col-xs-4 control-label"><?php esc_html_e('Gutter', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <select id="layout_gutter" class="form-control ">
                        <?php foreach ($gutter as $key => $val) { ?>
                            <option
                                value="<?php echo esc_attr($key); ?>" <?php if (isset($grid['grid_config']['gutter']) && $grid['grid_config']['gutter'] == $key) {
                                echo 'selected';
                            }; ?> >
                                <?php echo esc_html($val); ?>
                            </option>
                        <?php }; ?>
                    </select>
                    <span
                        class="description"><?php esc_html_e('Please click generate layout after change gutter', 'grid-plus'); ?></span>
                </div>
            </div>

            <div class="form-group  "  data-depend-control="layout_type" data-depend-value="grid,masonry,metro">
                <label for="layout_fix_item_height"
                       class="control-label col-xs-4"><?php esc_html_e('Fixed Item Height', 'grid-plus'); ?></label>
                <div class=" col-xs-8">
                    <input type="checkbox"
                           id="layout_fix_item_height" <?php if (isset($grid['grid_config']['fix_item_height']) && $grid['grid_config']['fix_item_height'] == 'true') {
                        echo 'checked';
                    } ?> >
                </div>
            </div>

            <div class="form-group" >
                <label for="layout_crop_image"
                       class="control-label col-xs-4"><?php esc_html_e('Dynamic Crop Image', 'grid-plus'); ?></label>
                <div class=" col-xs-8">
                    <input type="checkbox"
                           id="layout_crop_image" <?php if (isset($grid['grid_config']['crop_image']) && $grid['grid_config']['crop_image'] == 'true') {
                        echo 'checked';
                    } ?> >
                </div>
            </div>
            <div class="form-group" data-depend-control="layout_crop_image" data-depend-value="false">
                <label for="layout_use_image"
                       class="control-label col-xs-4"><?php esc_html_e('Use image size as original', 'grid-plus'); ?></label>
                <div class=" col-xs-8">
                    <select id="layout_use_image" class="form-control ">
                        <?php foreach ($layout_use_image as $key => $val) { ?>
                            <option
                                    value="<?php echo esc_attr($key); ?>" <?php if (isset($grid['grid_config']['use_image']) && $grid['grid_config']['use_image'] == $key) {
                                echo 'selected';
                            }; ?> >
                                <?php echo esc_html($val); ?>
                            </option>
                        <?php }; ?>
                    </select>
                    <span class="description"><?php esc_html_e('Should use Full if Dynamic Crop Image selected', 'grid-plus'); ?></span>
                </div>
            </div>

            <div class="form-group" >
                <label for="layout_disable_link"
                       class="control-label col-xs-4"><?php esc_html_e('Disable Link', 'grid-plus'); ?></label>
                <div class=" col-xs-8">
                    <input type="checkbox"
                           id="layout_disable_link" <?php if (isset($grid['grid_config']['disable_link']) && $grid['grid_config']['disable_link'] == 'true') {
                        echo 'checked';
                    } ?> >
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div style="clear: both"></div>
</div>
