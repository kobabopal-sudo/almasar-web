(() => {
	document.documentElement.classList.add('uabb-js-ready');
	const menus = document.querySelectorAll('.uabb-nav-mega');
	const closeOthers = (activeMenu) => menus.forEach((otherMenu) => {
		if (otherMenu !== activeMenu) otherMenu.open = false;
	});
	if (menus.length) menus.forEach((menu) => {
		let closeTimer;
		let openedFromPointer = false;
		menu.addEventListener('toggle', () => {
			if (menu.open) closeOthers(menu);
		});
		menu.addEventListener('pointerenter', () => {
			if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
				window.clearTimeout(closeTimer);
				closeOthers(menu);
				if (!menu.open) openedFromPointer = true;
				menu.open = true;
			}
		});
		menu.querySelector('summary')?.addEventListener('click', (event) => {
			if (openedFromPointer) {
				event.preventDefault();
				openedFromPointer = false;
			}
		});
		menu.addEventListener('pointerleave', () => {
			if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
				closeTimer = window.setTimeout(() => { menu.open = false; openedFromPointer = false; }, 180);
			}
		});
		menu.addEventListener('focusout', (event) => {
			if (!menu.contains(event.relatedTarget)) menu.open = false;
		});
	});
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') menus.forEach((menu) => { menu.open = false; });
	});

	const filterForms = document.querySelectorAll('.uabb-catalog-filters');
	if (filterForms.length) {
		const compactViewport = window.matchMedia('(max-width: 760px)');
		filterForms.forEach((form, index) => {
			const toggle = form.querySelector('.uabb-filter-toggle');
			const content = form.querySelector('.uabb-filter-content');
			if (!toggle || !content) return;
			if (index > 0) {
				const id = `uabs-catalog-filter-fields-${index + 1}`;
				content.id = id;
				toggle.setAttribute('aria-controls', id);
			}
			const updateFilterSummary = () => {
				const activeCount = Array.from(content.querySelectorAll('select')).filter((select) => select.selectedIndex > 0 && select.value).length
					+ Array.from(content.querySelectorAll('input:not([type="hidden"])')).filter((input) => input.value.trim() !== '').length;
				const label = toggle.querySelector('span:not(.uabb-filter-toggle-icon)');
				if (label) label.textContent = activeCount
					? (document.body.classList.contains('uabb-lang-ar') ? `خيارات مفعّلة: ${new Intl.NumberFormat('ar-AE').format(activeCount)}` : `Refine your selection · ${activeCount} active`)
					: (document.body.classList.contains('uabb-lang-ar') ? 'تصفّح حسب تفضيلاتك' : 'Refine your selection');
				return activeCount;
			};
			const setExpanded = (expanded) => {
				toggle.setAttribute('aria-expanded', String(expanded));
				content.setAttribute('aria-hidden', String(!expanded));
				content.inert = !expanded;
				form.classList.toggle('uabb-filters-expanded', expanded);
				updateFilterSummary();
			};
			setExpanded(!compactViewport.matches || updateFilterSummary() > 0);
			toggle.addEventListener('click', () => setExpanded(toggle.getAttribute('aria-expanded') !== 'true'));
			compactViewport.addEventListener('change', (event) => setExpanded(!event.matches));
			content.addEventListener('change', updateFilterSummary);
		});
	}

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (!reduceMotion && 'IntersectionObserver' in window) {
		document.documentElement.classList.add('uabb-motion-ready');
		const revealItems = document.querySelectorAll('.uabb-department-card,.uabb-world-panel,.uabb-category-card,.uabb-catalog-filters,.uabb-content-wrap .products li.product,.uabb-featured-section .products li.product,.uabb-shop-gate');
		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				entry.target.classList.add('is-visible');
				observer.unobserve(entry.target);
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -28px 0px' });
		revealItems.forEach((item, index) => {
			item.classList.add('uabb-motion-item');
			item.style.setProperty('--uabb-reveal-delay', `${Math.min(index % 5, 4) * 38}ms`);
			observer.observe(item);
		});
	}
})();
