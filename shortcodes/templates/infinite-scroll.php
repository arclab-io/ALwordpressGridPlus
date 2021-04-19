<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 1/5/2017
 * Time: 3:26 PM
 * @var $current_page
 * @var $data_section_id
 */
$has_loadmore = false;
if (isset($item_per_page) && $item_per_page > 0) {
    $max_num_pages = floor($total_post / $item_per_page) + ($total_post % $item_per_page > 0 ? 1 : 0);
    if (($current_page) <  $max_num_pages) {
        $has_loadmore = true;
    }
}
if (!$has_loadmore) {
    return;
}
?>
<div class="grid-infinite-scroll-wrap text-center" data-section-id="<?php echo esc_attr($data_section_id) ?>">
    <a href="javascript:;" class="infinite-scroll ladda-button"
       data-next-page="<?php echo esc_attr($current_page+1);?>"
       data-spinner-color="#868686"
       data-spinner-size="30"
       data-style="zoom-in"
    ></a>
</div>