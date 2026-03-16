$.ajaxSetup({ url: 'api/', type: 'POST' });

var consolelog = true;

if (!consolelog) {
	console.log('!log jsi.js');
	console.log = function () { };
}

var typewriterTimeout = null;
var currentMessageId = null;

function typewriterEffect(element, text, speed) {
	speed = speed || 4;

	var messageId = new Date().getTime();
	currentMessageId = messageId;

	if (typewriterTimeout) {
		clearTimeout(typewriterTimeout);
		typewriterTimeout = null;
	}

	element.html('');
	var i = 0;

	function type() {
		if (currentMessageId === messageId && i < text.length) {
			try {
				var currentText = element.html() + text.charAt(i);
				element.html(currentText);
				i++;
				typewriterTimeout = setTimeout(type, speed);
			} catch (e) {
				console.error('Typewriter error:', e);
			}
		}
	}

	type();
}

document.addEventListener('DOMContentLoaded', function () {
	const toggleButtons = document.querySelectorAll('.toggle-password');

	toggleButtons.forEach(button => {
		button.addEventListener('click', function () {
			const input = this.closest('.control').querySelector('input[type="password"], input[type="text"]');
			const icon = this.querySelector('i');

			if (input) {
				if (input.type === 'password') {
					input.type = 'text';
					icon.classList.remove('fa-eye');
					icon.classList.add('fa-eye-slash');
				} else {
					input.type = 'password';
					icon.classList.remove('fa-eye-slash');
					icon.classList.add('fa-eye');
				}
			}
		});
	});
});

document.addEventListener('DOMContentLoaded', function () {
	const forms = document.querySelectorAll('form[data-onsubmit-confirm]');

	forms.forEach(function (form) {
		form.addEventListener('submit', function (e) {
			const confirmMessage = this.getAttribute('data-onsubmit-confirm');
			if (confirmMessage && !confirm(confirmMessage)) {
				e.preventDefault();
				return false;
			}
		});
	});
});


function yazdir(selector) {
	const element = document.querySelector(selector);
	if (!element) {
		console.warn('Yazdırılacak element bulunamadı:', selector);
		return false;
	}

	const printContent = element.innerHTML;
	const printWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');

	const htmlContent = `
		<!DOCTYPE html>
		<html>
		<head>
			<title>Yazdır</title>
			<style>
				body { 
					font-family: Arial, sans-serif; 
					line-height: 1.6; 
					color: #000; 
					padding: 20px; 
				}
				@media print {
					body { margin: 0; }
					* { color: #000 !important; }
				}
			</style>
		</head>
		<body>
			${printContent}
		</body>
		</html>
	`;

	printWindow.document.open();
	printWindow.document.write(htmlContent);
	printWindow.document.close();

	printWindow.focus();
	printWindow.onload = function () {
		printWindow.print();
	};

	return true;
}

function animate_opacity_01_1(element) {
	element
		.animate({ opacity: 0.1 }, 10)
		.animate({ opacity: 1 }, 400);
}

function validateEmail(email) {
	const regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
	return regex.test(email);
}

function id_belirle(secici, id_onek = '', tum_secicilere_uygula = false) {
	id_onek = String(id_onek || '').trim();

	const elements = (typeof secici === 'string')
		? document.querySelectorAll(secici)
		: (secici?.nodeType === 1 ? [secici] : []);

	if (!elements.length) return tum_secicilere_uygula ? [] : null;

	const generateId = () => id_onek + Math.random().toString().slice(2, 10);

	const setId = el => {
		if (!el.id) {
			el.id = generateId();
		}
		return el.id;
	};

	return tum_secicilere_uygula
		? Array.from(elements, setId)
		: setId(elements[0]);
}

function clipboard(element, _alert = false) {
	element = element.trim();

	if (navigator.clipboard.writeText) {
		navigator.clipboard.writeText(element).then(() => {
			console.log("Kopyalandı:" + element);
			if (_alert == true) {
				alert('Panoya kopyalandı');
			}
		}).catch((error) => {
			console.error("!" + error);
		});
	} else {
		var tempElement = document.createElement("textarea");
		tempElement.value = element;
		document.body.appendChild(tempElement);
		tempElement.select();
		document.execCommand("copy");
		document.body.removeChild(tempElement);
	}
}


function umesaj(umesaj, delay = 2000) {
	if (!$(".umesaj0").length) {
		$('body').prepend('<div class="umesaj0"></div>');
	}
	$('.umesaj0').html(umesaj).delay(delay).animate({ "opacity": "1" }, 800, function () {
		$('.umesaj0').fadeOut(1200, function () {
			$(this).remove();
		});
	});
}

