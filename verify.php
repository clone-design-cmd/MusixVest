<?php
$page_title = "MusixVest — Verify Identity";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'inc/head.php'; ?>
<meta name="description" content="Verify your identity to start investing on MusixVest.">
</head>
<body class="bg-white text-slate-800 antialiased min-h-screen">
<a href="#main" class="skip-link">Skip to main content</a>

<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">

  <div class="lg:col-span-4 bg-[#2D60C3] text-white p-8 sm:p-12 lg:p-16 flex flex-col justify-between">
    <div>
      <a href="index.php" class="inline-block mb-10">
        <img src="assets/img/logo.png" alt="MusixVest home" class="h-8 w-auto object-contain brightness-0 invert">
      </a>
      <div class="space-y-8">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold mb-3">Setting up your account</h2>
          <p class="text-blue-100 text-xs sm:text-sm leading-relaxed">
            This step confirms your identity and helps keep your account secure.
          </p>
        </div>
        <div>
          <h2 class="text-xl sm:text-2xl font-bold mb-3">Why we ask for this</h2>
          <ul class="space-y-3 text-xs sm:text-sm text-blue-100 leading-relaxed">
            <li><strong class="text-white">Citizenship:</strong> Determines which rules apply to your account.</li>
            <li><strong class="text-white">Date of birth:</strong> Confirms you're old enough to invest.</li>
            <li><strong class="text-white">ID number:</strong> Used to verify your identity and protect your account.</li>
            <li><strong class="text-white">Address:</strong> Should match the ID you'd use to verify in a real system.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <main id="main" class="lg:col-span-8 p-8 sm:p-12 lg:p-16 flex items-center justify-center">
    <div class="w-full max-w-2xl space-y-8">

      <h1 class="text-3xl font-extrabold text-slate-900">Verify identity</h1>
      <div id="form-alert" class="alert alert-danger hidden" role="alert"></div>

      <form id="verify-form" class="ajax-form space-y-6" data-redirect="dashboard.php" novalidate>
        <input type="hidden" name="action" value="verify_identity">

        <fieldset class="space-y-3 pt-2">
          <legend class="text-xs font-semibold text-slate-700 mb-1">Citizenship status</legend>
          <div class="flex flex-wrap items-center gap-6 text-xs font-medium text-slate-700">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="citizenship" value="us_citizen" class="h-4 w-4 text-[#2D60C3]" checked>
              <span>U.S. citizen</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="citizenship" value="us_resident" class="h-4 w-4 text-[#2D60C3]">
              <span>U.S. resident</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="citizenship" value="non_us_resident" class="h-4 w-4 text-[#2D60C3]">
              <span>Non-U.S. resident</span>
            </label>
          </div>
        </fieldset>

        <div class="bg-amber-50/50 border border-amber-200/80 rounded-xl p-4 text-xs text-slate-600">
          <span class="font-bold text-slate-800">Note:</span> Enter your first and last name as they appear on your government ID.
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="field-label" for="firstName">First name</label>
            <input type="text" id="firstName" name="first_name" required class="input uppercase">
            <p class="field-error" id="firstName-error">Enter your first name.</p>
          </div>
          <div class="field">
            <label class="field-label" for="lastName">Last name</label>
            <input type="text" id="lastName" name="last_name" required class="input uppercase">
            <p class="field-error" id="lastName-error">Enter your last name.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="field-label" for="dob">Date of birth</label>
            <input type="date" id="dob" name="dob" required class="input">
            <p class="field-hint">You must be 18 years old to register.</p>
            <p class="field-error" id="dob-error">Enter a valid date of birth. You must be 18 or older.</p>
          </div>
          <div class="field">
            <label class="field-label" for="idNumber">Identification number</label>
            <input type="password" id="idNumber" name="ssn" required inputmode="numeric" class="input" placeholder="Enter your SSN or ID number">
            <p class="field-error" id="idNumber-error">Enter your identification number.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="field-label" for="address1">Address line 1</label>
            <input type="text" id="address1" name="address1" required class="input" placeholder="Street address">
            <p class="field-error" id="address1-error">Enter your address.</p>
          </div>
          <div class="field">
            <label class="field-label" for="address2">Address line 2</label>
            <input type="text" id="address2" name="address2" class="input" placeholder="Apartment, suite, etc. (optional)">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="field-label" for="phone">Mobile number</label>
            <input type="tel" id="phone" name="mobile" required class="input" placeholder="(555) 555-5555">
            <p class="field-error" id="phone-error">Enter a valid phone number.</p>
          </div>
          <div class="field">
            <label class="field-label" for="city">City</label>
            <input type="text" id="city" name="city" required class="input" placeholder="Enter your city">
            <p class="field-error" id="city-error">Enter your city.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="field">
            <label class="field-label" for="state">State</label>
            <select id="state" name="state" required class="input">
              <option value="" disabled selected>Select your state</option>
              <option value="CA">California</option>
              <option value="NY">New York</option>
              <option value="TX">Texas</option>
              <option value="FL">Florida</option>
              <option value="OTHER">Other</option>
            </select>
            <p class="field-error" id="state-error">Select your state.</p>
          </div>
          <div class="field">
            <label class="field-label" for="zip">Zip code</label>
            <input type="text" id="zip" name="zip" required inputmode="numeric" class="input" placeholder="Enter your zip code">
            <p class="field-error" id="zip-error">Enter a valid zip code.</p>
          </div>
        </div>

        <div class="pt-2">
          <button type="submit" class="btn btn-primary px-10 py-3.5"><span class="btn-label">Save &amp; continue</span></button>
        </div>

      </form>
    </div>
  </main>

</div>

<script>
// Client-side pre-validation only — see login.php for how this hands off
// to assets/js/app.js's `.ajax-form` handler when the fields are valid.
(function () {
  var form = document.getElementById('verify-form');
  var formAlert = document.getElementById('form-alert');

  var requiredIds = ['firstName', 'lastName', 'dob', 'idNumber', 'address1', 'phone', 'city', 'state', 'zip'];
  var fields = requiredIds.map(function (id) {
    return { input: document.getElementById(id), error: document.getElementById(id + '-error') };
  });

  function isAdult(dateStr) {
    if (!dateStr) return false;
    var dob = new Date(dateStr);
    if (isNaN(dob.getTime())) return false;
    var eighteenYearsAgo = new Date();
    eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);
    return dob <= eighteenYearsAgo;
  }

  form.addEventListener('submit', function (e) {
    formAlert.classList.add('hidden');

    var allValid = true;
    fields.forEach(function (f) {
      var valid;
      if (f.input.id === 'dob') {
        valid = isAdult(f.input.value);
      } else {
        valid = f.input.value.trim().length > 0;
      }
      if (f.error) {
        f.input.setAttribute('aria-invalid', valid ? 'false' : 'true');
        f.error.classList.toggle('is-visible', !valid);
      }
      if (!valid) allValid = false;
    });

    if (!allValid) {
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
