<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 1/3/2017
 * Time: 9:58 AM
 */
add_action("wp_ajax_grid_plus_load_by_category", 'grid_plus_load_by_category_callback');
add_action("wp_ajax_nopriv_grid_plus_load_by_category", 'grid_plus_load_by_category_callback');
function grid_plus_load_by_category_callback(){
    global $category__in;
    check_ajax_referer('grid-plus-category', 'nonce');
    if(isset($_POST['category_id']) && $_POST['category_id']!=''){
        $category__in = explode(",",$_POST['category_id']);
    }
    $grid_name = $_POST['grid_name'];
    $current_page = isset($_POST['current_page']) ? $_POST['current_page'] : 1;
    $short_code = sprintf('[grid_plus name="%s" current_page="%s" ajax="1"]',$grid_name, $current_page);
    echo do_shortcode($short_code);
    if(isset($_POST['category_id']) && $_POST['category_id']!=''){
        unset($category__in);
    }
    wp_die();
}

add_action('wp_ajax_nopriv_grid_plus_load_gallery','grid_plus_load_gallery_callback');
add_action('wp_ajax_grid_plus_load_gallery', 'grid_plus_load_gallery_callback');

function grid_plus_load_gallery_callback(){
    $galleries = array();

    if(!isset($_REQUEST['post_id']) || $_REQUEST['post_id'] ==''){
        echo json_encode($galleries);
        wp_die();
    }

    $post_id = $_REQUEST['post_id'];
    $post_type = get_post_type($post_id);
    if($post_type=='attachment'){
        $image_attributes = wp_get_attachment_image_src($post_id,'full');
        $galleries[] = array(
            'subHtml' => get_the_title($post_id),
            'thumb' => $image_attributes[0],
            'src' => $image_attributes[0],
        );
        echo json_encode($galleries);
        wp_die();
    }

	$post_format = Grid_Plus_Base::gf_get_post_format($post_id);
    if ($post_format === 'video') {
	    $videos = get_post_meta($post_id, 'gf_format_video_embed', true);
	    if ($videos !== '') {
		    $video_links =  preg_split('/,/', $videos);
		    foreach($video_links as $video_link){
			    $galleries[] = array(
				    'src' => trim($video_link),
				    'iframe' => false,
			    );
		    }
	    }
    } elseif ($post_format === 'gallery') {
	    $images = get_post_meta($post_id, 'gf_format_gallery_images', true);
	    $images_arr = explode('|', $images);
	    foreach($images_arr as $image){
		    if (empty($image)) {
			    continue;
		    }
		    $image_attributes = wp_get_attachment_image_src($image,'full');
		    if (!empty($image_attributes) && is_array($image_attributes)) {
			    $galleries[] = array(
				    'subHtml' => get_the_title($image),
				    'thumb' => $image_attributes[0],
				    'src' => $image_attributes[0],
			    );
		    }
	    }
    }

	if(count($galleries)==0 && has_post_thumbnail($post_id) ){
		$image_attributes = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),'full');
		$galleries[] = array(
			'subHtml' => get_the_title(get_post_thumbnail_id($post_id)),
			'thumb' => $image_attributes[0],
			'src' => $image_attributes[0],
		);
	}

    echo json_encode($galleries);

    wp_die();
}