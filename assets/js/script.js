$(function () {
	/*
	let lastScroll = 0;
	const header = document.querySelector('.header');
	window.addEventListener('scroll', () => {
		const currentScroll = window.scrollY;

		if (currentScroll > lastScroll && currentScroll > 60) {
			header.style.top = '-60px';
		} else {
			header.style.top = '0';
		}
		lastScroll = currentScroll;
	});
	*/




	const submenuToggles = document.querySelectorAll('.submenu-toggle');

	submenuToggles.forEach(function (toggle) {
		toggle.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			const submenu = this.closest('.dropdown-submenu');

			// Close other submenus
			const otherSubmenus = document.querySelectorAll('.dropdown-submenu.active');
			otherSubmenus.forEach(function (other) {
				if (other !== submenu) {
					other.classList.remove('active');
				}
			});

			// Toggle current submenu
			submenu.classList.toggle('active');
		});
	});

	// Close submenus when clicking outside
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.dropdown-submenu')) {
			const activeSubmenus = document.querySelectorAll('.dropdown-submenu.active');
			activeSubmenus.forEach(function (submenu) {
				submenu.classList.remove('active');
			});
		}

	});


	window.getUserCountry = async (cacheDays = 7) => {
		const cacheKey = "userCountryCode";
		const cached = localStorage.getItem(cacheKey);

		if (cached) {
			try {
				const { code, time } = JSON.parse(cached);
				if (Date.now() - time < cacheDays * 24 * 60 * 60 * 1000) return code;
			} catch { }
		}

		try {
			const res = await fetch("https://ipwho.is/");
			const data = await res.json();
			const code = data.country_code?.toLowerCase() || "tr";
			localStorage.setItem(cacheKey, JSON.stringify({ code, time: Date.now() }));
			return code;
		} catch {
			return "tr";
		}
	};

	//document.querySelector("[data-country-bilgi-getir]") && getUserCountry();

	const intl_tel_inputs = document.querySelectorAll("[data-intl-tel]");
	if (intl_tel_inputs.length > 0 && typeof window.intlTelInput === "function") {
		intl_tel_inputs.forEach(input => {
			const pcAttr = input.getAttribute("data-pc");
			const preferredCountries = pcAttr
				? pcAttr.split(",").map(c => c.trim().toLowerCase())
				: ["tr"];

			getUserCountry(7).then(countryCode => {
				window.intlTelInput(input, {
					initialCountry: countryCode,
					preferredCountries: preferredCountries,
					separateDialCode: true,
					nationalMode: false,
					utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/utils.js"
				});
			});
		});
	} else {
		console.log("!intlTelInput");
	}

	// data-style attribute'larını style'a çevir
	document.querySelectorAll('[data-style]').forEach(function (el) {
		const dataStyle = el.getAttribute('data-style');
		if (dataStyle) {
			el.setAttribute('style', dataStyle);
			el.removeAttribute('data-style');
		}
	});

	// Firma Uyarı Badge & Panel
	const firmaUyariBadge = document.querySelector('[data-firma-uyari-toggle]');
	const firmaUyariPanel = document.querySelector('.firma_uyari_panel');
	const firmaUyariKapat = document.querySelector('[data-firma-uyari-kapat]');

	if (firmaUyariBadge && firmaUyariPanel) {
		// Overlay oluştur
		let overlay = document.querySelector('.firma_uyari_overlay');
		if (!overlay) {
			overlay = document.createElement('div');
			overlay.className = 'firma_uyari_overlay';
			document.body.appendChild(overlay);
		}

		// Badge tıklama
		firmaUyariBadge.addEventListener('click', function (e) {
			e.stopPropagation();
			firmaUyariPanel.classList.toggle('acik');
			overlay.classList.toggle('acik');
		});

		// Kapat butonu
		if (firmaUyariKapat) {
			firmaUyariKapat.addEventListener('click', function () {
				firmaUyariPanel.classList.remove('acik');
				overlay.classList.remove('acik');
			});
		}

		// Overlay tıklama
		overlay.addEventListener('click', function () {
			firmaUyariPanel.classList.remove('acik');
			overlay.classList.remove('acik');
		});

		// Panel dışı tıklama
		document.addEventListener('click', function (e) {
			if (!firmaUyariPanel.contains(e.target) && !firmaUyariBadge.contains(e.target)) {
				firmaUyariPanel.classList.remove('acik');
				overlay.classList.remove('acik');
			}
		});

		// ESC tuşu
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				if (firmaUyariPanel.classList.contains('acik')) {
					firmaUyariPanel.classList.remove('acik');
					overlay.classList.remove('acik');
				}

				$('.dropdown-acik').each(function () {
					$(this).removeClass('dropdown-acik');
				});
			}
		});
	}

	//slimselect için özelleştirme
	setTimeout(function () {
		document.querySelectorAll('.ss-option').forEach((el, index) => {
			const text = el.textContent.trim().toLowerCase();
			if (index === 0 && text === "") {
				el.classList.add('is-empty');
			}
			if (
				text.includes('havaliman') ||
				text.includes('havaalan') ||
				text.includes('airport')
			) {
				el.classList.add('has-airport');
			}
		});
	}, 100);



	document.querySelectorAll('.faq-question').forEach(button => {
		button.addEventListener('click', () => {
			const item = button.parentElement;

			document.querySelectorAll('.faq-item.active').forEach(activeItem => {
				if (activeItem !== item) {
					activeItem.classList.remove('active');
				}
			});

			item.classList.toggle('active');
		});
	});

});