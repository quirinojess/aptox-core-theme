(function () {
  const storageKey = 'aptox-footer-ad-dismissed';

  const clearFooterAdState = () => {
    const root = document.documentElement;

    root.removeAttribute('data-footer-ad');
    root.classList.remove('has-footer-ad');
    root.style.removeProperty('--footer-ad-lift');
    root.style.removeProperty('--footer-ad-bar-height');
  };

  const syncFooterAdState = (state) => {
    clearFooterAdState();

    if (state === 'active' && !applyMeasuredLift()) {
      clearFooterAdState();
    }
  };

  window.aptoxSyncFooterAdState = syncFooterAdState;

  const hasVisibleBox = (element) => {
    if (!element) {
      return false;
    }

    const rect = element.getBoundingClientRect();

    return rect.height > 1 && rect.width > 1;
  };

  const slotHasVisibleAd = (slot) => {
    if (!slot) {
      return false;
    }

    const iframes = slot.querySelectorAll('iframe');

    for (const iframe of iframes) {
      if (hasVisibleBox(iframe)) {
        return true;
      }
    }

    const images = slot.querySelectorAll('img[src]');

    for (const image of images) {
      if (hasVisibleBox(image)) {
        return true;
      }
    }

    const media = slot.querySelectorAll('video, embed, object');

    for (const element of media) {
      if (hasVisibleBox(element)) {
        return true;
      }
    }

    const adSense = slot.querySelector('ins[data-ad-client], ins[data-ad-slot], ins.adsbygoogle');

    if (adSense) {
      if (adSense.getAttribute('data-ad-status') === 'filled') {
        return true;
      }

      const innerFrame = adSense.querySelector('iframe');

      return hasVisibleBox(innerFrame);
    }

    return false;
  };

  const slotHasPendingAd = (slot) => {
    if (!slot) {
      return false;
    }

    return Boolean(
      slot.querySelector('ins[data-ad-client], ins[data-ad-slot], ins.adsbygoogle')
    );
  };

  const applyMeasuredLift = () => {
    const root = document.documentElement;
    const footerAd = document.getElementById('footerAd');

    clearFooterAdState();

    if (!footerAd || !footerAd.classList.contains('is-visible') || footerAd.classList.contains('is-hidden')) {
      return false;
    }

    const height = Math.ceil(footerAd.getBoundingClientRect().height);

    if (height < 2) {
      return false;
    }

    root.style.setProperty('--footer-ad-bar-height', `${height}px`);
    root.setAttribute('data-footer-ad', 'active');
    root.classList.add('has-footer-ad');

    return true;
  };

  const clearDismissedOnReload = () => {
    const navEntry = performance.getEntriesByType('navigation')[0];

    if (!navEntry || navEntry.type !== 'reload') {
      return;
    }

    try {
      sessionStorage.removeItem(storageKey);
    } catch (error) {
      /* Ignore storage errors. */
    }
  };

  const isDismissed = () => {
    try {
      return sessionStorage.getItem(storageKey) === 'true';
    } catch (error) {
      return false;
    }
  };

  const hideFooterAd = (footerAd, { remove = false } = {}) => {
    if (!footerAd) {
      clearFooterAdState();
      return;
    }

    if (remove) {
      footerAd.remove();
    } else {
      footerAd.classList.remove('is-visible');
      footerAd.classList.add('is-hidden');
      footerAd.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.add('footer-ad-dismissed');
    }

    clearFooterAdState();
  };

  const showFooterAd = (footerAd) => {
    footerAd.classList.remove('is-hidden');
    footerAd.classList.add('is-visible');
    footerAd.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.remove('footer-ad-dismissed');

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        if (!applyMeasuredLift()) {
          hideFooterAd(footerAd, { remove: true });
        }
      });
    });
  };

  const bindCloseButton = (footerAd) => {
    const closeBtn = footerAd.querySelector('.footer-ad__close');

    if (!closeBtn || closeBtn.dataset.bound === 'true') {
      return;
    }

    closeBtn.dataset.bound = 'true';
    closeBtn.addEventListener('click', () => {
      hideFooterAd(footerAd);

      try {
        sessionStorage.setItem(storageKey, 'true');
      } catch (error) {
        /* Ignore storage errors. */
      }
    });
  };

  let pendingRetryTimer = null;

  const clearPendingRetry = () => {
    if (pendingRetryTimer !== null) {
      window.clearInterval(pendingRetryTimer);
      pendingRetryTimer = null;
    }
  };

  const evaluateFooterAd = () => {
    clearPendingRetry();
    clearFooterAdState();

    const footerAd = document.getElementById('footerAd');

    if (!footerAd) {
      return;
    }

    const slot = footerAd.querySelector('.footer-ad__slot');

    if (!slotHasVisibleAd(slot)) {
      if (slotHasPendingAd(slot)) {
        let attempts = 0;

        pendingRetryTimer = window.setInterval(() => {
          attempts += 1;

          if (slotHasVisibleAd(slot)) {
            clearPendingRetry();
            evaluateFooterAd();
            return;
          }

          if (attempts >= 20) {
            clearPendingRetry();
            hideFooterAd(footerAd, { remove: true });
          }
        }, 500);

        return;
      }

      hideFooterAd(footerAd, { remove: true });
      return;
    }

    if (isDismissed()) {
      hideFooterAd(footerAd);
      bindCloseButton(footerAd);
      return;
    }

    showFooterAd(footerAd);
    bindCloseButton(footerAd);
  };

  const syncLiftToVisibleBar = () => {
    const footerAd = document.getElementById('footerAd');

    if (!footerAd || document.documentElement.classList.contains('footer-ad-dismissed')) {
      clearFooterAdState();
      return;
    }

    if (!footerAd.classList.contains('is-visible') || footerAd.classList.contains('is-hidden')) {
      clearFooterAdState();
      return;
    }

    const slot = footerAd.querySelector('.footer-ad__slot');

    if (!slotHasVisibleAd(slot)) {
      clearFooterAdState();
      return;
    }

    applyMeasuredLift();
  };

  clearDismissedOnReload();
  evaluateFooterAd();

  document.addEventListener('DOMContentLoaded', evaluateFooterAd);
  window.addEventListener('resize', syncLiftToVisibleBar);
  window.visualViewport?.addEventListener('resize', syncLiftToVisibleBar);
  window.visualViewport?.addEventListener('scroll', syncLiftToVisibleBar);

  window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
      return;
    }

    clearDismissedOnReload();
    evaluateFooterAd();
  });
})();
