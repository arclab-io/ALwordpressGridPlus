<?php
/**
 * Created by PhpStorm.
 * User: phuongth
 * Date: 2/17/2017
 * Time: 2:15 PM
 * @var $thumbnail
 * @var $post_link
 * @var $title
 * @var $ico_gallery
 * @var $disable_link
 * @var $price
 */
global $post;
$regular_price = $sale_price = $is_sale = $is_feature = 0;
$rating = '';
$sale_text = apply_filters('grid-plus-woocommerce-sale-text', esc_html__('Sale', 'grid-plus'));
$feature_text = apply_filters('grid-plus-woocommerce-feature-text', esc_html__('Hot', 'grid-plus'));
if (!isset($is_backend) && 'product' == get_post_type($post->ID)) {
    if (!isset($post->ID) || !function_exists('wc_get_product')) {
        return;
    }
    $product = wc_get_product($post->ID);
    $is_sale = $product->is_on_sale();
    $is_feature = $product->is_featured();
    $price = apply_filters('grid-plus-price', $product->get_price_html());
}
?>
<div class="grid-post-item woocommerce thumbnail-title post-info-center" data-post-info-class="post-info">
    <div class="thumbnail-image" data-img="<?php echo esc_url($thumbnail); ?>">
        <?php if(!empty($thumbnail)): ?>
            <img src="<?php echo esc_attr($thumbnail); ?>" alt="<?php echo esc_html($title); ?>" >
        <?php endif; ?>
        <div class="flag-wrap">
            <?php if ($is_sale): ?>
                <div class="flag on-sale">
                    <?php echo esc_html($sale_text); ?>
                </div>
            <?php endif; ?>
            <?php if ($is_feature): ?>
                <div class="flag on-feature">
                    <?php echo esc_html($feature_text); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="hover-outer transition-30">
            <?php if($disable_link != 'true'): ?>
                <a href="<?php echo esc_attr($post_link); ?>" title="<?php echo esc_html($title); ?>"></a>
            <?php endif; ?>
            <div class="hover-inner transition-50">
                <div class="icon-groups">
                    <a href="javascript:;" class="view-gallery" data-src="<?php echo esc_url($thumbnail); ?>"
                       data-post-id="<?php echo get_the_ID(); ?>" title="<?php echo esc_attr($title); ?>"
                       data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')) ?>"><i
                            class="<?php echo esc_attr($ico_gallery); ?>"></i></a>
                    <?php
                    if (!isset($is_backend) && 'product' == get_post_type($post->ID)) {
                        if (!shortcode_exists('yith_compare_button') && class_exists('YITH_Woocompare') && function_exists('yith_woocompare_constructor')) {
                            $context = isset($_REQUEST['context']) ? $_REQUEST['context'] : null;
                            $_REQUEST['context'] = 'frontend';
                            yith_woocompare_constructor();
                            $_REQUEST['context'] = $context;
                        }
                        if (shortcode_exists('yith_compare_button')) {
                            echo do_shortcode('[yith_compare_button]');
                        }
                        if (shortcode_exists('yith_wcwl_add_to_wishlist')) {
                            echo do_shortcode('[yith_wcwl_add_to_wishlist]');
                        }
                    }
                    ?>
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
        <?php if(!empty($price)): ?>
            <div class="price"><?php echo sprintf('%s', $price); ?></div>
        <?php endif; ?>
    </div>
</div>