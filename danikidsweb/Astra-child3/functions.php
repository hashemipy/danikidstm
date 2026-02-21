<?php
/**
 * Khoshtip Kocholo Child Theme Functions
 */

// Enqueue Parent and Child Theme Styles
function khoshtip_kocholo_enqueue_styles() {
    // Enqueue parent theme style
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue child theme style
    wp_enqueue_style('khoshtip-kocholo-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style'), '1.0.0');
    
    // Enqueue component styles
    wp_enqueue_style('khoshtip-kocholo-components', get_stylesheet_directory_uri() . '/assets/css/components.css', array('khoshtip-kocholo-child-style'), '1.0.0');
    
    // Enqueue authentication styles
    wp_enqueue_style('khoshtip-kocholo-auth', get_stylesheet_directory_uri() . '/assets/css/auth-styles.css', array('khoshtip-kocholo-child-style'), '1.0.0');
    
    // Enqueue Swiper CSS for carousels
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0');
    
    // Enqueue Swiper JS
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);
    
    // Enqueue custom JavaScript
    wp_enqueue_script('khoshtip-kocholo-main', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery', 'swiper-js'), '1.0.0', true);
    
    // Enqueue Google authentication script
    wp_enqueue_script('khoshtip-google-auth', get_stylesheet_directory_uri() . '/assets/js/google-auth.js', array('jquery'), '1.0.0', true);
    wp_localize_script('khoshtip-google-auth', 'khoshtipAuth', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('google_auth_nonce'),
        'smsNonce' => wp_create_nonce('sms_auth_nonce')
    ));
    
    if (get_option('khoshtip_sms_enabled') === '1') {
        wp_enqueue_script('khoshtip-sms-auth', get_stylesheet_directory_uri() . '/assets/js/sms-auth.js', array('jquery'), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'khoshtip_kocholo_enqueue_styles');

function khoshtip_add_custom_image_sizes() {
    // Add larger custom size for product cards (2x size for retina displays)
    add_image_size('product-card-high', 600, 750, true); // Hard crop for consistent dimensions
    add_image_size('product-card-medium', 480, 600, true);
}
add_action('after_setup_theme', 'khoshtip_add_custom_image_sizes');

function khoshtip_increase_image_quality($quality, $mime_type) {
    // Increase quality to 90% for JPEG and WebP
    return 90;
}
add_filter('jpeg_quality', 'khoshtip_increase_image_quality', 10, 2);
add_filter('wp_editor_set_quality', 'khoshtip_increase_image_quality', 10, 2);

function khoshtip_woocommerce_image_sizes() {
    return array(
        'woocommerce_thumbnail' => array(
            'width'  => 480,
            'height' => 600,
            'crop'   => 1,
        ),
        'woocommerce_single' => array(
            'width'  => 800,
            'height' => 1000,
            'crop'   => 1,
        ),
        'woocommerce_gallery_thumbnail' => array(
            'width'  => 150,
            'height' => 150,
            'crop'   => 1,
        ),
    );
}
add_filter('woocommerce_get_image_size_woocommerce_thumbnail', function() {
    return array('width' => 480, 'height' => 600, 'crop' => 1);
});
add_filter('woocommerce_get_image_size_woocommerce_single', function() {
    return array('width' => 800, 'height' => 1000, 'crop' => 1);
});

// Add WooCommerce Support
function khoshtip_kocholo_add_woocommerce_support() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'khoshtip_kocholo_add_woocommerce_support');

require_once get_stylesheet_directory() . '/khoshtip-kocholo-google-auth.php';
require_once get_stylesheet_directory() . '/khoshtip-kocholo-admin-settings.php';
require_once get_stylesheet_directory() . '/khoshtip-kocholo-sms-auth.php';

// Convert any image URL to optimized WebP URL if available
/**
 * Convert any image URL to optimized WebP URL if available
 * Works with direct URLs (not just attachment IDs)
 * 
 * @param string $image_url Original image URL
 * @param string $size Image size (for -size.webp suffix)
 * @return string Optimized WebP URL or original URL
 */
