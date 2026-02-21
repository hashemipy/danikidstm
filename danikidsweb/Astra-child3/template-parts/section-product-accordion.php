<?php
/**
 * Product Accordion Section Template
 * بازنویسی کامل - تبدیل آکاردئون‌ها به دکمه‌های گرید + اسکرول عمودی
 */

$accordion_data = json_decode(do_shortcode('[k_accordion_products_data]'), true);

$girls_title = $accordion_data['girls_title'] ?? 'محصولات دخترانه';
$boys_title = $accordion_data['boys_title'] ?? 'محصولات پسرانه';
$sport_title = $accordion_data['sport_title'] ?? 'محصولات ورزشی';

$girls_icon = $accordion_data['girls_icon'] ?? '👧';
$boys_icon = $accordion_data['boys_icon'] ?? '👦';
$sport_icon = $accordion_data['sport_icon'] ?? '⚽';

$girls_grid_limit = $accordion_data['girls_grid_limit'] ?? 10;
$boys_grid_limit = $accordion_data['boys_grid_limit'] ?? 10;
$sport_grid_limit = $accordion_data['sport_grid_limit'] ?? 10;

$girls_button_bg = $accordion_data['girls_button_bg'] ?? '#ff6b9d';
$boys_button_bg = $accordion_data['boys_button_bg'] ?? '#4dabf7';
$sport_button_bg = $accordion_data['sport_button_bg'] ?? '#51cf66';

$latest_icon = $accordion_data['latest_icon'] ?? '✨';
$latest_button_bg = '#667eea'; // رنگ پیش‌فرض برای همه محصولات

$sale_button_enabled = get_option('k_sale_button_enabled', '1');
$sale_button_color = get_option('k_sale_button_color', '#ff4757');
$sale_icon = '🔥';

$latest_products = array();
if (function_exists('wc_get_products')) {
    $cache_key_latest = 'k_latest_products_ids';
    $latest_products = get_transient($cache_key_latest);
    
    if (false === $latest_products) {
        $latest_products = wc_get_products(array(
            'limit' => 20,
            'orderby' => 'date',
            'order' => 'DESC',
            'status' => 'publish',
            'stock_status' => 'instock',
            'return' => 'ids'
        ));
        set_transient($cache_key_latest, $latest_products, 15 * MINUTE_IN_SECONDS);
    }
}

$sale_products = array();
if (function_exists('wc_get_products') && $sale_button_enabled === '1') {
    $cache_key_sale = 'k_sale_products_ids';
    $sale_products = get_transient($cache_key_sale);
    
    if (false === $sale_products) {
        $all_sale_products = wc_get_products(array(
            'limit' => -1,
            'status' => 'publish',
            'stock_status' => 'instock',
        ));
        
        $sale_products = array();
        foreach ($all_sale_products as $product) {
            if ($product->is_on_sale()) {
                $sale_products[] = $product->get_id();
            }
        }
        
        $sale_products = array_reverse($sale_products);
        
        set_transient($cache_key_sale, $sale_products, 15 * MINUTE_IN_SECONDS);
    }
}

$girls_products = $accordion_data['girls_products'] ?? array();
$boys_products = $accordion_data['boys_products'] ?? array();
$sport_products = $accordion_data['sport_products'] ?? array();

function render_icon($icon_value) {
    if (empty($icon_value)) {
        return '';
    }
    
    if (filter_var($icon_value, FILTER_VALIDATE_URL)) {
        $optimized_url = khoshtip_convert_to_webp_url($icon_value);
        return '<img src="' . esc_url($optimized_url) . '" style="width: 1.5rem; height: 1.5rem; object-fit: contain;" alt="icon" />';
    } else {
        return '<span style="font-size: 1.5rem;">' . esc_html($icon_value) . '</span>';
    }
}

$buttons = array();

if (!empty($latest_products)) {
    $buttons['latest'] = array(
        'title' => 'همه محصولات',
        'icon' => $latest_icon,
        'products' => $latest_products,
        'limit' => 20,
        'link' => home_url('/products/?type=latest'),
        'color' => $latest_button_bg
    );
}

if (!empty($sale_products) && $sale_button_enabled === '1') {
    $buttons['sale'] = array(
        'title' => 'حراجی',
        'icon' => $sale_icon,
        'products' => $sale_products,
        'limit' => 20,
        'link' => home_url('/products/?type=sale'),
        'color' => $sale_button_color
    );
}

if (!empty($girls_products) && !empty($girls_title)) {
    $buttons['girls'] = array(
        'title' => $girls_title,
        'icon' => $girls_icon,
        'products' => $girls_products,
        'limit' => $girls_grid_limit,
        'link' => home_url('/products/?category=girls'),
        'color' => $girls_button_bg
    );
}

if (!empty($boys_products) && !empty($boys_title)) {
    $buttons['boys'] = array(
        'title' => $boys_title,
        'icon' => $boys_icon,
        'products' => $boys_products,
        'limit' => $boys_grid_limit,
        'link' => home_url('/products/?category=boys'),
        'color' => $boys_button_bg
    );
}

if (!empty($sport_products) && !empty($sport_title)) {
    $buttons['sport'] = array(
        'title' => $sport_title,
        'icon' => $sport_icon,
        'products' => $sport_products,
        'limit' => $sport_grid_limit,
        'link' => home_url('/products/?category=sports'),
        'color' => $sport_button_bg
    );
}

