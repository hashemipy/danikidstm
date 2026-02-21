<?php
/**
 * Template Name: Products Page
 * Description: Dynamic page to display products by category or type
 */

get_header();

// Get category or type from URL parameter
$category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$extra = isset($_GET['extra']) ? sanitize_text_field($_GET['extra']) : '';
$sale_category = isset($_GET['sale_category']) ? sanitize_text_field($_GET['sale_category']) : '';

// Default values
$page_title = 'محصولات';
$page_icon = '🛍️';
$products = array();

// Query products based on category or type
if ($type === 'sale') {
    $page_title = 'محصولات حراجی';
    $page_icon = '🔥';
    
    $filter_category_term = null;
    if (!empty($sale_category)) {
        $filter_category_term = get_term_by('slug', $sale_category, 'product_cat');
    }
    
    if (function_exists('wc_get_product_ids_on_sale')) {
        $sale_ids = wc_get_product_ids_on_sale();
        $parent_ids = array();
        
        foreach ($sale_ids as $id) {
            $product = wc_get_product($id);
            if (!$product) {
                continue;
            }
            
            // Skip out of stock products
            if (!$product->is_in_stock()) {
                continue;
            }
            
            $parent_id = $id;
            $check_product = $product;
            
            // If the product is a variation, get the parent
            if ($product->is_type('variation')) {
                $parent_id = $product->get_parent_id();
                $check_product = wc_get_product($parent_id);
                
                if (!$check_product || !$check_product->is_in_stock()) {
                    continue;
                }
            }
            
            if ($filter_category_term && is_object($filter_category_term)) {
                $product_cat_ids = $check_product->get_category_ids();
                
                // Check if product is in the selected category or its children
                $category_and_children = get_term_children($filter_category_term->term_id, 'product_cat');
                $all_valid_cats = array_merge(array($filter_category_term->term_id), $category_and_children);
                
                $found_in_category = false;
                foreach ($product_cat_ids as $cat_id) {
                    if (in_array($cat_id, $all_valid_cats)) {
                        $found_in_category = true;
                        break;
                    }
                }
                
                if (!$found_in_category) {
                    continue;
                }
            }
            
            $parent_ids[] = $parent_id;
        }
        
        // Remove duplicate IDs
        $products = array_unique($parent_ids);
    }
    
    if (!empty($sale_category) && $filter_category_term) {
        $page_title = 'حراجی - ' . $filter_category_term->name;
    }
    
} elseif ($type === 'latest') {
    $page_title = 'جدیدترین محصولات';
    $page_icon = '✨';
    
    // Use wc_get_products which is more optimized than WP_Query
    if (function_exists('wc_get_products')) {
        $latest_products = wc_get_products(array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'status' => 'publish',
            'return' => 'ids' // Only return IDs, faster
        ));
        $products = $latest_products;
    }
    
} elseif ($category) {
    // Get information from accordion settings
    $accordion_data = json_decode(do_shortcode('[k_accordion_products_data]'), true);
    
    // Map categories with custom titles and icons
    $category_map = array(
        'girls' => array(
            'title' => $accordion_data['girls_title'] ?? 'محصولات دخترانه', 
            'icon' => $accordion_data['girls_icon'] ?? '👧',
            'products_key' => 'girls_products'
        ),
        'boys' => array(
            'title' => $accordion_data['boys_title'] ?? 'محصولات پسرانه', 
            'icon' => $accordion_data['boys_icon'] ?? '👦',
            'products_key' => 'boys_products'
        ),
        'sports' => array(
            'title' => $accordion_data['sport_title'] ?? 'محصولات ورزشی', 
            'icon' => $accordion_data['sport_icon'] ?? '⚽',
            'products_key' => 'sport_products'
        )
    );
    
    if (isset($category_map[$category])) {
        $page_title = $category_map[$category]['title'];
        $page_icon = $category_map[$category]['icon'];
        $products_key = $category_map[$category]['products_key'];
        
        // Get products from accordion settings
        $selected_products = $accordion_data[$products_key] ?? array();
        
        if (!empty($selected_products)) {
            $products = $selected_products;
        }
    }
} elseif ($extra !== '') {
    $accordion_data = json_decode(do_shortcode('[k_accordion_products_data]'), true);
    $extra_accordions = $accordion_data['extra_accordions'] ?? array();
    
    // Check if unique ID exists in extra_accordions
    if (isset($extra_accordions[$extra])) {
        $extra_accordion = $extra_accordions[$extra];
        $page_title = $extra_accordion['title'] ?? 'محصولات';
        $page_icon = $extra_accordion['icon'] ?? '🎁';
        
        // Get products directly from database, not from cache
        $products = $extra_accordion['products'] ?? array();
    }
}

function render_page_icon($icon_value) {
    if (empty($icon_value)) {
        return '';
    }
    
    if (filter_var($icon_value, FILTER_VALIDATE_URL)) {
        $optimized_url = khoshtip_convert_to_webp_url($icon_value);
        return '<img src="' . esc_url($optimized_url) . '" style="width: 2.5rem; height: 2.5rem; object-fit: contain; vertical-align: middle;" alt="icon" />';
    } else {
        return esc_html($icon_value);
    }
}

$product_categories = array();
if ($type === 'sale') {
    $product_categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => 0, // Only top-level categories
    ));
}
?>

<style>
.products-page-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .products-page-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 1.5rem;
    }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.category-filter-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    justify-content: center;
}

