<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form();
    return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'khoshtip-single-product', $product ); ?>>

    <div class="khoshtip-product-container">
        
        <!-- Product Gallery -->
        <div class="khoshtip-product-gallery">
            <div class="khoshtip-main-image">
                <?php if ( $product->is_on_sale() ) : ?>
                    <span class="khoshtip-gallery-badge">
                        <?php echo esc_html__( 'حراج!', 'khoshtip-kocholo' ); ?>
                    </span>
                <?php endif; ?>
                <?php
                $image_id = $product->get_image_id();
                $image_url = wp_get_attachment_image_url( $image_id, 'full' );
                if ( $image_url ) {
                    echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $product->get_name() ) . '" id="main-product-image">';
                } else {
                    echo wc_placeholder_img();
                }
                ?>
            </div>

            <?php
            $attachment_ids = $product->get_gallery_image_ids();
            if ( $attachment_ids ) : ?>
                <div class="khoshtip-gallery-thumbnails">
                    <div class="khoshtip-thumbnail active" data-image="<?php echo esc_url( $image_url ); ?>">
                        <?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
                    </div>
                    <?php foreach ( $attachment_ids as $attachment_id ) : ?>
                        <div class="khoshtip-thumbnail" data-image="<?php echo esc_url( wp_get_attachment_image_url( $attachment_id, 'full' ) ); ?>">
                            <?php echo wp_get_attachment_image( $attachment_id, 'thumbnail' ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="khoshtip-product-info">
            
            <!-- Header with Title and Price -->
            <div class="khoshtip-product-header">
                <h1 class="khoshtip-product-title"><?php the_title(); ?></h1>
                
                <!-- اصلاح نمایش قیمت برای محصولات متغیر و ساده -->
                <div class="khoshtip-product-price-container">
                    <?php
                    if ( $product->is_type( 'variable' ) ) {
                        // برای محصولات متغیر
                        $variation_prices = $product->get_variation_prices( true );
                        $min_regular = min( $variation_prices['regular_price'] );
                        $min_sale = min( $variation_prices['price'] );
                        
                        if ( $product->is_on_sale() && $min_sale > 0 && $min_regular > 0 && $min_sale < $min_regular ) {
                            echo '<div class="khoshtip-single-product-price">';
                            echo '<del><span class="woocommerce-Price-amount amount">' . number_format( $min_regular ) . '</span></del>';
                            echo '<ins><span class="woocommerce-Price-amount amount">' . wc_price( $min_sale ) . '</span></ins>';
                            echo '</div>';
                        } elseif ( $min_regular > 0 ) {
                            // محصول قیمت عادی دارد
                            echo '<div class="khoshtip-single-product-price"><span class="woocommerce-Price-amount amount">' . wc_price( $min_regular ) . '</span></div>';
                        } else {
                            // هیچ قیمتی تعریف نشده
                            echo '<div class="khoshtip-single-product-price"><span class="woocommerce-Price-amount amount">تماس بگیرید</span></div>';
                        }
                    } else {
                        // برای محصولات ساده
                        $regular_price = $product->get_regular_price();
                        $sale_price = $product->get_sale_price();
                        
                        if ( $product->is_on_sale() && ! empty( $sale_price ) && $sale_price > 0 && ! empty( $regular_price ) && $regular_price > 0 ) {
                            echo '<div class="khoshtip-single-product-price">';
                            echo '<del><span class="woocommerce-Price-amount amount">' . number_format( $regular_price ) . '</span></del>';
                            echo '<ins><span class="woocommerce-Price-amount amount">' . wc_price( $sale_price ) . '</span></ins>';
                            echo '</div>';
                        } elseif ( ! empty( $regular_price ) && $regular_price > 0 ) {
                            // محصول قیمت عادی دارد
                            echo '<div class="khoshtip-single-product-price"><span class="woocommerce-Price-amount amount">' . wc_price( $regular_price ) . '</span></div>';
                        } else {
                            // هیچ قیمتی تعریف نشده
                            echo '<div class="khoshtip-single-product-price"><span class="woocommerce-Price-amount amount">تماس بگیرید</span></div>';
                        }
                    }
                    ?>
                </div>

                <!-- Stock Status -->
                <div class="khoshtip-product-stock <?php echo $product->is_in_stock() ? 'in-stock' : 'out-of-stock'; ?>">
                    <?php if ( $product->is_in_stock() ) : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <?php echo esc_html__( 'موجود در انبار', 'khoshtip-kocholo' ); ?>
                    <?php else : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <?php echo esc_html__( 'ناموجود', 'khoshtip-kocholo' ); ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Short Description -->
            <?php if ( $product->get_short_description() ) : ?>
                <div class="khoshtip-product-description">
                    <h3><?php echo esc_html__( 'توضیحات کوتاه', 'khoshtip-kocholo' ); ?></h3>
                    <?php echo wpautop( do_shortcode( $product->get_short_description() ) ); ?>
                </div>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <div class="khoshtip-cart-wrapper">
                <?php woocommerce_template_single_add_to_cart(); ?>
            </div>

            <!-- Product Meta -->
            <div class="khoshtip-product-meta">
                <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
                    <div class="khoshtip-meta-item">
                        <span class="khoshtip-meta-label"><?php echo esc_html__( 'شناسه:', 'khoshtip-kocholo' ); ?></span>
                        <span class="sku"><?php echo $product->get_sku() ? $product->get_sku() : esc_html__( 'ندارد', 'khoshtip-kocholo' ); ?></span>
                    </div>
                <?php endif; ?>

                <?php echo wc_get_product_category_list( $product->get_id(), ', ', '<div class="khoshtip-meta-item"><span class="khoshtip-meta-label">' . _n( 'دسته‌بندی:', 'دسته‌بندی‌ها:', count( $product->get_category_ids() ), 'khoshtip-kocholo' ) . '</span> ', '</div>' ); ?>

                <?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<div class="khoshtip-meta-item"><span class="khoshtip-meta-label">' . _n( 'برچسب:', 'برچسب‌ها:', count( $product->get_tag_ids() ), 'khoshtip-kocholo' ) . '</span> ', '</div>' ); ?>
            </div>

        </div>

    </div>

    <!-- Product Tabs -->
    <div class="khoshtip-product-tabs">
        <div class="khoshtip-tabs-nav">
            <button class="khoshtip-tab-button active" data-tab="description">
                <?php echo esc_html__( 'توضیحات', 'khoshtip-kocholo' ); ?>
            </button>
            <button class="khoshtip-tab-button" data-tab="additional">
                <?php echo esc_html__( 'اطلاعات بیشتر', 'khoshtip-kocholo' ); ?>
            </button>
            <button class="khoshtip-tab-button" data-tab="reviews">
                <?php echo esc_html__( 'نظرات', 'khoshtip-kocholo' ); ?> (<?php echo $product->get_review_count(); ?>)
            </button>
        </div>

        <div class="khoshtip-tab-content" id="tab-description">
            <?php the_content(); ?>
        </div>

        <div class="khoshtip-tab-content" id="tab-additional" style="display: none;">
            <?php do_action( 'woocommerce_product_additional_information', $product ); ?>
        </div>

        <div class="khoshtip-tab-content" id="tab-reviews" style="display: none;">
            <?php comments_template(); ?>
        </div>
    </div>

</div>

<?php
// ارسال داده‌های variations به JavaScript برای محصولات متغیر
if ( $product->is_type( 'variable' ) ) {
    $variations = $product->get_available_variations();
    $variations_json = wp_json_encode( $variations );
    ?>
    <script type="text/javascript">
        var khoshtipVariationsData = <?php echo $variations_json; ?>;
    </script>
    <?php
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gallery thumbnail click
    const thumbnails = document.querySelectorAll('.khoshtip-thumbnail');
    const mainImage = document.getElementById('main-product-image');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            mainImage.src = this.dataset.image;
        });
    });

    // تغییر تصویر بر اساس انتخاب variation (برای محصولات متغیر)
    if (typeof khoshtipVariationsData !== 'undefined') {
        const variationForm = document.querySelector('.variations_form');
        
        if (variationForm) {
            // گوش دادن به تغییرات در فرم variations
            variationForm.addEventListener('change', function(e) {
                if (e.target.classList.contains('value')) {
                    // کمی تاخیر برای اطمینان از به‌روزرسانی WooCommerce
                    setTimeout(function() {
                        checkSelectedVariation();
                    }, 100);
                }
            });
            
            // گوش دادن به رویداد found_variation از WooCommerce
            jQuery(variationForm).on('found_variation', function(event, variation) {
                if (variation.image && variation.image.src) {
                    // تغییر تصویر اصلی
                    mainImage.src = variation.image.src;
                    mainImage.srcset = variation.image.srcset || '';
                    mainImage.alt = variation.image.alt || '';
                    
                    // اگر thumbnail برای این variation وجود دارد، active کن
                    const matchingThumb = Array.from(thumbnails).find(thumb => 
                        thumb.dataset.image === variation.image.src ||
                        thumb.dataset.image === variation.image.full_src
                    );
                    
                    if (matchingThumb) {
                        thumbnails.forEach(t => t.classList.remove('active'));
                        matchingThumb.classList.add('active');
                    }
                }
            });
            
            // بازگشت به تصویر اصلی وقتی variation پاک می‌شود
            jQuery(variationForm).on('reset_image', function() {
                const defaultImage = thumbnails[0];
                if (defaultImage) {
                    mainImage.src = defaultImage.dataset.image;
                    thumbnails.forEach(t => t.classList.remove('active'));
                    defaultImage.classList.add('active');
                }
            });
        }
    }

    // Tabs
    const tabButtons = document.querySelectorAll('.khoshtip-tab-button');
    const tabContents = document.querySelectorAll('.khoshtip-tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            
            document.getElementById(`tab-${targetTab}`).style.display = 'block';
        });
    });
});
</script>

<?php do_action( 'woocommerce_after_single_product' ); ?>
