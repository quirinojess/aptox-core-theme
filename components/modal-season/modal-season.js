document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('seasonModal');

  if (!modal) {
    return;
  }

  const storageKey = 'aptox-season-modal-shown';
  const closeBtn = modal.querySelector('.close');
  const navEntry = performance.getEntriesByType('navigation')[0];
  const isReload = navEntry?.type === 'reload';

  if (!isReload && sessionStorage.getItem(storageKey) === 'true') {
    return;
  }

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  };

  const openModal = () => {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    sessionStorage.setItem(storageKey, 'true');
  };

  setTimeout(openModal, 400);

  closeBtn?.addEventListener('click', closeModal);

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
});