$extra_accordions_data = $accordion_data['extra_accordions'] ?? array();
if (!empty($extra_accordions_data)) {
    foreach ($extra_accordions_data as $unique_id => $accordion) {
        $title = $accordion['title'] ?? '';
        $products = $accordion['products'] ?? array();
        $icon = $accordion['icon'] ?? '🎁';
        $grid_limit = $accordion['grid_limit'] ?? 10;
        $button_bg = $accordion['button_bg'] ?? '#9c27b0';
        
        if (!empty($products) && !empty($title)) {
            $buttons['extra_' . $unique_id] = array(
                'title' => $title,
                'icon' => $icon,
                'products' => $products,
                'limit' => $grid_limit,
                'link' => home_url('/products/?extra=' . urlencode($unique_id)),
                'color' => $button_bg
            );
        }
    }
}

if (empty($buttons)) {
    return;
}
?>

<style>
/* استایل جدید دکمه‌های گرید */
.product-buttons-section {
    padding: 2rem 0;
}

.product-buttons-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
    .product-buttons-grid {
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
    }
}

.product-category-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem 0.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    min-height: 80px;
    color: white; /* رنگ متن سفید برای همه دکمه‌ها */
}

.product-category-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    opacity: 0.9;
}

/* حذف استایل active قبلی - دکمه فعال فقط با opacity مشخص می‌شود */
.product-category-btn.active {
    opacity: 1;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.btn-icon {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.btn-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: white; /* رنگ متن سفید */
    line-height: 1.2;
}

@media (min-width: 768px) {
    .btn-title {
        font-size: 0.85rem;
    }
    
    .product-category-btn {
        min-height: 90px;
        padding: 1.25rem 0.75rem;
    }
}

/* محتوای محصولات - اسکرول عمودی */
.products-content-area {
    display: none;
    padding: 1rem 0;
}

.products-content-area.active {
    display: block;
}

.products-vertical-scroll {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    padding: 0.5rem;
}

@media (min-width: 768px) {
    .products-vertical-scroll {
        grid-template-columns: repeat(4, 1fr);
    }
}

.view-all-link {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    padding: 0.75rem 2rem;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
}
</style>

<section class="product-buttons-section">
    <div class="container">
        <!-- دکمه‌های گرید -->
        <div class="product-buttons-grid">
            <?php 
            $first = true;
            foreach ($buttons as $key => $button) : 
                $btn_color = $button['color'];
            ?>
                <!-- رنگ همیشه روی دکمه اعمال می‌شود -->
                <button type="button" 
                        class="product-category-btn<?php echo $first ? ' active' : ''; ?>" 
                        data-target="products-<?php echo esc_attr($key); ?>"
                        data-color="<?php echo esc_attr($btn_color); ?>"
                        style="background: <?php echo esc_attr($btn_color); ?>;">
                    <span class="btn-icon"><?php echo render_icon($button['icon']); ?></span>
                    <span class="btn-title"><?php echo esc_html($button['title']); ?></span>
                </button>
            <?php 
                $first = false;
            endforeach; 
            ?>
        </div>
        
        <!-- محتوای محصولات با اسکرول عمودی -->
        <?php 
        $first = true;
        foreach ($buttons as $key => $button) : 
        ?>
            <div class="products-content-area<?php echo $first ? ' active' : ''; ?>" id="products-<?php echo esc_attr($key); ?>">
                <div class="products-vertical-scroll">
                    <?php 
                    $count = 0;
                    $has_products = false;
                    foreach ($button['products'] as $product_id) : 
                        if ($count >= $button['limit']) break;
                        $product_card = khoshtip_kocholo_get_product_card($product_id);
                        if (!empty($product_card)) :
                            $has_products = true;
                            $count++;
                    ?>
                        <?php echo $product_card; ?>
                    <?php 
                        endif;
                    endforeach; 
                    
                    if (!$has_products) :
                    ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; color: #6c757d;">
                            <p style="font-size: 1.1rem; font-weight: 500;">
                                <?php 
                                if ($key === 'sale') {
                                    echo 'حراجی موجود نیست';
                                } else {
                                    echo 'در حال حاضر محصولی موجود نیست';
                                }
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($has_products) : ?>
                <!-- اعمال رنگ داینامیک به دکمه مشاهده همه -->
                <a href="<?php echo esc_url($button['link']); ?>" class="view-all-link" style="background: <?php echo esc_attr($button['color']); ?>;">
                    مشاهده همه <?php echo esc_html($button['title']); ?> ←
                </a>
                <?php endif; ?>
            </div>
        <?php 
            $first = false;
        endforeach; 
        ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] Product accordion script loaded');
    
    const buttons = document.querySelectorAll('.product-category-btn');
    const contentAreas = document.querySelectorAll('.products-content-area');
    
    console.log('[v0] Found buttons:', buttons.length);
    console.log('[v0] Found content areas:', contentAreas.length);
    
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            console.log('[v0] Button clicked:', this.getAttribute('data-target'));
            
            const targetId = this.getAttribute('data-target');
            
            buttons.forEach(function(b) {
                b.classList.remove('active');
            });
            
            contentAreas.forEach(function(content) {
                content.classList.remove('active');
            });
            
            this.classList.add('active');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.classList.add('active');
                console.log('[v0] Target activated:', targetId);
            } else {
                console.error('[v0] Target element not found:', targetId);
            }
        });
    });
});
</script>
