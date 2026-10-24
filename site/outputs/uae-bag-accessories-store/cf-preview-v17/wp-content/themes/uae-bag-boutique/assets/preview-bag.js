(() => {
	const key = 'almasar-preview-bag-v1';
	const isArabic = document.documentElement.lang.toLowerCase().startsWith('ar');
	const catalog = {
		'sample-bag': {
			title: isArabic ? 'حقيبة حمل بتصميم منحوت' : 'The Sculpted Carry Tote',
			department: isArabic ? 'الحقائب' : 'Bags',
			image: '/wp-content/themes/uae-bag-boutique/assets/images/category-handbags.jpg',
			alt: isArabic ? 'صورة توضيحية لحقيبة بلون محايد دافئ' : 'Sample concept handbag in a warm neutral tone',
			sequence: '01',
		},
		'sample-gadget': {
			title: isArabic ? 'طقم الطقوس اليومية' : 'The Daily Ritual Set',
			department: isArabic ? 'الأجهزة والمنزل' : 'Gadgets & home',
			image: '/wp-content/themes/uae-bag-boutique/assets/images/category-kitchen-ritual.jpg',
			alt: isArabic ? 'صورة توضيحية لأدوات القهوة والشرب في المطبخ' : 'Sample drinkware and coffee accessories arranged in a kitchen',
			sequence: '02',
		},
	};
	const copy = isArabic ? {
		added: 'أُضيف العنصر التجريبي إلى السلة.',
		remove: 'إزالة',
		minus: 'تقليل الكمية',
		plus: 'زيادة الكمية',
		quantity: 'الكمية',
		price: 'السعر سيُضاف مع تفاصيل المنتج الحقيقية.',
		count: (n) => `${new Intl.NumberFormat('ar-AE').format(n)} عنصر في السلة`,
		empty: 'سلتك فارغة حالياً.',
		preview: 'عنصر تجريبي · لا يوجد سعر أو دفع في هذه النسخة',
	} : {
		added: 'Sample listing added to your bag.',
		remove: 'Remove',
		minus: 'Decrease quantity',
		plus: 'Increase quantity',
		quantity: 'Quantity',
		price: 'Price will be added with the real product details.',
		count: (n) => `${n} ${n === 1 ? 'item' : 'items'} in your bag`,
		empty: 'Your bag is empty for now.',
		preview: 'Sample listing · no price or payment in this preview',
	};
	const read = () => {
		try {
			const raw = JSON.parse(localStorage.getItem(key) || '{}');
			return Object.fromEntries(Object.entries(raw).filter(([id, count]) => catalog[id] && Number.isInteger(count) && count > 0).map(([id, count]) => [id, Math.min(count, 99)]));
		} catch { return {}; }
	};
	const write = (items) => {
		try { localStorage.setItem(key, JSON.stringify(items)); } catch { /* Keep the page usable if storage is unavailable. */ }
	};
	const sum = (items) => Object.values(items).reduce((total, quantity) => total + quantity, 0);
	const toast = document.querySelector('[data-preview-bag-status]') || (() => {
		const node = document.createElement('p');
		node.className = 'uabb-preview-bag-status';
		node.dataset.previewBagStatus = '';
		node.setAttribute('role', 'status');
		node.setAttribute('aria-live', 'polite');
		document.body.append(node);
		return node;
	})();
	const esc = (value) => String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character]);
	const updateHeader = (items) => {
		const count = sum(items);
		const badge = document.querySelector('.uabb-cart b');
		if (badge) badge.textContent = new Intl.NumberFormat(isArabic ? 'ar-AE' : 'en-AE').format(count);
	};
	const renderCart = () => {
		const mount = document.querySelector('[data-preview-cart-items]');
		if (!mount) return;
		const items = read();
		const total = sum(items);
		const summary = document.querySelector('[data-preview-cart-summary]');
		if (summary) summary.textContent = total ? copy.count(total) : '';
		if (!total) {
			mount.innerHTML = `<p class="uabb-preview-cart-empty">${copy.empty}</p><nav class="uabb-preview-cart-browse"><a href="${isArabic ? '/ar' : ''}/product-category/bags/">${isArabic ? 'تصفّح الحقائب' : 'Explore bags'}</a><a href="${isArabic ? '/ar' : ''}/product-category/everyday-objects/">${isArabic ? 'تصفّح الأجهزة' : 'Explore gadgets'}</a></nav>`;
			return;
		}
		mount.innerHTML = `<ul class="uabb-preview-cart-list">${Object.entries(items).map(([id, quantity]) => {
			const product = catalog[id];
			return `<li class="uabb-preview-cart-row" data-preview-row="${id}"><img src="${product.image}" alt="${esc(product.alt)}" loading="lazy"><div class="uabb-preview-cart-product"><p class="uabb-demo-product-brand">ALMASAR / ${product.sequence}</p><h2>${esc(product.title)}</h2><p>${esc(product.department)} · ${copy.preview}</p><p class="uabb-preview-cart-price">${copy.price}</p><div class="uabb-preview-quantity"><span>${copy.quantity}</span><button type="button" data-preview-qty="-1" data-preview-id="${id}" aria-label="${copy.minus}">−</button><output>${new Intl.NumberFormat(isArabic ? 'ar-AE' : 'en-AE').format(quantity)}</output><button type="button" data-preview-qty="1" data-preview-id="${id}" aria-label="${copy.plus}">+</button><button class="uabb-preview-remove" type="button" data-preview-remove="${id}">${copy.remove}</button></div></div></li>`;
		}).join('')}</ul>`;
	};
	const refresh = () => { const items = read(); updateHeader(items); renderCart(); };
	document.addEventListener('click', (event) => {
		const add = event.target.closest('[data-preview-add]');
		if (add && catalog[add.dataset.previewAdd]) {
			const items = read();
			items[add.dataset.previewAdd] = Math.min((items[add.dataset.previewAdd] || 0) + 1, 99);
			write(items); refresh(); toast.textContent = copy.added; return;
		}
		const change = event.target.closest('[data-preview-qty]');
		const remove = event.target.closest('[data-preview-remove]');
		const clear = event.target.closest('[data-preview-clear]');
		if (!change && !remove && !clear) return;
		const items = read();
		if (clear) write({});
		else if (remove) { delete items[remove.dataset.previewRemove]; write(items); }
		else {
			const id = change.dataset.previewId;
			items[id] = Math.max(0, Math.min(99, (items[id] || 0) + Number(change.dataset.previewQty)));
			if (!items[id]) delete items[id];
			write(items);
		}
		refresh();
	});
	refresh();
})();
