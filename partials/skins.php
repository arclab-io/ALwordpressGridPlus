<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/15/2016
 * Time: 9:01 AM
 */
$args = array(
    'thumbnail' => G5PLUS_GRID_URL . 'assets/images/grid-thumb.jpg',
    'img_origin' => G5PLUS_GRID_URL . 'assets/images/grid-thumb.jpg',
    'title'     => 'The post title',
    'cat'       => 'Categories',
    'ico_gallery' => 'fa fa-search',
    'excerpt'   => 'In eam evertitur ullamcorper signiferumque',
    'price'     => '$ 15.00',
    'post_link' => 'javascript:;',
    'is_backend'  => true,
    'disable_link' => 'false'
);

$grid_plus = new Grid_Plus();
$grid_plus_skins = $grid_plus->get_all_skins();
if ($args && is_array($args)) {
    extract($args);
}
$skin_css = '';
?>
<div class="grid-plus-container">
    <?php Grid_Plus_Base::gf_get_template('partials/bar/submit-bar'); ?>
    <div class="grid-col-md-12">
        <div class="grid-plus-layout" id="list_skins">
            <ul class="list-skins">
                <?php foreach ($grid_plus_skins as $skin) {
                    $skin_css = '';
                    if(isset($skin['skin_css'])){
                        wp_enqueue_style($skin['slug'], $skin['skin_css']);
                        $skin_css = 'data-skin-css="'.$skin['skin_css'].'"';
                    }
                    if (isset($skin['template']) && file_exists($skin['template'])):
                        ?>
                        <li class="skin-item col-md-4" data-skin="<?php echo esc_attr($skin['slug']); ?>" data-skin-name="<?php echo esc_attr($skin['name']); ?>"
                            data-skin-template="<?php echo esc_attr($skin['template']) ?>" <?php echo esc_attr($skin_css); ?> >
                            <div class="skin-item-content">
                                <span class="skin-title"><?php echo esc_html($skin['name']); ?></span>
                                <?php include $skin['template']; ?>
                            </div>
                        </li>
                    <?php endif;
                } ?>
            </ul>
        </div>
    </div>
</div>