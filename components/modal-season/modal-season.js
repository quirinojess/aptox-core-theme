document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('seasonModal');

  if (!modal) {
    return;
  }

  const storageKey = 'aptox-season-modal-shown';
  const closeBtn = modal.querySelector('.close');

  const markAsShown = () => {
    try {
      localStorage.setItem(storageKey, 'true');
    } catch (error) {
      // Ignore private browsing / storage restrictions.
    }
  };

  const hasBeenShown = () => {
    try {
      return localStorage.getItem(storageKey) === 'true';
    } catch (error) {
      return false;
    }
  };

  if (hasBeenShown()) {
    return;
  }

  const getStickyChrome = () => document.getElementById('aptoxStickyChrome');

  const setStickyBlocked = (blocked) => {
    getStickyChrome()?.style.setProperty('pointer-events', blocked ? 'none' : '');
  };

  const restoreSticky = () => {
    setStickyBlocked(false);

    if (typeof window.aptoxSyncFooterAdState === 'function') {
      window.aptoxSyncFooterAdState('active');
    }
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    markAsShown();
    restoreSticky();
  };

  const openModal = () => {
    if (hasBeenShown()) {
      return;
    }

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    markAsShown();
    setStickyBlocked(true);
  };

  setTimeout(openModal, 400);

  closeBtn?.addEventListener('click', (event) => {
    event.preventDefault();
    event.stopPropagation();
    closeModal();
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      event.preventDefault();
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
});
