/**
 * اسکریپت مدیریت ورود با Google
 */

;(($) => {
  // Declare khoshtipAuth variable here or import it if it's defined elsewhere
  var khoshtipAuth = window.khoshtipAuth // Assuming khoshtipAuth is attached to window object

  // بررسی وجود متغیر khoshtipAuth
  if (typeof khoshtipAuth === "undefined") {
    console.error("[v0] khoshtipAuth object is not defined")
    return
  }

  // مدیریت کلیک روی دکمه ورود با Google
  $(document).on("click", ".google-login-button", function (e) {
    e.preventDefault()

    const $button = $(this)

    // جلوگیری از کلیک‌های متعدد
    if ($button.hasClass("loading")) {
      return
    }

    $button.addClass("loading")
    $button.prop("disabled", true)

    console.log("[v0] Starting Google authentication...")

    // دریافت URL احراز هویت از سرور
    $.ajax({
      url: khoshtipAuth.ajaxurl,
      type: "POST",
      data: {
        action: "google_auth_url",
        nonce: khoshtipAuth.nonce,
      },
      success: (response) => {
        console.log("[v0] Google auth URL response:", response)

        if (response.success && response.data.url) {
          console.log("[v0] Redirecting to Google...")
          // هدایت به صفحه احراز هویت Google
          window.location.href = response.data.url
        } else {
          console.error("[v0] Invalid response from server")
          alert("خطا در اتصال به Google. لطفاً دوباره تلاش کنید.")
          $button.removeClass("loading")
          $button.prop("disabled", false)
        }
      },
      error: (xhr, status, error) => {
        console.error("[v0] AJAX error:", status, error)
        alert("خطا در اتصال به سرور. لطفاً دوباره تلاش کنید.")
        $button.removeClass("loading")
        $button.prop("disabled", false)
      },
    })
  })

  // نمایش پیام خطا در صورت وجود
  const urlParams = new URLSearchParams(window.location.search)
  const error = urlParams.get("error")

  if (error) {
    let errorMessage = "خطا در ورود با Google."

    switch (error) {
      case "missing_params":
        errorMessage = "اطلاعات ناقص از Google دریافت شد."
        break
      case "invalid_state":
        errorMessage = "درخواست نامعتبر است. لطفاً دوباره تلاش کنید."
        break
      case "token_failed":
        errorMessage = "خطا در دریافت توکن از Google."
        break
      case "user_info_failed":
        errorMessage = "خطا در دریافت اطلاعات کاربری."
        break
      case "login_failed":
        errorMessage = "خطا در ورود به سیستم."
        break
    }

    console.error("[v0] Google auth error:", error, errorMessage)

    // نمایش پیام خطا
    if ($(".woocommerce-error").length === 0) {
      $(".khoshtip-auth-container, .woocommerce")
        .first()
        .prepend('<div class="woocommerce-error" role="alert">' + errorMessage + "</div>")
    }

    // حذف پارامتر error از URL
    const newUrl = window.location.pathname
    window.history.replaceState({}, document.title, newUrl)
  }

  console.log("[v0] Google auth script loaded successfully")
})(window.jQuery)
