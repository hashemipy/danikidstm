<?php
/**
 * Custom Footer Template
 */

// Get footer data from shortcode
$footer_data = json_decode(do_shortcode('[k_footer_data]'), true);
$site_title = $footer_data['site_title'] ?? 'دنیای رنگارنگ کودکان';
$site_description = $footer_data['site_description'] ?? 'فروشگاه تخصصی لباس و اکسسوری کودکان با کیفیت بالا و قیمت مناسب';
$useful_links = $footer_data['useful_links'] ?? array();
$customer_service_links = $footer_data['customer_service_links'] ?? array();
$contact_address = $footer_data['contact_address'] ?? '';
$contact_phone = $footer_data['contact_phone'] ?? '';
$copyright = $footer_data['copyright'] ?? '';

$bottom_bar = json_decode(do_shortcode('[k_bottom_bar]'), true);
$show_on_home = $bottom_bar['show_on_home'] ?? true;
$show_on_products = $bottom_bar['show_on_products'] ?? true;
$show_on_all_products = $bottom_bar['show_on_all_products'] ?? true;
$show_on_profile = $bottom_bar['show_on_profile'] ?? true;
$buttons = $bottom_bar['buttons'] ?? array();

$should_show_bottom_bar = false;
if ((is_front_page() && $show_on_home) || 
    (is_product() && $show_on_products) ||
    (is_page_template('page-products.php') && $show_on_all_products) ||
    (is_account_page() && $show_on_profile)) {
    $should_show_bottom_bar = true;
}
?>

<footer class="site-footer" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 0 1rem; margin-top: 4rem; <?php echo $should_show_bottom_bar ? 'margin-bottom: 70px;' : ''; ?>">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <div class="footer-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2.5rem; margin-bottom: 2.5rem;">
            
            <!-- About Column -->
            <div class="footer-column">
                <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: white;"><?php echo esc_html($site_title); ?></h3>
                <p style="opacity: 0.95; line-height: 1.7; font-size: 0.95rem;"><?php echo esc_html($site_description); ?></p>
            </div>
            
            <!-- Useful Links Column -->
            <?php if (!empty($useful_links)) : ?>
            <div class="footer-column">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: white;">لینک‌های مفید</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($useful_links as $link) : ?>
                        <a href="<?php echo esc_url($link['url']); ?>" style="color: white; opacity: 0.9; text-decoration: none; transition: opacity 0.3s; font-size: 0.95rem;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                            <?php echo esc_html($link['text']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <?php endif; ?>
            
            <!-- Customer Service Column -->
            <?php if (!empty($customer_service_links)) : ?>
            <div class="footer-column">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: white;">خدمات مشتریان</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($customer_service_links as $link) : ?>
                        <a href="<?php echo esc_url($link['url']); ?>" style="color: white; opacity: 0.9; text-decoration: none; transition: opacity 0.3s; font-size: 0.95rem;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                            <?php echo esc_html($link['text']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <?php endif; ?>
            
            <!-- Contact Column -->
            <div class="footer-column">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: white;">تماس با ما</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.95rem;">
                    <?php if (!empty($contact_address)) : ?>
                        <div style="display: flex; align-items: start; gap: 0.5rem; opacity: 0.95;">
                            <span style="font-size: 1.2rem;">📍</span>
                            <span style="line-height: 1.6;"><?php echo esc_html($contact_address); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($contact_phone)) : ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; opacity: 0.95;">
                            <span style="font-size: 1.2rem;">📞</span>
                            <a href="tel:<?php echo esc_attr($contact_phone); ?>" style="color: white; text-decoration: none; direction: ltr; display: inline-block;"><?php echo esc_html($contact_phone); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Copyright -->
        <div class="footer-bottom" style="margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.2); text-align: center;">
            <p style="opacity: 0.9; font-size: 0.95rem;"><?php echo esc_html($copyright); ?></p>
        </div>
    </div>
</footer>

<!-- Added floating bottom bar -->
<?php if ($should_show_bottom_bar && !empty($buttons)) : 
    $bg_start_color = get_option('k_bottom_bar_bg_start', '#ffffff');
    $bg_end_color = get_option('k_bottom_bar_bg_end', '#f9fafb');
    $text_color = get_option('k_bottom_bar_text_color', '#374151');
?>
<!-- حذف افکت موجی/ابری -->

<div class="k-bottom-bar" style="position: fixed; bottom: 0; left: 0; right: 0; background: linear-gradient(135deg, <?php echo esc_attr($bg_start_color); ?> 0%, <?php echo esc_attr($bg_end_color); ?> 100%); box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 999; padding: 8px 0; border-top: 1px solid #e5e7eb;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-around; align-items: center; gap: 8px;">
            <?php foreach ($buttons as $button) : 
                if (empty($button['label'])) continue;
                $button_image = !empty($button['image']) ? khoshtip_convert_to_webp_url($button['image']) : '';
            ?>
                <!-- استفاده از رنگ متن قابل تنظیم -->
                <a href="<?php echo esc_url($button['url'] ?? '#'); ?>" 
                   style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; color: <?php echo esc_attr($text_color); ?>; transition: opacity 0.2s; padding: 4px 8px; min-width: 60px;">
                    <?php if (!empty($button_image)) : ?>
                        <img src="<?php echo esc_url($button_image); ?>" 
                             alt="<?php echo esc_attr($button['label']); ?>" 
                             style="width: 48px; height: 34px; object-fit: contain;" />
                    <?php else : ?>
                        <span style="font-size: 24px;">🔘</span>
                    <?php endif; ?>
                    <span style="font-size: 11px; text-align: center; line-height: 1.2;"><?php echo esc_html($button['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
