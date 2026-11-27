(() => {
  'use strict';
  const dialog = document.createElement('dialog');
  dialog.className = 'confirm-dialog';
  dialog.setAttribute('aria-labelledby', 'confirm-title');
  const title = document.createElement('h2'); title.id = 'confirm-title'; title.textContent = 'Confirm change';
  const text = document.createElement('p');
  const actions = document.createElement('div'); actions.className = 'd-flex justify-content-end gap-2 mt-4';
  const cancel = document.createElement('button'); cancel.type = 'button'; cancel.className = 'btn btn-outline-secondary'; cancel.textContent = 'Cancel';
  const confirm = document.createElement('button'); confirm.type = 'button'; confirm.className = 'btn btn-primary'; confirm.textContent = 'Confirm';
  actions.append(cancel, confirm); dialog.append(title, text, actions); document.body.append(dialog);
  let pending, previous;
  cancel.addEventListener('click', () => dialog.close());
  dialog.addEventListener('close', () => previous?.focus());
  confirm.addEventListener('click', () => { const form = pending; dialog.close(); form.dataset.confirmed = 'true'; form.requestSubmit(); });
  document.addEventListener('submit', event => {
    const form = event.target;
    if (form.dataset.confirm && !form.dataset.confirmed) {
      event.preventDefault(); pending = form; previous = document.activeElement;
      text.textContent = form.dataset.confirm; dialog.showModal(); cancel.focus(); return;
    }
    form.classList.add('busy'); form.setAttribute('aria-busy', 'true');
    for (const button of form.querySelectorAll('button[type="submit"], button:not([type])')) { button.disabled = true; button.textContent = 'Please wait…'; }
  });
})();

(() => {
  'use strict';
  const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)');
  let navigating = false;

  document.addEventListener('click', event => {
    const origin = event.target instanceof Element ? event.target : event.target.parentElement;
    const link = origin?.closest('a[href]');
    if (!link) {
      return;
    }
    if (navigating) {
      event.preventDefault();
      return;
    }
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || reducedMotion?.matches || link.hasAttribute('download') || (link.target && link.target !== '_self') || link.dataset.noPageTransition !== undefined) {
      return;
    }

    const destination = new URL(link.href, window.location.href);
    const current = new URL(window.location.href);
    const isHashOnly = destination.pathname === current.pathname && destination.search === current.search && destination.hash !== current.hash;
    if (!['http:', 'https:'].includes(destination.protocol) || destination.origin !== current.origin || destination.href === current.href || isHashOnly) {
      return;
    }

    event.preventDefault();
    navigating = true;
    document.documentElement.classList.add('page-leaving');
    window.setTimeout(() => window.location.assign(destination.href), 180);
  });
})();
