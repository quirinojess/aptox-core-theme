document.addEventListener('DOMContentLoaded', function () {
  const sections = Array.from(document.querySelectorAll('[data-home-section]'));

  if (!sections.length || !window.aptoxHomeLazy?.restUrl) {
    return;
  }

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const seasonSlug = window.aptoxHomeLazy?.seasonSlug || '';
  const htmlCache = new Map();
  const fetchPromises = new Map();

  const getSectionCacheKey = (sectionName) =>
    seasonSlug ? `${sectionName}:${seasonSlug}` : sectionName;

  const getSectionRequestUrl = (sectionName) => {
    const baseUrl = `${window.aptoxHomeLazy.restUrl}${sectionName}`;

    if (!seasonSlug) {
      return baseUrl;
    }

    const params = new URLSearchParams();
    params.set('estacao', seasonSlug);

    return `${baseUrl}?${params.toString()}`;
  };

  const ensureContentNode = (section) => {
    let content = section.querySelector('.home-lazy-section__content');

    if (!content) {
      content = document.createElement('div');
      content.className = 'home-lazy-section__content';
      section.appendChild(content);
    }

    return content;
  };

  const fetchSectionHtml = async (sectionName) => {
    const cacheKey = getSectionCacheKey(sectionName);

    if (htmlCache.has(cacheKey)) {
      return htmlCache.get(cacheKey);
    }

    if (fetchPromises.has(cacheKey)) {
      return fetchPromises.get(cacheKey);
    }

    const request = fetch(getSectionRequestUrl(sectionName), {
      method: 'GET',
      cache: 'no-store',
      headers: {
        Accept: 'application/json',
      },
    })
      .then(async (response) => {
        if (!response.ok) {
          return '';
        }

        const data = await response.json();
        return typeof data.html === 'string' ? data.html.trim() : '';
      })
      .catch(() => '')
      .finally(() => {
        fetchPromises.delete(cacheKey);
      });

    fetchPromises.set(cacheKey, request);
    const html = await request;

    if (html) {
      htmlCache.set(cacheKey, html);
    }

    return html;
  };

  const loadSection = async (section, observer) => {
    const sectionName = section.dataset.homeSection;

    if (
      !sectionName ||
      section.classList.contains('home-lazy-section--loaded') ||
      section.classList.contains('home-lazy-section--empty') ||
      section.classList.contains('home-lazy-section--loading')
    ) {
      if (observer) {
        observer.unobserve(section);
      }
      return;
    }

    const content = ensureContentNode(section);

    section.classList.add('home-lazy-section--loading');
    section.setAttribute('aria-busy', 'true');

    const html = await fetchSectionHtml(sectionName);

    section.classList.remove('home-lazy-section--loading');

    if (!html) {
      section.classList.add('home-lazy-section--empty');
      section.removeAttribute('aria-busy');
      if (observer) {
        observer.unobserve(section);
      }
      return;
    }

    content.innerHTML = html;
    section.classList.add('home-lazy-section--loaded');
    section.removeAttribute('aria-busy');

    if (observer) {
      observer.unobserve(section);
    }

    document.dispatchEvent(
      new CustomEvent('aptox:section-loaded', {
        detail: {
          section: sectionName,
          root: section,
        },
      })
    );
  };

  if (!('IntersectionObserver' in window)) {
    sections.forEach((section) => {
      loadSection(section, null);
    });
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        loadSection(entry.target, observer);
      });
    },
    {
      root: null,
      rootMargin: prefersReducedMotion ? '0px' : '0px 0px 72px 0px',
      threshold: 0,
    }
  );

  sections.forEach((section) => {
    observer.observe(section);
  });
});
