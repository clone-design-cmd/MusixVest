<?php
require_once __DIR__ . '/config/admin_session.php';

// Already signed in? Skip straight to the dashboard.
if (admin_is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$page_title = "MusixVest Admin — Log In";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'inc/admin-head.php'; ?>
<meta name="description" content="Sign in to the MusixVest admin panel.">
</head>
<body class="bg-white text-slate-800 antialiased min-h-screen relative">
<a href="#main" class="skip-link">Skip to main content</a>

<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">

  <div class="lg:col-span-4 mv-gradient text-white p-8 sm:p-12 lg:p-12 flex flex-col justify-between relative overflow-hidden min-h-[300px] lg:min-h-screen">
    <div class="relative z-10">
      <a href="../index.php" class="inline-block mb-12">
        <img src="../assets/img/logo.png" alt="MusixVest home" class="h-8 w-auto object-contain brightness-0 invert">
      </a>
      <div class="space-y-6 mt-8">
        <p class="text-xs uppercase tracking-widest font-semibold text-blue-200">Admin panel</p>
        <p class="text-blue-100 text-sm leading-relaxed">
          Sign in to publish SongShare offerings, review deposits and withdrawals, and manage payout wallets.
        </p>
      </div>
    </div>
  </div>

  <main id="main" class="lg:col-span-8 p-8 sm:p-12 lg:p-16 flex flex-col justify-center items-center relative">

    <div class="w-full max-w-md py-8 space-y-6">

      <h1 class="text-3xl font-extrabold text-slate-900">Admin sign in</h1>

      <p class="text-xs text-slate-400">Demo login: admin@musixvest.com / admin12345</p>

      <div id="form-alert" class="alert alert-danger hidden" role="alert"></div>

      <form id="admin-login-form" class="ajax-form space-y-5" data-redirect="dashboard.php" novalidate>
        <input type="hidden" name="action" value="admin_login">

        <div class="field">
          <label class="field-label" for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="email" class="input" placeholder="Enter your admin email">
          <p class="field-error" id="email-error">Enter your email address.</p>
        </div>

        <div class="field">
          <label class="field-label" for="password">Password</label>
          <div class="relative">
            <input type="password" id="password" name="password" required autocomplete="current-password" class="input pr-10" placeholder="Enter your password">
            <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-700 transition-colors" aria-label="Show password" aria-pressed="false">
              <svg id="icon-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg id="icon-eye-off" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.025 10.025 0 012.132-3.821m3.821-2.132A9.958 9.958 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.978 9.978 0 01-1.563 3.029M3 3l18 18M9.878 9.878a3 3 0 104.243 4.243"/></svg>
            </button>
          </div>
          <p class="field-error" id="password-error">Enter your password.</p>
        </div>

        <div class="pt-2">
          <button type="submit" class="btn btn-primary w-full"><span class="btn-label">Sign in</span></button>
        </div>

      </form>

      <p class="text-xs text-slate-400 pt-4 border-t border-slate-100">
        This is a separate login from investor accounts — admin credentials live in their own table and can never sign in on the investor site.
      </p>
    </div>

  </main>

</div>

<a href="../index.php" class="fixed bottom-6 right-6 bg-[#003B73] hover:bg-[#002d58] text-white font-semibold text-xs px-5 py-3 rounded-xl shadow-lg transition-all flex items-center gap-2">
  Back to site
</a>

<script>
(function () {
  var toggleBtn = document.getElementById('toggle-password');
  var passwordInput = document.getElementById('password');
  var eyeIcon = document.getElementById('icon-eye');
  var eyeOffIcon = document.getElementById('icon-eye-off');

  toggleBtn.addEventListener('click', function () {
    var isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';
    toggleBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
    toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    eyeIcon.style.display = isHidden ? 'none' : '';
    eyeOffIcon.style.display = isHidden ? '' : 'none';
  });
})();
</script>
<script>
// Client-side pre-validation only; a valid submit bubbles up to
// admin-app.js's delegated `.ajax-form` handler which posts to
// config/admin_request.php.
(function () {
  var form = document.getElementById('admin-login-form');
  var formAlert = document.getElementById('form-alert');
  var emailInput = document.getElementById('email');
  var emailError = document.getElementById('email-error');
  var passwordInput = document.getElementById('password');
  var passwordError = document.getElementById('password-error');

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  form.addEventListener('submit', function (e) {
    formAlert.classList.add('hidden');

    var emailValid = isValidEmail(emailInput.value.trim());
    emailInput.setAttribute('aria-invalid', emailValid ? 'false' : 'true');
    emailError.classList.toggle('is-visible', !emailValid);

    var passwordValid = passwordInput.value.length > 0;
    passwordInput.setAttribute('aria-invalid', passwordValid ? 'false' : 'true');
    passwordError.classList.toggle('is-visible', !passwordValid);

    if (!emailValid || !passwordValid) {
      e.preventDefault();
      e.stopImmediatePropagation();
      formAlert.textContent = 'Please fix the highlighted fields before continuing.';
      formAlert.classList.remove('hidden');
    }
  });
})();
</script>
</body>
</html>
