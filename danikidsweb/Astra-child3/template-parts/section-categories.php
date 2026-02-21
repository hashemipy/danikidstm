<?php
/**
 * Category Buttons Section Template
 */

// Get categories data from shortcode
$categories_data = json_decode(do_shortcode('[k_categories_data]'), true);
$categories = $categories_data['categories'] ?? array();

if (empty($categories)) {
    return;
}
?>

<!-- حذف container و استفاده از max-width مستقیم برای grid -->
<section class="categories-section" style="background: white; padding: 2rem 0;">
    <div class="categories-grid">
        <style>
            .categories-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1rem !important;
                width: 100% !important;
                max-width: 1280px !important;
                margin: 0 auto !important;
                padding: 0 1rem !important;
            }
            
            @media (min-width: 768px) {
                .categories-grid {
                    grid-template-columns: repeat(4, 1fr) !important;
                    gap: 1.5rem !important;
                }
            }
            
            .category-item {
                position: relative !important;
                aspect-ratio: 2/1 !important;
                overflow: hidden !important;
                border-radius: 12px !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
                transition: all 0.3s ease !important;
                width: 100% !important;
                display: block !important;
                /* تبدیل از لینک به دکمه برای باز کردن مودال */
                cursor: pointer !important;
            }
            
            
            .category-item:hover {
                transform: translateY(-4px) !important;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
            }
            
            .category-bg-image {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                transition: transform 0.3s ease !important;
            }
            
            .category-item:hover .category-bg-image {
                transform: scale(1.05) !important;
            }
            
            .category-title-overlay {
                position: absolute !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                background: rgba(0, 0, 0, 0.75) !important;
                padding: 1rem 1.5rem !important;
                backdrop-filter: blur(4px) !important;
            }
            
            .category-title-overlay h3 {
                margin: 0 !important;
                color: white !important;
                font-size: 0.9rem !important;
                font-weight: 600 !important;
                text-align: center !important;
            }
            
            @media (min-width: 768px) {
                .category-title-overlay h3 {
                    font-size: 1.25rem !important;
                }
            }

            /* استایل مودال مثل استوری‌های هدر - حذف کادر سفید و تنظیم ابعاد دقیق */
            .category-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.95);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 250;
            }

            .category-modal.active {
                display: flex;
            }

            /* کانتنت مودال با ابعاد دقیق استوری‌ها: max-width 500px و aspect-ratio 9/16 */
            .category-modal-content {
                position: relative;
                width: 90%;
                max-width: 500px;
                aspect-ratio: 9/16;
                border-radius: var(--radius);
                overflow: hidden;
            }

            /* دکمه بستن مثل استوری‌ها - گوشه بالا راست */
            .category-modal-close {
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: rgba(0, 0, 0, 0.5);
                border: none;
                color: white;
                font-size: 2rem;
                cursor: pointer;
                z-index: 301;
                line-height: 1;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
            }

            .category-modal-close:hover {
                background: rgba(0, 0, 0, 0.7);
            }

            /* عکس پس‌زمینه تمام‌صفحه مثل استوری‌ها */
            .category-modal-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                position: absolute;
                top: 0;
                left: 0;
            }

            /* محتوای روی عکس - در پایین مودال */
            .category-modal-body {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 2rem;
                background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 50%, transparent 100%);
                text-align: center;
            }

            .category-modal-title {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
                color: white;
            }

            .category-modal-description {
                font-size: 0.95rem;
                line-height: 1.5;
                color: rgba(255, 255, 255, 0.9);
                margin-bottom: 1.5rem;
            }

            /* دکمه دیدن بیشتر مثل استوری‌ها - گرد کامل با رنگ primary */
            .category-modal-link {
                display: inline-block;
                padding: 1rem 2rem;
                background-color: hsl(var(--primary));
                color: hsl(var(--primary-foreground));
                text-decoration: none;
                border-radius: 9999px;
                font-weight: 700;
                white-space: nowrap;
            }

            .category-modal-link:hover {
                opacity: 0.9;
            }
            
            /* 15% کاهش به جای 25% برای اندازه متعادل‌تر */
            @media (max-width: 767px) {
                .category-modal-content {
                    width: 76.5% !important; /* 90% * 0.85 = 76.5% */
                    max-width: 425px !important; /* 500px * 0.85 = 425px */
                }
            }
        </style>
        <?php foreach ($categories as $index => $category) : 
            $category_image = khoshtip_convert_to_webp_url($category['image'] ?? '');
        ?>
            <!-- تغییر از <a> به <div> با data attribute برای مودال -->
            <div class="category-item" 
                 data-category-modal-trigger="modal-<?php echo $index; ?>"
                 style="text-decoration: none; display: block;">
                <?php if (!empty($category_image)) : ?>
                    <img src="<?php echo esc_url($category_image); ?>" alt="<?php echo esc_attr($category['name']); ?>" class="category-bg-image" />
                <?php else : ?>
                    <div class="category-bg-image" style="background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%); display: flex; align-items: center; justify-content: center; font-size: 3rem;">📦</div>
                <?php endif; ?>
                
                <?php if (!empty($category['name'])) : ?>
                    <div class="category-title-overlay">
                        <h3><?php echo esc_html($category['name']); ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- اضافه کردن مودال‌ها برای هر دسته‌بندی -->
    <?php foreach ($categories as $index => $category) : 
        $modal_image = !empty($category['modal_image']) ? $category['modal_image'] : ($category['image'] ?? '');
        $modal_image = khoshtip_convert_to_webp_url($modal_image);
    ?>
        <div class="category-modal" data-category-modal="modal-<?php echo $index; ?>">
            <div class="category-modal-content">
                <button class="category-modal-close" data-category-modal-close>&times;</button>
                <?php if (!empty($modal_image)) : ?>
                    <img src="<?php echo esc_url($modal_image); ?>" 
                         alt="<?php echo esc_attr($category['name']); ?>" 
                         class="category-modal-image" />
                <?php endif; ?>
                
                <div class="category-modal-body">
                    <h2 class="category-modal-title"><?php echo esc_html($category['name']); ?></h2>
                    
                    <?php if (!empty($category['description'])) : ?>
                        <p class="category-modal-description">
                            <?php echo esc_html($category['description']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Show "دیدن بیشتر" button only if link exists -->
                    <?php if (!empty($category['link'])) : ?>
                        <a href="<?php echo esc_url($category['link']); ?>" class="category-modal-link">
                            دیدن بیشتر
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- اضافه کردن JavaScript برای مدیریت مودال‌ها -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // باز کردن مودال
            document.querySelectorAll('[data-category-modal-trigger]').forEach(function(trigger) {
                trigger.addEventListener('click', function() {
                    const modalId = this.getAttribute('data-category-modal-trigger');
                    const modal = document.querySelector('[data-category-modal="' + modalId + '"]');
                    if (modal) {
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            });

            // بستن مودال با دکمه X
            document.querySelectorAll('[data-category-modal-close]').forEach(function(closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const modal = this.closest('.category-modal');
                    if (modal) {
                        modal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });

            // بستن مودال با کلیک روی backdrop
            document.querySelectorAll('.category-modal').forEach(function(modal) {
                modal.addEventListener('click', function(e) {
                    // فقط اگر روی پس‌زمینه خارجی (نه روی content) کلیک شد، مودال را ببند
                    if (e.target === this) {
                        this.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });

            // بستن مودال با کلید ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.category-modal.active').forEach(function(modal) {
                        modal.classList.remove('active');
                        document.body.style.overflow = '';
                    });
                }
            });
        });
    </script>
</section>