function khoshtip_convert_to_webp_url($image_url, $size = 'medium') {
    if (empty($image_url)) {
        return '';
    }
    
    // دریافت اطلاعات پوشه آپلود
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    $base_dir = $upload_dir['basedir'];
    
    // بررسی اینکه URL در پوشه uploads باشد
    if (strpos($image_url, $base_url) === false) {
        return $image_url;
    }
    
    // اگر قبلاً در پوشه webp هست، برگردان
    if (strpos($image_url, '/webp/') !== false) {
        return $image_url;
    }
    
    // استخراج مسیر نسبی از URL
    // مثال: https://site.com/wp-content/uploads/2024/01/image.jpg -> /2024/01/image.jpg
    $relative_path = str_replace($base_url, '', $image_url);
    
    // دریافت اطلاعات فایل
    $path_info = pathinfo($relative_path);
    $dir = $path_info['dirname'];
    $filename = $path_info['filename'];
    
    $webp_variations = array(
        // فرمت با -size.webp (مثل image-300x300-medium.webp)
        '/webp' . $dir . '/' . $filename . '-' . $size . '.webp',
        // فرمت ساده .webp (مثل image-300x300.webp)
        '/webp' . $dir . '/' . $filename . '.webp',
    );
    
    foreach ($webp_variations as $webp_relative_path) {
        $webp_file_path = $base_dir . $webp_relative_path;
        
        if (file_exists($webp_file_path)) {
            $webp_url = $base_url . $webp_relative_path;
            return $webp_url;
        }
    }
    
    // اگر فایل WebP وجود نداشت، URL اصلی را برگردان
    return $image_url;
}

/**
 * Get optimized WebP image URL if available, otherwise return original
 * 
 * @param int $attachment_id Image attachment ID
 * @param string $size Image size (thumbnail, medium, large, full)
 * @return string Image URL
 */
function khoshtip_get_optimized_image_url($attachment_id, $size = 'medium') {
    if (empty($attachment_id)) {
        return '';
    }
    
    // دریافت مسیر اصلی تصویر
    $image_data = wp_get_attachment_image_src($attachment_id, $size);
    if (!$image_data) {
        return '';
    }
    
    $original_url = $image_data[0];
    
    return khoshtip_convert_to_webp_url($original_url, $size);
}

/**
 * Get optimized product image HTML
 * 
 * @param WC_Product $product Product object
 * @param string $size Image size
 * @param array $attr Additional attributes
 * @return string Image HTML
 */
function khoshtip_get_optimized_product_image($product, $size = 'medium', $attr = array()) {
    if (!$product) {
        return '';
    }
    
    $image_id = $product->get_image_id();
    
    if (!$image_id) {
        // اگر تصویر شاخص نداشت، تصویر پیش‌ف��ض WooCommerce را نمایش بده
        return $product->get_image($size, $attr);
    }
    
    $display_size = ($size === 'medium') ? 'product-card-medium' : $size;
    
    // دریافت URL بهینه شده
    $image_url = khoshtip_get_optimized_image_url($image_id, $display_size);
    
    if (empty($image_url)) {
        return $product->get_image($display_size, $attr);
    }
    
    // دریافت اطلاعات تصویر
    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    if (empty($image_alt)) {
        $image_alt = $product->get_name();
    }
    
    // ساخت attributes
    $default_attr = array(
        'alt' => $image_alt,
        'loading' => 'lazy',
        'onload' => 'this.classList.add("loaded")',
    );
    
    $attr = array_merge($default_attr, $attr);
    
    // دریافت ابعاد تصویر
    $image_data = wp_get_attachment_image_src($image_id, $display_size);
    $width = $image_data[1] ?? '';
    $height = $image_data[2] ?? '';
    
    $srcset_array = array();
    $sizes_to_include = array('product-card-medium', 'product-card-high', 'woocommerce_thumbnail');
    
    foreach ($sizes_to_include as $size_name) {
        $size_data = wp_get_attachment_image_src($image_id, $size_name);
        if ($size_data) {
            $webp_url = khoshtip_convert_to_webp_url($size_data[0], $size_name);
            $descriptor_width = $size_data[1];
            if (!empty($webp_url) && !empty($descriptor_width)) {
                $srcset_array[] = esc_url($webp_url) . ' ' . $descriptor_width . 'w';
            }
        }
    }
    
    $srcset = !empty($srcset_array) ? implode(', ', $srcset_array) : '';
    $sizes = '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 20vw';
    
    // ساخت HTML
    $attr_string = '';
    foreach ($attr as $name => $value) {
        $attr_string .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
    }
    
    // Add custom srcset and sizes
    if ($srcset) {
        $attr_string .= ' srcset="' . $srcset . '"';
    }
    if ($sizes) {
        $attr_string .= ' sizes="' . esc_attr($sizes) . '"';
    }
    
    $html = sprintf(
        '<img src="%s" width="%s" height="%s"%s>',
        esc_url($image_url),
        esc_attr($width),
        esc_attr($height),
        $attr_string
    );
    
    return $html;
}

