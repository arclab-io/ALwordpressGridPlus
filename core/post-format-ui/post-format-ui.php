<?php
/**
 * The template for displaying post-format-ui.php
 *
 * @package WordPress
 * @subpackage g5theme-framework
 * @since g5theme-framework 1.0
 */
if (!defined('ABSPATH')) {
	exit('Direct script access denied.');
}
if (!class_exists('GF_Post_Formats_UI')) {
	class GF_Post_Formats_UI
	{

		private $format_link_text = 'gf_format_link_text';
		private $format_link_url = 'gf_format_link_url';


		private $format_quote_content = 'gf_format_quote_content';
		private $format_quote_author_text = 'gf_format_quote_author_text';
		private $format_quote_author_url = 'gf_format_quote_author_url';


		private $format_video_embed = 'gf_format_video_embed';

		private $format_audio_embed = 'gf_format_audio_embed';

		private $format_gallery_images = 'gf_format_gallery_images';


		/**
		 * The instance of this object
		 *
		 * @var null|object
		 */
		private static $instance;

		private $has_gutenberg = false;


		/**
		 * Init GF_Custom_Css
		 *
		 * @return GF_Post_Formats_UI|null|object
		 */
		public static function init()
		{
			if (self::$instance == NULL) {
				self::$instance = new self();
				self::$instance->afterInit();
			}

			return self::$instance;
		}

		public function afterInit()
		{
			add_action('save_post', array($this, 'update_post_meta'));
			add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
			add_action( 'enqueue_block_editor_assets', array($this,'check_gutenberg' ));
		}

		public function check_gutenberg() {
			$this->has_gutenberg = true;
		}

		/**
		 * Get Plugin Url
		 *
		 * @return string
		 */
		public function get_plugin_url()
		{
			$plugin_url = trailingslashit(plugins_url('g5theme-framework/core/post-format-ui'));
			$plugin_url = apply_filters('gf-post-format-ui/plugin-url', $plugin_url);
			return $plugin_url;
		}

		/**
		 * Get Plugin Dir
		 *
		 * @return string
		 */
		public function get_plugin_dir()
		{
			return plugin_dir_path(__FILE__);
		}

		/**
		 * Plugin Version
		 *
		 * @return string
		 */
		public function get_plugin_version()
		{
			return '1.0';
		}

		public function get_plugin_prefix()
		{
			return apply_filters('gf-post-format-ui/plugin-prefix', 'sf_');
		}

		public function get_template_part($slug, $args = array())
		{
			if ($args && is_array($args)) {
				extract($args);
			}
			$located = $this->get_plugin_dir() . 'templates/' . $slug . '.php';
			if (!file_exists($located)) {
				_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $slug), '1.0');
				return;
			}
			include($located);
		}

		public function get_format_link_text()
		{
			return $this->format_link_text;
		}

		public function get_format_link_url()
		{
			return $this->format_link_url;
		}

		public function get_format_quote_content()
		{
			return $this->format_quote_content;
		}

		public function get_format_quote_author_text()
		{
			return $this->format_quote_author_text;
		}

		public function get_format_quote_author_url()
		{
			return $this->format_quote_author_url;
		}

		public function get_format_video_embed()
		{
			return $this->format_video_embed;
		}

		public function get_format_audio_embed()
		{
			return $this->format_audio_embed;
		}

		public function get_format_gallery_images()
		{
			return $this->format_gallery_images;
		}

		public function get_post_type_support()
		{
			$post_type = array('post');
			return apply_filters('gf-post-format-ui/post-type', $post_type);
		}

		public function update_post_meta($post_id)
		{
			if (!defined('XMLRPC_REQUEST')) {
				$keys = array(
					$this->format_link_text,
					$this->format_link_url,
					$this->format_quote_content,
					$this->format_quote_author_text,
					$this->format_quote_author_url,
					$this->format_video_embed,
					$this->format_audio_embed,
					$this->format_gallery_images
				);
				foreach ($keys as $key) {
					if (isset($_POST[$key])) {
						update_post_meta($post_id, $key, $_POST[$key]);
					}
				}

				if (isset($_POST['gsf_post_format'])) {
					set_post_format( $post_id, $_POST['gsf_post_format']);
				}
			}
		}

		public function add_meta_boxes($post_type)
		{
			$post_type_support = $this->get_post_type_support();
			if (in_array($post_type, $post_type_support)) {
				wp_enqueue_style($this->get_plugin_prefix() . 'post-format-ui', $this->get_plugin_url() . 'assets/css/post-format-ui.css', array(), $this->get_plugin_version());
				wp_enqueue_script($this->get_plugin_prefix() . 'media', $this->get_plugin_url() . 'assets/js/media.js', array('jquery'), $this->get_plugin_version(), true);
				wp_enqueue_script($this->get_plugin_prefix() . 'gallery', $this->get_plugin_url() . 'assets/js/gallery.js', array(), $this->get_plugin_version(), true);
				wp_enqueue_script($this->get_plugin_prefix() . 'post-format-ui', $this->get_plugin_url() . 'assets/js/post-format-ui.js', array('jquery'), $this->get_plugin_version(), true);
				if ($this->has_gutenberg) {
					if ($post_type === 'post') {
						add_meta_box('g5plus-post-format', esc_html__( 'Post Format Meta', 'grid-plus' ), array($this, 'render_meta_boxes'), 'post', 'advanced', 'default');
					} else {
						add_meta_box('g5plus-post-format', esc_html__( 'Post Format Meta', 'grid-plus' ), array($this, 'render_meta_boxes_with_tabs'), $post_type_support, 'advanced', 'default');
					}

				} else {
					add_action('edit_form_after_title', array($this, 'render_meta_boxes_after_title'));
				}


			}
		}

		public function render_meta_boxes_after_title($post) {
			$post_type = get_post_type($post);
			$post_format_views = array('standard', 'gallery', 'video', 'link', 'quote', 'audio');
			$post_formats = get_theme_support('post-formats');
			if ($post_formats === false) {
				$post_formats = array();
			} else {
				$post_formats = $post_formats[0];
			}

			$current_post_format = $this->get_post_format($post->ID);
			array_unshift($post_formats, 'standard');
			if ($current_post_format === false) {
				$current_post_format = 'standard';
			}
			if ($post_type !== 'post') {
				$post_formats = array('standard', 'video', 'gallery');
			}
			$this->get_template_part('tabs', array(
				'post_formats' => $post_formats,
				'current_post_format' => $current_post_format,
				'post_format_views' => $post_format_views,
				'post_type' => $post_type,
				'tabs' => true
			));
		}

		public function render_meta_boxes_with_tabs($post) {
			$post_type = get_post_type($post);
			$post_format_views = array('standard', 'gallery', 'video', 'link', 'quote', 'audio');
			$post_formats = get_theme_support('post-formats');
			if ($post_formats === false) {
				$post_formats = array();
			} else {
				$post_formats = $post_formats[0];
			}

			$current_post_format = $this->get_post_format($post->ID);
			array_unshift($post_formats, 'standard');
			if ($current_post_format === false) {
				$current_post_format = 'standard';
			}
			if ($post_type !== 'post') {
				$post_formats = array('standard', 'video', 'gallery');
			}
			$this->get_template_part('tabs', array(
				'post_formats' => $post_formats,
				'current_post_format' => $current_post_format,
				'post_format_views' => $post_format_views,
				'post_type' => $post_type,
				'tabs' => true
			));
		}

		public function render_meta_boxes($post)
		{
			$post_type = get_post_type($post);
			$post_format_views = array('standard', 'gallery', 'video', 'link', 'quote', 'audio');
			$post_formats = get_theme_support('post-formats');
			if ($post_formats === false) {
				$post_formats = array();
			} else {
				$post_formats = $post_formats[0];
			}

			$current_post_format = $this->get_post_format($post->ID);
			array_unshift($post_formats, 'standard');
			if ($current_post_format === false) {
				$current_post_format = 'standard';
			}
			if ($post_type !== 'post') {
				$post_formats = array('standard', 'video', 'gallery');
			}
			$this->get_template_part('tabs', array(
				'post_formats' => $post_formats,
				'current_post_format' => $current_post_format,
				'post_format_views' => $post_format_views,
				'post_type' => $post_type,
				'tabs' => false
			));
		}

		public function get_post_format($post = null)
		{
			if (!$post = get_post($post))
				return false;

			$_format = get_the_terms($post->ID, 'post_format');

			if (empty($_format))
				return false;

			$format = reset($_format);

			return str_replace('post-format-', '', $format->slug);
		}
	}

	function gfPostFormatUi()
	{
		return GF_Post_Formats_UI::init();
	}

	gfPostFormatUi();

}