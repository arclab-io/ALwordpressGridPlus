<?php
/**
 * Created by PhpStorm.
 * User: phuongth
 * Date: 12/17/2016
 * Time: 3:03 PM
 * @var $thumbnail
 * @var $post_link
 * @var $title
 * @var $img_origin
 * @var $ico_gallery
 * @var $disable_link
 */
?>
<div class="grid-post-item thumbnail-title" data-post-info-class="post-info">
    <div class="thumbnail-image" data-img="<?php echo esc_url($thumbnail); ?>">
        <?php if(!empty($thumbnail)): ?>
            <img src="<?php echo esc_attr($thumbnail); ?>" alt="<?php echo esc_html($title); ?>" >
        <?php endif; ?>
        <div class="hover-outer transition-30">
            <?php if($disable_link != 'true'): ?>
                <a href="<?php echo esc_attr($post_link); ?>" title="<?php echo esc_html($title); ?>"></a>
            <?php endif; ?>
            <div class="hover-inner transition-50">
                <div class="icon-groups">
                    <?php if($disable_link != 'true'): ?>
                        <a href="<?php echo esc_attr($post_link); ?>" class="view-detail" ><i class="fa fa-link"></i></a>
                    <?php endif; ?>
                    <a href="javascript:;" data-src="<?php echo esc_url($img_origin); ?>" class="view-gallery" data-post-id="<?php echo get_the_ID(); ?>"
                       data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')) ?>"><i class="<?php echo esc_attr($ico_gallery); ?>"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="post-info">
        <?php if(!empty($title)): ?>
            <div class="title">
                <?php if($disable_link != 'true'): ?>
                    <a href="<?php echo esc_attr($post_link); ?>" title="<?php echo esc_html($title); ?>"><?php echo esc_html($title); ?></a>
                <?php else: ?>
                    <?php echo esc_html($title); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($excerpt)): ?>
            <div class="excerpt"><?php echo wp_kses_post($excerpt); ?></div>
        <?php endif; ?>
    </div>
</div>
