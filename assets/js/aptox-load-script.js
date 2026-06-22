(function () {
  const loaded = new Set();
  const loading = new Map();

  window.aptoxLoadScript = function (src) {
    if (!src) {
      return Promise.resolve();
    }

    if (loaded.has(src)) {
      return Promise.resolve();
    }

    if (loading.has(src)) {
      return loading.get(src);
    }

    const promise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = src;
      script.defer = true;
      script.onload = () => {
        loaded.add(src);
        resolve();
      };
      script.onerror = reject;
      document.head.appendChild(script);
    }).finally(() => {
      loading.delete(src);
    });

    loading.set(src, promise);
    return promise;
  };

  const footerSrc = window.aptoxFooterLojaScript;
  const footer = document.querySelector('.footer-loja');

  if (!footerSrc || !footer) {
    return;
  }

  const loadFooterScript = () => {
    window.aptoxLoadScript(footerSrc).catch(() => {});
  };

  if (!('IntersectionObserver' in window)) {
    loadFooterScript();
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) {
        return;
      }

      observer.disconnect();
      loadFooterScript();
    },
    {
      root: null,
      rootMargin: '200px 0px',
      threshold: 0,
    }
  );

  observer.observe(footer);
})();
