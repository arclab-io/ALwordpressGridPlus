<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/15/2016
 * Time: 9:01 AM
 */
$grids = get_option(G5PLUS_GRID_OPTION_KEY);
$grids = is_array($grids) ? $grids : array();

?>
<div class="grid-plus-wrap listing-grid wrap">
    <h1 style="margin-bottom: 30px"><?php esc_html_e('All Grid Plus', 'grid-plus') ?></h1>
    <div class="grid-plus-container">
        <div class="g5plus-container-fluid">
            <div class="form-import">
                <p class="import-notice"><?php esc_attr_e('Please select .json file and click import to import grid','grid-plus');?></p>
                <form method="post" enctype="multipart/form-data">
                    <p>
                        <input type="file" name="import_file"/>
                        <input type="hidden" name="grid_plus_action" value="import_grid" />
                        <?php wp_nonce_field( 'grid_plus_import_nonce', 'grid_plus_import_nonce' ); ?>
                        <?php submit_button( esc_html__('Import','grid-plus'), 'secondary', 'submit', false ); ?>
                    </p>

                </form>
                <a class="close-import" href="javascript:;"><i class="fa fa-times-circle"></i></a>
            </div>
            <?php Grid_Plus_Base::gf_get_template('partials/bar/listing-bar'); ?>
            <div class="g5plus-row">
                <table cellpadding="0" cellspacing="0" id="table_list_grid">
                    <thead>
                    <tr>
                        <td width="100px"><?php esc_html_e('ID','grid-plus');?></td>
                        <td width="35%"> <?php esc_html_e('Name','grid-plus');?></td>
                        <td width="35%"><?php esc_html_e('Shortcode','grid-plus');?></td>
                        <td width="300px"><?php esc_html_e('Actions','grid-plus');?></td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $index = 0;
                    $grid_setting_url = menu_page_url('grid_plus_setting', false);
                    foreach($grids as $grid){
                        ?>
                        <tr>
                            <td class="grid-plus-id"><?php echo esc_html(++$index); ?></td>
                            <td class="grid-plus-name"><?php echo esc_html($grid['name']); ?></td>
                            <td class="grid-plus-shortcode">
                                <span id="layout_shortcode_<?php echo esc_attr($grid['id']); ?>"> [grid_plus name="<?php echo esc_html($grid['name']); ?>"] </span>
                                <a class="copy-clipboard" href="javascript:;" title="<?php esc_attr_e('Copy to clipboard','grid-plus'); ?>" data-clipboard-target="#layout_shortcode_<?php echo esc_attr($grid['id']); ?>"><i class="fa fa-clipboard"></i></a>
                            </td>
                            <td class="grid-plus-shortcode" >
                                <div class="actions" data-id="<?php echo esc_attr($grid['id']); ?>"
                                     data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" >
                                    <a href="<?php echo sprintf('%s&grid_id=%s',$grid_setting_url, $grid['id']); ?>" class="edit-grid" ><i class="fa fa-pencil"></i><?php esc_html_e('Edit','grid-plus'); ?></a>
                                    <a href="javascript:;" data-clone-url="<?php echo sprintf('%s&grid_id=%s&clone=true&grid_name=',$grid_setting_url, $grid['id']); ?>" class="clone-grid"><i class="fa fa-clone"></i><?php esc_html_e('Clone','grid-plus'); ?></a>
                                    <a href="javascript:;" class="delete-grid"><i class="fa fa-trash-o"></i><?php esc_html_e('Delete','grid-plus'); ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php Grid_Plus_Base::gf_get_template('partials/donate-rate'); ?>
</div>

<?php Grid_Plus_Base::gf_get_template('partials/tmpl'); ?>

