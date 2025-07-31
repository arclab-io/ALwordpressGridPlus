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

add_action("wp_ajax_increment_post_remix_counter", 'increment_post_remix_counter_callback');
add_action("wp_ajax_nopriv_increment_post_remix_counter", 'increment_post_remix_counter_callback');
function increment_post_remix_counter_callback(){
    $post_id = $_REQUEST['post_id'];
    $remix_counter = get_post_meta($post_id, 'remixCounter', 1);
    $incremented_remix_counter = (int)$remix_counter + 1;
    update_post_meta($post_id, 'remixCounter', $incremented_remix_counter);
    echo(((int)$remix_counter + 1));
    wp_die();
}

add_action('wp_ajax_nopriv_grid_plus_load_gallery','grid_plus_load_gallery_callback');
add_action('wp_ajax_grid_plus_load_gallery', 'grid_plus_load_gallery_callback');

add_action('wp_ajax_nopriv_grid_plus_get_post_tags', 'grid_plus_get_post_tags_callback');
add_action('wp_ajax_grid_plus_get_post_tags', 'grid_plus_get_post_tags_callback');

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

function grid_plus_get_post_tags_callback() {
    if (!isset($_REQUEST['post_id']) || $_REQUEST['post_id'] == '') {
        echo json_encode(array('error' => 'No post ID provided'));
        wp_die();
    }
    
    $post_id = intval($_REQUEST['post_id']);
    
    // Get ALL tags directly from database, bypassing any cache
    $tags = wp_get_post_tags($post_id);
    
    $tag_data = array();
    if ($tags && !is_wp_error($tags)) {
        foreach ($tags as $tag) {
            $tag_data[] = array(
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'link' => get_tag_link($tag->term_id)
            );
        }
    }
    
    // Clear any potential cache
    wp_cache_delete($post_id, 'post_tag_relationships');
    
    echo json_encode(array(
        'success' => true,
        'post_id' => $post_id,
        'tags' => $tag_data,
        'tag_count' => count($tag_data),
        'timestamp' => current_time('mysql')
    ));
    
    wp_die();
}