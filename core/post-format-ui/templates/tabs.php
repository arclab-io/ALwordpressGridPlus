<?php
/**
 * The template for displaying tabs.php
 *
 * @package WordPress
 * @subpackage g5theme-framework
 * @since g5theme-framework 1.0
 * @var $current_post_format
 * @var $post_formats
 * @var $post_format_views
 * @var $post_type
 * @var $tabs
 */
if(!is_array($post_formats) || empty($post_formats)) return;
?>
<div class="gf-post-formats-ui-tabs" id="gf-post-formats-ui-tabs">
	<?php if ($post_type !== 'post'): ?>
		<input type="hidden" name="gsf_post_format" id="gsf_post_format" value="<?php echo esc_attr($current_post_format) ?>">
	<?php endif; ?>
	<?php if ($tabs === true): ?>
		<ul class="tab-nav clearfix">
			<?php
			foreach ($post_format_views as $post_format) {
				if (!in_array($post_format,$post_formats)) continue;
				$class = 'post-format-icon post-format-'. $post_format .  ($post_format == $current_post_format || (empty($current_post_format) && $post_format == 'standard') ? ' active' : '');
				if ($post_format == 'standard') {
					$format_string = esc_html__('Standard','g5theme-framework');
					$format_hash = 'post-format-0';
					$format_id = '0';
				}
				else {
					$format_string = get_post_format_string($post_format);
					$format_hash = 'post-format-'.$post_format;
					$format_id = $post_format;
				}
				?>
				<li>
					<a data-format="<?php echo esc_attr($format_id); ?>" class="<?php echo esc_attr($class); ?>" href="#<?php echo esc_attr($format_hash) ?>">
						<?php echo esc_html($format_string); ?>
					</a>
				</li>
				<?php
			}
			?>
		</ul>
	<?php endif; ?>
	<div class="tab-content">
		<?php
		foreach ($post_format_views as $post_format) {
			if (!in_array($post_format,$post_formats) || ($post_format === 'standard')) continue;
			$class = ($post_format == $current_post_format)  ? ' active' : '';
			$format_hash = 'post-format-'.$post_format;
			?>
			<div class="postbox tab-pane<?php echo esc_attr($class); ?>" id="tab-<?php echo esc_attr($format_hash);?>">
				<?php gfPostFormatUi()->get_template_part("format-{$post_format}");?>
			</div>
			<?php
		}
		?>
	</div>
</div>
