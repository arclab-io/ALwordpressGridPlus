<?php
/**
 *    Plugin Name: Grid Plus
 *    Plugin URI: http://themes.g5plus.net/plugins/grid/
 *    Description: Grid Plus - Unlimited grid layout for any post type (configured for ArcLab Discover use).
 *    Version: 9.9.9
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

// Intercept requests for /discover/N URLs very early
add_filter('request', function($query_vars) {
    // Check if the current request is for /discover/N
    if (isset($_SERVER['REQUEST_URI']) && preg_match('/\/discover\/(\d+)\/?$/', $_SERVER['REQUEST_URI'], $matches)) {
        // Force WordPress to load the discover page
        $query_vars['pagename'] = 'discover';
        $query_vars['grid_module'] = $matches[1];
        unset($query_vars['error']);
        unset($query_vars['attachment']);
        unset($query_vars['name']);
    }
    return $query_vars;
}, 1);

// Prevent WordPress from showing 404 for /discover/N URLs
add_action('wp', function() {
    global $wp_query;
    
    // If we're on /discover/N URL, make sure it's not a 404
    if (isset($_SERVER['REQUEST_URI']) && preg_match('/\/discover\/(\d+)\/?$/', $_SERVER['REQUEST_URI'])) {
        if ($wp_query->is_404()) {
            // Get the discover page
            $discover_page = get_page_by_path('discover');
            if ($discover_page) {
                // Override the 404
                $wp_query->is_404 = false;
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->queried_object = $discover_page;
                $wp_query->queried_object_id = $discover_page->ID;
                $wp_query->posts = array($discover_page);
                $wp_query->post = $discover_page;
                $wp_query->found_posts = 1;
                $wp_query->post_count = 1;
                $wp_query->max_num_pages = 1;
                
                // Set global post
                global $post;
                $post = $discover_page;
                setup_postdata($post);
                
                // Set the proper template
                status_header(200);
            }
        }
    }
}, 1);

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

            // Add rewrite rules for clean URLs
            add_action('init', array($this, 'grid_plus_add_rewrite_rules'));
            add_filter('query_vars', array($this, 'grid_plus_add_query_vars'));
            add_action('template_redirect', array($this, 'grid_plus_handle_module_redirect'));
            add_filter('redirect_canonical', array($this, 'grid_plus_prevent_redirect'), 10, 2);
            add_action('parse_request', array($this, 'grid_plus_parse_request'));

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
                // name originally 'Thumbnail - icon, title, excerpt'
                array(
                    'name'    => 'Module Modal Template',
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
            wp_register_script('grid-plus-settings', G5PLUS_GRID_URL . 'assets/js/frontend/grid'. $min .'.js', array('wp-util', 'match-media','jquery'), time(), true);
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

        /**
         * Add rewrite rules for clean module URLs
         */
        public function grid_plus_add_rewrite_rules() {
            // Add rewrite rule for /discover/1, /discover/2, etc.
            add_rewrite_rule(
                '^discover/([0-9]+)/?$',
                'index.php?pagename=discover&grid_module=$1',
                'top'
            );
            
            // Also add a more permissive rule for any number
            add_rewrite_rule(
                'discover/([0-9]+)/?$',
                'index.php?pagename=discover&grid_module=$1',
                'top'
            );
        }

        /**
         * Add custom query vars
         */
        public function grid_plus_add_query_vars($vars) {
            $vars[] = 'grid_module';
            return $vars;
        }

        /**
         * Handle module redirect for clean URLs
         */
        public function grid_plus_handle_module_redirect() {
            global $wp_query;
            
            // Debug: Check if we're getting the module number
            if (isset($_SERVER['REQUEST_URI']) && preg_match('/\/discover\/(\d+)\/?$/', $_SERVER['REQUEST_URI'], $matches)) {
                $url_module = $matches[1];
                $query_module = get_query_var('grid_module');
                
                // If we have a module number from URL but not in query var, set it
                if ($url_module && !$query_module) {
                    set_query_var('grid_module', $url_module);
                }
            }
            
            // Check if we're on the discover page with a module number
            if (is_page('discover') && get_query_var('grid_module')) {
                $module_number = intval(get_query_var('grid_module'));
                
                // Add JavaScript to open the module
                add_action('wp_footer', function() use ($module_number) {
                    ?>
                    <script>
                    jQuery(document).ready(function($) {
                        console.log('[GridPlus PHP] Opening module <?php echo $module_number; ?> from server-side');
                        // Store the intended module number globally
                        window.gridPlusUrlModule = <?php echo $module_number; ?>;
                        
                        // Add flag to prevent multiple triggers
                        if (!window.gridPlusPhpTriggered) {
                            window.gridPlusPhpTriggered = true;
                            
                            // Wait for grid to load and ensure handlers are bound
                            setTimeout(function() {
                                // Ensure view gallery handlers are initialized
                                if (typeof GridPlus !== 'undefined' && GridPlus.initViewGallery) {
                                    console.log('[GridPlus PHP] Ensuring view gallery handlers are initialized');
                                    GridPlus.initViewGallery($('.grid-plus-container'));
                                }
                                
                                setTimeout(function() {
                                    // Find the nth module (1-indexed)
                                    var $module = $('.grid-post-item:eq(' + (<?php echo $module_number; ?> - 1) + ')');
                                    if ($module.length) {
                                        var $link = $module.find('a.view-gallery');
                                        if ($link.length && !$('body').hasClass('lg-on')) {
                                            // Set the module index before clicking
                                            $link.attr('data-module-index', <?php echo $module_number; ?>);
                                            console.log('[GridPlus PHP] Triggering click on module <?php echo $module_number; ?>');
                                            $link.trigger('click');
                                        } else if ($('body').hasClass('lg-on')) {
                                            console.log('[GridPlus PHP] Light Gallery already open, skipping trigger');
                                        }
                                    } else {
                                        console.log('[GridPlus PHP] Module <?php echo $module_number; ?> not found');
                                    }
                                }, 200);
                            }, 1800);
                        } else {
                            console.log('[GridPlus PHP] Already triggered, skipping duplicate');
                        }
                    });
                    </script>
                    <?php
                }, 100);
            }
            
            // Also check for query parameter ?m=N
            if (is_page('discover') && isset($_GET['m'])) {
                $module_number = intval($_GET['m']);
                
                // Add JavaScript to open the module
                add_action('wp_footer', function() use ($module_number) {
                    ?>
                    <script>
                    jQuery(document).ready(function($) {
                        console.log('[GridPlus PHP] Opening module <?php echo $module_number; ?> from query parameter');
                        // Store in sessionStorage for JavaScript to pick up
                        sessionStorage.setItem('gridPlusModuleToOpen', '<?php echo $module_number; ?>');
                        // Reload without query parameter to clean URL
                        if (window.location.search.includes('m=')) {
                            history.replaceState(null, null, '/discover/');
                        }
                    });
                    </script>
                    <?php
                }, 100);
            }
        }
        
        /**
         * Prevent WordPress from redirecting /discover/N URLs
         */
        public function grid_plus_prevent_redirect($redirect_url, $requested_url) {
            // Check if this is a /discover/N URL
            if (preg_match('/\/discover\/\d+\/?$/', $requested_url)) {
                // Prevent redirect for these URLs
                return false;
            }
            return $redirect_url;
        }
        
        /**
         * Parse request to handle /discover/N URLs
         */
        public function grid_plus_parse_request($wp) {
            // Check if this is a /discover/N URL
            if (preg_match('/discover\/(\d+)\/?$/', $_SERVER['REQUEST_URI'], $matches)) {
                // Force WordPress to load the discover page
                $wp->query_vars['pagename'] = 'discover';
                $wp->query_vars['grid_module'] = $matches[1];
                $wp->query_vars['error'] = false; // Prevent 404
            }
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