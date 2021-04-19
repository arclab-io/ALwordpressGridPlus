<?php
/**
 *    Plugin Name: Grid Plus
 *    Plugin URI: http://themes.g5plus.net/plugins/grid/
 *    Description: Grid Plus - Unlimited grid layout for any post type.
 *    Version: 1.3.2
 *    Author: G5Theme
 *    Author URI: http://themeforest.net/user/g5theme
 *
 *    Text Domain: grid-plus
 *    Domain Path: /languages/
 *
 **/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
defined('G5PLUS_GRID_DIR') or define('G5PLUS_GRID_DIR', plugin_dir_path(__FILE__));
defined('G5PLUS_GRID_URL') or define('G5PLUS_GRID_URL', trailingslashit(plugins_url(basename( __DIR__ ) )));
defined('G5PLUS_GRID_OPTION_KEY') or define('G5PLUS_GRID_OPTION_KEY', 'grid_plus');

if (!class_exists('Grid_Plus')) {
    class Grid_Plus
    {

        public function __construct()
        {
            $this->includes();
            $this->grid_plus_load_textdomain();

            add_action('wp_enqueue_scripts', array($this, 'grid_plus_shortcode_register_css'));
            add_action('wp_enqueue_scripts', array($this, 'grid_plus_shortcode_register_script'));
            add_shortcode('grid_plus', array($this, 'grid_plus_shortcode'));


            if (is_admin()) {
                add_action('admin_enqueue_scripts', array($this, 'grid_plus_admin_enqueue_script'));
                add_action('admin_menu', array($this, 'grid_plus_menu'));
                add_filter('gf-post-format-ui/plugin-url', array($this, 'post_format_ui_url'));
                add_filter('gf-post-format-ui/post-type', array($this, 'post_format_ui_post_type'));
            }
            add_filter('grid_plus_post_types', array($this, 'grid_plus_post_types'));

	        add_filter( 'attachment_fields_to_edit', array($this,'add_attachment_field_video') , 10, 2 );
	        add_filter( 'attachment_fields_to_save', array($this,'save_attachment_field_video') , 10, 2 );
        }

        function grid_plus_load_textdomain()
        {
            load_plugin_textdomain('grid-plus', FALSE, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        function grid_plus_admin_enqueue_script()
        {
            $screen = get_current_screen();
            if (isset($screen->base)) {
                //setting grid
                $min = (defined('GRID_PLUS_DEBUG') && GRID_PLUS_DEBUG) ? '' : '.min';
                if ($screen->base === 'grid-plus_page_grid_plus_setting') {
                    if ( function_exists( 'wp_enqueue_media' ) ) {
                        wp_enqueue_media();
                    } else {
                        if (!wp_script_is ( 'media-upload' )) {
                            wp_enqueue_script( 'media-upload' );
                        }
                    }
                    wp_enqueue_style('font-awesome', G5PLUS_GRID_URL . 'assets/lib/font-awesome/css/font-awesome.min.css');

                    wp_enqueue_style('animate', G5PLUS_GRID_URL . 'assets/lib/animate/animate.css');

                    wp_enqueue_script('ace_editor', '//cdnjs.cloudflare.com/ajax/libs/ace/1.2.5/ace.js', array('jquery'), '1.2.5', true);

                    wp_enqueue_style('wp-color-picker');
                    wp_enqueue_script('wp-color-picker');
                    wp_enqueue_script('wp-color-picker-alpha', G5PLUS_GRID_URL . 'assets/lib/color-picker/wp-color-picker-alpha.js', array('wp-color-picker'), '1.0', true);
	                global $wp_version;
	                if ( version_compare($wp_version,'5.5') >= 0) {
		                wp_localize_script('wp-color-picker-alpha',
			                'wpColorPickerL10n',
			                array(
				                'clear'            => esc_html__( 'Clear','grid-plus' ),
				                'clearAriaLabel'   => esc_html__( 'Clear color','grid-plus'  ),
				                'defaultString'    => esc_html__( 'Default','grid-plus'  ),
				                'defaultAriaLabel' => esc_html__( 'Select default color','grid-plus'  ),
				                'pick'             => esc_html__( 'Select Color','grid-plus'  ),
				                'defaultLabel'     => esc_html__( 'Color value','grid-plus'  ),
			                ));
	                }

                    wp_enqueue_style('selectize', G5PLUS_GRID_URL . 'assets/lib/selectize/css/selectize.default.css');
                    wp_enqueue_script('selectize', G5PLUS_GRID_URL . 'assets/lib/selectize/js/selectize.min.js', false, true);

                    wp_enqueue_style('perfect-scrollbar', G5PLUS_GRID_URL . 'assets/lib/perfect-scrollbar/css/perfect-scrollbar.min.css');
                    wp_enqueue_script('perfect-scrollbar-jquery', G5PLUS_GRID_URL . 'assets/lib/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js', false, true);

                    wp_enqueue_style('grid-plus-stack', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack.min.css');
                    wp_enqueue_style('grid-plus-stack-extra', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack-extra.min.css');
                    wp_enqueue_script('jquery-ui', G5PLUS_GRID_URL . 'assets/lib/grid-stack/jquery-ui.js',array('jquery'), false, true);
                    //wp_enqueue_script('lodash', G5PLUS_GRID_URL . 'assets/lib/grid-stack/lodash.min.js', false, true);
                    wp_enqueue_script('grid-plus-stack', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack' . $min . '.js', array('jquery','underscore'), true);
                    wp_enqueue_script('grid-plus-stack-jUI', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack.jQueryUI.min.js', false, true);

                    wp_enqueue_script('grid-plus-clipboard', G5PLUS_GRID_URL . 'assets/lib/clipboard/clipboard.min.js', array('wp-util'), true, true);

                    wp_enqueue_style('grid-plus-be-style', G5PLUS_GRID_URL . 'assets/css/be_style.css', array(), false);
                    wp_enqueue_script('grid-plus-utils', G5PLUS_GRID_URL . 'assets/js/backend/utils.min.js', array('wp-util'), true, true);

                    wp_enqueue_script('sf_media', G5PLUS_GRID_URL . 'core/post-format-ui/assets/js/media.js', array('jquery'), false, true);
                    wp_enqueue_style('sf_post-format-ui', G5PLUS_GRID_URL . 'core/post-format-ui/assets/css/post-format-ui.css', array(), false);
                    wp_enqueue_script('sf_gallery', G5PLUS_GRID_URL . 'core/post-format-ui/assets/js/gallery.js', array(), false, true);

                    $grid_script_data = array(
                        'grid_id'  => isset($_GET['grid_id']) ? $_GET['grid_id'] : '',
                        'ajax_url' => admin_url('admin-ajax.php')
                    );
                    wp_register_script('grid-plus-settings', G5PLUS_GRID_URL . 'assets/js/backend/settings.min.js', array('wp-util'), true, true);
                    //wp_register_script('grid-plus-settings', G5PLUS_GRID_URL . 'assets/js/backend/settings.js', array('wp-util'), true, true);
                    wp_localize_script('grid-plus-settings', 'grid_script_data', $grid_script_data);
                    wp_enqueue_script('grid-plus-settings');
                }
                //listing grid
                if ($screen->base === 'toplevel_page_grid_plus') {
                    wp_enqueue_style('font-awesome', G5PLUS_GRID_URL . 'assets/lib/font-awesome/css/font-awesome.min.css');

                    wp_enqueue_script('jquery-ui', G5PLUS_GRID_URL . 'assets/lib/grid-stack/jquery-ui.js', false, true);

                    wp_enqueue_script('file-save', G5PLUS_GRID_URL . 'assets/lib/file-save/FileSaver.min.js', false, true);

                    wp_enqueue_script('grid-plus-clipboard', G5PLUS_GRID_URL . 'assets/lib/clipboard/clipboard.min.js', array('wp-util'), true, true);

                    wp_enqueue_style('grid-plus-be-style', G5PLUS_GRID_URL . 'assets/css/be_style.css', array(), false);
                    wp_enqueue_script('grid-plus-utils', G5PLUS_GRID_URL . 'assets/js/backend/utils.min.js', array('wp-util'), true, true);
                    wp_enqueue_script('grid-plus-listing', G5PLUS_GRID_URL . 'assets/js/backend/listing.min.js', array('wp-util'), true, true);
                }
            }
        }

        function grid_plus_menu()
        {
            add_menu_page(
                esc_html__('Grid Plus', 'grid-plus'),
                esc_html__('Grid Plus', 'grid-plus'),
                'manage_options',
                'grid_plus',
                array($this, 'grid_plus_menu_callback'),
                'dashicons-screenoptions',
                3
            );
            add_submenu_page(
                'grid_plus',
                esc_html__('All grid', 'grid-plus'),
                esc_html__('All grid', 'grid-plus'),
                'manage_options',
                'grid_plus',
                array($this, 'grid_plus_menu_callback')
            );
            add_submenu_page(
                'grid_plus',
                esc_html__('Add grid', 'grid-plus'),
                esc_html__('Add grid', 'grid-plus'),
                'manage_options',
                'grid_plus_setting',
                array($this, 'grid_plus_setting_menu_callback')
            );
        }

        function get_skin_template($skin_slug)
        {
            $grid_plus_skins = $this->get_all_skins();
            foreach ($grid_plus_skins as $skin) {
                if (isset($skin['slug']) && $skin_slug === $skin['slug']) {
                    return $skin['template'];
                }
            }
        }

        function get_all_skins() {
            global $grid_plus_skins;
            $grid_plus_skins = array(
                array(
                    'name'    => 'Thumbnail only',
                    'slug'     => 'thumbnail',
                    'template' => G5PLUS_GRID_DIR . 'skins/thumbnail.php',
                ),
                array(
                    'name'    => 'Thumbnail - title, excerpt',
                    'slug'     => 'thumbnail-title-excerpt',
                    'template' => G5PLUS_GRID_DIR . 'skins/thumbnail-title-excerpt.php',
                ),
                array(
                    'name'    => 'Thumbnail - icon',
                    'slug'     => 'thumbnail-icon',
                    'template' => G5PLUS_GRID_DIR . 'skins/thumbnail-icon.php',
                ),
                array(
                    'name'    => 'Thumbnail - icon gallery',
                    'slug'     => 'thumbnail-icon-gallery',
                    'template' => G5PLUS_GRID_DIR . 'skins/thumbnail-icon-gallery.php',
                ),
                array(
                    'name'    => 'Thumbnail - icon, title, categories',
                    'slug'     => 'thumbnail-icon-title-cat',
                    'template' => G5PLUS_GRID_DIR . 'skins/thumbnail-icon-title-cat.php',
                ),
                array(
                    'name'    => 'Thumbnail - Title, excerpt hover top',
                    'slug'     => 'thumbnail-title-hover-top',
                    'template' => G5PLUS_GRID_DIR . 'skins/thumbnail-title-hover-top.php',
                ),
                array(
                    'name'    => 'Thumbnail - icon, title, excerpt',
                    'slug'     => 'thumbnail-icon-title-excerpt',
                    'template' => G5PLUS_GRID_DIR . 'skins/thumbnail-icon-title-excerpt.php',
                ),
                array(
                    'name'    => 'Woocommerce: Thumbnail - icon, title, price, rate',
                    'slug'     => 'woo-thumb-icon-cat-title-price-rate',
                    'template' => G5PLUS_GRID_DIR . 'skins/woo-thumb-icon-cat-title-price-rate.php',
                ),
                array(
                    'name'    => 'Woocommerce: Thumbnail, icon, title, price',
                    'slug'     => 'woo-thumb-icon-title-price',
                    'template' => G5PLUS_GRID_DIR . 'skins/woo-thumb-icon-title-price.php',
                ),
                array(
                    'name'    => 'Woocommerce. Thumb - title, price, icon',
                    'slug'     => 'woo-thumb-title-price-icon',
                    'template' => G5PLUS_GRID_DIR . 'skins/woo-thumb-title-price-icon.php',
                )
            );
            $grid_plus_skins = apply_filters('grid-plus-skins', $grid_plus_skins);
            return $grid_plus_skins;
        }

        function grid_plus_menu_callback()
        {
            Grid_Plus_Base::gf_get_template('partials/listing');
        }

        function grid_plus_setting_menu_callback()
        {

            Grid_Plus_Base::gf_get_template('partials/settings');
        }

        function post_format_ui_url()
        {
            return G5PLUS_GRID_URL . 'core/post-format-ui/';
        }

        function post_format_ui_post_type($post_type)
        {
            $post_types = Grid_Plus_Base::gf_get_posttypes();
            foreach ($post_types as $key => $value) {
                $post_type[] = $key;
            }
            return $post_type;
        }

        function grid_plus_shortcode_register_css()
        {
            wp_register_style('font-awesome', G5PLUS_GRID_URL . 'assets/lib/font-awesome/css/font-awesome.min.css');
            wp_register_style('animate', G5PLUS_GRID_URL . 'assets/lib/animate/animate.css');
            wp_register_style('light-gallery', G5PLUS_GRID_URL . 'assets/lib/light-gallery/css/lightgallery.min.css', array());
            wp_register_style('ladda', G5PLUS_GRID_URL . 'assets/lib/ladda/ladda.min.css');
            wp_register_style('grid-plus-stack', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack.min.css');
            wp_register_style('grid-plus-stack-extra', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack-extra.min.css');
            wp_register_style('grid-owl-carousel', G5PLUS_GRID_URL . 'assets/lib/owl-carousel/grid.owl.carousel.min.css');
            wp_register_style('grid-plus-fe-style', G5PLUS_GRID_URL . 'assets/css/fe_style.css', array(), false);
        }

        function grid_plus_shortcode_register_script()
        {
            $min = (defined('GRID_PLUS_DEBUG') && GRID_PLUS_DEBUG) ? '' : '.min';
            wp_register_script('light-gallery', G5PLUS_GRID_URL . 'assets/lib/light-gallery/js/lightgallery-all.min.js',array('jquery'), true);
            wp_register_script('ladda-spin', G5PLUS_GRID_URL . 'assets/lib/ladda/spin.min.js',array('jquery'), false, true);
            wp_register_script('ladda', G5PLUS_GRID_URL . 'assets/lib/ladda/ladda.min.js',array('jquery'), false, true);
            wp_register_script('jquery-ui', G5PLUS_GRID_URL . 'assets/lib/grid-stack/jquery-ui.js',array('jquery'), false, true);
            //wp_register_script('lodash', G5PLUS_GRID_URL . 'assets/lib/grid-stack/lodash.min.js', false, true);
            wp_register_script('grid-plus-stack', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack' . $min . '.js', array('jquery','underscore'), true);
            wp_register_script('grid-plus-stack-jUI', G5PLUS_GRID_URL . 'assets/lib/grid-stack/gridstack.jQueryUI.min.js',array('jquery'), false, true);
            wp_register_script('grid-owl-carousel', G5PLUS_GRID_URL . 'assets/lib/owl-carousel/grid.owl.carousel.min.js',array('jquery'), false, true);
            wp_register_script('match-media', G5PLUS_GRID_URL . 'assets/lib/matchmedia/matchmedia.js',array('jquery'), false, true);
            wp_register_script('grid-plus-settings', G5PLUS_GRID_URL . 'assets/js/frontend/grid'. $min .'.js', array('wp-util', 'match-media','jquery'), true, true);
        }

        function grid_plus_shortcode($atts)
        {

            if (!isset($atts['name']) || $atts['name'] == '') {
                esc_html_e('Missing parameter "name" in shortcode', 'grid-plus');
                return;
            }

            $grid = Grid_Plus_Base::gf_get_grid_by_name($atts['name']);
            if ($grid == null || !isset($grid['grid_config'])) {
                esc_html_e('Cannot find grid information', 'grid-plus');
                return;
            }

            $grid_config = $grid['grid_config'];
            $layout_type = $grid_config['type'];

            $this->grid_plus_shortcode_enqueue_script($layout_type);

            ob_start();
            if($layout_type =='metro'){
                return 'Please use premium version to create metro layout';
            }
            if ($layout_type == 'carousel') {
                Grid_Plus_Base::gf_get_template('shortcodes/carousel-shortcode', $atts);
            } else {
                Grid_Plus_Base::gf_get_template('shortcodes/grid-shortcode', $atts);
            }
            $ret = ob_get_contents();
            ob_end_clean();
            return $ret;
        }

        function grid_plus_post_types($post_types) {
            $post_types['page'] = esc_html__('Pages', 'grid-plus');
            return $post_types;
        }

        function grid_plus_shortcode_enqueue_script()
        {
            wp_enqueue_style('font-awesome');
            wp_enqueue_style('animate');
            wp_enqueue_style('light-gallery');
            wp_enqueue_style('ladda');
            wp_enqueue_style('grid-plus-stack');
            wp_enqueue_style('grid-plus-stack-extra');
            wp_enqueue_style('grid-owl-carousel');
            wp_enqueue_style('grid-plus-fe-style');

            wp_enqueue_script('light-gallery');
            wp_enqueue_script('ladda-spin');
            wp_enqueue_script('ladda');
            wp_enqueue_script('jquery-ui');
            //wp_enqueue_script('lodash');
            wp_enqueue_script('grid-plus-stack');
            wp_enqueue_script('grid-plus-stack-jUI');
            wp_enqueue_script('grid-owl-carousel');
            wp_enqueue_script('match-media');
            wp_enqueue_script('grid-plus-settings');
        }

        private function includes()
        {
            include_once G5PLUS_GRID_DIR . 'core/post-format-ui/post-format-ui.php';
            include_once G5PLUS_GRID_DIR . 'core/class-g5plus-image-resize.php';
            include_once G5PLUS_GRID_DIR . 'core/grid.plus.base.class.php';
            include_once G5PLUS_GRID_DIR . 'core/ajax_be.php';
            include_once G5PLUS_GRID_DIR . 'core/ajax_fe.php';
            include_once G5PLUS_GRID_DIR . 'partials/grid-editor.php';
        }

	    public function add_attachment_field_video($form_fields, $post) {
		    $form_fields['gsf-photographer-video-url'] = array(
			    'label' => esc_html__('Video URL','grid-plus'),
			    'input' => 'text',
			    'value' => get_post_meta( $post->ID, 'gsf_photographer_video_url', true ),
		    );

		    return $form_fields;
	    }

	    public function save_attachment_field_video($post, $attachment ) {
		    if( isset( $attachment['gsf-photographer-video-url'] ) ) {
			    update_post_meta( $post['ID'], 'gsf_photographer_video_url', esc_url( $attachment['gsf-photographer-video-url'] ) );
		    }
		    return $post;
	    }
    }

    if (!function_exists('grid_plus_load')) {
        function grid_plus_load()
        {
            new Grid_Plus();
        }

        add_action('wp_loaded', 'grid_plus_load');
    } else {
        new Grid_Plus();
    }

}