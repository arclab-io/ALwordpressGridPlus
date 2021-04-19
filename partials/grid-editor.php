<?php
/**
 * Class Dashboard
 *
 * @package WordPress
 * @subpackage emo
 * @since emo 1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( !class_exists( 'GP_Custom_Editor' ) ) {
    class GP_Custom_Editor
    {
        /**
         * The instance of this object
         *
         * @var null|object
         */
        private static $instance;

        /**
         * Init GP_Custom_Editor
         *
         * @return GP_Custom_Editor|null|object
         */
        public static function init()
        {
            if ( self::$instance == NULL ) {
                self::$instance = new self();
                self::$instance->afterInit();
            }
            return self::$instance;
        }

        public function afterInit()
        {
            add_filter('mce_buttons', array($this, 'grid_custom_editor_register_buttons'));
            add_filter('mce_external_plugins', array($this, 'grid_custom_editor_register_tinymce_javascript'));
            add_action('admin_enqueue_scripts', array($this, 'grid_enqueue_scripts'));
        }

        public function grid_enqueue_scripts()
        {
            wp_enqueue_style( 'grid-custom-editor', G5PLUS_GRID_URL . 'assets/css/grid-editor.css' );
            wp_enqueue_script( 'grid-custom-editor', G5PLUS_GRID_URL . 'assets/js/backend/grid-editor.min.js' );
            wp_localize_script( 'grid-custom-editor', 'grid_custom_editor_var',
                array(
                    'menu_name' => esc_html__('Grid Plus', 'grid-plus'),
                    'sub_menu' => $this->grid_get_sub_menu()
                )
            );
        }

        public function grid_get_sub_menu()
        {
            $sub_menu = array();
            $grids = get_option(G5PLUS_GRID_OPTION_KEY, array());
            if (is_array($grids)) {
                foreach ($grids as $grid) {
                    $sub_menu[] = $grid['name'];
                }
            }
            return $sub_menu;
        }

        public function grid_custom_editor_register_buttons( $buttons )
        {
            array_push( $buttons, 'grid_custom_editor', 'separator' );
            return $buttons;
        }

        public function grid_custom_editor_register_tinymce_javascript( $plugin_array )
        {
            $plugin_array['grid_custom_editor'] = G5PLUS_GRID_URL . 'assets/js/backend/grid-editor.min.js';
            return $plugin_array;
        }
    }

    function grid_custom_editor()
    {
        return GP_Custom_Editor::init();
    }
    grid_custom_editor();
}