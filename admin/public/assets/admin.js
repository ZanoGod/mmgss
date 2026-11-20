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