function khoshtip_kocholo_flush_rewrite_rules() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'khoshtip_kocholo_flush_rewrite_rules');

// Remove Astra default header if needed
function khoshtip_kocholo_remove_astra_header() {
    remove_action('astra_header', 'astra_header_markup');
}
add_action('init', 'khoshtip_kocholo_remove_astra_header');

// Custom function to get WooCommerce product card HTML
function khoshtip_kocholo_get_product_card($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_in_stock()) {
        return '';
    }
    
    $price_html = '';
    if ($product->is_type('variable')) {
        // For variable products
        $min_regular_price = $product->get_variation_regular_price('min', true);
        $min_sale_price = $product->get_variation_sale_price('min', true);
        
        // بررسی اینکه قیمت معتبر است یا نه
        if (empty($min_regular_price) || $min_regular_price <= 0) {
            return ''; // اگر قیمت معتبر نیست، محصول رو نمایش نده
        }
        
        if (!empty($min_sale_price) && $min_sale_price < $min_regular_price) {
            $price_html = '<del>' . number_format($min_regular_price) . '</del> <ins>' . wc_price($min_sale_price) . '</ins>';
        } else {
            $price_html = wc_price($min_regular_price);
        }
    } else {
        // For simple products
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        
        // بررسی اینکه قیمت معتبر است یا نه
        if (empty($regular_price) || $regular_price <= 0) {
            return ''; // اگر قیمت معتبر نیست، محصول رو نمایش نده
        }
        
        if (!empty($sale_price) && $sale_price < $regular_price) {
            $price_html = '<del>' . number_format($sale_price) . '</del> <ins>' . wc_price($sale_price) . '</ins>';
        } else {
            $price_html = wc_price($regular_price);
        }
    }
    
    ob_start();
    ?>
    <div class="product-card">
        <a href="<?php echo esc_url($product->get_permalink()); ?>" class="product-card-link">
            <div class="product-card-image">
                <?php 
                $image = khoshtip_get_optimized_product_image($product, 'medium', array(
                    'loading' => 'lazy',
                    'onload' => 'this.classList.add("loaded")'
                ));
                echo $image;
                ?>
                <?php if ($product->is_on_sale()) : ?>
                    <span class="sale-badge">حراج</span>
                <?php endif; ?>
            </div>
            <div class="product-card-content">
                <h4 class="product-card-title"><?php echo esc_html($product->get_name()); ?></h4>
                <div class="product-card-price">
                    <?php echo $price_html; ?>
                </div>
            </div>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

function khoshtip_load_more_products_ajax() {
    // Verify nonce
    if (!check_ajax_referer('load_more_products', 'nonce', false)) {
        wp_send_json_error('Nonce verification failed');
        return;
    }
    
    // Get product IDs from request
    $raw_ids = isset($_POST['product_ids']) ? stripslashes($_POST['product_ids']) : '';
    
    $product_ids = json_decode($raw_ids, true);
    
    if (!is_array($product_ids) || empty($product_ids)) {
        wp_send_json_error('Invalid product IDs');
        return;
    }
    
    // Generate HTML for products
    $html = '';
    foreach ($product_ids as $product_id) {
        $card_html = khoshtip_kocholo_get_product_card($product_id);
        if (!empty($card_html)) {
            $html .= $card_html;
        }
    }
    
    wp_send_json_success(array('html' => $html));
}

add_action('wp_ajax_load_more_products', 'khoshtip_load_more_products_ajax');
add_action('wp_ajax_nopriv_load_more_products', 'khoshtip_load_more_products_ajax');

function khoshtip_add_products_rewrite_rule() {
    add_rewrite_rule('^products/?', 'index.php?products_page=1', 'top');
}
add_action('init', 'khoshtip_add_products_rewrite_rule');

