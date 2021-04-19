<?php
/**
 * The template for displaying format-link
 *
 * @package WordPress
 * @subpackage emo
 * @since emo 1.0
 */
global $post;
?>
<div class="gf-form-group">
	<label for="<?php echo esc_attr(gfPostFormatUi()->get_format_link_text()) ?>"><?php esc_html_e('Link Text','grid-plus'); ?></label>
	<input class="gf-form-control" type="text" placeholder="<?php esc_attr_e('Link Text','grid-plus'); ?>" name="<?php echo esc_attr(gfPostFormatUi()->get_format_link_text()) ?>" value="<?php echo esc_attr(get_post_meta($post->ID, gfPostFormatUi()->get_format_link_text(), true)); ?>" id="<?php echo esc_attr(gfPostFormatUi()->get_format_link_text()) ?>" tabindex="1" />
</div>
<div class="gf-form-group">
	<label for="<?php echo esc_attr(gfPostFormatUi()->get_format_link_url()) ?>"><?php esc_html_e('Link Url','grid-plus'); ?></label>
	<input class="gf-form-control" type="text" placeholder="<?php esc_attr_e('Link Url','grid-plus'); ?>" name="<?php echo esc_attr(gfPostFormatUi()->get_format_link_url()) ?>" value="<?php echo esc_attr(get_post_meta($post->ID, gfPostFormatUi()->get_format_link_url(), true)); ?>" id="<?php echo esc_attr(gfPostFormatUi()->get_format_link_url()) ?>" tabindex="2" />
</div>
