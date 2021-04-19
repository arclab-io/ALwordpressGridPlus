<?php
/**
 * The template for displaying format-audio.php
 *
 * @package WordPress
 * @subpackage emo
 * @since emo 1.0
 */
global $post;
?>
<div class="gf-form-group">
	<label for="<?php echo esc_attr(gfPostFormatUi()->get_format_audio_embed()) ?>"><?php esc_html_e('Audio URL (oEmbed) or Embed Code','grid-plus'); ?></label>
	<textarea rows="5" placeholder="<?php esc_attr_e('Audio URL (oEmbed) or Embed Code','grid-plus'); ?>" class="gf-form-control" name="<?php echo esc_attr(gfPostFormatUi()->get_format_audio_embed()) ?>" id="<?php echo esc_attr(gfPostFormatUi()->get_format_audio_embed()) ?>" tabindex="1"><?php echo esc_textarea(get_post_meta($post->ID, gfPostFormatUi()->get_format_audio_embed(), true)); ?></textarea>
</div>
