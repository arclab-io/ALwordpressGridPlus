<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 2/14/2017
 * Time: 2:29 PM
 * @var $name
 */

global $category__in;
$section_id = uniqid();
$grid = Grid_Plus_Base::gf_get_grid_by_name($name);
$grid_config = $grid['grid_config'];
$grid_data_source = $grid['grid_data_source'];

$crop_image = isset($grid_config['crop_image']) ? $grid_config['crop_image'] : 'false';
$crop_image = 'true' == $crop_image ? true : false;
$disable_link = isset($grid_config['disable_link']) ? $grid_config['disable_link'] : 'false';
$layout_type = $grid_config['type'];
$columns = $grid_config['columns'];
$height_ratio = $grid_config['carousel_height_ratio'];
$width_ratio = $grid_config['carousel_width_ratio'];
$gutter = $grid_config['gutter'];
$use_image = isset($grid_config['use_image']) ? $grid_config['use_image'] : 'full' ;
$total_item = isset($grid_config['total_item']) ? $grid_config['total_item'] : 0;
$carousel_skin = isset($grid_config['carousel_skin']) ? $grid_config['carousel_skin'] : '';
$carousel_skin_css = isset($grid_config['carousel_skin_css']) ? $grid_config['carousel_skin_css'] : '';

$carousel_total_items = isset($grid['grid_config']['carousel_total_items']) ? $grid['grid_config']['carousel_total_items'] : 0;
$carousel_desktop_large_col = isset($grid['grid_config']['carousel_desktop_large_col']) ? $grid['grid_config']['carousel_desktop_large_col'] : 6;
$carousel_desktop_medium_col = isset($grid['grid_config']['carousel_desktop_medium_col']) ? $grid['grid_config']['carousel_desktop_medium_col'] : 5;
$carousel_desktop_small_col = isset($grid['grid_config']['carousel_desktop_small_col']) ? $grid['grid_config']['carousel_desktop_small_col'] : 4;
$carousel_tablet_col = isset($grid['grid_config']['carousel_tablet_col']) ? $grid['grid_config']['carousel_tablet_col'] : 3;
$carousel_tablet_small_col = isset($grid['grid_config']['carousel_tablet_small_col']) ? $grid['grid_config']['carousel_tablet_small_col'] : 2;
$carousel_mobile_col = isset($grid['grid_config']['carousel_mobile_col']) ? $grid['grid_config']['carousel_mobile_col'] : 1;

$carousel_desktop_large_width = isset($grid['grid_config']['carousel_desktop_large_width']) ? $grid['grid_config']['carousel_desktop_large_width'] : 1200;
$carousel_desktop_medium_width = isset($grid['grid_config']['carousel_desktop_medium_width']) ? $grid['grid_config']['carousel_desktop_medium_width'] : 992;
$carousel_desktop_small_width = isset($grid['grid_config']['carousel_desktop_small_width']) ? $grid['grid_config']['carousel_desktop_small_width'] : 768;
$carousel_tablet_width = isset($grid['grid_config']['carousel_tablet_width']) ? $grid['grid_config']['carousel_tablet_width'] : 600;
$carousel_tablet_small_width = isset($grid['grid_config']['carousel_tablet_small_width']) ? $grid['grid_config']['carousel_tablet_small_width'] : 480;
$carousel_mobile_width = isset($grid['grid_config']['carousel_mobile_width']) ? $grid['grid_config']['carousel_mobile_width'] : 320;

$nav_next_text = isset($grid['grid_config']['carousel_next_text']) ? $grid['grid_config']['carousel_next_text'] : '<i class=&quot;fa fa-angle-right&quot;></i>';
$nav_prev_text = isset($grid['grid_config']['carousel_prev_text']) ? $grid['grid_config']['carousel_prev_text'] : '<i class=&quot;fa fa-angle-left&quot;></i>';

$authors = $grid_data_source['authors'];
$post_type = $grid_data_source['post_type'];
$categories = isset($grid_data_source['categories']) && $grid_data_source['categories'] != '' ? $grid_data_source['categories'] : array();
$show_category = $grid_data_source['show_category'];
$cate_multi_line = $grid_data_source['cate_multi_line'];
$include_ids = $grid_data_source['include_ids'];
$exclude_ids = $grid_data_source['exclude_ids'];
$order = $grid_data_source['order'];
$order_by = $grid_data_source['order_by'];

if(count($categories)==0 && isset($show_category) && $show_category!='none'){
    $categories_info = Grid_Plus_Base::gf_get_categories_info_by_posttype($post_type);
    foreach($categories_info as $cat){
        $categories[] = $cat['term_id'];
    }
}

