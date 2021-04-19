<?php
/**
 * The template for displaying format-gallery.php
 *
 * @package WordPress
 * @subpackage emo
 * @since emo 1.0
 */
global $post;
$images = get_post_meta($post->ID, gfPostFormatUi()->get_format_gallery_images(), true);
$images_arr = explode('|', $images);
?>
<div class="gf-form-group">
	<label><?php esc_html_e('Gallery','grid-plus'); ?></label>
	<div class="sf-field-gallery-inner">
		<input type="hidden" name="<?php echo esc_attr(gfPostFormatUi()->get_format_gallery_images()) ?>" value="<?php echo esc_attr($images); ?>" />
		<?php foreach ($images_arr as $image) : ?>
				<?php
					if (empty($image)) {
						continue;
					}
					$image_url = '';
					$image_attributes = wp_get_attachment_image_src($image);
					if (!empty($image_attributes) && is_array($image_attributes)) {
						$image_url = $image_attributes[0];
					}
				?>
				<div class="sf-image-preview" data-id="<?php echo esc_attr($image); ?>">
					<div class="centered">
						<img src="<?php echo esc_url($image_url); ?>"/>
					</div>
					<span class="sf-gallery-remove dashicons dashicons dashicons-no-alt"></span>
				</div>
		<?php endforeach; ?>
		<div class="sf-gallery-add">
			<?php esc_html_e('+ Add Images', 'grid-plus'); ?>
		</div>
	</div>
</div>
