<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/14/2016
 * Time: 9:31 AM
 */

$skins = array('thumbnail', 'thumbnail-title','thumbnail-title-hover-top');
$args = array(
    'thumbnail' => G5PLUS_GRID_URL.'/assets/images/grid-thumb.jpg',
    'title' => esc_html__('The post title','grid-plus'),
    'excerpt' => 'In eam evertitur ullamcorper signiferumque'
)

?>
<div class="grid-plus-container">
    <div class="grid-plus-layout">
        <?php Grid_Plus_Base::gf_get_template('partials/bar/layout-bar'); ?>
        <div class="grid-stack-container" data-resource-url="<?php echo esc_attr(G5PLUS_GRID_URL) ;?>">
            <div class="grid-stack">
            </div>
        </div>
    </div>
</div>
