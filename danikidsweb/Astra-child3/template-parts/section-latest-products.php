<?php
/**
 * Latest Products Section Template (Accordion Style - Open by Default)
 */

// Get latest products data from shortcode
$products_data = json_decode(do_shortcode('[k_latest_products_data]'), true);
$product_ids = $products_data['product_ids'] ?? array();
$gradient_start = $products_data['gradient_start'] ?? '#667eea';
$gradient_end = $products_data['gradient_end'] ?? '#d4d9ff';
$button_bg = $products_data['button_bg'] ?? '#667eea';
$button_text = $products_data['button_text'] ?? '#ffffff';

if (empty($product_ids)) {
    return;
}
?>

<section class="latest-products-section accordion-section" style="padding: 2rem 0;">
    <div class="container">
        <!-- Added active class and proper data attributes for accordion functionality -->
        <div class="accordion-item active" style="margin-bottom: 2rem;">
            <!-- استفاده از رنگ‌های دینامیک به جای رنگ‌های استاتیک -->
            <button class="accordion-button" data-accordion-trigger style="width: 100%; padding: 1.5rem; font-size: 1.5rem; font-weight: 700; text-align: center; border: noneش; border-radius: var(--radius); cursor: pointer; transition: all 0.3s; background: linear-gradient(135deg, <?php echo esc_attr($gradient_start); ?> 0%, <?php echo esc_attr($gradient_end); ?> 100%); color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <span>جدیدترین محصولات</span>
                <span class="accordion-icon" style="margin-right: 1rem; transition: transform 0.3s; display: inline-block;">▼</span>
            </button>
            
            <!-- Content visible by default with data attribute -->
            <div data-accordion-content style="padding-top: 2rem; display: block;">
                <div class="swiper latest-products-swiper">
                    <div class="swiper-wrapper">
                        <?php 
                        foreach ($product_ids as $product_id) : 
                            $product_card = khoshtip_kocholo_get_product_card($product_id);
                            if (!empty($product_card)) :
                        ?>
                            <div class="swiper-slide">
                                <?php echo $product_card; ?>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <!-- استفاده از رنگ‌های دینامیک برای دکمه -->
                    <a href="<?php echo esc_url(home_url('/products/?type=latest')); ?>" class="view-more-btn" style="display: inline-block; padding: 0.75rem 2rem; background-color: <?php echo esc_attr($button_bg); ?>; color: <?php echo esc_attr($button_text); ?>; text-decoration: none; border-radius: var(--radius); font-weight: 600; transition: all 0.3s;">
                        مشاهده همه محصولات جدید ←
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
