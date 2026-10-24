(() => {
	const menus = document.querySelectorAll('.uabb-nav-mega');
	if (!menus.length) return;
	menus.forEach((menu) => {
		let closeTimer;
		menu.addEventListener('pointerenter', () => {
			if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
				window.clearTimeout(closeTimer);
				menu.open = true;
			}
		});
		menu.addEventListener('pointerleave', () => {
			if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
				closeTimer = window.setTimeout(() => { menu.open = false; }, 140);
			}
		});
		menu.addEventListener('focusout', (event) => {
			if (!menu.contains(event.relatedTarget)) menu.open = false;
		});
	});
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') menus.forEach((menu) => { menu.open = false; });
	});
})();