function pretty_url(options = {}) {
	const {
		triggerParams = [],
		mode = 'any'
	} = options;

	const url = new URL(window.location.href);
	const params = Object.fromEntries(url.searchParams.entries());

	let shouldDecode = false;
	if (mode === 'all') {
		shouldDecode = triggerParams.every(param => param in params);
	} else {
		shouldDecode = triggerParams.some(param => param in params);
	}

	if (!shouldDecode) return;

	const decodedParams = [];
	for (const key in params) {
		decodedParams.push(key + '=' + decodeURIComponent(params[key]));
	}

	const newUrl = url.pathname + '?' + decodedParams.join('&');
	history.replaceState(null, '', newUrl);
}

//pretty_url({ triggerParams: ['b1','b2','t1','t2'], mode: 'all' });

// d parametresini sil
(function () {
	let currentUrl = new URL(window.location.href);
	let params = new URLSearchParams(currentUrl.search);

	if (params.has('d')) {
		params.delete('d');
		currentUrl.search = params.toString();
		window.history.replaceState({}, document.title, currentUrl.toString());
	}
})();


function formatHTML(html) {
	const tab = '    ';
	let result = '';
	let indent = 0;

	html.split(/>\s*</).forEach((element, index) => {
		if (element.match(/^\/\w/)) {
			indent--;
		}

		if (index > 0) {
			result += '\n' + tab.repeat(Math.max(0, indent));
		}

		let processedElement = element;

		// HTML yorumu kontrolü (<!-- ... -->)
		if (element.startsWith('!--')) {
			const commentMatch = element.match(/^!--(.*?)--$/s);
			if (commentMatch) {
				const commentContent = commentMatch[1].trim();

				// Yorum 160 karakterden uzunsa formatla
				if (commentContent.length > 160) {
					// Yorumu satırlara böl (80 karakter civarında)
					const words = commentContent.split(/\s+/);
					const lines = [];
					let currentLine = '';

					words.forEach(word => {
						if ((currentLine + ' ' + word).length > 80) {
							if (currentLine) lines.push(currentLine.trim());
							currentLine = word;
						} else {
							currentLine += (currentLine ? ' ' : '') + word;
						}
					});
					if (currentLine) lines.push(currentLine.trim());

					// Yorumu yeniden oluştur
					processedElement = '!--\n' + tab.repeat(indent + 1);
					processedElement += lines.join('\n' + tab.repeat(indent + 1));
					processedElement += '\n' + tab.repeat(indent) + '--';
				} else {
					processedElement = element;
				}
			}
		}
		// Sadece data- attribute'ları içeren ve 160+ karakter olan tag'leri formatla
		else if (element.length > 160 && element.includes('data-')) {
			const tagMatch = element.match(/^<?(\w+)(.*?)(\/?|)$/);
			if (tagMatch) {
				const tagName = tagMatch[1];
				const attrsString = tagMatch[2];
				const selfClosing = tagMatch[3];

				// Attribute'ları ayır
				const attrRegex = /\s+([\w-]+(?:="[^"]*"|='[^']*'|=[^\s>]*)?)/g;
				const attrs = [];
				let match;
				while ((match = attrRegex.exec(attrsString)) !== null) {
					attrs.push(match[1]);
				}

				// data- ve diğer attribute'ları ayır
				const dataAttrs = attrs.filter(attr => attr.trim().startsWith('data-'));
				const otherAttrs = attrs.filter(attr => !attr.trim().startsWith('data-'));

				if (dataAttrs.length > 0) {
					// Tag'i yeniden oluştur - data- olmayan attribute'lar aynı satırda
					processedElement = tagName + (otherAttrs.length > 0 ? ' ' + otherAttrs.join(' ') : '');

					// data- attribute'larını alt alta ekle
					dataAttrs.forEach((attr) => {
						processedElement += '\n' + tab.repeat(indent + 1) + attr.trim();
					});

					processedElement += selfClosing;
				}
			}
		}
		// Tag içeriğini kontrol et ve varsa alt satıra al
		else {
			// İçerik ve kapanış tag'i birlikte kontrol et
			const contentMatch = element.match(/^<?(\w+[^>]*?)>(.+?)<\/(\w+)$/s);
			if (contentMatch) {
				const tagPart = contentMatch[1];
				const content = contentMatch[2].trim();
				const closingTag = contentMatch[3];

				// İçerik 20 karakterden uzunsa formatla
				if (content && content.length > 20) {
					processedElement = tagPart + '>\n' + tab.repeat(indent + 1) + content + '\n' + tab.repeat(indent) + '</' + closingTag;
				} else {
					// 20 karakterden kısa ise olduğu gibi bırak
					processedElement = element;
				}
			} else {
				// Sadece açılış ve içerik varsa (kapanış ayrı)
				const openContentMatch = element.match(/^<?(\w+[^>]*?)>(.+)$/s);
				if (openContentMatch) {
					const tagPart = openContentMatch[1];
					const content = openContentMatch[2].trim();

					// İçerik 20 karakterden uzunsa formatla
					if (content && content.length > 20) {
						processedElement = tagPart + '>\n' + tab.repeat(indent + 1) + content;
					} else {
						processedElement = element;
					}
				}
			}
		}

		result += '<' + processedElement + '>';

		if (element.match(/^<?\w[^>]*[^\/]$/) && !element.startsWith("input") && !element.startsWith("br") && !element.startsWith("img")) {
			indent++;
		}
	});

	return result.substring(1, result.length - 1);
}


$(document).ready(function () {

	$('.uye_cikis').on('click', function (e) {
		e.preventDefault();

		$.ajax({
			data: { islem: 'uye_cikis' },
			cache: false,
			success: function (response) {
				if (response.return === 1) {
					window.location.href = window.location.pathname + window.location.search;
				} else {
					console.error("Çıkış işlemi başarısız:", response.mesaj);
				}
			},
			error: function (xhr, status, error) {
				console.error("Çıkış işlemi başarısız:", error);
			}
		});
	});

	$(".goRegister").on("click", function (e) {
		e.preventDefault();
		$(".loginForm").fadeOut(100, function () {
			$(".registerForm").slideDown(100).css('display', 'block');
		});
	});

	$(".goLogin").on("click", function (e) {
		e.preventDefault();
		$(".registerForm").fadeOut(100, function () {
			$(".loginForm").slideDown(100).css('display', 'block');
		});
	});

	$('textarea.format_html').each(function () {
		$(this).val(formatHTML($(this).val()));
	});

	$('form').on('submit', function() {
		$(this).find('.textarea_tag_kontrol').each(function() {
			this.value = this.value
				.replace(/<\s*textarea\b/gi,'<__textarea__')
				.replace(/<\s*\/\s*textarea\s*>/gi,'</__textarea__>');
		});
	});

	$(document).on('click', function (e) {
		if (!$(e.target).closest('[data-dc_hide]').length) {
			$('[data-dc_hide]').hide();
		}
	}).on('click', '[data-dc_hide]', function (e) {
		e.stopPropagation();
	});


	$(document).on('click','[data-confirm]',function(e){
		let el = $(this);
		let raw = el.data('confirm');
		if(!raw) return;
		let parts = raw.split('|');
		let msg = parts[0];
		let okText = parts[1] || 'OK';
		if(el.data('confirmed')){
			el.data('confirmed', false);
			return;
		}
		e.preventDefault();
		if(confirm(msg)){
			el.data('confirmed', true);
			if(this.href){
				window.location = this.href;
			}else if(el.is('button, input[type="submit"]')){
				el.closest('form').trigger('submit');
			}else{
				el.trigger('click');
			}
		}
	});

	$(document).on('click','[data-click2]',function(e){
		let el = $(this);
		if(!el.data('armed')){
			e.preventDefault();
			el.data('armed',true).addClass('confirming');
			setTimeout(function(){
				el.data('armed',false).removeClass('confirming');
			},3000);
			return;
		}
		el.data('armed',false).removeClass('confirming');
	});

	$(document).on('submit', 'form[data-submit-confirm], form[data-onsubmit-confirm]', function (e) {
		let message = $(this).attr('data-submit-confirm') || $(this).attr('data-onsubmit-confirm');
		if (message && message.length > 0) {
			if (!window.confirm(message)) {
				e.preventDefault();
				return false;
			}
		}
	});

	if (typeof $.fn.iziModal === 'function' && $('#izi_modal').length) {
		jQuery('#izi_modal').iziModal({
			overlayClose: true,
			autoHeight: true,
			width: "95%",
			zindex: 9999
		});
	}

	$(document).on("click", ".yazdir", function () {
		var yazdirc = $(this).attr("data-yazdir");
		if (yazdirc) {
			yazdir(yazdirc);
		}
	});


	$(document).on('click', '[data-copyinputid]', function () {
		const targetId = $(this).data('copyinputid');
		const textToCopy = $(this).text().trim();
		$(`#${targetId}`).val(textToCopy);
	});

	$(document).on("click", ".__dd_copytoc, [data-kopyala], .panoya_kopyala", function () {
		let dbt = $(this).attr('data-kopyala') || $(this).text().trim();
		clipboard(dbt);
		umesaj('Kopyalandı : ' + dbt);
	});

	$(document).on('click', '[data-nta-input]', function () {
		const $el = $(this);
		if ($el.find('input').length) return;

		const currentText = $el.text().trim();
		const nta = $el.attr('data-nta-input');

		const customClass = $el.attr('data-nta-class');
		const inputClass = customClass ? customClass : 'dbtext g_99__';

		const $input = $('<input>', {
			type: 'text',
			class: inputClass,
			'data-nta': nta,
			val: currentText
		});

		$el.empty().append($input);
		$el.removeAttr('data-nta-input');
		$input.focus();
	});



	$(document).on('click', 'a[href^="#"]', function (e) {
		const targetHash = this.hash;
		const hrefAttr = $(this).attr('href');

		if (!targetHash || targetHash === "#") {
			e.preventDefault();
			return;
		}

		const target = $(targetHash);
		const isSubPage = window.location.pathname.split('/').filter(Boolean).length > 1;

		if (!target.length && hrefAttr === targetHash && isSubPage) {
			const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
			const rootUrl = baseUrl || window.location.origin;

			console.log('Hedef bulunamadı, ana sayfaya yönlendiriliyor:', rootUrl + '/' + targetHash);
			window.location.href = rootUrl + '/' + targetHash;
			return;
		}

		if (target.length) {
			e.preventDefault();
			$('html, body').animate({ scrollTop: target.offset().top - 100 }, 1000);
			const currentUrl = window.location.href.split('#')[0];
			const newUrl = currentUrl + targetHash;
			history.pushState(null, null, newUrl);
		}
	});

	$('.dyokac').on('click', function () {
		console.log($(this).next('.dyok'));
		$(this).next('.dyok').show();
	});

	$(".click_history_back").on("click", function (e) {
		e.preventDefault();
		history.back();
	});

	$('.auto_resize').each(function () {
		$(this).css({
			height: 'auto',
			overflowY: 'hidden'
		});
		$(this).css('height', this.scrollHeight + 'px');
	});
	$('.auto_resize').on('input', function () {
		let textarea = this;
		let cursorPosition = textarea.selectionStart;
		$(textarea).css('height', 'auto');
		$(textarea).css('height', textarea.scrollHeight + 4 + 'px');
		textarea.selectionStart = cursorPosition;
		textarea.selectionEnd = cursorPosition;
		textarea.scrollTop = textarea.scrollHeight;
	});


	$(document).off('click.datatoggle').on('click.datatoggle', '[data-toggle]', function (e) {
		e.preventDefault();
		e.stopPropagation();
		let toggle_element = $(this).attr('data-toggle');
		console.log('Toggle clicked:', {
			element: this,
			toggleSelector: toggle_element,
			targetExists: $(toggle_element).length > 0,
			targetVisible: $(toggle_element).is(':visible')
		});
		let $target = $(toggle_element);
		if ($target.hasClass('dyok')) {
			$target.toggleClass('dyok');
		} else {
			$target.toggle();
		}
	});


	let dbl_disabled_clickCount = 0;
	$('.dbldisabled, .dblreadonly').on('dblclick', function () {
		dbl_disabled_clickCount++;
		if (dbl_disabled_clickCount === 2) {
			dbl_disabled_clickCount = 0;
			$(this)
				.prop('readonly', false)
				.prop('disabled', false)
				.css({ fontSize: "1em" });
		}
	});

	$(document).on("change", '.changesubmit', function () {
		$(this).closest('form').submit();
	});
	$(document).on("change", '.changesubmitform select', function () {
		$(this).closest('form').submit();
	});


	const sortableLists = document.querySelectorAll('.sirala-liste');

	sortableLists.forEach(el => {
		let escPressed = false;
		let initialOrder = [];

		document.addEventListener('keydown', e => {
			if (e.key === 'Escape') {
				escPressed = true;
			}
		});

		Sortable.create(el, {
			animation: 150,
			handle: '.stut',
			scroll: true,

			onStart: evt => {
				initialOrder = Array.from(el.children).map(child => child.cloneNode(true));
			},

			onEnd: evt => {
				if (escPressed) {
					el.innerHTML = '';
					initialOrder.forEach(node => el.appendChild(node));

					escPressed = false;
					return;
				}

				const newOrder = Array.from(el.children).map(el =>
					el.id.replace('sira_', '')
				);

				const oldOrder = initialOrder.map(el =>
					el.id.replace('sira_', '')
				);

				const isSame = oldOrder.every((id, idx) => id === newOrder[idx]);
				if (isSame) return;

				const t = el.getAttribute('data-t');

				$.ajax({
					data: {
						islem: 'uis_sirala',
						stip: t,
						sira: newOrder
					},
					dataType: 'json',
					success: yaz => {
						if (yaz.return == 1) {
							iziToast.success({
								message: yaz.mesaj,
								position: 'topRight'
							});
						} else {
							iziToast.error({
								message: yaz.mesaj,
								position: 'topRight'
							});
						}
					},
					error: (xhr, status, error) => {
						iziToast.error({
							title: 'Sistem Hatası',
							message: 'Sıralama kaydedilirken bir hata oluştu',
							position: 'topRight'
						});
						console.error("AJAX Hatası:", error);
					}
				});
			}
		});
	});

	if (typeof iziToast !== 'undefined') {
		$('.izitoast_goruntule').each(function () {
			const $el = $(this);
			const mesaj = $el.attr('data-text');
			const timeout = parseInt($el.attr('data-delay') || 5000, 10);

			if (mesaj) {
				iziToast.info({
					message: mesaj,
					position: 'bottomCenter',
					timeout: timeout
				});
			}
		});
	}

	$('[data-iziToast-mesaj]').each(function () {
		const $el = $(this);
		const mesaj = $el.attr('data-iziToast-mesaj');
		const timeout = parseInt($el.attr('data-delay') || 5000, 10);

		if (mesaj) {
			iziToast.info({
				message: mesaj,
				position: 'topCenter',
				timeout: timeout
			});
		}
	});

	$(document).on('mouseenter', '[data-tooltip], [data-tooltip-html]', function () {
		$('.tooltip_text').remove();

		const $element = $(this);
		const selectorOrHtml = $element.attr('data-tooltip-html');
		const text = $element.attr('data-tooltip');
		let $tooltip = $('<div class="tooltip_text"></div>');
		if (selectorOrHtml) {
			const trimmed = selectorOrHtml.trim();

			if (trimmed.startsWith('#') || trimmed.startsWith('.')) {
				const $target = $(trimmed).first();
				if ($target.length) {
					$tooltip.html($target.html());
				} else {
					console.log('!Tooltip target bulunamadı:', trimmed);
					return;
				}
			}
			else {
				$tooltip.html(selectorOrHtml);
			}
		}
		else if (text) {
			$tooltip.text(text);
		}

		$('body').append($tooltip);

		const elementRect = this.getBoundingClientRect();
		const scrollTop = $(window).scrollTop();
		const scrollLeft = $(window).scrollLeft();

		$tooltip.css({ visibility: 'hidden', display: 'block', opacity: 0 });
		const tooltipWidth = $tooltip.outerWidth();
		const tooltipHeight = $tooltip.outerHeight();
		$tooltip.css({ visibility: '', display: '', opacity: '' });

		const windowHeight = $(window).height();
		const windowWidth = $(window).width();

		const elementMiddle = elementRect.top + (elementRect.height / 2);
		const screenMiddle = windowHeight / 2;
		const offset = 8;
		const slideDistance = 5;

		let top, left, initialTop;

		if (elementMiddle <= screenMiddle) {
			top = scrollTop + elementRect.bottom + offset;
			initialTop = top + slideDistance;
		} else {
			top = scrollTop + elementRect.top - tooltipHeight - offset;
			initialTop = top - slideDistance;
		}

		left = scrollLeft + elementRect.left + (elementRect.width / 2) - (tooltipWidth / 2);

		if (left < scrollLeft + 10) {
			left = scrollLeft + 10;
		} else if (left + tooltipWidth > scrollLeft + windowWidth - 10) {
			left = scrollLeft + windowWidth - tooltipWidth - 10;
		}

		$tooltip.css({
			top: initialTop + 'px',
			left: left + 'px',
			position: 'absolute',
			opacity: 0
		});

		$tooltip.animate({
			top: top + 'px',
			opacity: 1
		}, 200);
	})
		.on('mouseleave', '[data-tooltip], [data-tooltip-html]', function () {
			$('.tooltip_text').stop(true, true).fadeOut(200, function () {
				$(this).remove();
			});
		});

	$(document).on('click', '[data-click-yaziekle]', function (e) {
		const el = $(this);
		if (!el.hasClass('data-click-yaziekle-clicked')) {
			e.preventDefault();
			const text = el.attr('data-click-yaziekle');
			if (text) {
				if (el.is('[data-append]')) {
					el.append(' ' + text);
				} else {
					el.prepend(text + ' ');
				}
			}
			el.addClass('data-click-yaziekle-clicked');
			return;
		}
	});




	function url_degistir(reload = false, element = null) {
		let delay = 800;
		if (element) {
			let dataDelay = element.getAttribute('data-delay');
			if (dataDelay) {
				delay = parseInt(dataDelay, 10);
			}
		}
		setTimeout(function () {
			let url = new URL(window.location.href);
			let params = new URLSearchParams(url.search);

			let paramsAttr = element?.getAttribute('data-params');
			if (paramsAttr) {
				try {
					let newParams = JSON.parse(paramsAttr);
					for (let key in newParams) {
						if (newParams[key]) {
							params.set(key, newParams[key]);
						}
					}
				} catch (e) {
					console.error("url_degistir parse error:", e);
				}
			}
			let silAttr = element?.getAttribute('data-sil');
			if (silAttr) {
				let silList = silAttr.split(',').map(x => x.trim());
				silList.forEach(key => params.delete(key));
			}

			url.search = params.toString();
			window.history.replaceState({}, document.title, url.toString());

			if (reload || (element && element.getAttribute('data-reload') === '1')) {
				location.reload();
			}
		}, delay);
	}

	$('.url_degistir').each(function () {
		url_degistir(false, this);
	});

	function url_reset(reload = false, element = null) {
		let delay = 800;
		if (element) {
			let dataDelay = element.getAttribute('data-delay');
			if (dataDelay) {
				delay = parseInt(dataDelay, 10);
			}
		}
		setTimeout(function () {
			let url = new URL(window.location.href);
			let params = new URLSearchParams(url.search);

			let excludeAttr = element?.getAttribute('data-reset-exclude');
			let excludeList = excludeAttr ? excludeAttr.split(',').map(x => x.trim()) : [];

			for (let key of [...params.keys()]) {
				if (!excludeList.includes(key)) {
					params.delete(key);
				}
			}

			url.search = params.toString();
			window.history.pushState({}, document.title, url.toString());

			if (reload || (element && element.getAttribute('data-reload') === '1')) {
				location.reload();
			}
		}, delay);
	}

	$(document).on('click', '.url_filtre_reset', function () {
		url_reset(true, this);
	});


	$('.url_reset').each(function () {
		url_reset(false, this);
	});


	$(document).on('click', '.filtre_temizle', function (e) {
		e.preventDefault();

		let exclude_params = $(this).attr('data-exclude');
		let currentUrl = new URL(window.location.href);
		if (exclude_params) {
			let excludeArray = exclude_params.split(',');
			let params = new URLSearchParams(currentUrl.search);
			for (let key of Array.from(params.keys())) {
				if (!excludeArray.includes(key)) {
					params.delete(key);
				}
			}
			currentUrl.search = params.toString();
			window.location.href = currentUrl.toString();
		} else {
			window.location.href = currentUrl.origin + currentUrl.pathname;
		}
	});


	const colorMap = {};
	function stringToColor(str) {
		let hash = 0;
		for (let i = 0; i < str.length; i++) {
			hash = str.charCodeAt(i) + ((hash << 5) - hash);
		}
		const r = (hash & 0xFF);
		const g = (hash >> 8) & 0xFF;
		const b = (hash >> 16) & 0xFF;

		return `rgb(${r}, ${g}, ${b})`;
	}

	$('[data-font-color-base]').each(function () {
		const base = $(this).data('font-color-base');
		if (!colorMap[base]) {
			colorMap[base] = stringToColor(base);
		}
		$(this).css('color', colorMap[base]);
	});

	$('[data-beklesayi]').on('click', function (e) {
		let $this = $(this);
		if (!$this.hasClass('tiklandi')) {
			e.preventDefault();
			$this.addClass('tiklandi');
		}
	});

	$('[data-hide-all]').on('dblclick', function () {
		$('[data-hide-all]').hide();
	});


	$(document).on('click', '[data-click-location-href]', function (e) {
		e.preventDefault();
		let lhref = $(this).attr('data-click-location-href');
		if (lhref) {
			window.location.href = lhref;
		}
	});


	$(document).on("dblclick", "table thead .toggle_empty", function () {
		const $th = $(this);
		const colIndex = $th.index();
		const $table = $th.closest("table");
		$table.find("tbody tr").each(function () {
			const $cell = $(this).children().eq(colIndex);
			const $input = $cell.find("input");
			let isEmpty = false;

			if ($input.length) {
				const inputValue = $.trim($input.val());
				isEmpty = (inputValue === "");
			} else {
				const $cell_value = $.trim($cell.text());
				isEmpty = ($cell_value === "" || $cell_value === "0");
			}

			if (isEmpty) {
				$(this).toggle();
			}
		});
	});

	$(document).on('change', '[data-checkbox-toggle]', function () {
		const $checkbox = $(this);
		const selector = $checkbox.attr('data-checkbox-toggle');
		const $target = $(selector);

		const reverse = $checkbox.attr('data-reverse') == 1;

		const checked = $checkbox.is(':checked');
		const shouldShow = reverse ? !checked : checked;

		$target.toggle(shouldShow);
	});

	$('[data-hide-delay]').each(function () {
		let delay_ms = parseInt($(this).attr('data-hide-delay'), 10) || 1000;
		setTimeout(() => {
			$(this).slideUp(200);
		}, delay_ms);
	});


	document.querySelectorAll('select.select_c').forEach((el, index) => {

		const isMultiple = el.hasAttribute('multiple');

		if (typeof SlimSelect !== 'undefined') {

			new SlimSelect({
				select: el,
				placeholder: 'Bir seçenek seçin',
				showSearch: true,
				searchPlaceholder: 'Ara...',
				searchText: 'Sonuç bulunamadı',
				beforeOpen: () => console.log(`Dropdown ${index} açılacak`),
				afterOpen: () => console.log(`Dropdown ${index} açıldı`),
				beforeClose: () => console.log(`Dropdown ${index} kapanacak`),
				afterClose: () => console.log(`Dropdown ${index} kapandı`)
			});

		} else if (typeof Choices !== 'undefined') {

			const placeholder =
				el.getAttribute('data-placeholder') ||
				el.options[0]?.text ||
				'Seçiniz';

			const itemSelectText = index === 0
				? (el.getAttribute('data-choices_text') || 'Seçmek için tıklayın')
				: '';

			const inst = new Choices(el, {
				searchEnabled: true,
				shouldSort: false,
				placeholderValue: placeholder,
				removeItemButton: isMultiple,
				placeholder: isMultiple,
				itemSelectText: null,//itemSelectText,
				searchFloor: 1,
				searchFields: ['label', 'value']
			});

			$(el).data('choicesInstance', inst);

		} else {
			console.warn('No select plugin (SlimSelect, Choices.js) is available for:', el);
		}
	});


	document.querySelectorAll('select[data-change_c]').forEach(selectElement => {
		selectElement.addEventListener('change', function () {
			const cookieName = this.getAttribute('data-change_c');
			const cookieValue = this.value;

			if (cookieName && cookieValue) {
				const maxAge = 30 * 24 * 60 * 60;
				document.cookie = `${cookieName}=${cookieValue}; path=/; max-age=${maxAge}`;
			}
		});
	});


	document.querySelectorAll('select.chosen').forEach(el => {
		if (typeof jQuery !== 'undefined' && typeof jQuery.fn.chosen === 'function') {
			let ds = el.getAttribute('data-search') || 10;
			jQuery(el).chosen({
				disable_search_threshold: ds,
				placeholder_text_multiple: 'Seçiniz',
				placeholder_text_single: 'Seçiniz',
				maxWidth: '100%'
			});
		}
	});



	if (typeof $.fn.daterangepicker !== 'undefined') {
		$('input.tarihsec[type="text"]').each(function () {
			let singleDatePicker = !$(this).attr('data-range');

			$(this).daterangepicker({
				locale: {
					format: singleDatePicker ? 'DD.MM.YYYY' : 'DD.MM.YYYY',//'DD.MM.YYYY' : 'DD.MM.YY - DD.MM.YY',
					firstDay: 1
				},
				singleDatePicker: singleDatePicker,
				autoUpdateInput: true,
				drops: 'auto',
				autoClose: false,
				showDropdowns: true
			});
		});

		$('input.tarihsec[type="text"]').on('apply.daterangepicker', function (ev, picker) {
			let e = jQuery.Event('keydown');
			e.which = 13;
			$(picker.element).trigger(e);
		});
	}


	function stickyTheadWithScrollBackground(selector) {
		const $thead = $(selector);
		if ($thead.length === 0) return;

		const tableTop = $thead.closest("table").offset().top;

		$(window).on("scroll", function () {
			const scrollTop = $(window).scrollTop();

			if (scrollTop >= tableTop) {
				$thead.addClass("scrolled");
			} else {
				$thead.removeClass("scrolled");
			}
		});
	}
	stickyTheadWithScrollBackground("thead.sticky");


	document.addEventListener('click', e => {
		const el = e.target.closest('.clickhide');
		if (!el) return;
		const hideAllEl = e.target.closest('[data-hideall]');
		if (hideAllEl) {
			document.querySelectorAll('.clickhide').forEach(el => el.style.display = 'none');
		} else {
			el.style.display = 'none';
		}
	});


	$('[data-autoclicktrigger]').each(function () {
		let delay = parseInt($(this).attr('data-autoclicktrigger'), 10) || 0;
		let targetSelector = $(this).attr('data-target');

		setTimeout(() => {
			if (targetSelector) {
				$(targetSelector).trigger('click');
			} else {
				$(this).trigger('click');
			}
		}, delay);
	});

	$(document).on('click', '[data-cookie-name]', function (e) {
		const $this = $(this);
		const name = $this.attr('data-cookie-name');
		const value = $this.attr('data-cookie-value');
		const hours = $this.attr('data-cookie-saat');
		const reloadAttr = $this.attr('data-reload');

		if (name && value) {
			let maxAge = 86400;
			if (hours) {
				maxAge = parseInt(hours) * 3600;
			}
			document.cookie = `${name}=${value}; path=/; max-age=${maxAge}`;

			if ($this.hasClass('__reload') || reloadAttr === 'true' || reloadAttr === '1') {
				window.location.reload();
			}
		}
	});


	$('input[data-typew]').each(function() {
		let $input = $(this);
		let phrases = ($input.data('typew') || "").split('|');
		if (phrases.length === 0 || phrases[0] === "") return;

		let phrase_index = 0;
		let type_index = 0;
		let typingInterval;
		let timeoutRef;

		function typePhrase() {
			let text = phrases[phrase_index];
			if (type_index < text.length) {
				$input.attr('placeholder', text.substring(0, type_index + 1));
				type_index++;
			} else {
				stopTyping();
				timeoutRef = setTimeout(function() {
					phrase_index = (phrase_index + 1) % phrases.length;
					type_index = 0;
					startTyping();
				}, 5000);
			}
		}

		function startTyping() {
			stopTyping();
			typingInterval = setInterval(typePhrase, 50);
		}

		function stopTyping() {
			clearInterval(typingInterval);
			clearTimeout(timeoutRef);
		}

		$input.on('focus', function() {
			stopTyping();
		});

		$input.on('blur focusout', function() {
			phrase_index = 0;
			type_index = 0;
			startTyping();
		});

		startTyping();
	});

});


/*WS Dropdown*/
(function() {
	'use strict';

	function initWSDropdowns() {
		document.querySelectorAll('.ws_dropdown-kutu').forEach(kutu => {
			const dropdownBaslik = kutu.querySelector('.ws_dropdown-baslik');
			const dropdownIcerik = kutu.querySelector('.ws_dropdown-icerik');

			if (!dropdownBaslik || !dropdownIcerik) return;

			let hiddenInput = null;
			if (kutu.dataset.inputName) {
				hiddenInput = document.createElement('input');
				hiddenInput.type = 'hidden';
				hiddenInput.name = kutu.dataset.inputName;
				hiddenInput.value = kutu.dataset.defaultValue || '';
				kutu.appendChild(hiddenInput);
			}

			const seciliSecenek = kutu.querySelector('.ws_dropdown-secenek.ws_secili');
			if (seciliSecenek) {
				dropdownBaslik.innerHTML = seciliSecenek.innerHTML;
				if (hiddenInput) {
					hiddenInput.value = seciliSecenek.dataset.value || seciliSecenek.textContent.trim();
				}
			}

			dropdownBaslik.addEventListener('click', function(e) {
				if (dropdownBaslik.classList.contains("ws_pd_")) {
					e.preventDefault();
				}
				e.stopPropagation();

				const isOpen = kutu.classList.contains('ws_dropdown-acik');

				if (!isOpen) {
					document.querySelectorAll('.ws_dropdown-acik').forEach(dk => {
						if (dk !== kutu) {
							dk.classList.remove('ws_dropdown-acik');
						}
					});
				}

				kutu.classList.toggle('ws_dropdown-acik');
			});

			kutu.querySelectorAll('.ws_dropdown-secenek').forEach(el => {
				el.addEventListener('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					const value = this.dataset.value || this.textContent.trim();

					kutu.querySelectorAll('.ws_dropdown-secenek').forEach(option => {
						option.classList.remove('ws_secili');
					});
					this.classList.add('ws_secili');

					dropdownBaslik.innerHTML = this.innerHTML;

					if (hiddenInput) {
						hiddenInput.value = value;
					}

					kutu.classList.remove('ws_dropdown-acik');

					if (this.dataset.cookieName) {
						const cookieValue = this.dataset.cookieValue || value;
						document.cookie = `${this.dataset.cookieName}=${cookieValue}; path=/; max-age=86400`;
					}

					if (this.dataset.updateUrl) {
						const url = new URL(window.location.href);
						url.searchParams.set(this.dataset.updateUrl, this.dataset.value || value);
						window.history.replaceState({}, '', url);
					}

					if (this.dataset.reload === 'true') {
						window.location.reload();
						return;
					}

					const changeEvent = new CustomEvent('ws-dropdown-change', {
						detail: {
							element: kutu,
							value: value,
							option: this
						},
						bubbles: true
					});
					kutu.dispatchEvent(changeEvent);

					if (kutu.dataset.onchange && typeof window[kutu.dataset.onchange] === 'function') {
						window[kutu.dataset.onchange](value, kutu);
					}
				});
			});
		});

		document.addEventListener('click', function(e) {
			if (!e.target.closest('.ws_dropdown-kutu')) {
				document.querySelectorAll('.ws_dropdown-acik').forEach(dd => {
					dd.classList.remove('ws_dropdown-acik');
				});
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initWSDropdowns);
	} else {
		initWSDropdowns();
	}

	window.wsDropdownRefresh = initWSDropdowns;
})();