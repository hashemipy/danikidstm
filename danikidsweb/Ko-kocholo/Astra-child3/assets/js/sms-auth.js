;(($) => {
  // Declare jQuery and khoshtipAuth variables
  const jQuery = window.jQuery
  const khoshtipAuth = window.khoshtipAuth
  let resendTimer = null
  let resendSeconds = 0

  jQuery(document).ready(($) => {
    // Function to start 60-second timer
    const startResendTimer = () => {
      resendSeconds = 60
      const $btn = $("#resendCodeBtn")
      $btn.prop("disabled", true)

      const updateTimer = () => {
        if (resendSeconds > 0) {
          $btn.text(`ارسال مجدد کد (${resendSeconds} ثانیه)`)
          resendSeconds--
          resendTimer = setTimeout(updateTimer, 1000)
        } else {
          $btn.text("ارسال مجدد کد")
          $btn.prop("disabled", false)
        }
      }

      updateTimer()
    }

    // Function to stop timer
    const stopResendTimer = () => {
      if (resendTimer) {
        clearTimeout(resendTimer)
        resendTimer = null
      }
      resendSeconds = 0
      $("#resendCodeBtn").text("ارسال مجدد کد").prop("disabled", false)
    }

    // Open SMS login modal
    $("#smsLoginBtn").on("click", () => {
      $("#smsLoginModal").fadeIn()
      $("#smsPhone").focus()
    })

    // Send SMS code
    $("#sendCodeBtn").on("click", function () {
      const phone = $("#smsPhone").val().trim()
      const $btn = $(this)
      const $error = $("#smsError")

      // Validate phone number
      if (!/^09[0-9]{9}$/.test(phone)) {
        $error.text("شماره موبایل نامعتبر است").fadeIn()
        return
      }

      $error.hide()
      $btn.prop("disabled", true).text("در حال ارسال...")

      $.ajax({
        url: khoshtipAuth.ajaxurl,
        type: "POST",
        data: {
          action: "send_sms_code",
          nonce: khoshtipAuth.smsNonce,
          phone: phone,
        },
        success: (response) => {
          if (response.success) {
            $("#smsStep1").hide()
            $("#smsStep2").show()
            $("#displayPhone").text(phone)
            $("#smsCode").focus()
            // Start timer after sending code
            startResendTimer()
          } else {
            $error.text(response.data.message || "خطا در ارسال کد").fadeIn()
          }
        },
        error: () => {
          $error.text("خطا در ارتباط با سرور").fadeIn()
        },
        complete: () => {
          $btn.prop("disabled", false).text("ارسال کد تایید")
        },
      })
    })

    // Verify SMS code
    $("#verifyCodeBtn").on("click", function () {
      const phone = $("#smsPhone").val().trim()
      const code = $("#smsCode").val().trim()
      const $btn = $(this)
      const $error = $("#smsError2")

      if (code.length !== 6) {
        $error.text("کد تایید باید 6 رقم باشد").fadeIn()
        return
      }

      $error.hide()
      $btn.prop("disabled", true).text("در حال تایید...")

      $.ajax({
        url: khoshtipAuth.ajaxurl,
        type: "POST",
        data: {
          action: "verify_sms_code",
          nonce: khoshtipAuth.smsNonce,
          phone: phone,
          code: code,
        },
        success: (response) => {
          if (response.success) {
            // Stop timer before redirect
            stopResendTimer()
            window.location.href = response.data.redirect
          } else {
            $error.text(response.data.message || "کد تایید نادرست است").fadeIn()
          }
        },
        error: () => {
          $error.text("خطا در ارتباط با سرور").fadeIn()
        },
        complete: () => {
          $btn.prop("disabled", false).text("تایید و ورود")
        },
      })
    })

    // Resend code
    $("#resendCodeBtn").on("click", () => {
      // If timer is active, do not allow click
      if (resendSeconds > 0) {
        return
      }

      // Stop previous timer
      stopResendTimer()

      $("#smsStep2").hide()
      $("#smsStep1").show()
      $("#smsCode").val("")
      $("#smsError2").hide()
    })

    // Enter key handlers
    $("#smsPhone").on("keypress", (e) => {
      if (e.which === 13) {
        $("#sendCodeBtn").click()
      }
    })

    $("#smsCode").on("keypress", (e) => {
      if (e.which === 13) {
        $("#verifyCodeBtn").click()
      }
    })
  })

  window.closeSmsModal = () => {
    jQuery("#smsLoginModal").fadeOut()
    jQuery("#smsStep1").show()
    jQuery("#smsStep2").hide()
    jQuery("#smsPhone").val("")
    jQuery("#smsCode").val("")
    jQuery("#smsError, #smsError2").hide()

    // Stop timer when closing modal
    if (resendTimer) {
      clearTimeout(resendTimer)
      resendTimer = null
    }
    resendSeconds = 0
    jQuery("#resendCodeBtn").text("ارسال مجدد کد").prop("disabled", false)
  }
})(window.jQuery)
