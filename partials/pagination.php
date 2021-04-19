<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/16/2016
 * Time: 3:37 PM
 */
$grid_id = isset($_GET['grid_id']) ? $_GET['grid_id'] : '';
$grid = get_option(G5PLUS_GRID_OPTION_KEY . '_' . $grid_id, array(
    'id'               => $grid_id,
    'name'             => '',
    'grid_config'      => '',
    'grid_data_source' => '',
    'grid_layout'      => ''
));
$pagination_type = isset($grid['grid_config']['pagination_type']) ? $grid['grid_config']['pagination_type'] : 'pagination';
$page_next_text = isset($grid['grid_config']['page_next_text']) ? $grid['grid_config']['page_next_text'] : esc_html__('Next','grid-plus');
$page_prev_text = isset($grid['grid_config']['page_prev_text']) ? $grid['grid_config']['page_prev_text'] : esc_html('Previous','grid-plus');
$page_loadmore_text = isset($grid['grid_config']['page_loadmore_text']) ? $grid['grid_config']['page_loadmore_text'] : esc_html('Load more','grid-plus');
$total_item = isset($grid['grid_config']['total_item']) ? $grid['grid_config']['total_item'] : '';
$item_per_page = isset($grid['grid_config']['item_per_page']) ? $grid['grid_config']['item_per_page'] : '8';
?>
<div class="grid-plus-container">
    <?php Grid_Plus_Base::gf_get_template('partials/bar/submit-bar'); ?>
    <div class="form-groups" id="form_layout_pagination">
        <div class="col-md-6">
            <div class="form-group">
                <label for="layout_pagination_type" class="col-xs-4 control-label"><?php esc_html_e('Pagination type', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <select id="layout_pagination_type" class="form-control "  data-selected="<?php echo esc_attr($pagination_type); ?>" >
                        <option value="pagination" selected><?php esc_html_e('Page Numbers', 'grid-plus'); ?></option>
                        <option value="load-more"><?php esc_html_e('Load more button', 'grid-plus'); ?></option>
                        <option value="infinite-scroll"><?php esc_html_e('Infinite scroll', 'grid-plus'); ?></option>
                        <option value="show_all" selected><?php esc_html_e('Show All', 'grid-plus'); ?></option>
                    </select>
                </div>
            </div>
            <div class="form-group" data-depend-control="layout_pagination_type" data-depend-value="pagination,load-more,infinite-scroll">
                <label for="layout_item_per_page" class="control-label col-xs-4"><?php esc_html_e('Item per page', 'grid-plus'); ?></label>
                <div class=" col-xs-8">
                    <input type="number" class="form-control" min="1" required value="<?php echo esc_attr($item_per_page); ?>" id="layout_item_per_page">
                </div>
    
            </div>
            <div class="form-group" data-depend-control="layout_pagination_type" data-depend-value="pagination,load-more,infinite-scroll,show_all">
                <label for="layout_total_items" class="control-label col-xs-4"><?php esc_html_e('Total items', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <input type="number" class="form-control" min="0" value="<?php echo esc_attr($total_item); ?>" id="layout_total_items">
                    <span
                        class="description"><?php esc_html_e('Set empty for display all', 'grid-plus'); ?></span>
                </div>
    
            </div>
            <div class="form-group" data-depend-control="layout_pagination_type" data-depend-value="pagination">
                <label for="layout_page_prev_text" class="col-xs-4 control-label"><?php esc_html_e('Prev text', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                   <input type="text" id="layout_page_prev_text" value="<?php echo esc_attr($page_prev_text); ?>">
                </div>
            </div>
            <div class="form-group" data-depend-control="layout_pagination_type" data-depend-value="pagination">
                <label for="layout_page_next_text" class="col-xs-4 control-label"><?php esc_html_e('Next text', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <input type="text" id="layout_page_next_text" value="<?php echo esc_attr($page_next_text); ?>">
                </div>
            </div>
            <div class="form-group" data-depend-control="layout_pagination_type" data-depend-value="load-more">
                <label for="layout_page_loadmore_text" class="col-xs-4 control-label"><?php esc_html_e('Load more text', 'grid-plus'); ?></label>
                <div class="col-xs-8">
                    <input type="text" id="layout_page_loadmore_text" value="<?php echo esc_attr($page_loadmore_text); ?>">
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

