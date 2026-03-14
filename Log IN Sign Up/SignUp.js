(function () {
  var params = new URLSearchParams(window.location.search);
  var errorEl = document.getElementById('signup-error');
  var successEl = document.getElementById('signup-success');
  var error = params.get('error');
  var success = params.get('success');
  if (error && errorEl) errorEl.textContent = decodeURIComponent(error);
  if (success && successEl) successEl.textContent = decodeURIComponent(success);
})();
