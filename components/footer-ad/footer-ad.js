(function () {
  const syncFooterAdState = (state) => {
    const root = document.documentElement;

    root.removeAttribute('data-footer-ad');
    root.classList.remove('has-footer-ad');
    root.style.removeProperty('--footer-ad-lift');
    root.style.removeProperty('--footer-ad-bar-height');

    if (state === 'active') {
      root.setAttribute('data-footer-ad', 'active');
      root.classList.add('has-footer-ad');
    }
  };

  window.aptoxSyncFooterAdState = syncFooterAdState;

  document.addEventListener('DOMContentLoaded', () => {
    const footerAd = document.getElementById('footerAd');

    if (!footerAd) {
      return;
    }

    const storageKey = 'aptox-footer-ad-dismissed';
    const closeBtn = footerAd.querySelector('.footer-ad__close');

    const hideFooterAd = () => {
      footerAd.classList.remove('is-visible');
      footerAd.classList.add('is-hidden');
      footerAd.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.add('footer-ad-dismissed');
      syncFooterAdState('dismissed');

      try {
        sessionStorage.setItem(storageKey, 'true');
      } catch (error) {
        /* Ignore storage errors. */
      }
    };

    if (footerAd.classList.contains('is-hidden') || document.documentElement.classList.contains('footer-ad-dismissed')) {
      syncFooterAdState('dismissed');
      closeBtn?.addEventListener('click', hideFooterAd);
      return;
    }

    footerAd.classList.add('is-visible');
    footerAd.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.remove('footer-ad-dismissed');
    syncFooterAdState('active');

    closeBtn?.addEventListener('click', hideFooterAd);
  });

  window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
      return;
    }

    const footerAd = document.getElementById('footerAd');

    if (!footerAd) {
      return;
    }

    const storageKey = 'aptox-footer-ad-dismissed';
    let isDismissed = false;

    try {
      isDismissed = sessionStorage.getItem(storageKey) === 'true';
    } catch (error) {
      /* Ignore storage errors. */
    }

    if (isDismissed) {
      footerAd.classList.remove('is-visible');
      footerAd.classList.add('is-hidden');
      document.documentElement.classList.add('footer-ad-dismissed');
      syncFooterAdState('dismissed');
      return;
    }

    footerAd.classList.remove('is-hidden');
    footerAd.classList.add('is-visible');
    document.documentElement.classList.remove('footer-ad-dismissed');
    syncFooterAdState('active');
  });
})();