.category-filter-btn {
    padding: 0.5rem 1rem;
    background: hsl(var(--muted));
    border: 1px solid hsl(var(--border));
    border-radius: var(--radius);
    font-size: 0.9rem;
    cursor: pointer;
    text-decoration: none;
    color: hsl(var(--foreground));
    transition: all 0.2s;
}

.category-filter-btn:hover,
.category-filter-btn.active {
    background: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    border-color: hsl(var(--primary));
}
</style>

<main style="padding: 3rem 0; min-height: 60vh;">
    <div class="container">
        <h1 style="font-size: 2.5rem; font-weight: 700; text-align: center; margin-bottom: 2rem; color: hsl(var(--foreground));">
            <?php echo render_page_icon($page_icon); ?> <?php echo esc_html($page_title); ?>
        </h1>
        
        <?php if ($type === 'sale' && !empty($product_categories) && !is_wp_error($product_categories)) : ?>
            <div class="category-filter-wrapper">
                <a href="<?php echo esc_url(home_url('/products/?type=sale')); ?>" 
                   class="category-filter-btn <?php echo empty($sale_category) ? 'active' : ''; ?>">
                    همه دسته‌بندی‌ها
                </a>
                <?php foreach ($product_categories as $cat) : ?>
                    <a href="<?php echo esc_url(home_url('/products/?type=sale&sale_category=' . $cat->slug)); ?>" 
                       class="category-filter-btn <?php echo ($sale_category === $cat->slug) ? 'active' : ''; ?>">
                        <?php echo esc_html($cat->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($products)) : ?>
            <div class="products-page-grid" id="products-grid">
                <?php 
                $initial_count = 10;
                $displayed = 0;
                foreach ($products as $product_id) : 
                    if ($displayed < $initial_count) :
                        $card = khoshtip_kocholo_get_product_card($product_id);
                        if (!empty($card)) :
                            echo $card;
                            $displayed++;
                        endif;
                    endif;
                endforeach; 
                ?>
            </div>
            
            <div id="loading-indicator" style="text-align: center; padding: 2rem; display: none;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid hsl(var(--muted)); border-top-color: hsl(var(--primary)); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <p style="margin-top: 1rem; color: hsl(var(--muted-foreground));">در حال بارگذاری محصولات...</p>
            </div>
            
            <script id="remaining-products-data" type="application/json">
                <?php 
                $remaining_products = array();
                $hidden_index = 0;
                foreach ($products as $product_id) : 
                    if ($hidden_index >= $initial_count) :
                        $product = wc_get_product($product_id);
                        if ($product && $product->is_in_stock()) :
                            $remaining_products[] = intval($product_id);
                        endif;
                    endif;
                    $hidden_index++;
                endforeach;
                echo json_encode($remaining_products);
                ?>
            </script>
            
            <script>
            (function() {
                const productsGrid = document.getElementById('products-grid');
                const loadingIndicator = document.getElementById('loading-indicator');
                const remainingProductsDataElement = document.getElementById('remaining-products-data');
                
                if (!remainingProductsDataElement) {
                    return;
                }
                
                let remainingProductsData;
                try {
                    remainingProductsData = JSON.parse(remainingProductsDataElement.textContent);
                } catch (e) {
                    console.error('Error parsing remaining products data:', e);
                    return;
                }
                
                if (!Array.isArray(remainingProductsData) || remainingProductsData.length === 0) {
                    return;
                }
                
                let currentIndex = 0;
                let isLoading = false;
                const productsPerLoad = 10;
                
                function loadMoreProducts() {
                    if (isLoading || currentIndex >= remainingProductsData.length) {
                        return;
                    }
                    
                    isLoading = true;
                    loadingIndicator.style.display = 'block';
                    
                    const endIndex = Math.min(currentIndex + productsPerLoad, remainingProductsData.length);
                    const productIdsToLoad = remainingProductsData.slice(currentIndex, endIndex);
                    
                    const formData = new FormData();
                    formData.append('action', 'load_more_products');
                    formData.append('product_ids', JSON.stringify(productIdsToLoad));
                    formData.append('nonce', '<?php echo wp_create_nonce('load_more_products'); ?>');
                    
                    // Use AJAX to load product cards
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data && data.data.html) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = data.data.html;
                            
                            // Append each product card to the grid
                            while (tempDiv.firstElementChild) {
                                productsGrid.appendChild(tempDiv.firstElementChild);
                            }
                            
                            currentIndex = endIndex;
                        }
                        
                        isLoading = false;
                        loadingIndicator.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error loading products:', error);
                        isLoading = false;
                        loadingIndicator.style.display = 'none';
                    });
                }
                
                function checkScroll() {
                    const scrollPosition = window.innerHeight + window.scrollY;
                    const pageHeight = document.documentElement.scrollHeight;
                    const triggerPoint = pageHeight - 1200;
                    
                    if (scrollPosition >= triggerPoint && !isLoading && currentIndex < remainingProductsData.length) {
                        loadMoreProducts();
                    }
                }
                
                let scrollTimeout;
                window.addEventListener('scroll', function() {
                    if (scrollTimeout) {
                        clearTimeout(scrollTimeout);
                    }
                    scrollTimeout = setTimeout(checkScroll, 100);
                }, { passive: true });
                
                setTimeout(checkScroll, 500);
                
                window.addEventListener('resize', function() {
                    setTimeout(checkScroll, 200);
                }, { passive: true });
            })();
            </script>
        <?php else : ?>
            <p style="text-align: center; color: hsl(var(--muted-foreground)); font-size: 1.125rem;">
                محصولی یافت نشد.
            </p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
