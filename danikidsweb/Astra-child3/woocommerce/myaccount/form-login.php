<?php
/**
 * فرم ورود سفارشی WooCommerce
 * صفحه ورود زیبا با قابلیت ورود با Google و شماره موبایل
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="khoshtip-auth-container">
    
    <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

    <div class="khoshtip-auth-wrapper" id="customer_login">

        <div class="khoshtip-auth-box">

    <?php endif; ?>

            <h2 class="khoshtip-auth-title">ورود به حساب کاربری</h2>
            <p class="khoshtip-auth-subtitle">به فروشگاه hashemipy خوش آمدید</p>

            <?php
            if ( isset( $_GET['error'] ) ) {
                $error = sanitize_text_field( $_GET['error'] );
                $message = isset( $_GET['message'] ) ? sanitize_text_field( urldecode( $_GET['message'] ) ) : '';
                
                switch ( $error ) {
                    case 'missing_params':
                        wc_add_notice( 'پارامترهای مورد نیاز ارسال نشده است.', 'error' );
                        break;
                    case 'invalid_state':
                        wc_add_notice( 'درخواست نامعتبر است. لطفا دوباره امتحان کنید.', 'error' );
                        break;
                    case 'token_failed':
                        wc_add_notice( 'خطا در دریافت توکن: ' . $message, 'error' );
                        break;
                    case 'user_info_failed':
                        wc_add_notice( 'خطا در دریافت اطلاعات کاربر: ' . $message, 'error' );
                        break;
                    case 'login_failed':
                        wc_add_notice( 'خطا در ورود: ' . $message, 'error' );
                        break;
                }
            }
            ?>

            <!-- دکمه ورود با Google -->
            <?php 
            $google_enabled = get_option( 'khoshtip_google_enabled', '0' );
            $sms_enabled = get_option( 'khoshtip_sms_enabled', '0' );
            
            // چک کردن تنظیمات دیگر که ممکن است استفاده شود
            if ( empty( $google_enabled ) || $google_enabled === '0' ) {
                $google_enabled = get_option( 'khoshtip_enable_google_auth' );
            }
            if ( empty( $sms_enabled ) || $sms_enabled === '0' ) {
                $sms_enabled = get_option( 'khoshtip_enable_sms_auth' );
            }
            
            // اگر Client ID و Secret گوگل تنظیم شده، فعال در نظر بگیر
            $google_client_id = get_option('khoshtip_google_client_id');
            if ( !empty($google_client_id) ) {
                $google_enabled = '1';
            }
            ?>
            
            <?php if ( $google_enabled === '1' || $google_enabled === 'yes' || $sms_enabled === '1' || $sms_enabled === 'yes' ) : ?>
            <div class="google-login-section">
                
                <?php if ( $google_enabled === '1' || $google_enabled === 'yes' ) : ?>
                <button type="button" id="googleLoginBtn" class="google-login-button">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    ورود با Google
                </button>
                <?php endif; ?>
                
                <?php if ( $sms_enabled === '1' || $sms_enabled === 'yes' ) : ?>
                <button type="button" class="sms-login-button" id="smsLoginBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                        <line x1="12" y1="18" x2="12.01" y2="18"/>
                    </svg>
                    ورود با شماره موبایل
                </button>
                <?php endif; ?>
                
                <div class="google-login-divider">
                    <span>یا</span>
                </div>
            </div>
            <?php endif; ?>

            <form class="woocommerce-form woocommerce-form-login login" method="post">

                <?php do_action( 'woocommerce_login_form_start' ); ?>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="username">نام کاربری یا آدرس ایمیل&nbsp;<span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
                </p>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="password">رمز عبور&nbsp;<span class="required">*</span></label>
                    <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required />
                </p>

                <?php do_action( 'woocommerce_login_form' ); ?>

                <p class="form-row">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                        <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span>مرا به خاطر بسپار</span>
                    </label>
                    <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                    <button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">ورود</button>
                </p>
                <p class="woocommerce-LostPassword lost_password">
                    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">رمز عبور خود را فراموش کرده‌اید؟</a>
                </p>

                <?php do_action( 'woocommerce_login_form_end' ); ?>

            </form>

    <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

        </div>

        <div class="khoshtip-auth-box">

            <h2 class="khoshtip-auth-title">ثبت‌نام</h2>
            <p class="khoshtip-auth-subtitle">حساب کاربری جدید ایجاد کنید</p>

            <!-- دکمه ثبت‌نام با Google -->
            <?php if ( $google_enabled === '1' || $google_enabled === 'yes' ) : ?>
            <div class="google-login-section">
                <button type="button" id="googleLoginBtn" class="google-login-button">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    ثبت‌نام با Google
                </button>
                
                <div class="google-login-divider">
                    <span>یا</span>
                </div>
            </div>
            <?php endif; ?>

            <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

                <?php do_action( 'woocommerce_register_form_start' ); ?>

                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="reg_username">نام کاربری&nbsp;<span class="required">*</span></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
                    </p>

                <?php endif; ?>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_email">آدرس ایمیل&nbsp;<span class="required">*</span></label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
                </p>

                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="reg_password">رمز عبور&nbsp;<span class="required">*</span></label>
                        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required />
                    </p>

                <?php else : ?>

                    <p>یک رمز عبور به آدرس ایمیل شما ارسال خواهد شد.</p>

                <?php endif; ?>

                <?php do_action( 'woocommerce_register_form' ); ?>

                <p class="woocommerce-form-row form-row">
                    <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                    <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">ثبت‌نام</button>
                </p>

                <?php do_action( 'woocommerce_register_form_end' ); ?>

            </form>

        </div>

    </div>
    <?php endif; ?>

</div>

<!-- دکمه ورود با شماره موبایل -->
<?php if ( $sms_enabled === '1' || $sms_enabled === 'yes' ) : ?>
<div id="smsLoginModal" class="khoshtip-modal" style="display: none;">
    <div class="khoshtip-modal-content sms-modal-content">
        <span class="khoshtip-modal-close" onclick="closeSmsModal()">&times;</span>
        
        <div id="smsStep1" class="sms-step">
            <h2>ورود با شماره موبایل</h2>
            <p>شماره موبایل خود را وارد کنید</p>
            
            <div class="sms-form-group">
                <input type="tel" 
                       id="smsPhone" 
                       class="sms-input" 
                       placeholder="09123456789" 
                       pattern="09[0-9]{9}"
                       maxlength="11">
                <button type="button" id="sendCodeBtn" class="sms-button">ارسال کد تایید</button>
            </div>
            <div id="smsError" class="sms-error" style="display: none;"></div>
        </div>
        
        <div id="smsStep2" class="sms-step" style="display: none;">
            <h2>کد تایید را وارد کنید</h2>
            <p>کد 6 رقمی ارسال شده به شماره <span id="displayPhone"></span> را وارد کنید</p>
            
            <div class="sms-form-group">
                <input type="text" 
                       id="smsCode" 
                       class="sms-input" 
                       placeholder="123456" 
                       maxlength="6"
                       pattern="[0-9]{6}">
                <button type="button" id="verifyCodeBtn" class="sms-button">تایید و ورود</button>
            </div>
            <div id="smsError2" class="sms-error" style="display: none;"></div>
            <button type="button" id="resendCodeBtn" class="sms-link-button">ارسال مجدد کد</button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
