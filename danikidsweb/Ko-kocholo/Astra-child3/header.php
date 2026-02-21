<?php
/**
 * Custom Header Template
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Get header data from shortcode
$header_data = json_decode(do_shortcode('[k_header_data]'), true);
$site_title = $header_data['site_title'] ?? get_bloginfo('name');
$site_tagline = $header_data['site_tagline'] ?? ''; // Added site tagline
$site_title_font_size = get_option('k_site_title_font_size', '1.5');
$site_title_color = get_option('k_site_title_color', '#ffffff');
$site_tagline_color = get_option('k_site_tagline_color', '#ffffff');
$nav_links = $header_data['nav_links'] ?? array();
$mobile_menu_links = $header_data['mobile_menu_links'] ?? $nav_links;

$header_gradient_start = get_option('k_header_gradient_start', '#ff6b9d');
$header_gradient_end = get_option('k_header_gradient_end', '#ffc3d7');

$search_enabled_sizes = get_option('k_search_enabled_sizes', []);
$clothing_sizes = [];
if (taxonomy_exists('pa_size') && !empty($search_enabled_sizes)) {
    $terms = get_terms(array(
        'taxonomy' => 'pa_size',
        'hide_empty' => true,
        'slug' => $search_enabled_sizes,
    ));
    if (!is_wp_error($terms)) {
        $clothing_sizes = $terms;
    }
}

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$google_picture = $is_logged_in ? get_user_meta(get_current_user_id(), 'google_profile_picture', true) : '';
$avatar_url = $google_picture ? $google_picture : ($is_logged_in ? get_avatar_url(get_current_user_id(), array('size' => 40)) : '');
?>

<!-- Applied gradient background to header -->
<header class="site-header sticky top-0 z-50 w-full border-b text-primary-foreground" style="position: sticky; top: 0; z-index: 50; width: 100%; border-bottom: 1px solid hsl(var(--border)); background: linear-gradient(135deg, <?php echo esc_attr($header_gradient_start); ?>, <?php echo esc_attr($header_gradient_end); ?>); color: #fff;">
    <div class="container mx-auto flex items-center justify-between" style="display: flex; height: 60px; align-items: center; justify-content: space-between; padding: 0 1rem;">
        
        <div class="flex items-center gap-6" style="display: flex; align-items: center; gap: 1.5rem;">
            <!-- Applied custom color to site title and tagline -->
            <div>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="text-2xl font-bold" style="font-size: <?php echo esc_attr($site_title_font_size); ?>rem; font-weight: 700; color: <?php echo esc_attr($site_title_color); ?>; text-decoration: none; display: block;">
                    <?php echo esc_html($site_title); ?>
                </a>
                <?php if (!empty($site_tagline)) : ?>
                    <p style="font-size: 0.75rem; color: <?php echo esc_attr($site_tagline_color); ?>; opacity: 0.9; margin: 0; margin-top: 2px;">
                        <?php echo esc_html($site_tagline); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <nav class="hidden md:flex gap-6 desktop-nav" style="display: none; gap: 1.5rem;">
                <?php foreach ($nav_links as $link) : ?>
                    <a href="<?php echo esc_url($link['url']); ?>" class="text-md font-medium nav-link" style="font-size: 1rem; font-weight: 500; color: #fff; opacity: 0.8; text-decoration: none;">
                        <?php echo esc_html($link['text']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        
        <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 0.5rem;">
            <!-- Search Icon -->
            <button aria-label="جستجو" data-search-trigger class="p-2 hover:opacity-80" style="padding: 0.5rem; background: none; border: none; cursor: pointer; color: #fff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
            
            <!-- Updated user profile icon to show actual profile picture when logged in -->
            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" aria-label="حساب کاربری" class="p-2 hover:opacity-80" style="padding: 0.5rem; color: #fff; text-decoration: none; position: relative; display: flex; align-items: center; justify-content: center;">
                <?php if ($is_logged_in && $avatar_url) : ?>
                    <!-- Show user profile picture when logged in -->
                    <img src="<?php echo esc_url($avatar_url); ?>" 
                         alt="<?php echo esc_attr($current_user->display_name); ?>" 
                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff;" />
                <?php else : ?>
                    <!-- Show default user icon when not logged in -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                <?php endif; ?>
            </a>
            
            <!-- Shopping Cart Icon -->
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" aria-label="سبد خرید" class="p-2 hover:opacity-80" style="padding: 0.5rem; color: #fff; text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="md:hidden mobile-menu-toggle p-2" aria-label="منو" data-mobile-menu-trigger style="padding: 0.5rem; background: none; border: none; cursor: pointer; color: #fff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu (Off-canvas) -->
    <div class="mobile-menu-content" data-mobile-menu style="position: fixed; top: 0; right: -100%; width: 80%; max-width: 300px; height: 100vh; background-color: hsl(var(--background)); box-shadow: -2px 0 10px rgba(0,0,0,0.1); transition: right 0.3s ease; z-index: 100; overflow-y: auto;">
        <div style="padding: 2rem;">
            <button class="mobile-menu-close" data-mobile-menu-close style="position: absolute; top: 1rem; left: 1rem; background: none; border: none; cursor: pointer; font-size: 1.5rem; color: #000;">×</button>
            
            <!-- Add user info in mobile menu -->
            <?php if ($is_logged_in) : ?>
                <div style="margin-top: 1rem; padding: 1rem; background: #f7fafc; border-radius: 12px; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;" />
                        <div>
                            <div style="font-weight: 600; color: #1a202c; margin-bottom: 4px;"><?php echo esc_html($current_user->display_name); ?></div>
                            <div style="font-size: 12px; color: #718096;"><?php echo esc_html($current_user->user_email); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <nav style="margin-top: 2rem;">
                <?php foreach ($mobile_menu_links as $link) : ?>
                    <a href="<?php echo esc_url($link['url']); ?>" style="display: block; padding: 1rem 0; color: hsl(var(--foreground)); text-decoration: none; border-bottom: 1px solid hsl(var(--border));">
                        <?php echo esc_html($link['text']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    
    <!-- Enhanced Search Modal with size filter -->
    <div class="search-modal" data-search-modal style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 200;">
        <div style="background-color: hsl(var(--background)); padding: 2rem; border-radius: var(--radius); width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; position: relative;">
            <button class="search-modal-close" data-search-modal-close style="position: absolute; top: 1rem; left: 1rem; background: none; border: none; cursor: pointer; font-size: 1.5rem; color: #000;">×</button>
            
            <h3 style="margin: 0 0 1.5rem 0; font-size: 1.25rem; text-align: center; color: hsl(var(--foreground));">جستجوی محصولات</h3>
            
            <!-- Separate form for text search -->
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="margin-top: 1rem;">
                <input type="search" name="s" placeholder="نام محصول را وارد کنید..." style="width: 100%; padding: 1rem; border: 1px solid hsl(var(--border)); border-radius: var(--radius); font-size: 1rem; margin-bottom: 1rem;" />
                <input type="hidden" name="post_type" value="product" />
                <button type="submit" style="width: 100%; padding: 1rem; background-color: hsl(var(--primary)); color: hsl(var(--primary-foreground)); border: none; border-radius: var(--radius); font-size: 1rem; cursor: pointer; font-weight: 600;">جستجو بر اساس نام</button>
            </form>
            
            <!-- Separate section for size-based search -->
            <?php if (!empty($clothing_sizes)) : ?>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid hsl(var(--border));">
                    <label style="display: block; margin-bottom: 0.75rem; font-weight: 600; color: hsl(var(--foreground)); text-align: center;">جستجو بر اساس سایز موجود:</label>
                    <div id="size-filter-container" style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
                        <?php foreach ($clothing_sizes as $size) : ?>
                            <label style="display: flex; align-items: center; gap: 5px; padding: 10px 15px; background: hsl(var(--muted)); border-radius: 8px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent;" class="size-checkbox-label">
                                <input type="checkbox" name="size_filter" value="<?php echo esc_attr($size->slug); ?>" style="margin: 0; width: 16px; height: 16px;">
                                <span style="font-size: 0.95rem; font-weight: 500;"><?php echo esc_html($size->name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="search-by-size-btn" style="width: 100%; padding: 1rem; background-color: #10b981; color: #fff; border: none; border-radius: var(--radius); font-size: 1rem; cursor: pointer; font-weight: 600; margin-top: 1rem;">جستجو بر اساس سایز</button>
                    
                    <!-- Results container -->
                    <div id="size-search-results" style="margin-top: 1rem; display: none;"></div>
                    <div id="size-search-loading" style="text-align: center; padding: 1rem; display: none;">
                        <span>در حال جستجو...</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add JavaScript for size search AJAX -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchBySizeBtn = document.getElementById('search-by-size-btn');
        const sizeSearchResults = document.getElementById('size-search-results');
        const sizeSearchLoading = document.getElementById('size-search-loading');
        
        if (searchBySizeBtn) {
            searchBySizeBtn.addEventListener('click', function() {
                const selectedSizes = [];
                document.querySelectorAll('input[name="size_filter"]:checked').forEach(function(cb) {
                    selectedSizes.push(cb.value);
                });
                
                if (selectedSizes.length === 0) {
                    alert('لطفاً حداقل یک سایز انتخاب کنید');
                    return;
                }
                
                sizeSearchLoading.style.display = 'block';
                sizeSearchResults.style.display = 'none';
                
                const formData = new FormData();
                formData.append('action', 'search_products_by_size');
                formData.append('nonce', '<?php echo wp_create_nonce('size_search_nonce'); ?>');
                selectedSizes.forEach(function(size) {
                    formData.append('sizes[]', size);
                });
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    sizeSearchLoading.style.display = 'none';
                    sizeSearchResults.style.display = 'block';
                    
                    if (data.success) {
                        sizeSearchResults.innerHTML = '<p style="text-align:center;margin-bottom:1rem;font-weight:600;">' + data.data.count + ' محصول یافت شد</p>' + data.data.html;
                    } else {
                        sizeSearchResults.innerHTML = '<p style="text-align:center;color:#666;">خطا در جستجو</p>';
                    }
                })
                .catch(error => {
                    sizeSearchLoading.style.display = 'none';
                    sizeSearchResults.innerHTML = '<p style="text-align:center;color:red;">خطا در ارتباط با سرور</p>';
                    sizeSearchResults.style.display = 'block';
                });
            });
        }
        
        // Style selected checkboxes
        document.querySelectorAll('.size-checkbox-label input').forEach(function(cb) {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    this.closest('label').style.borderColor = 'hsl(var(--primary))';
                    this.closest('label').style.background = 'hsl(var(--primary) / 0.1)';
                } else {
                    this.closest('label').style.borderColor = 'transparent';
                    this.closest('label').style.background = 'hsl(var(--muted))';
                }
            });
        });
    });
    </script>
</header>
</body>
</html>