function khoshtip_add_query_vars($vars) {
    $vars[] = 'products_page';
    return $vars;
}
add_filter('query_vars', 'khoshtip_add_query_vars');

function khoshtip_products_template_redirect() {
    if (get_query_var('products_page')) {
        include(get_stylesheet_directory() . '/page-products.php');
        exit;
    }
}
add_action('template_redirect', 'khoshtip_products_template_redirect');

// Add custom body classes
function khoshtip_kocholo_body_classes($classes) {
    $classes[] = 'khoshtip-kocholo-theme';
    return $classes;
}
add_filter('body_class', 'khoshtip_kocholo_body_classes');

// New AJAX handler for size-based product search
function khoshtip_search_products_by_size() {
    // Verify nonce
    if (!check_ajax_referer('size_search_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce verification failed');
        return;
    }
    
    $sizes = isset($_POST['sizes']) ? array_map('sanitize_text_field', $_POST['sizes']) : array();
    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
    
    if (empty($sizes)) {
        wp_send_json_error('No sizes selected');
        return;
    }
    
    $matching_product_ids = array();
    
    // Get all variable products
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_type',
                'field' => 'slug',
                'terms' => 'variable',
            ),
        ),
    );
    
    // If search term provided, add title search
    if (!empty($search_term)) {
        $args['s'] = $search_term;
    }
    
    $variable_products = get_posts($args);
    
    foreach ($variable_products as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_type('variable')) {
            continue;
        }
        
        // Get all variations
        $variations = $product->get_available_variations();
        
        foreach ($variations as $variation) {
            // Check if variation is in stock
            $variation_obj = wc_get_product($variation['variation_id']);
            if (!$variation_obj || !$variation_obj->is_in_stock()) {
                continue;
            }
            
            // Check stock quantity > 0
            $stock_qty = $variation_obj->get_stock_quantity();
            if ($stock_qty !== null && $stock_qty <= 0) {
                continue;
            }
            
            // Check if this variation has one of the selected sizes
            $variation_attributes = $variation_obj->get_attributes();
            
            foreach ($variation_attributes as $attr_name => $attr_value) {
                if ($attr_name === 'pa_size' || $attr_name === 'attribute_pa_size') {
                    // Compare with selected sizes (check both slug and name)
                    foreach ($sizes as $selected_size) {
                        // Match by slug or name (case-insensitive)
                        if (strtolower($attr_value) === strtolower($selected_size) || 
                            strtolower($attr_value) === strtolower(sanitize_title($selected_size))) {
                            $matching_product_ids[] = $product_id;
                            break 3; // Found match, move to next product
                        }
                    }
                }
            }
        }
    }
    
    $matching_product_ids = array_unique($matching_product_ids);
    
    if (empty($matching_product_ids)) {
        wp_send_json_success(array('html' => '<p style="text-align:center;color:#666;padding:2rem;">محصولی با این سایز موجود یافت نشد.</p>', 'count' => 0));
        return;
    }
    
    // Generate HTML
    $html = '<div class="products-page-grid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;">';
    $count = 0;
    foreach ($matching_product_ids as $product_id) {
        $card = khoshtip_kocholo_get_product_card($product_id);
        if (!empty($card)) {
            $html .= $card;
            $count++;
        }
    }
    $html .= '</div>';
    
    wp_send_json_success(array('html' => $html, 'count' => $count));
}

add_action('wp_ajax_search_products_by_size', 'khoshtip_search_products_by_size');
add_action('wp_ajax_nopriv_search_products_by_size', 'khoshtip_search_products_by_size');

// تنظیم حداقل تعداد خرید به‌جای 1
function khoshtip_adjust_quantity_input_args($args, $product) {
    // دریافت حداقل تعداد خرید ذخیره شده
    $min_quantity = get_post_meta($product->get_id(), '_min_quantity', true);
    
    if (!empty($min_quantity) && is_numeric($min_quantity)) {
        $min_quantity = intval($min_quantity);
        // تنظیم حداقل تعداد و مقدار پیش‌فرض
        $args['min_value'] = $min_quantity;
        $args['input_value'] = $min_quantity;
        $args['max_value'] = '';
    }
    
    return $args;
}
add_filter('woocommerce_quantity_input_args', 'khoshtip_adjust_quantity_input_args', 10, 2);

?>
