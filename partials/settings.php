<?php
/**
 * Created by PhpStorm.
 * User: g5theme
 * Date: 12/14/2016
 * Time: 8:40 AM
 */
?>
<div class="grid-plus-wrap wrap" style="opacity: 0">
    <h1><?php esc_html_e('Grid Plus Settings', 'grid-plus') ?></h1>
    <!-- Nav tabs -->

    <nav>
        <ul class="nav-tabs">
            <li class="tab-current">
                <a href="javascript:;" data-section-id='layout-config'>
                    <i class="fa fa-cogs"></i>
                    <span><?php esc_html_e('Grid Configs', 'grid-plus'); ?></span>
                </a>
            </li>
            <li>
                <a href="javascript:;" data-section-id='data-source'>
                    <i class="fa fa-database"></i>
                    <span><?php esc_html_e('Data Source', 'grid-plus'); ?></span>
                </a>
            </li>
            <li data-depend-control="layout_type" data-depend-value="carousel">
                <a href="javascript:;" data-section-id='carousel'>
                    <i class="fa fa-ellipsis-h"></i>
                    <span><?php esc_html_e('Carousel Options', 'grid-plus'); ?></span>
                </a>
            </li>
            <li data-depend-control="layout_type" data-depend-value="grid,masonry,metro">
                <a href="javascript:;" data-section-id='pagination'>
                    <i class="fa fa-ellipsis-h"></i>
                    <span><?php esc_html_e('Pagination', 'grid-plus'); ?></span>
                </a>
            </li>
            <li>
                <a href="javascript:;" data-section-id='animation'>
                    <i class="fa fa-sliders"></i>
                    <span><?php esc_html_e('Animation', 'grid-plus'); ?></span>
                </a>
            </li>
            <li>
                <a href="javascript:;" data-section-id='skin-config'>
                    <i class="fa fa-paint-brush"></i>
                    <span><?php esc_html_e('Skin Options', 'grid-plus'); ?></span>
                </a>
            </li>
            <li>
                <a href="javascript:;" data-section-id='color-config'>
                    <i class="fa fa-eyedropper"></i>
                    <span><?php esc_html_e('Color & CSS', 'grid-plus'); ?></span>
                </a>
            </li>
            <li data-depend-control="layout_type" data-depend-value="grid,masonry,metro">
                <a href="javascript:;" data-section-id='layout'>
                    <i class="fa fa-puzzle-piece"></i>
                    <span><?php esc_html_e('Layout Builder', 'grid-plus'); ?></span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Tab panes -->
    <div class="content-wrap">
        <section id='layout-config' class="content-current">
            <?php Grid_Plus_Base::gf_get_template('partials/layout-config'); ?>
        </section>
        <section id='data-source'>
            <?php Grid_Plus_Base::gf_get_template('partials/data-source'); ?>
        </section>
        <section id='carousel'>
            <?php Grid_Plus_Base::gf_get_template('partials/carousel'); ?>
        </section>
        <section id='pagination'>
            <?php Grid_Plus_Base::gf_get_template('partials/pagination'); ?>
        </section>
        <section id='animation'>
            <?php Grid_Plus_Base::gf_get_template('partials/animation'); ?>
        </section>
        <section id='skin-config'>
            <?php Grid_Plus_Base::gf_get_template('partials/skins'); ?>
        </section>
        <section id='color-config'>
            <?php Grid_Plus_Base::gf_get_template('partials/color'); ?>
        </section>
        <section id='layout'>
            <?php Grid_Plus_Base::gf_get_template('partials/layout'); ?>
        </section>
        <section id="system-status">
            <?php ?>
        </section>
    </div>
    <?php Grid_Plus_Base::gf_get_template('partials/donate-rate'); ?>
</div>
<?php Grid_Plus_Base::gf_get_template('partials/tmpl'); ?>

