<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/14/2016
 * Time: 11:23 AM
 */

add_action("wp_ajax_grid_plus_save_layout", 'grid_plus_save_layout_callback');
function grid_plus_save_layout_callback()
{
    $grid_layout = $_POST['grid_layout'];
    $grid_data_source = $_POST['grid_data_source'];
    $grid_config = $_POST['grid_config'];

    if($grid_config['type']=='metro'){
        echo json_encode(array(
            'id' => -1,
            'message' => 'Please use premium version to create metro layout'
        ));
    }
    $grids = get_option(G5PLUS_GRID_OPTION_KEY, array());

    $grids = is_array($grids) ? $grids : array();

    if (!isset($grid_config['name']) || $grid_config['name'] == '') {
        echo json_encode(array(
            'code'    => -1,
            'message' => esc_html__('Please input grid name !', 'grid-plus')
        ));
        wp_die();
    }
    if (!isset($grid_config['id']) || $grid_config['id'] == '') {
        $grid = Grid_Plus_Base::gf_get_grid_by_name(strtolower($grid_config['name']));
        if ($grid != null) {
            echo json_encode(array(
                'code'    => -1,
                'message' => esc_html__('The grid name already exist. Please change grid name !', 'grid-plus')
            ));
            wp_die();
        }
        $grid_config['id'] = uniqid();
    }

    $grids[$grid_config['id']] = array(
        'id'   => $grid_config['id'],
        'name' => $grid_config['name'],
        'type' => $grid_config['type'],
    );

    update_option(G5PLUS_GRID_OPTION_KEY, $grids);

    update_option(G5PLUS_GRID_OPTION_KEY . '_' . $grid_config['id'], array(
        'id'               => $grid_config['id'],
        'name'             => $grid_config['name'],
        'grid_config'      => $grid_config,
        'grid_data_source' => $grid_data_source,
        'grid_layout'      => $grid_layout
    ), false);

    echo json_encode(array(
        'id' => $grid_config['id'],
    ));

    wp_die();
}

add_action("wp_ajax_grid_plus_delete", 'grid_plus_delete_callback');
function grid_plus_delete_callback()
{
    $grid_id = $_POST['grid_id'];
    $grids = get_option(G5PLUS_GRID_OPTION_KEY, array());
    unset($grids[$grid_id]);
    update_option(G5PLUS_GRID_OPTION_KEY, $grids);
    delete_option(G5PLUS_GRID_OPTION_KEY . '_' . $grid_id);
    $list_grid = array();
    foreach($grids as $key => $value){
        $list_grid[] = $value;
    }
    echo json_encode($list_grid);
    wp_die();
}

add_action("wp_ajax_grid_plus_get_info", 'grid_plus_get_info_callback');
add_action("wp_ajax_nopriv_grid_plus_get_info", 'grid_plus_get_info_callback');
function grid_plus_get_info_callback()
{
    $grid_id = $_POST['grid_id'];
    $grid =  get_option(G5PLUS_GRID_OPTION_KEY. '_' . $grid_id, array());
    if (isset($grid['id'])) {
        echo json_encode($grid);
    } else {
        echo json_encode(array(
            'code'    => -1,
            'message' => esc_html__('Cannot find grid information !', 'grid-plus')
        ));
    }
    wp_die();
}

add_action("wp_ajax_grid_plus_get_list", 'grid_plus_get_list_callback');
function grid_plus_get_list_callback()
{
    $grid_name = isset($_POST['grid_name']) ? $_POST['grid_name'] : '';
    $grids = get_option(G5PLUS_GRID_OPTION_KEY, array());
    $list_grids = array();

    foreach ($grids as $grid) {
        if ($grid_name != '') {
            if (strripos($grid['name'], $grid_name) !== false) {
                $list_grids[] = array(
                    'id'   => $grid['id'],
                    'name' => $grid['name']
                );
            }
        } else {
            $list_grids[] = array(
                'id'   => $grid['id'],
                'type' => $grid['type'],
                'name' => $grid['name']
            );
        }

    }
    echo json_encode($list_grids);
    wp_die();
}

if (!function_exists('grid_plus_title_like_posts_where')) {
    function grid_plus_title_like_posts_where($where, &$wp_query)
    {
        global $wpdb;
        if ($search_term = $wp_query->get('post_title_like')) {
            $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($wpdb->esc_like($search_term)) . '%\'';
        }
        return $where;
    }
}

if (!function_exists('grid_plus_get_posts')) {
    function grid_plus_get_posts()
    {
        add_filter('posts_where', 'grid_plus_title_like_posts_where', 10, 2);
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
        $search_query = array(
            'post_title_like' => $title,
            'order'           => 'ASC',
            'orderby'         => 'post_title',
            'post_type'       => $post_type,
            'post_status'     => 'publish',
            'posts_per_page'  => 10,
        );

        $search = new WP_Query($search_query);
        $ret = array();
        foreach ($search->posts as $post) {
            $ret[] = array(
                'value' => $post->ID,
                'label' => $post->post_title
            );
        }
        echo json_encode($ret);
        die();
    }

    add_action('wp_ajax_grid_plus_get_posts', 'grid_plus_get_posts');
}


