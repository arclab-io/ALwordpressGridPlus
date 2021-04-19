<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 1/3/2017
 * Time: 2:13 PM
 */
$animation = array(
    'none' => esc_html__('None', 'grid-plus'),
    'bounceInDown' => esc_html__('Bounce In Down', 'grid-plus'),
    'bounceInLeft' => esc_html__('Bounce In Left', 'grid-plus'),
    'bounceInRight' => esc_html__('Bounce In Right', 'grid-plus'),
    'bounceInUp' => esc_html__('Bounce In Up', 'grid-plus'),
    'fadeInUp' => esc_html__('Fade In Up', 'grid-plus'),
    'fadeInDown' => esc_html__('Fade In Down', 'grid-plus'),
    'fadeInLeft' => esc_html__('Fade In Left', 'grid-plus'),
    'fadeInRight' => esc_html__('Fade In Right', 'grid-plus'),
    'flipInX' => esc_html__('Flip Horizontal', 'grid-plus'),
    'slideInUp' => esc_html__('Slide In Up', 'grid-plus'),
    'zoomIn' => esc_html__('Zoom In', 'grid-plus')
);
$grid_id = isset($_GET['grid_id']) ? $_GET['grid_id'] : '';
$grid = get_option(G5PLUS_GRID_OPTION_KEY . '_' . $grid_id, array(
    'id'               => $grid_id,
    'name'             => '',
    'grid_config'      => '',
    'grid_data_source' => '',
    'grid_layout'      => ''
));
$animation_type = isset($grid['grid_config']['animation_type']) ? $grid['grid_config']['animation_type'] : 'fadeInUp';
?>
<div class="grid-plus-container">
    <?php Grid_Plus_Base::gf_get_template('partials/bar/submit-bar'); ?>
    <div class="form-groups">
        <div class="col-md-6">
            <div class="form-group">
                <div class="col-xs-8">
                    <select id="layout_animation_type" data-selected="<?php echo esc_attr($animation_type); ?>">
                        <?php foreach($animation as $key=> $value){ ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-xs-4">
                    <a class="preview-animation" href="javascript:;"><i class="fa fa-play-circle-o"></i><?php esc_attr_e('Preview','grid-plus'); ?></a>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-8">
                    <div class="animation-wrap">
                        <img src="<?php echo esc_url(G5PLUS_GRID_URL . 'assets/images/grid-thumb.jpg'); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
