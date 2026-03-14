(function () {
  var form = document.getElementById('forgot-form');
  var messageEl = document.getElementById('forgot-message');
  var successEl = document.getElementById('forgot-success');

  function showError(msg) {
    if (successEl) successEl.textContent = '';
    if (messageEl) {
      messageEl.textContent = msg;
      messageEl.classList.add('auth-error');
    }
  }

  function showSuccess(msg) {
    if (messageEl) messageEl.textContent = '';
    if (successEl) successEl.textContent = msg;
  }

  function clearMessages() {
    if (messageEl) messageEl.textContent = '';
    if (successEl) successEl.textContent = '';
  }

  // Show message from URL (e.g. after redirect from PHP)
  var params = new URLSearchParams(window.location.search);
  var error = params.get('error');
  var success = params.get('success');
  var resetLink = params.get('reset_link');
  if (error) showError(decodeURIComponent(error));
  if (success) showSuccess(decodeURIComponent(success));
  // If PHP passed reset link (e.g. when mail is not configured), show it for copying
  if (resetLink && successEl) {
    var linkPara = document.createElement('p');
    linkPara.className = 'text-sm mt-2 break-all';
    linkPara.style.wordBreak = 'break-all';
    linkPara.innerHTML = 'Use this link to set a new password (valid 1 hour): <a href="' + resetLink + '" class="underline">Open reset page</a>';
    successEl.appendChild(linkPara);
  }

  if (form) {
    form.addEventListener('submit', function () {
      clearMessages();
    });
  }
})();
