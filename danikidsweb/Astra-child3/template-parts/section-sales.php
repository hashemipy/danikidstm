<?php
/**
 * Sales Section Template
 */

// Get sales data from shortcode
$sales_data = json_decode(do_shortcode('[k_sales_data]'), true);
$sale_end_timestamp = $sales_data['sale_end_timestamp'] ?? 0;
$product_ids = $sales_data['product_ids'] ?? array();

if (empty($product_ids) || ($sale_end_timestamp > 0 && $sale_end_timestamp < time())) {
    return;
}
?>

<section class="sales-section" style="padding: 1.5rem 0;">
    <div class="container">
        <!-- Redesigned sale banner with matte red background and glowing white text -->
        <div class="sale-banner" style="background: #dc143c; color: white; padding: 1rem 1.5rem; margin-bottom: 2rem; border-radius: var(--radius); display: flex; flex-direction: row; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 8px 24px rgba(220, 20, 60, 0.5); position: relative;">
            <h3 style="font-size: clamp(1.25rem, 4vw, 1.75rem); font-weight: 900; margin: 0; white-space: nowrap; color: white; text-shadow: 0 0 15px rgba(255, 255, 255, 0.9), 0 0 25px rgba(255, 255, 255, 0.6), 0 2px 8px rgba(0, 0, 0, 0.4); animation: shimmer 3s ease-in-out infinite;">🔥 حراج ویژه</h3>
            <?php if (!empty($sale_end_timestamp)) : ?>
                <!-- Multiply by 1000 to convert Unix timestamp (seconds) to JavaScript timestamp (milliseconds) -->
                <div id="countdown-timer" data-target-timestamp="<?php echo esc_attr($sale_end_timestamp * 1000); ?>" class="countdown-timer" style="flex: 1;">
                    <div class="countdown-days-wrapper countdown-unit">
                        <span class="countdown-number countdown-days">00</span>
                        <span class="countdown-label">روز</span>
                    </div>
                    <div class="countdown-unit">
                        <span class="countdown-number countdown-seconds">00</span>
                        <span class="countdown-label">ثانیه</span>
                    </div>
                    <div class="countdown-unit">
                        <span class="countdown-number countdown-minutes">00</span>
                        <span class="countdown-label">دقیقه</span>
                    </div>
                    <div class="countdown-unit">
                        <span class="countdown-number countdown-hours">00</span>
                        <span class="countdown-label">ساعت</span>
                    </div>
                </div>
            <?php endif; ?>
            <!-- More attractive white glowing button -->
            <a href="<?php echo esc_url(home_url('/products/?type=sale')); ?>" style="padding: 0.6rem 1.2rem; background: white; color: #dc143c; border-radius: 0.5rem; font-weight: 800; text-decoration: none; font-size: 0.95rem; white-space: nowrap; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255, 255, 255, 0.4), 0 0 20px rgba(255, 255, 255, 0.3); text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(255, 255, 255, 0.6), 0 0 30px rgba(255, 255, 255, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(255, 255, 255, 0.4), 0 0 20px rgba(255, 255, 255, 0.3)'">
                نمایش همه ←
            </a>
        </div>
        
        <!-- Product Carousel -->
        <div class="swiper sales-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($product_ids as $product_id) : ?>
                    <div class="swiper-slide">
                        <?php echo khoshtip_kocholo_get_product_card($product_id); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<style>
@keyframes shimmer {
  0% { text-shadow: 0 0 15px rgba(255, 255, 255, 0.9), 0 0 25px rgba(255, 255, 255, 0.6), 0 2px 8px rgba(0, 0, 0, 0.4); }
  50% { text-shadow: 0 0 25px rgba(255, 255, 255, 1), 0 0 40px rgba(255, 255, 255, 0.8), 0 2px 8px rgba(0, 0, 0, 0.4); }
  100% { text-shadow: 0 0 15px rgba(255, 255, 255, 0.9), 0 0 25px rgba(255, 255, 255, 0.6), 0 2px 8px rgba(0, 0, 0, 0.4); }
}
</style>
