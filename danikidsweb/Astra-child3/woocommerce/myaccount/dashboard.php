<?php
/**
 * My Account Dashboard - Modern Mobile Design
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$customer_orders = wc_get_orders( array(
    'customer' => get_current_user_id(),
    'limit'    => 5,
) );
?>

<div class="khoshtip-modern-dashboard">
    <!-- Header Section with Gradient -->
    <div class="dashboard-header">
        <div class="user-profile-section">
            <div class="avatar-wrapper">
                <?php echo get_avatar( $current_user->ID, 80 ); ?>
                <div class="status-indicator"></div>
            </div>
            <div class="user-info">
                <h1 class="user-greeting">سلام، <?php echo esc_html( $current_user->display_name ); ?> 👋</h1>
                <p class="user-email"><?php echo esc_html( $current_user->user_email ); ?></p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orders-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3zM8 8h8M8 12h8M8 16h5"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo count( $customer_orders ); ?></span>
                <span class="stat-label">سفارش‌ها</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon wishlist-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">0</span>
                <span class="stat-label">علاقه‌مندی</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon address-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">0</span>
                <span class="stat-label">آدرس‌ها</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <h2 class="section-title">دسترسی سریع</h2>
        <div class="actions-grid">
            <a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="action-card">
                <div class="action-icon purple-gradient">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <span class="action-title">سفارش‌های من</span>
                <span class="action-subtitle">مشاهده و پیگیری</span>
            </a>

            <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>" class="action-card">
                <div class="action-icon blue-gradient">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <span class="action-title">آدرس‌ها</span>
                <span class="action-subtitle">مدیریت آدرس‌ها</span>
            </a>

            <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>" class="action-card">
                <div class="action-icon pink-gradient">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <span class="action-title">ویرایش حساب</span>
                <span class="action-subtitle">اطلاعات کاربری</span>
            </a>

            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="action-card">
                <div class="action-icon red-gradient">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </div>
                <span class="action-title">خروج</span>
                <span class="action-subtitle">از حساب کاربری</span>
            </a>
        </div>
    </div>

    <!-- Recent Orders -->
    <?php if ( ! empty( $customer_orders ) ) : ?>
    <div class="recent-orders-section">
        <h2 class="section-title">آخرین سفارشات</h2>
        <div class="orders-list">
            <?php foreach ( $customer_orders as $order ) : ?>
            <div class="order-item">
                <div class="order-info">
                    <span class="order-number">#<?php echo $order->get_order_number(); ?></span>
                    <span class="order-date"><?php echo wc_format_datetime( $order->get_date_created() ); ?></span>
                </div>
                <div class="order-status">
                    <span class="status-badge status-<?php echo esc_attr( $order->get_status() ); ?>">
                        <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                    </span>
                    <span class="order-total"><?php echo $order->get_formatted_order_total(); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="view-all-btn">
            مشاهده همه سفارشات
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
    </div>
    <?php else : ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
        </div>
        <h3 class="empty-title">هنوز سفارشی ندارید</h3>
        <p class="empty-subtitle">محصولات مورد علاقه خود را کشف کنید</p>
        <a href="<?php echo esc_url( home_url() ); ?>" class="shop-now-btn">
            شروع خرید
        </a>
    </div>
    <?php endif; ?>
</div>
