document.addEventListener('DOMContentLoaded', function () {
  const supportsHover = window.matchMedia('(hover: hover)').matches;
  const mediaItems = document.querySelectorAll('.post-image-pin__media');

  function closeAllPinMedia(except) {
    mediaItems.forEach(function (item) {
      if (item !== except) {
        item.classList.remove('is-pin-active');
      }
    });
  }

  mediaItems.forEach(function (media, index) {
    const button = media.querySelector('.post-image-pin__button');
    const ring = button && button.querySelector('.post-image-pin__ring');
    if (!button || !ring) {
      return;
    }

    const pathId = 'post-image-pin-path-' + index;
    const marquee = button.dataset.marquee || 'pin it · pin it · pin it · pin it · pin it · pin it · ';

    ring.innerHTML =
      '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
        '<defs>' +
          '<path id="' + pathId + '" d="M 50,50 m -37,0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0"></path>' +
        '</defs>' +
        '<text>' +
          '<textPath href="#' + pathId + '" startOffset="3%" textLength="215" lengthAdjust="spacing">' +
            marquee +
          '</textPath>' +
        '</text>' +
      '</svg>';

    button.addEventListener('click', function (event) {
      event.stopPropagation();
    });

    if (supportsHover) {
      let hideTimer;

      function showButton() {
        window.clearTimeout(hideTimer);
        media.classList.add('is-pin-active');
      }

      function hideButton() {
        hideTimer = window.setTimeout(function () {
          media.classList.remove('is-pin-active');
        }, 120);
      }

      media.addEventListener('mouseenter', showButton);
      media.addEventListener('mouseleave', hideButton);
      button.addEventListener('mouseenter', showButton);
      button.addEventListener('mouseleave', hideButton);
      return;
    }

    media.addEventListener('click', function (event) {
      if (event.target.closest('.post-image-pin__button')) {
        return;
      }

      const isActive = media.classList.contains('is-pin-active');

      closeAllPinMedia(null);

      if (!isActive) {
        media.classList.add('is-pin-active');
      }
    });
  });

  if (!supportsHover) {
    document.addEventListener('click', function (event) {
      if (event.target.closest('.post-image-pin__media')) {
        return;
      }

      closeAllPinMedia(null);
    });
  }
});