$args = array(
    'offset'         => 0,
    'posts_per_page' => -1,
    'post_type'      => $post_type,
    'post_status'    => 'publish'
);

if ($carousel_total_items > 0) {
    $args['posts_per_page'] = $carousel_total_items;
}

$category_taxonomy = Grid_Plus_Base::gf_get_category_taxonomy($post_type);
$terms_in = array();


if (isset($category_taxonomy) && is_array($category_taxonomy) && count($category_taxonomy) > 0) {

    if (isset($category__in) && is_array($category__in) && count($category__in) > 0) {
        $terms_in = $category__in;
    } else {
        if (isset($categories) && is_array($categories) && count($categories) > 0) {
            $terms_in = $categories;
        }
    }

    if (!empty($terms_in)) {
        $taxonomy_query = array('relation' => 'OR');
        foreach ($category_taxonomy as $taxonomy) {
            $taxonomy_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $terms_in,
                'operator' => 'IN'
            );
        }
        $args['tax_query'] = $taxonomy_query;
    }
}

if(!isset($include_ids) || empty($include_ids)) {
    $include_ids = array();
}
if(!isset($exclude_ids) || empty($exclude_ids)) {
    $exclude_ids = array();
}
if (is_array($include_ids) && count($include_ids) > 0) {
    $args['post__in'] = $include_ids;
    $args['orderby'] = 'post__in';
    $disable_source = true;
}
if (is_array($exclude_ids) && count($exclude_ids) > 0) {
    $args['post__not_in'] = $exclude_ids;
}

if ($order_by) {
    $args['orderby'] = $order_by;
    $args['order'] = $order;
}
if ($post_type == 'attachment') {
    $args['post_mime_type'] = 'image/jpeg,image/gif,image/jpg,image/png';
    $args['post_status'] = 'any';
    if(isset($grid['grid_data_source']['grid_gallery']) && !empty($grid['grid_data_source']['grid_gallery'])) {
        $custom_urls = isset($grid_data_source['custom_urls']) ? $grid_data_source['custom_urls'] : '';
        $disable_source = true;
        $grid_gallery = $grid['grid_data_source']['grid_gallery'];
        $grid_gallery = explode('|', $grid_gallery);
        $args['post__in'] = $grid_gallery;
        $args['orderby'] = 'post__in';
        $args['posts_per_page'] = count($grid_gallery);
    }
}

if (isset($authors) && is_array($authors) && count($authors) > 0) {
    $args['author__in'] = $authors;
}

$posts = new WP_Query($args);
$total_post = $posts->found_posts;

$grid_full_layout = array();
$ajax_nonce = wp_create_nonce("grid-plus-category");

$is_loop = isset($grid['grid_config']['loop']) && $grid['grid_config']['loop'] == 'true';
$is_show_nav = isset($grid['grid_config']['show_nav']) && $grid['grid_config']['show_nav'] == 'true';
$is_show_dot = isset($grid['grid_config']['show_dot']) && $grid['grid_config']['show_dot'] == 'true';
$is_rtl = isset($grid['grid_config']['carousel_rtl']) && $grid['grid_config']['carousel_rtl'] == 'true';
$autoplay = isset($grid['grid_config']['autoplay']) && $grid['grid_config']['autoplay'] == 'true';
$autoplay_time = isset($grid['grid_config']['autoplay_time']) ? $grid['grid_config']['autoplay_time'] : 3000;
$owl_options = array(
    'items'           => intval($carousel_desktop_large_col),
    'loop'            => $is_loop,
    'margin'          => intval($gutter),
    'dots'            => $is_show_dot,
    'nav'             => $is_show_nav,
    'autoplay'        => $autoplay,
    'autoplayTimeout' => intval($autoplay_time),
    'rtl'             => $is_rtl,
    'responsive'      => array(
        $carousel_desktop_large_width  => array(
            'items' => intval($carousel_desktop_large_col)
        ),
        $carousel_desktop_medium_width => array(
            'items' => intval($carousel_desktop_medium_col)
        ),
        $carousel_desktop_small_width  => array(
            'items' => intval($carousel_desktop_small_col)
        ),
        $carousel_tablet_width         => array(
            'items' => intval($carousel_tablet_col)
        ),
        $carousel_tablet_small_width   => array(
            'items' => intval($carousel_tablet_small_col)
        ),
        $carousel_mobile_width         => array(
            'items' => intval($carousel_mobile_col)
        )
    )
);

if ($carousel_skin_css != '') {
    wp_enqueue_style($carousel_skin, str_replace('\\"', '', $carousel_skin_css));
}

