(() => {
  const form = document.querySelector('form[data-sawerna-newsletter]');
  if (!form || !window.fetch || !window.FormData) {
    return;
  }

  const config = window.sawernaNewsletter || {};
  const messages = config.messages || {};
  const ajaxUrl = config.ajaxUrl || form.getAttribute('action');
  const fallbackError = messages.error || 'Something went wrong. Please try again.';
  const email = form.querySelector('input[type="email"]');
  const button = form.querySelector('button[type="submit"]');
  const status = form.querySelector('[role="status"]');
  let sending = false;

  const setStatus = (text, type) => {
    if (!status) {
      return;
    }
    status.classList.remove('is-success', 'is-error');
    if (type) {
      status.classList.add(`is-${type}`);
    }
    // textContent only: server text is never parsed as HTML.
    status.textContent = text || '';
  };

  const setBusy = (busy) => {
    sending = busy;
    form.setAttribute('aria-busy', busy ? 'true' : 'false');
    if (button) {
      button.disabled = busy;
    }
  };

  if (email) {
    // Native validation blocks submit before our handler runs; mirror its state for assistive tech.
    email.addEventListener('invalid', () => email.setAttribute('aria-invalid', 'true'));
    email.addEventListener('input', () => email.removeAttribute('aria-invalid'));
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (sending) {
      return;
    }

    if (!form.checkValidity()) {
      if (email) {
        email.setAttribute('aria-invalid', email.validity.valid ? 'false' : 'true');
      }
      form.reportValidity();
      return;
    }
    if (email) {
      email.removeAttribute('aria-invalid');
    }

    setStatus('', null);
    setBusy(true);

    let response;
    try {
      response = await fetch(ajaxUrl, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });
    } catch (err) {
      setBusy(false);
      setStatus(messages.network || fallbackError, 'error');
      return;
    }

    let payload = null;
    try {
      payload = await response.json();
    } catch (err) {
      payload = null;
    }

    const serverMessage =
      payload && payload.data && typeof payload.data.message === 'string' ? payload.data.message : '';

    setBusy(false);

    if (response.ok && payload && payload.success) {
      form.reset();
      setStatus(serverMessage || fallbackError, 'success');
      return;
    }

    if (response.status === 429) {
      setStatus(serverMessage || messages.rateLimited || fallbackError, 'error');
      return;
    }

    // 403 usually means an expired nonce on a cached page; the server message asks for a reload.
    setStatus(serverMessage || fallbackError, 'error');
  });
})();
