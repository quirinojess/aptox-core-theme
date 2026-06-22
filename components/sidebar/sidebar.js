(function () {
	const desktopQuery = window.matchMedia('(min-width: 769px)');

	function getFirstContentImage(content) {
		if (!content) {
			return null;
		}

		return content.querySelector('figure img, .wp-block-image img, p img, img');
	}

	function syncPostSideAuthorOffset() {
		const authorSection = document.querySelector('.post-side-author');
		const content = document.querySelector('.align-posts #content');

		if (!authorSection || !content) {
			return;
		}

		if (!desktopQuery.matches) {
			authorSection.style.marginTop = '';
			return;
		}

		const firstImage = getFirstContentImage(content);

		if (!firstImage) {
			authorSection.style.marginTop = '';
			return;
		}

		const offset =
			firstImage.getBoundingClientRect().top -
			authorSection.getBoundingClientRect().top;

		authorSection.style.marginTop = offset > 0 ? `${Math.round(offset)}px` : '0';
	}

	function init() {
		syncPostSideAuthorOffset();

		desktopQuery.addEventListener('change', syncPostSideAuthorOffset);
		window.addEventListener('resize', syncPostSideAuthorOffset);
		window.addEventListener('load', syncPostSideAuthorOffset);

		const content = document.querySelector('.align-posts #content');
		const firstImage = getFirstContentImage(content);

		if (firstImage && !firstImage.complete) {
			firstImage.addEventListener('load', syncPostSideAuthorOffset, { once: true });
		}

		const alignPosts = document.querySelector('.align-posts');

		if (alignPosts && typeof ResizeObserver !== 'undefined') {
			const observer = new ResizeObserver(syncPostSideAuthorOffset);
			observer.observe(alignPosts);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
