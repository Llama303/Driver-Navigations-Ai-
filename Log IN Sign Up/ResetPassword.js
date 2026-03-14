(function () {
  var params = new URLSearchParams(window.location.search);
  var token = params.get('token');
  var errorMsg = params.get('error');
  var tokenInput = document.getElementById('reset-token');
  var form = document.getElementById('reset-form');
  var errorEl = document.getElementById('reset-error');

  if (tokenInput && token) {
    tokenInput.value = token;
  }

  if (errorMsg && errorEl) {
    errorEl.textContent = decodeURIComponent(errorMsg);
  } else if (!token && errorEl) {
    errorEl.textContent = 'Invalid or missing reset link. Request a new one from the Forgot password page.';
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      var newPwd = document.getElementById('new-password');
      var confirmPwd = document.getElementById('confirm-password');
      if (errorEl) errorEl.textContent = '';
      if (!token) {
        e.preventDefault();
        if (errorEl) errorEl.textContent = 'Invalid or missing reset link.';
        return;
      }
      if (newPwd && confirmPwd && newPwd.value !== confirmPwd.value) {
        e.preventDefault();
        if (errorEl) errorEl.textContent = 'Passwords do not match.';
        return;
      }
      if (newPwd && newPwd.value.length < 8) {
        e.preventDefault();
        if (errorEl) errorEl.textContent = 'Password must be at least 8 characters.';
        return;
      }
    });
  }
})();
