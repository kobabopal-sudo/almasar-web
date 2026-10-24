(() => {
	document.documentElement.classList.add('uabb-js-ready');
	const path = window.location.pathname;
	const isArabic = /^\/ar(?:\/|$)/.test(path);
	const localeBase = isArabic ? '/ar' : '';
	const bagsUrl = `${localeBase}/product-category/bags/`;
	const objectsUrl = `${localeBase}/product-category/everyday-objects/`;
	const newGadgetsUrl = `${objectsUrl}?orderby=date`;
	const t = (english, arabic) => isArabic ? arabic : english;
	const body = document.body;
	const departmentQuery = new URLSearchParams(location.search).get('uabs_department');
	const isBagPage = /\/product-category\/bags(?:\/|$)/.test(path) || departmentQuery === 'bags';
	const isObjectPage = /\/product-category\/everyday-objects(?:\/|$)/.test(path) || departmentQuery === 'gadgets';

	if (path.includes('/atelier-edit')) {
		window.location.replace(bagsUrl);
		return;
	}
	if (isArabic) body.classList.add('uabb-lang-ar');
	if (isObjectPage) body.classList.add('uabb-department-objects');
	if (isBagPage) body.classList.add('uabb-department-bags');

	const bagCategories = [
		['shoulder-bags', 'Shoulder bags', 'حقائب كتف'],
		['crossbody-bags', 'Crossbody bags', 'حقائب كروس'],
		['tote-bags', 'Tote bags', 'حقائب توت'],
		['mini-bags', 'Mini bags', 'حقائب صغيرة'],
		['clutches', 'Evening & clutches', 'حقائب سهرة وكلتش'],
		['small-leather-goods', 'Small leather goods', 'منتجات جلدية صغيرة'],
	];
	const objectCategories = [
		['speakers-and-audio', 'Speakers & audio', 'السماعات والصوتيات'],
		['bottles-and-hydration', 'Drinkware', 'الأكواب والقوارير'],
		['gaming', 'Gaming', 'الألعاب'],
		['kitchen-and-coffee', 'Kitchen & coffee', 'المطبخ والقهوة'],
		['lighting', 'Lighting', 'الإضاءة'],
		['personal-care', 'Personal care', 'العناية الشخصية'],
		['projectors', 'Projectors', 'أجهزة العرض'],
		['home-fragrance', 'Home fragrance', 'العطور المنزلية'],
		['air-care', 'Air care', 'تنقية الهواء'],
		['outdoor-travel', 'Outdoor & travel', 'الرحلات والسفر'],
	];
	const esc = (value) => String(value).replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
	const rootLink = (root, slug = '') => `${root}${slug ? `?uabs_category=${encodeURIComponent(slug)}` : ''}`;
	const sideLinks = (items, root) => items.map(([slug, english, arabic]) => `<a href="${rootLink(root, slug)}">${esc(t(english, arabic))}<span aria-hidden="true">↗</span></a>`).join('');
	const imageRoot = '/wp-content/themes/uae-bag-boutique-preview/assets/images/';
	const nav = document.querySelector('.uabb-nav');
	if (nav) {
		nav.setAttribute('aria-label', t('Explore collections', 'استكشف التشكيلات'));
		nav.innerHTML = `
			<details class="uabb-nav-mega uabb-nav-mega-bags"><summary>${t('Bags','الحقائب')}<span aria-hidden="true">⌄</span></summary>
			<div class="uabb-mega-panel"><div class="uabb-mega-side"><p class="uabb-eyebrow">${t('The Carry Archive','أرشيف الحقائب')}</p>${sideLinks(bagCategories,bagsUrl)}<a class="uabb-mega-all" href="${bagsUrl}">${t('Explore all bags','استكشف كل الحقائب')}<span aria-hidden="true">↗</span></a></div>
			<div class="uabb-mega-tiles uabb-mega-tiles-bags"><a href="${bagsUrl}"><img src="${imageRoot}category-bags-editorial.jpg" alt=""><span>${t('The Carry Archive','أرشيف الحقائب')}</span></a><a href="${bagsUrl}"><img src="${imageRoot}category-leather-goods.jpg" alt=""><span>${t('Form & material','الشكل والخامة')}</span></a><a class="uabb-mega-note" href="${bagsUrl}"><span>ALMASAR / 01</span><strong>${t('A quieter kind of statement.','حضور هادئ بتفاصيل واثقة.')}</strong><small>${t('Shapes made for the everyday.','تصاميم ترافق تفاصيل اليوم.')}</small></a></div></div></details>
			<details class="uabb-nav-mega uabb-nav-mega-objects"><summary>${t('Gadgets & home','الأجهزة والمنزل')}<span aria-hidden="true">⌄</span></summary>
			<div class="uabb-mega-panel"><div class="uabb-mega-side"><p class="uabb-eyebrow">${t('The Gadget Studio','استوديو الأجهزة')}</p>${sideLinks(objectCategories,objectsUrl)}<a class="uabb-mega-all" href="${objectsUrl}">${t('Explore all gadgets','استكشف كل الأجهزة')}<span aria-hidden="true">↗</span></a></div>
			<div class="uabb-mega-tiles uabb-mega-tiles-objects"><a href="${objectsUrl}"><img src="${imageRoot}category-gadgets-studio.jpg" alt=""><span>${t('The Gadget Studio','استوديو الأجهزة')}</span></a><a href="${objectsUrl}"><img src="${imageRoot}category-kitchen-ritual.jpg" alt=""><span>${t('Kitchen & coffee','المطبخ والقهوة')}</span></a><a class="uabb-mega-note" href="${objectsUrl}"><span>ALMASAR / 02</span><strong>${t('Useful, with a point of view.','منفعة بتصميم له حضوره.')}</strong><small>${t('Sound · ritual · home · travel','صوت · طقوس · منزل · سفر')}</small></a></div></div></details>
			<a class="uabb-nav-story" href="${bagsUrl}">${t('The Carry Archive','أرشيف الحقائب')}</a><a class="uabb-nav-story uabb-nav-story-new-gadgets" href="${newGadgetsUrl}"${isObjectPage ? ' aria-current="page"' : ''}>${t('New Gadgets','أحدث الأجهزة')}</a>`;
		const megaMenus = nav.querySelectorAll('.uabb-nav-mega');
		const closeOtherMenus = (active) => megaMenus.forEach((menu) => { if (menu !== active) menu.open = false; });
		megaMenus.forEach((menu) => {
			let closeTimer;
			let openedFromPointer = false;
			menu.addEventListener('toggle', () => { if (menu.open) closeOtherMenus(menu); });
			menu.addEventListener('pointerenter', () => {
				if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
					window.clearTimeout(closeTimer);
					closeOtherMenus(menu);
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
			menu.addEventListener('focusout', (event) => { if (!menu.contains(event.relatedTarget)) menu.open = false; });
		});
		document.addEventListener('keydown', (event) => { if (event.key === 'Escape') megaMenus.forEach((menu) => { menu.open = false; }); });
	}

	const header = document.querySelector('.uabb-header');
	const actions = header?.querySelector('.uabb-header-actions');
	if (actions && !actions.querySelector('.uabb-account-link')) {
		const account = document.createElement('a');
		account.className = 'uabb-account-link';
		account.href = `${localeBase}/my-account/`;
		account.textContent = t('Account', 'حسابي');
		actions.prepend(account);
	}
	if (header && !header.querySelector('.uabb-collection-switch')) {
		const bar = document.createElement('nav');
		bar.className = 'uabb-collection-switch';
		bar.setAttribute('aria-label', t('Choose a collection','اختر التشكيلة'));
		bar.innerHTML = `<a class="uabb-collection-switch-bags${isBagPage ? ' is-active' : ''}" href="${bagsUrl}"${isBagPage ? ' aria-current="page"' : ''}><span>01</span><strong>${t('The Carry Archive','أرشيف الحقائب')}</strong><small>${t('Bags','الحقائب')}</small><b aria-hidden="true">↗</b></a><span class="uabb-collection-switch-mark"><img src="${imageRoot}almasar-logo.png" alt="${t('Almasar Al Raed','المسار الرائد')}"></span><a class="uabb-collection-switch-gadgets${isObjectPage ? ' is-active' : ''}" href="${objectsUrl}"${isObjectPage ? ' aria-current="page"' : ''}><span>02</span><strong>${t('The Gadget Studio','استوديو الأجهزة')}</strong><small>${t('Gadgets & home','الأجهزة والمنزل')}</small><b aria-hidden="true">↗</b></a>`;
		const mainNav = header.querySelector('.uabb-nav');
		if (mainNav) mainNav.insertAdjacentElement('afterend', bar);
	}

	if (/^\/(?:ar\/)?$/.test(path)) {
		document.querySelectorAll('.uabb-worlds-section,.uabb-home-featured').forEach((section) => section.remove());
		const cards = document.querySelector('.uabb-department-grid');
		const bagMeta = document.querySelector('.uabb-department-card-bags .uabb-department-meta span:last-child');
		if (bagMeta && /ATELIER|أتيليه|الأتيليه/i.test(bagMeta.textContent)) bagMeta.textContent = t('FORM & MOVEMENT','الشكل والحركة');
		const gadgetTitle = document.querySelector('.uabb-department-card-objects .uabb-department-content strong');
		if (gadgetTitle) gadgetTitle.textContent = t('Gadgets','الأجهزة');
		const bagImage = document.querySelector('.uabb-department-card-bags > img');
		if (bagImage) bagImage.alt = t('A sculptural bag in warm editorial light','حقيبة بتصميم أنيق وإضاءة دافئة');
		if (bagImage) bagImage.src = `${imageRoot}category-bags-editorial.jpg`;
		const gadgetImage = document.querySelector('.uabb-department-card-objects > img');
		if (gadgetImage) { gadgetImage.src = `${imageRoot}category-gadgets-studio.jpg`; gadgetImage.alt = t('Modern gadgets for sound, home and everyday rituals','أجهزة عصرية للصوت والمنزل وتفاصيل الحياة اليومية'); }
		const gadgetMeta = document.querySelector('.uabb-department-card-objects .uabb-department-meta span');
		if (gadgetMeta) gadgetMeta.textContent = `02 / ${t('THE GADGET STUDIO','استوديو الأجهزة')}`;
		const gadgetMetaRight = document.querySelector('.uabb-department-card-objects .uabb-department-meta span:last-child');
		if (gadgetMetaRight) gadgetMetaRight.textContent = t('GADGETS & HOME','الأجهزة والمنزل');
		const gadgetDescription = document.querySelector('.uabb-department-card-objects .uabb-department-description');
		if (gadgetDescription) gadgetDescription.textContent = t('Useful discoveries for sound, home, personal care, travel and the outdoors.','اكتشافات عملية للصوت والمنزل والعناية الشخصية والسفر والهواء الطلق.');
		const gadgetCta = document.querySelector('.uabb-department-card-objects .uabb-department-cta');
		if (gadgetCta) gadgetCta.firstChild.textContent = t('Explore the gadget collection','اكتشف مجموعة الأجهزة');
		if (cards && !document.querySelector('.uabb-entry-note')) {
			const note = document.createElement('section');
			note.className = 'uabb-entry-note';
			note.innerHTML = `<p class="uabb-eyebrow">${t('A house of two distinct worlds','دار لعالمين مختلفين')}</p><h2>${t('Choose your point of view.','اختر عالمك.')}</h2><p>${t('The Carry Archive for considered bags. The Gadget Studio for thoughtful finds across home, sound and travel.','أرشيف الحقائب لتصاميم منتقاة، واستوديو الأجهزة لاكتشافات مدروسة للمنزل والصوت والسفر.')}</p>`;
			cards.insertAdjacentElement('afterend', note);
		}
	}

	const isBagRoot = /^\/(?:ar\/)?product-category\/bags\/?$/.test(path);
	const isGadgetRoot = /^\/(?:ar\/)?product-category\/everyday-objects\/?$/.test(path);
	if ((isBagRoot || isGadgetRoot) && document.querySelector('.uabb-entry-intro')) {
		const bags = isBagRoot;
		const intro = document.querySelector('.uabb-entry-intro');
		const hero = document.createElement('section');
		hero.className = `uabb-archive-hero ${bags ? 'uabb-archive-hero-bags' : 'uabb-archive-hero-objects'}`;
		const headline = bags ? t('The Carry Archive','أرشيف الحقائب') : t('Everyday Gadgets','الأجهزة اليومية');
		const eyebrow = bags ? t('01 · THE CARRY ARCHIVE','٠١ · أرشيف الحقائب') : t('02 · THE GADGET STUDIO','٠٢ · استوديو الأجهزة');
		const description = bags ? t('A study in form, material and the everyday ritual of carrying.','تأمل في الشكل والخامة وتفاصيل ما نحمله كل يوم.') : t('Thoughtful finds for sound, home, care, travel and everyday rituals.','اختيارات مدروسة للصوت والمنزل والعناية والسفر وتفاصيل الحياة اليومية.');
		const image = bags ? 'category-bags-editorial.jpg' : 'category-gadgets-studio.jpg';
		const imageAlt = bags ? t('A sculptural bag in warm editorial light','حقيبة بتصميم أنيق وإضاءة دافئة') : t('A refined selection of modern everyday gadgets','تشكيلة منتقاة من الأجهزة اليومية العصرية');
		hero.innerHTML = `<div class="uabb-archive-hero-copy"><p class="uabb-eyebrow">${eyebrow}</p><h1>${headline}</h1><p>${description}</p></div><img src="${imageRoot}${image}" alt="${imageAlt}" fetchpriority="high">`;
		intro.replaceWith(hero);
	}

	if (isBagPage || isObjectPage) {
		const isBags = isBagPage;
		const root = isBags ? bagsUrl : objectsUrl;
		const categories = isBags ? bagCategories : objectCategories;
		const archive = document.querySelector('.uabb-content-wrap') || document.querySelector('.uabb-main');
		if (archive) {
			if (!archive.querySelector('.uabb-archive-categories,.uabb-category-ideas')) {
				const selectedCategory = new URLSearchParams(location.search).get('uabs_category');
				const shortcuts = document.createElement('nav');
				shortcuts.className = 'uabb-category-ideas';
				shortcuts.setAttribute('aria-label', t('Browse categories','تصفح الفئات'));
				shortcuts.innerHTML = categories.map(([slug,en,ar]) => `<a href="${rootLink(root,slug)}"${selectedCategory === slug ? ' aria-current="page"' : ''}>${esc(t(en,ar))}<span aria-hidden="true"> ↗</span></a>`).join('');
				const heading = archive.querySelector('.uabb-archive-hero,h1,.woocommerce-products-header__title');
				if (heading) heading.insertAdjacentElement('afterend', shortcuts); else archive.prepend(shortcuts);
			}
			if (!archive.querySelector('.uabb-catalog-filters')) {
				const form = document.createElement('form');
				form.className = 'uabb-catalog-filters uabb-preview-filters';
				form.dataset.demoFilter = 'true';
				form.innerHTML = `<div class="uabb-filter-field"><label for="preview-category">${t('Category','الفئة')}</label><select id="preview-category" name="category"><option>${t('All categories','كل الفئات')}</option>${categories.map(([,en,ar])=>`<option>${esc(t(en,ar))}</option>`).join('')}</select></div><div class="uabb-filter-field"><label for="preview-color">${t('Color','اللون')}</label><select id="preview-color" name="color"><option>${t('All colors','كل الألوان')}</option>${[['Black','أسود'],['Brown','بني'],['Beige','بيج'],['Blue','أزرق'],['Cream','كريمي'],['White','أبيض']].map(([en,ar])=>`<option>${t(en,ar)}</option>`).join('')}</select></div>${isBags ? `<div class="uabb-filter-field"><label for="preview-size">${t('Size','المقاس')}</label><select id="preview-size"><option>${t('All sizes','كل المقاسات')}</option>${[['Mini','ميني'],['Small','صغير'],['Medium','متوسط'],['Large','كبير']].map(([en,ar])=>`<option>${t(en,ar)}</option>`).join('')}</select></div>` : ''}<div class="uabb-filter-field"><label for="preview-brand">${t('Brand','العلامة التجارية')}</label><select id="preview-brand"><option>${t('Brands appear with products','تظهر العلامات التجارية بعد إضافة المنتجات')}</option></select></div>${isBags ? '' : `<div class="uabb-filter-field"><label for="preview-min">${t('Price from · AED','السعر من · درهم')}</label><input id="preview-min" type="number" min="0" step="0.01"></div><div class="uabb-filter-field"><label for="preview-max">${t('Price to · AED','السعر إلى · درهم')}</label><input id="preview-max" type="number" min="0" step="0.01"></div>`}<div class="uabb-filter-actions"><button class="uabb-filter-submit" type="submit">${t('Apply','تطبيق')}</button><a class="uabb-filter-clear" href="${root}">${t('Clear','مسح')}</a></div>`;
				const categoryNav = archive.querySelector('.uabb-archive-categories,.uabb-category-ideas');
				if (categoryNav) categoryNav.insertAdjacentElement('afterend', form); else archive.prepend(form);
				const notice = document.createElement('p');
				notice.className = 'uabb-demo-filter-notice';
			notice.textContent = t('Filter choices will populate from your product list. This is a preview until the products are added.','ستظهر خيارات الفلترة بعد إضافة المنتجات. هذه معاينة إلى حين استكمال الكتالوج.');
				form.insertAdjacentElement('afterend', notice);
				form.addEventListener('submit', (event) => { event.preventDefault(); notice.textContent = t('Your product-specific filters are prepared and will become active with the catalog.','تم تجهيز الفلاتر لتعمل عند إضافة منتجات الكتالوج.'); });
			}
			archive.querySelectorAll('.uabb-featured-section .uabb-category-links').forEach((node) => node.remove());
		}
	}

	if (/^\/(?:ar\/)?product-category\/everyday-objects\/?$/.test(path)) {
		const title = t('Everyday Gadgets · Almasar Al Raed','الأجهزة اليومية | المسار الرائد');
		document.title = title;
		const heading = document.querySelector('.uabb-entry-intro h1,.uabb-archive-hero-copy h1,.woocommerce-products-header__title');
		if (heading) heading.textContent = t('Everyday Gadgets','الأجهزة اليومية');
		document.querySelectorAll('meta[property="og:title"],meta[name="twitter:title"]').forEach((metaTitle) => metaTitle.setAttribute('content', title));
	}

	if (/\/(?:ar\/)?shop\/?$/.test(path) && !new URLSearchParams(location.search).has('uabs_department')) {
		const content = document.querySelector('.uabb-content-wrap');
		if (content) {
			content.querySelectorAll('.uabb-catalog-filters,.woocommerce-result-count,.woocommerce-ordering,.products,.woocommerce-pagination').forEach((node) => { node.hidden = true; node.classList.add('uabb-preview-hidden'); });
			const gate = document.createElement('section');
			gate.className = 'uabb-shop-gate';
			gate.innerHTML = `<p class="uabb-eyebrow">${t('Two distinct collections','تشكيلتان مستقلتان')}</p><h2>${t('Choose your world to browse.','اختر عالمك للتسوق.')}</h2><a href="${bagsUrl}">${t('The Carry Archive · Bags','أرشيف الحقائب · حقائب')}<span aria-hidden="true">↗</span></a><a href="${objectsUrl}">${t('The Gadget Studio · Gadgets','استوديو الأجهزة · أجهزة')}<span aria-hidden="true">↗</span></a>`;
			content.prepend(gate);
		}
	}

	const form = document.querySelector('.uabb-search');
	if (form && !form.querySelector('[name="uabs_department"]')) {
		const choice = document.createElement('select');
		choice.className = 'uabb-search-department';
		choice.name = 'uabs_department';
		choice.setAttribute('aria-label', t('Choose a collection','اختر التشكيلة'));
		choice.innerHTML = `<option value="bags">${t('Bags','الحقائب')}</option><option value="gadgets">${t('Gadgets','الأجهزة')}</option>`;
		form.prepend(choice);
	}
	const searchDepartment = form?.querySelector('[name="uabs_department"]');
	if (searchDepartment) {
		searchDepartment.value = departmentQuery === 'gadgets' || (!departmentQuery && isObjectPage) ? 'gadgets' : 'bags';
	}
	if (!path.includes('/order-progress-preview')) {
		const footerLinks = document.querySelector('.uabb-footer-links');
		if (footerLinks) {
			footerLinks.querySelectorAll('a[href$="/shop/"]').forEach((link) => {
				link.href = `${localeBase}/`;
				link.textContent = t('Choose a collection','اختر التشكيلة');
			});
		}
		if (footerLinks && !footerLinks.querySelector('[data-order-preview-link]')) {
			const previewLink = document.createElement('a');
			previewLink.href = `${localeBase}/order-progress-preview/`;
			previewLink.dataset.orderPreviewLink = 'true';
			previewLink.textContent = t('Order journey preview','معاينة رحلة الطلب');
			footerLinks.append(previewLink);
		}
	}

	document.querySelectorAll('a[href*="atelier-edit"]').forEach((link) => link.remove());

	const filterForms = document.querySelectorAll('.uabb-catalog-filters');
	if (filterForms.length) {
		const compactViewport = window.matchMedia('(max-width: 760px)');
		filterForms.forEach((filterForm, index) => {
			if (filterForm.dataset.filterEnhanced === 'true') return;
			filterForm.dataset.filterEnhanced = 'true';
			let panel = filterForm.querySelector('.uabb-filter-content');
			let toggle = filterForm.querySelector('.uabb-filter-toggle');
			if (!panel) {
				const filterFields = document.createElement('div');
				filterFields.className = 'uabb-filter-fields';
				filterForm.querySelectorAll(':scope > .uabb-filter-field, :scope > .uabb-filter-actions').forEach((field) => filterFields.append(field));
				panel = document.createElement('div');
				panel.className = 'uabb-filter-content';
				panel.id = `uabs-catalog-filter-fields-${index + 1}`;
				panel.append(filterFields);
				toggle = document.createElement('button');
				toggle.className = 'uabb-filter-toggle';
				toggle.type = 'button';
				toggle.setAttribute('aria-controls', panel.id);
				toggle.innerHTML = `<span>${t('Refine your selection','تصفّح حسب تفضيلاتك')}</span><span class="uabb-filter-toggle-icon" aria-hidden="true">+</span>`;
				filterForm.prepend(toggle);
				filterForm.append(panel);
			} else if (!toggle) {
				toggle = document.createElement('button');
				toggle.className = 'uabb-filter-toggle';
				toggle.type = 'button';
				toggle.setAttribute('aria-controls', panel.id);
				toggle.innerHTML = `<span>${t('Refine your selection','تصفّح حسب تفضيلاتك')}</span><span class="uabb-filter-toggle-icon" aria-hidden="true">+</span>`;
				filterForm.prepend(toggle);
			}
			const updateFilterSummary = () => {
				const activeCount = Array.from(panel.querySelectorAll('select')).filter((select) => select.selectedIndex > 0 && select.value).length
					+ Array.from(panel.querySelectorAll('input:not([type="hidden"])')).filter((input) => input.value.trim() !== '').length;
				const label = toggle.querySelector('span:not(.uabb-filter-toggle-icon)');
				if (label) label.textContent = activeCount
					? (isArabic ? `خيارات مفعّلة: ${new Intl.NumberFormat('ar-AE').format(activeCount)}` : `Refine your selection · ${activeCount} active`)
					: t('Refine your selection','تصفّح حسب تفضيلاتك');
				return activeCount;
			};
			const setExpanded = (expanded) => {
				toggle.setAttribute('aria-expanded', String(expanded));
				panel.setAttribute('aria-hidden', String(!expanded));
				panel.inert = !expanded;
				filterForm.classList.toggle('uabb-filters-expanded', expanded);
				updateFilterSummary();
			};
			setExpanded(!compactViewport.matches || updateFilterSummary() > 0);
			toggle.addEventListener('click', () => setExpanded(toggle.getAttribute('aria-expanded') !== 'true'));
			compactViewport.addEventListener('change', (event) => setExpanded(!event.matches));
			panel.addEventListener('change', updateFilterSummary);
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
