(() => {
	const isArabic = /^\/ar(?:\/|$)/.test(location.pathname);
	const page = document.body;
	page.lang = isArabic ? 'ar' : 'en';
	page.dir = isArabic ? 'rtl' : 'ltr';
	document.documentElement.lang = page.lang;
	document.documentElement.dir = page.dir;
	document.querySelectorAll('[data-en][data-ar]').forEach((node) => { node.textContent = node.dataset[isArabic ? 'ar' : 'en']; });
	const homeLink = document.querySelector('[data-home-link]');
	if (homeLink) homeLink.href = isArabic ? '/ar/' : '/';
	const languageLink = document.querySelector('[data-language-link]');
	if (languageLink) {
		languageLink.href = isArabic ? '/order-progress-preview/' : '/ar/order-progress-preview/';
		languageLink.textContent = isArabic ? 'English' : 'العربية';
		languageLink.lang = isArabic ? 'en' : 'ar';
		languageLink.dir = isArabic ? 'ltr' : 'rtl';
	}
	const root = document.querySelector('.order-printer');
	const status = document.querySelector('[data-order-status]');
	const receipt = document.querySelector('[data-receipt-state]');
	const replay = document.querySelector('[data-replay]');
	let timers = [];
	const setStage = (stage) => {
		root.dataset.stage = stage;
		const labels = {
			processing: ['Processing your order', 'جارٍ تجهيز طلبك'],
			printing: ['Preparing your receipt', 'جارٍ إعداد إيصالك'],
			complete: ['Order complete', 'اكتمل الطلب'],
		};
		const receiptLabels = { processing: ['PROCESSING', 'قيد التجهيز'], printing: ['PREPARING', 'قيد الإعداد'], complete: ['COMPLETE', 'مكتمل'] };
		status.textContent = labels[stage][isArabic ? 1 : 0];
		receipt.textContent = receiptLabels[stage][isArabic ? 1 : 0];
	};
	const play = () => {
		timers.forEach(clearTimeout);
		timers = [];
		setStage('processing');
		timers.push(setTimeout(() => setStage('printing'), 1550));
		timers.push(setTimeout(() => setStage('complete'), 3850));
	};
	replay.addEventListener('click', play);
	play();
})();
