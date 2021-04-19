<?php
if (!defined('ABSPATH')) {
    exit;
}

class Grid_Plus_Base
{

    /**
     * GET Plugin template
     * *******************************************************
     */

    public static function gf_get_template($slug, $args = array())
    {
        if ($args && is_array($args)) {
            extract($args);
        }
        $located = G5PLUS_GRID_DIR . $slug . '.php';
        if (!file_exists($located)) {
            _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $slug), '1.0');
            return;
        }
        include($located);
    }

    /**
     * GET list post type
     * *******************************************************
     */

    public static function gf_get_posttypes()
    {
        $post_types = get_post_types(array(
            'public'             => true,
            'publicly_queryable' => true,
        ));
        foreach ($post_types as $key => $type) {
            $post_type_object = get_post_type_object($type);
            if (empty($post_type_object)) {
                $post_types[$key] = $type;
                continue;
            }
            $post_types[$key] = $post_type_object->labels->name;
        }
        return apply_filters('grid_plus_post_types', $post_types);
    }

    /**
     * GET list categories
     * *******************************************************
     */

    public static function gf_get_categories()
    {
        $post_categories = $terms = $obj_taxonomies = array();
        $post_types = Grid_Plus_Base::gf_get_posttypes();

        foreach ($post_types as $post_type => $value) {
            $terms = $obj_taxonomies = array();
            $obj_taxonomies = get_object_taxonomies(
                array('post_type' => $post_type),
                'objects'
            );

            foreach ($obj_taxonomies as $taxonomy_key => $taxonomy_values) {
                $terms = get_terms(array(
                    'taxonomy'   => $taxonomy_values->name,
                    'hide_empty' => false,
                ));
                if (isset($terms) && is_array($terms)) {
                    foreach ($terms as $term) {
                        $post_categories[$post_type][$taxonomy_values->labels->name][] = array(
                            'taxonomy' => $taxonomy_values->name,
                            'term_id'    => $term->term_id,
                            'term_name'  => $term->name,
                            'term_count' => $term->count
                        );
                    }
                }
            }
        }
        return $post_categories;
    }

    /**
     * GET categories taxonomy by post type
     * *******************************************************
     */

    public static function gf_get_category_taxonomy($post_type)
    {
        $obj_taxonomies = get_object_taxonomies(
            array('post_type' => $post_type),
            'objects'
        );
        $category_taxonomy = array();
        if (is_array($obj_taxonomies) && count($obj_taxonomies) > 0) {
            foreach ($obj_taxonomies as $taxonomy_key => $taxonomy_values) {
                $category_taxonomy[] = $taxonomy_values->name;
            }
            return $category_taxonomy;
        }
        return $category_taxonomy[] = 'category';
    }

    /**
     * GET categories by post type
     * *******************************************************
     */
    public static function gf_get_categories_info($post_type, $category_ids)
    {
        $post_categories = $terms = array();
        $obj_taxonomies = get_object_taxonomies( array('post_type' => $post_type), 'objects' );
        foreach ($obj_taxonomies as $taxonomy_key => $taxonomy_values) {
            $terms = get_categories(array( 'taxonomy'   => $taxonomy_values->name, 'hide_empty' => '0' ));
            if (isset($terms) && is_array($terms)) {
                foreach ($terms as $term) {
                    if (in_array($term->term_id, $category_ids)) {
                        $post_categories[array_search($term->term_id, $category_ids)] = array(
                            'term_id' => $term->term_id,
                            'slug'    => $term->slug,
                            'name'    => $term->name,
                            'count'   => $term->count
                        );
                    }
                }
            }
        }
        return $post_categories;
    }

    public static function gf_get_categories_info_by_posttype($post_type)
    {
        $post_categories = $terms = $obj_taxonomies = array();

        $terms = $obj_taxonomies = array();
        $obj_taxonomies = get_object_taxonomies(
            array('post_type' => $post_type),
            'objects'
        );
        foreach ($obj_taxonomies as $taxonomy_key => $taxonomy_values) {
            $terms = get_terms(array(
                'taxonomy'   => $taxonomy_values->name,
                'hide_empty' => true,
            ));
            if (isset($terms) && is_array($terms)) {
                foreach ($terms as $term) {
                    $post_categories[$term->slug] = array(
                        'term_id' => $term->term_id,
                        'slug'    => $term->slug,
                        'name'    => $term->name,
                        'count'   => $term->count
                    );
                }
            }
        }
        return $post_categories;
    }


    /**
     * GET list user
     * *******************************************************
     */
    public static function gf_get_users()
    {
        $users = get_users(array(
            'orderby' => 'display_name',
            'order'   => 'DESC',
            'fields'  => array('ID', 'user_nicename'),
        ));
        if ($users) {
            $array = array();
            foreach ($users as $user) {
                $array[$user->ID] = $user->user_nicename;
            }
        }
        return $users;
    }

    /**
     * GET grid by grid name
     * *******************************************************
     */
    public static function gf_get_grid_by_name($name)
    {
        $grids = get_option(G5PLUS_GRID_OPTION_KEY, array());
        if (is_array($grids)) {
            foreach ($grids as $grid) {
                if (strtolower($grid['name']) == strtolower($name)) {
                    return get_option(G5PLUS_GRID_OPTION_KEY . '_' . $grid['id'], array());
                }
            }
        }
        return null;
    }

    /**
     * GET post format
     * *******************************************************
     */
    public static function gf_get_post_format($post = null)
    {
        if (!$post = get_post($post))
            return false;

        $_format = get_the_terms($post->ID, 'post_format');

        if (empty($_format))
            return false;

        $format = reset($_format);

        return str_replace('post-format-', '', $format->slug);
    }

    /**
     * Enqueue custom css
     * *******************************************************
     */
    public static function gf_enqueue_custom_css()
    {
        global $grid_plus_custom_css;
        if (isset($grid_plus_custom_css) && is_array($grid_plus_custom_css)) {
            $css = '<div id="grid-plus-custom-css"><style type="text/css">';
            foreach ($grid_plus_custom_css as $section => $grid_config) {
                if(isset($grid_config['category_color']) && $grid_config['category_color']!=''){
                    $css .= "
                        .grid-{$section} .grid-category a,
                        .grid-{$section} .grid-cate-expanded > span {
                            color: {$grid_config['category_color']} !important;
                        }
                        ";
                }
                if(isset($grid_config['category_hover_color']) && $grid_config['category_hover_color']!=''){
                    $css .= "
                        .grid-{$section} .grid-category a.active,
                        .grid-{$section} .grid-category a:hover,
                        .grid-{$section} .grid-category a:focus,
                        .grid-{$section} .grid-category a:active,
                        .grid-{$section} .grid-cate-expanded > span:hover,
                        .grid-{$section} .grid-cate-expanded > span:active,
                        .grid-{$section} .grid-cate-expanded > span:focus {
                            color: {$grid_config['category_hover_color']} !important;
                        }
                        ";
                }

                if(isset($grid_config['no_image_background_color']) && $grid_config['no_image_background_color']!=''){
                    $css .= "
                        .grid-{$section} div.grid-post-item .thumbnail-image {
                            background-color: {$grid_config['no_image_background_color']} !important;
                        }
                        ";
                }
                if(isset($grid_config['background_color']) && $grid_config['background_color']!=''){
                    $css .= "
                        .grid-{$section} div.grid-post-item .hover-outer {
                            background-color: {$grid_config['background_color']} !important;
                        }
                        ";
                }
                if(isset($grid_config['icon_color']) && $grid_config['icon_color']!=''){
                    $css .= "
                        .grid-{$section} .icon-groups a {
                            color: {$grid_config['icon_color']} !important;
                            border-color: {$grid_config['icon_color']} !important;
                        }
                        ";
                }
                if(isset($grid_config['icon_hover_color']) && $grid_config['icon_hover_color']!=''){
                    $css .= "
                        .grid-{$section} .icon-groups a:hover {
                            color: {$grid_config['icon_hover_color']} !important;
                            border-color: {$grid_config['icon_hover_color']} !important;
                        }
                        ";
                }
                if(isset($grid_config['title_color']) && $grid_config['title_color']!=''){
                    $css .= "
                        .grid-{$section} .grid-plus-inner .grid-post-item .title,
                        .grid-{$section} .grid-plus-inner .grid-post-item .title a {
                            color:  {$grid_config['title_color']} !important;
                        }
                        ";
                }
                if(isset($grid_config['title_hover_color']) && $grid_config['title_hover_color']!=''){
                    $css .= "
                        .grid-{$section} .grid-plus-inner .grid-post-item .title:hover,
                        .grid-{$section} .grid-plus-inner .grid-post-item .title a:hover {
                            color:  {$grid_config['title_hover_color']} !important;
                        }
                        ";
                }
                if(isset($grid_config['excerpt_color']) && $grid_config['excerpt_color']!=''){
                    $css .= "
                        .grid-{$section} .grid-plus-inner .grid-post-item .excerpt,
                        .grid-{$section} .grid-plus-inner .grid-post-item .categories {
                             color:  {$grid_config['excerpt_color']} !important;
                        }
                        ";
                }
            }
            $css .= $grid_config['custom_css'];
            $css .="</style></div>";
            echo sprintf('%s',$css);
        }
    }

    public static function gf_get_attachment_image($attachment_id, $crop, $crop_size, &$with_after_crop,
                                                   &$height_after_crop, &$with_origin, &$height_origin, &$attachment_url, &$crop_url){

        $orig_image = wp_get_attachment_image_src($attachment_id, 'full');
        if ($orig_image === false) {
            return;
        }
        $attachment_url = $crop_url = isset($orig_image[0]) ? $orig_image[0] : '' ;
        $with_origin = $with_after_crop = isset($orig_image[1]) ? $orig_image[1] : $with_origin;
        $height_origin = $height_after_crop = isset($orig_image[2]) ? $orig_image[2] : $height_origin;

        if($crop){
            $percent = 1;
            if($with_after_crop>=$crop_size){
                $percent = floor($with_after_crop/$crop_size);
                $with_after_crop = $crop_size;
                $height_after_crop = floor($height_after_crop/$percent);
            }elseif($height_after_crop>=$crop_size){
                $percent = floor($height_after_crop/$crop_size);
                $height_after_crop = $crop_size;
                $with_after_crop = floor($with_after_crop/$percent);
            }
            $crop_url = G5Plus_Image_Resize::init()->resize(array(
                'image_id' => $attachment_id,
                'width' => $with_after_crop,
                'height' => $height_after_crop,
            ));
            $crop_url = isset($crop_url['url']) ? $crop_url['url'] : '';
        }
    }
    public static function gf_get_attachment_image_size($attachment_id, $crop, $crop_size, &$with_after_crop,
                                                   &$height_after_crop, &$with_origin, &$height_origin, &$attachment_url, &$crop_url, $use_image){

    	if ($crop) {
		    $use_image = 'full';
	    }
        $orig_image = wp_get_attachment_image_src($attachment_id, $use_image);

        //haven't got image
        if ($orig_image === false) {
            return;
        }
        $attachment_url = $crop_url = isset($orig_image[0]) ? $orig_image[0] : '' ;
        $with_origin = $with_after_crop = isset($orig_image[1]) ? $orig_image[1] : $with_origin;
        $height_origin = $height_after_crop = isset($orig_image[2]) ? $orig_image[2] : $height_origin;

        if($crop){
            $percent = 1;
            if($with_after_crop>=$crop_size){
                $percent = floor($with_after_crop/$crop_size);
                $with_after_crop = $crop_size;
                $height_after_crop = floor($height_after_crop/$percent);
            }elseif($height_after_crop>=$crop_size){
                $percent = floor($height_after_crop/$crop_size);
                $height_after_crop = $crop_size;
                $with_after_crop = floor($with_after_crop/$percent);
            }
            $crop_url = G5Plus_Image_Resize::init()->resize(array(
                'image_id' => $attachment_id,
                'width' => $with_after_crop,
                'height' => $height_after_crop,
            ));
            $crop_url = isset($crop_url['url']) ? $crop_url['url'] : '';
        }
    }
}