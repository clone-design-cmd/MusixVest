<?php
$page_title = "MusixVest — Create Account";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'inc/head.php'; ?>
<meta name="description" content="Create your MusixVest account to start investing in fractional music royalties.">
</head>
<body class="bg-white text-slate-800 antialiased min-h-screen">
<a href="#main" class="skip-link">Skip to main content</a>

<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">

  <div class="lg:col-span-4 bg-[#2D60C3] text-white p-8 sm:p-12 lg:p-16 flex flex-col justify-between">
    <div>
      <a href="index.php" class="inline-block mb-12">
        <img src="assets/img/logo.png" alt="MusixVest home" class="h-8 w-auto object-contain brightness-0 invert">
      </a>
      <div class="space-y-8">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold mb-3">What is MusixVest</h2>
          <p class="text-blue-100 text-xs sm:text-sm leading-relaxed">
            MusixVest lets fans invest in songs through SongShares, fractional royalty interests. Create an account
            to browse offerings and build a music portfolio.
          </p>
        </div>
        <div>
          <h2 class="text-xl sm:text-2xl font-bold mb-3">Getting started</h2>
          <p class="text-blue-100 text-xs sm:text-sm leading-relaxed">Complete the form to create your account. You'll verify your identity next.</p>
        </div>
      </div>
    </div>
  </div>

  <main id="main" class="lg:col-span-8 p-8 sm:p-12 lg:p-16 flex items-center justify-center">
    <div class="w-full max-w-2xl space-y-8">

      <h1 class="text-3xl font-extrabold text-slate-900">Create account</h1>

      <div id="form-alert" class="alert alert-danger hidden" role="alert"></div>

      <form id="register-form" class="ajax-form space-y-6" data-redirect="verify.php" novalidate>
        <input type="hidden" name="action" value="register">

        <div class="bg-amber-50/50 border border-amber-200/80 rounded-xl p-4 text-xs text-slate-600">
          <span class="font-bold text-slate-800">Note:</span> Enter your first and last name as they appear on your government ID.
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="field-label" for="firstName">First name</label>
            <input type="text" id="firstName" name="first_name" required autocomplete="given-name" class="input" placeholder="Enter your first name">
            <p class="field-error" id="firstName-error">Enter your first name.</p>
          </div>
          <div class="field">
            <label class="field-label" for="lastName">Last name</label>
            <input type="text" id="lastName" name="last_name" required autocomplete="family-name" class="input" placeholder="Enter your last name">
            <p class="field-error" id="lastName-error">Enter your last name.</p>
          </div>
        </div>

        <div class="field">
          <label class="field-label" for="email">Email address</label>
          <input type="email" id="email" name="email" required autocomplete="email" class="input" placeholder="Enter your email address">
          <p class="field-error" id="email-error">Enter a valid email address.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="field-label" for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8" class="input" placeholder="Enter your password">
            <p class="field-hint">Minimum 8 characters.</p>
            <p class="field-error" id="password-error">Password must be at least 8 characters.</p>
          </div>
          <div class="field">
            <label class="field-label" for="confirmPassword">Confirm password</label>
            <input type="password" id="confirmPassword" name="confirm_password" required autocomplete="new-password" class="input" placeholder="Re-enter your password">
            <p class="field-error" id="confirmPassword-error">Passwords do not match.</p>
          </div>
        </div>

        <p class="text-xs text-slate-500">
          By clicking Register, you agree to MusixVest's <a href="about.php" class="text-[#2D60C3] font-medium hover:underline">Terms of Service</a> and <a href="about.php" class="text-[#2D60C3] font-medium hover:underline">Privacy Policy</a>.
        </p>

        <div>
          <button type="submit" class="btn btn-primary px-8 py-3.5"><span class="btn-label">Register</span></button>
        </div>

        <p class="text-sm text-slate-600 pt-2">
          Already have an account? <a href="login.php" class="text-[#2D60C3] font-semibold hover:underline">Log in</a>
        </p>

      </form>
    </div>
  </main>

</div>

<script>
// Client-side pre-validation only — see login.php for how this hands off
// to assets/js/app.js's `.ajax-form` handler when the fields are valid.
(function () {
  var form = document.getElementById('register-form');
  var formAlert = document.getElementById('form-alert');

  var fields = ['firstName', 'lastName', 'email', 'password', 'confirmPassword'].map(function (id) {
    return { input: document.getElementById(id), error: document.getElementById(id + '-error') };
  });

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  form.addEventListener('submit', function (e) {
    formAlert.classList.add('hidden');
    var firstValid = fields[0].input.value.trim().length > 0;
    var lastValid = fields[1].input.value.trim().length > 0;
    var emailValid = isValidEmail(fields[2].input.value.trim());
    var passwordValid = fields[3].input.value.length >= 8;
    var confirmValid = fields[4].input.value.length > 0 && fields[4].input.value === fields[3].input.value;

    var results = [firstValid, lastValid, emailValid, passwordValid, confirmValid];
    results.forEach(function (valid, i) {
      fields[i].input.setAttribute('aria-invalid', valid ? 'false' : 'true');
      fields[i].error.classList.toggle('is-visible', !valid);
    });

    if (results.indexOf(false) !== -1) {
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
