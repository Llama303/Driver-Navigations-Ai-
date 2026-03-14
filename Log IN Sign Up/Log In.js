(function () {
  var errorEl = document.getElementById('login-error');
  var successEl = document.getElementById('login-success');
  var form = document.querySelector('form[action*="Login"]') || document.querySelector('form');
  var usernameInput = document.getElementById('username');
  var passwordInput = document.getElementById('password');

  function showError(msg) {
    if (successEl) successEl.textContent = '';
    if (errorEl) {
      errorEl.textContent = msg;
      errorEl.style.display = '';
    }
  }

  function clearError() {
    if (errorEl) errorEl.textContent = '';
  }

  // Show error or success from URL (e.g. after redirect from Login.PHP or SignUp.PHP)
  var params = new URLSearchParams(window.location.search);
  var errorMsg = params.get('error');
  var successMsg = params.get('success');
  if (errorMsg) showError(decodeURIComponent(errorMsg));
  if (successMsg && successEl) successEl.textContent = decodeURIComponent(successMsg);
  if (errorMsg || successMsg) {
    if (window.history && window.history.replaceState) {
      params.delete('error');
      params.delete('success');
      var newSearch = params.toString();
      var newUrl = window.location.pathname + (newSearch ? '?' + newSearch : '') + window.location.hash;
      window.history.replaceState({}, '', newUrl);
    }
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      clearError();
      var user = usernameInput ? usernameInput.value.trim() : '';
      var pass = passwordInput ? passwordInput.value : '';

      if (!user) {
        e.preventDefault();
        showError('Please enter your username.');
        if (usernameInput) {
          usernameInput.focus();
          usernameInput.style.borderColor = 'var(--auth-error, #f87171)';
        }
        return;
      }
      if (!pass) {
        e.preventDefault();
        showError('Please enter your password.');
        if (passwordInput) {
          passwordInput.focus();
          passwordInput.style.borderColor = 'var(--auth-error, #f87171)';
        }
        return;
      }

      // Clear any inline error border from previous attempt
      if (usernameInput) usernameInput.style.borderColor = '';
      if (passwordInput) passwordInput.style.borderColor = '';
    });
  }

  // Clear error border when user types
  if (usernameInput) {
    usernameInput.addEventListener('input', function () { this.style.borderColor = ''; });
  }
  if (passwordInput) {
    passwordInput.addEventListener('input', function () { this.style.borderColor = ''; });
  }
})();
