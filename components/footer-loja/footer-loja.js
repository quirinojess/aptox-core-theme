document.addEventListener('DOMContentLoaded', function () {
  const DESKTOP_NAV_MQ = '(min-width: 768px) and (hover: hover) and (pointer: fine)';

  document.querySelectorAll('.footer-loja .recipe-tags-carousel').forEach((carousel) => {
    const track = carousel.querySelector('.tags-track');
    const prev = carousel.querySelector('.recipe-tags-nav--prev');
    const next = carousel.querySelector('.recipe-tags-nav--next');

    if (!track || !prev || !next) {
      return;
    }

    const desktopNav = window.matchMedia(DESKTOP_NAV_MQ);
    let navReady = false;

    const getMaxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);

    const hasOverflow = () => getMaxScroll() > 2;

    const getScrollStep = () => {
      const item = track.querySelector('.tag-item');

      if (!item) {
        return Math.round(track.clientWidth * 0.75);
      }

      const trackStyles = window.getComputedStyle(track);
      const gap = parseFloat(trackStyles.columnGap || trackStyles.gap) || 0;

      return item.offsetWidth + gap;
    };

    const updateNavState = () => {
      if (!navReady) {
        return;
      }

      const maxScroll = getMaxScroll();
      const overflow = hasOverflow();
      const atStart = track.scrollLeft <= 2;
      const atEnd = track.scrollLeft >= maxScroll - 2;

      prev.disabled = !overflow || atStart;
      next.disabled = !overflow || atEnd;
    };

    const scheduleNavUpdate = () => {
      requestAnimationFrame(() => {
        requestAnimationFrame(updateNavState);
      });
    };

    const whenTrackReady = (callback) => {
      const images = track.querySelectorAll('img');
      let pending = 0;

      images.forEach((image) => {
        if (!image.complete) {
          pending += 1;
        }
      });

      const finish = () => {
        scheduleNavUpdate();

        if (typeof callback === 'function') {
          callback();
        }
      };

      if (!pending) {
        finish();
        return;
      }

      const done = () => {
        pending -= 1;

        if (pending <= 0) {
          finish();
        }
      };

      images.forEach((image) => {
        if (image.complete) {
          return;
        }

        image.addEventListener('load', done, { once: true });
        image.addEventListener('error', done, { once: true });
      });
    };

    const scrollTrack = (direction) => {
      track.scrollBy({
        left: direction * getScrollStep() * 3,
        behavior: 'smooth',
      });
    };

    const onPrevClick = () => scrollTrack(-1);
    const onNextClick = () => scrollTrack(1);

    const enableDesktopNav = () => {
      if (navReady) {
        scheduleNavUpdate();
        return;
      }

      prev.hidden = false;
      next.hidden = false;
      prev.addEventListener('click', onPrevClick);
      next.addEventListener('click', onNextClick);
      navReady = true;
      scheduleNavUpdate();
    };

    const disableDesktopNav = () => {
      prev.hidden = true;
      next.hidden = true;
      prev.disabled = true;
      next.disabled = true;
      navReady = false;
    };

    const syncNavMode = () => {
      if (desktopNav.matches) {
        enableDesktopNav();
      } else {
        disableDesktopNav();
      }
    };

    track.addEventListener('scroll', updateNavState, { passive: true });
    window.addEventListener('resize', scheduleNavUpdate);
    desktopNav.addEventListener('change', syncNavMode);

    if ('ResizeObserver' in window) {
      const resizeObserver = new ResizeObserver(scheduleNavUpdate);
      resizeObserver.observe(track);
    }

    syncNavMode();
    whenTrackReady();
  });
});
