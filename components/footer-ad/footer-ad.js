(function () {
  const activateSticky = () => {
    const root = document.documentElement;

    root.setAttribute('data-aptox-sticky', 'active');
    root.classList.add('has-aptox-sticky');
    root.classList.remove('aptox-sticky-dismissed');
  };

  const hideSticky = () => {
    const root = document.documentElement;
    const chrome = document.getElementById('aptoxStickyChrome');

    chrome?.classList.add('is-hidden');
    root.classList.add('aptox-sticky-dismissed');
    root.removeAttribute('data-aptox-sticky');
    root.classList.remove('has-aptox-sticky');
  };

  const requestAdSenseRender = (slot) => {
    if (!slot) {
      return;
    }

    slot.querySelectorAll('ins.adsbygoogle').forEach((ins) => {
      if (ins.getAttribute('data-adsbygoogle-status')) {
        return;
      }

      try {
        (window.adsbygoogle = window.adsbygoogle || []).push({});
      } catch (error) {
        /* Ignore AdSense init errors. */
      }
    });
  };

  const initSticky = () => {
    try {
      sessionStorage.removeItem('aptox-footer-ad-dismissed');
    } catch (error) {
      /* Ignore storage errors. */
    }

    const chrome = document.getElementById('aptoxStickyChrome');

    if (!chrome || chrome.dataset.hasWidget !== 'true' || chrome.classList.contains('is-hidden')) {
      return;
    }

    const slot = document.getElementById('aptoxStickySlot');
    const closeBtn = chrome.querySelector('.close');

    requestAdSenseRender(slot);

    chrome.classList.remove('is-hidden');
    chrome.classList.add('is-visible');
    activateSticky();

    if (!closeBtn || closeBtn.dataset.bound === 'true') {
      return;
    }

    closeBtn.dataset.bound = 'true';
    closeBtn.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      hideSticky();
    });
  };

  window.aptoxSyncFooterAdState = (state) => {
    const chrome = document.getElementById('aptoxStickyChrome');

    if (!chrome || chrome.dataset.hasWidget !== 'true') {
      return;
    }

    if (state === 'active') {
      chrome.classList.remove('is-hidden');
      chrome.classList.add('is-visible');
      activateSticky();
      return;
    }

    hideSticky();
  };

  initSticky();
  document.addEventListener('DOMContentLoaded', initSticky);
  window.addEventListener('load', initSticky);
})();
