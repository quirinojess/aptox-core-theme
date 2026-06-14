document.addEventListener('DOMContentLoaded', function () {
  const sections = Array.from(document.querySelectorAll('[data-home-section]'));

  if (!sections.length || !window.aptoxHomeLazy?.restUrl) {
    return;
  }

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const htmlCache = new Map();
  const fetchPromises = new Map();

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
    if (htmlCache.has(sectionName)) {
      return htmlCache.get(sectionName);
    }

    if (fetchPromises.has(sectionName)) {
      return fetchPromises.get(sectionName);
    }

    const request = fetch(`${window.aptoxHomeLazy.restUrl}${sectionName}`, {
      method: 'GET',
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
        fetchPromises.delete(sectionName);
      });

    fetchPromises.set(sectionName, request);
    const html = await request;

    if (html) {
      htmlCache.set(sectionName, html);
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