?>
<div class="grid-plus-container grid-<?php echo esc_attr($section_id); ?> <?php echo esc_attr($post_type); ?> <?php if('true' == $cate_multi_line): ?> grid-cate-multi-line<?php endif; ?>"
     id="<?php echo esc_attr($section_id); ?>"
     data-grid-name="<?php echo esc_attr($name); ?>"
     data-animation="<?php echo esc_attr($grid_config['animation_type']); ?>">
    <div class="carousel-container grid-plus-inner"
         data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')) ?>"
         data-grid-id="<?php echo esc_attr($grid['id']); ?>"
         data-current-category="<?php echo implode(",", $categories); ?>"
         data-section-id="<?php echo esc_attr($section_id); ?>"
         data-gutter="<?php echo esc_attr($gutter); ?>"
         data-columns="<?php echo esc_attr($columns); ?>"
         data-nonce="<?php echo esc_attr($ajax_nonce); ?>"
         data-layout-type="<?php echo esc_attr($layout_type); ?>"
         data-height-ratio="<?php echo esc_attr($height_ratio); ?>"
         data-width-ratio="<?php echo esc_attr($width_ratio); ?>"
    >
        <?php if (isset($show_category) && $show_category != '' && $show_category != 'none') {
            Grid_Plus_Base::gf_get_template('shortcodes/templates/category', array(
                'section_id'    => $section_id,
                'post_type'     => $post_type,
                'categories'    => $categories,
                'show_category' => $show_category,
                'cate_multi_line' => $cate_multi_line
            ));
        } ?>

        <div class="carousel-items grid-owl-carousel" data-owl-options='<?php echo json_encode($owl_options); ?>'
            <?php if ($is_show_nav) {
                echo 'data-show-nav="1"';?>
                data-nav-next-text="<?php echo esc_html($nav_next_text); ?>"
                data-nav-prev-text="<?php echo esc_html($nav_prev_text); ?>"
            <?php
            }; ?>
            <?php if ($is_show_dot) {
                echo 'data-show-dot="1"';
            }; ?>
        >
            <?php
            $crop_size = 800;
            $grid_plus = new Grid_Plus();
            while ($posts->have_posts()) : $posts->the_post();
                $post_thumbnail_id = $width = $height = $width_crop = $height_crop = 0;
                $img_origin = $thumbnail = '';
                $title = get_the_title();
                $excerpt = get_the_excerpt();
                $post_thumbnail_id = $post_type != 'attachment' ? get_post_thumbnail_id(get_the_ID()) : get_the_ID();
                if(!empty($post_thumbnail_id)){
                    Grid_Plus_Base::gf_get_attachment_image_size($post_thumbnail_id, $crop_image, $crop_size, $width_crop, $height_crop, $width, $height, $img_origin, $thumbnail, $use_image);
                }

                $terms = wp_get_post_terms(get_the_ID(), $category_taxonomy);
                $cat = $cat_filter = '';
                foreach ($terms as $term) {
                    $cat_filter .= $term->slug . ' ';
                    $cat .= $term->name . ', ';
                }
                $cat = rtrim($cat, ', ');


	            $ico_gallery = 'fa fa-search';
	            $post_format = Grid_Plus_Base::gf_get_post_format(get_the_ID());
	            if (isset($post_format) && $post_format == 'video') {
		            $videos = get_post_meta(get_the_ID(), 'gf_format_video_embed', true);
		            if ($videos !== '') {
			            $ico_gallery = 'fa fa-play';
		            }

	            }

	            if ($post_type === 'attachment') {
		            $video_url = get_post_meta(get_the_ID(),'gsf_photographer_video_url',true);
		            if ($video_url !== '') {
			            $img_origin = $video_url;
			            $ico_gallery = 'fa fa-play';
		            }
	            }

	            $ico_gallery = apply_filters('grid_plus_icon_gallery', $ico_gallery);


                $post_link = get_permalink();

                ?>
                <div class="item">
                    <?php
                    $item_template = $grid_plus->get_skin_template($carousel_skin);
                    if (file_exists($item_template)) {
                        include $item_template;
                    } else {
                        echo esc_html__('Could not find this template', 'grid-plus');
                    }
                    ?>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</div>

<?php

global $grid_plus_custom_css;
if (!isset($grid_plus_custom_css) || !is_array($grid_plus_custom_css)) {
    $grid_plus_custom_css = array();
}
$grid_plus_custom_css[$section_id] = $grid_config;
remove_action('wp_footer', 'Grid_Plus_Base::gf_enqueue_custom_css');
add_action('wp_footer', 'Grid_Plus_Base::gf_enqueue_custom_css');
?>
