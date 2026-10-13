/* Contact form submission feedback: prevent duplicate submits and announce result. */
(() => {
  const form = document.querySelector('.contact-page .contact-form[action*="sent-email.php"]');
  if (!form) return;

  const button = form.querySelector('.contact-submit-button');
  const label = form.querySelector('.contact-submit-label');
  const status = form.querySelector('#contactFormStatus');
  if (!button || !label || !status) return;

  const originalLabel = label.textContent.trim() || 'Send inquiry';

  function showStatus(kind, message) {
    status.hidden = false;
    status.className = `contact-form-status is-${kind}`;
    status.textContent = message;
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (button.disabled) return;

    if (!form.reportValidity()) return;

    button.disabled = true;
    button.classList.remove('is-success', 'is-error');
    button.classList.add('is-loading');
    button.setAttribute('aria-busy', 'true');
    label.textContent = 'Sending…';
    showStatus('pending', 'Sending your inquiry. Please wait.');

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'text/plain' }
      });
      const message = (await response.text()).trim();

      if (!response.ok || /could not be sent|not configured|please fill|valid email|error/i.test(message)) {
        throw new Error(message || 'Your inquiry could not be sent. Please try again.');
      }

      button.classList.remove('is-loading');
      button.classList.add('is-success');
      label.textContent = 'Sent successfully';
      showStatus('success', message || 'Thank you. Your inquiry has been sent successfully.');
      form.reset();
    } catch (error) {
      button.classList.remove('is-loading');
      button.classList.add('is-error');
      label.textContent = 'Try again';
      showStatus('error', error.message || 'We could not send your inquiry. Please check your connection and try again.');
    } finally {
      button.classList.remove('is-loading');
      button.removeAttribute('aria-busy');
      button.disabled = false;
      window.setTimeout(() => {
        button.classList.remove('is-success', 'is-error');
        label.textContent = originalLabel;
      }, 4500);
    }
  });
})();
