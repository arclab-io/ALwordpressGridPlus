<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 1/3/2017
 * Time: 9:33 AM
 * @var $post_type
 * @var $categories
 * @var $show_category
 * @var $cate_multi_line
 */
$terms = Grid_Plus_Base::gf_get_categories_info($post_type, $categories);
global $grid_plus_custom_css;
$spin_color = '#5d97af';
if (isset($grid_plus_custom_css) && is_array($grid_plus_custom_css)) {
    foreach ($grid_plus_custom_css as $section => $grid_config) {
        if (isset($grid_config['category_hover_color']) && $grid_config['category_hover_color'] != '') {
            $spin_color = $grid_config['category_hover_color'];
        }
    }
}
?>
<div class="grid-category <?php if('false' === $cate_multi_line): ?>hidden <?php endif; ?><?php echo esc_attr($show_category); ?>">
    <a href="javascript:;" class="ladda-button active"
       data-category="<?php echo implode(",", $categories); ?>" data-style="zoom-in"
       data-spinner-color="<?php echo esc_attr($spin_color); ?>">
        <?php esc_html_e('All', 'grid-plus'); ?>
    </a>
    <?php for($index = 0; $index < count($terms); $index++) { ?>
        <a href="javascript:;" class="ladda-button"
           data-category="<?php echo esc_attr($terms[$index]['term_id']) ?>" data-style="zoom-in"
           data-spinner-color="<?php echo esc_attr($spin_color); ?>">
            <?php echo wp_kses_post($terms[$index]['name']) ?>
        </a>
    <?php } ?>
    <div class="grid-cate-expanded hidden">
        <span class="grid-dropdown-toggle">+</span>
        <ul class="grid-dropdown-menu"></ul>
    </div>
</div>
