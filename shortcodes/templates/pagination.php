<?php
/**
 * The template for displaying paging style navigation
 *
 * @package WordPress
 * @subpackage g5plus-grid
 * @since g5plus-grid v1.0
 * @var $item_per_page
 * @var $current_page
 * @var $data_section_id
 * @var $page_prev_text
 * @var $page_next_text
 */

$has_paging = false;
$max_num_pages = 1;
if ($item_per_page && $item_per_page > 0) {
    $max_num_pages = floor($total_post / $item_per_page) + ($total_post % $item_per_page > 0 ? 1 : 0);
    if ($max_num_pages > 1) {
        $has_paging = true;
    }
}
if (!$has_paging) {
    return;
}

global $wp_query, $wp_rewrite;
$paged = get_query_var('paged') ? intval(get_query_var('paged')) : $current_page;
$pagenum_link = html_entity_decode(get_pagenum_link());
$query_args = array();
$url_parts = explode('?', $pagenum_link);

if (isset($url_parts[1])) {
    wp_parse_str($url_parts[1], $query_args);
}

$pagenum_link = esc_url(remove_query_arg(array_keys($query_args), $pagenum_link));
$pagenum_link = trailingslashit($pagenum_link) . '%_%';

$format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged') : '?paged=%#%';
?>
<div class="grid-paging-navigation-wrap text-center">
    <div class="grid-paging-navigation clearfix" data-section-id="<?php echo esc_attr($data_section_id) ?>">
        <?php echo paginate_links(array(
            'base'               => $pagenum_link,
            'format'             => $format,
            'total'              => $max_num_pages,
            'current'            => $paged,
            'mid_size'           => 1,
            'prev_text'          => '<span ><i class="fa fa-angle-left"></i>' . $page_prev_text . '</span>',
            'next_text'          => '<span >' . $page_next_text . '<i class="fa fa-angle-right"></i></span>',
            'before_page_number' => '<span >',
            'after_page_number'  => '</span>'
        )); ?>
    </div>
</div>
