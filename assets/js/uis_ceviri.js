$(document).ready(function () {

	$('.text_ceviri_buton').on("click", function () {
		let __this = $(this);
		let __closest = $(this).closest('.text_ceviri');
		let ceviri_input = $(this).attr("data-input-id");
		let ceviri_text = __closest.find("label").text();
		let hedef_dil = __closest.attr("data-hedef");

		let postData = {
			islem: 'uis_text_ceviri',
			hedef_dil: hedef_dil,
			ceviri_text: ceviri_text,
		};

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function (response) {
				console.log(response);
				if (response.return === 1) {
					$(ceviri_input).val(response.mesaj).css({ backgroundColor: "#fff9e7" });
				}
				if (response.mesaj != "") {
					__this.remove();
				}
			}
		});
	});

	const CEVIRI_ISTEK_ARALIK_MS = 1000; // ceviri istegi arasi min bekleme (ms)
	const KAYDIRMA_IYILESTIRME_MS = 3000; // kullanici kaydirinca otomatik kaydirmayi ertele (ms)
	let sonManuelKaydirma = 0;
	$(window).on('wheel touchmove', function () {
		sonManuelKaydirma = Date.now();
	});

	let stopScrollingInputText = false;
	$('.text_ceviri_tumunu_cevir').on("click", async function () {
		$('.text_ceviri_tumunu_cevir').text('🔴').css({ 'pointer-events': 'none' }).addClass('blinking');
		$('.stopScrollingInputText').show();

		const buttons = $('.text_ceviri_buton');
		for (let index = 0; index < buttons.length; index++) {
			if (stopScrollingInputText) { break; }
			await new Promise(resolve => {
				setTimeout(() => {
					if (Date.now() - sonManuelKaydirma >= KAYDIRMA_IYILESTIRME_MS) {
						const currentButtonTop = $(buttons[index]).offset().top;
						$('html, body').animate({ scrollTop: currentButtonTop - 200 }, 500);
					}
					$(buttons[index]).trigger("click");
					resolve();
				}, CEVIRI_ISTEK_ARALIK_MS);
			});
		}
	});

	$(document).on("click", ".stopScrollingInputText, .startScrollingInputText", function () {
		stopScrollingInputText = !stopScrollingInputText;
		$(this).toggleClass('startScrollingInputText stopScrollingInputText');

		if (!stopScrollingInputText) {
			$('.text_ceviri_tumunu_cevir').trigger("click");
			$(this).text("＝");
		} else {
			$('.text_ceviri_tumunu_cevir').text('🔘').removeClass('blinking');
			$(this).text("▼");
		}
	});


	$('[data-textarea-ceviri-id]').on("click", function () {
		let textarea_text = "";
		let textarea_id = $(this).attr("data-textarea-ceviri-id");
		$('[data-val-id]').each(function (index) {
			let _val_id = $(this).attr("data-val-id");
			let _adi = $(this).val();
			let _text = $('#' + _val_id).val();
			textarea_text += _adi + ':::' + _text + '____';
		});

		$(textarea_id).val(textarea_text);

		$('[data-val-id]').promise().done(function () {
			setTimeout(function () {
				$('#text_ceviri_form').submit();
			}, 400);
		});
	});

	$(document).on('click', '.form_submit_link', function () {
		let formId = $(this).data('formid');
		if (formId) {
			$(formId).submit();
		}
	});


	$('.input_sadece_bos_alanlar').click(function () {
		let _switch_text = $(this).attr('data-text');
		let _text = $(this).text();
		$(this).toggleClass("acik");
		let _acik = $(this).hasClass('acik');
		$('.input_placeholder').each(function () {
			if (!_acik) {
				$(this).show();
			} else {
				if ($(this).find('input[type="text"]').val() != "") {
					$(this).hide();
				}
			}
		});
		$(this).attr('data-text', _text).text(_switch_text);
	});


	$('.text_ceviri_yeni_ekle').click(function () {
		$('.text_ceviri_yeni').toggle();
		$('.input_placeholder').toggle();
	});

	$('.yazi_ceviri_filtrele').on('keyup', function (event) {
		let filtre_kelime = normalizeText($(this).val()).toLowerCase().trim();
		$('.input_placeholder').each(function (index, element) {
			let _text = $(this).find('[data-val-id]').val();
			let text_filtre = normalizeText(_text).toLowerCase().trim();
			if (text_filtre.includes(filtre_kelime)) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});

	$('.text_ceviri_sil_buton').on('click', function (e) {
		if (!confirm('Tamamıyla silinecek. Emin misiniz?')) {
			e.preventDefault();
		}
	});


	/**
	 * 
	 * 
	 * 
	 * 
	 * */


	if ((typeof (js_vars.__ceviri) !== 'undefined') && js_vars.__ceviri == true) {

		$('.ceviri').each(function () {

			let _this = $(this);
			let parentDiv = $('<div>').addClass('ceviri_dil_wrap');
			let ceviriDiv = $('<div>').addClass('ceviri_diller_buton');
			_this.wrap(parentDiv);
			_this.after(ceviriDiv);
		});
	}

	$(document).on('click', '.vcd_ekle', function (e) {
		e.stopPropagation();
	});

	let ceviri_diller_buton_click = false;
	$(document).on('mouseover', '.ceviri_diller_buton', function () {
		let _this = $(this);
		let _ceviri = _this.closest('.ceviri_dil_wrap').find('.ceviri');
		let atn = _ceviri.attr("data-ceviridil");

		_this.removeClass('ceviri_diller_buton_width0');

		if (_this.hasClass('ceviri_diller_eklendi')) {
			return false;
		}

		let postData = {
			islem: 'uis_diller_getir',
			atn: atn,
		};

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function (response) {
				if (response.return != 0) {
					_this.addClass('ceviri_diller_eklendi');
					_this.html(response.mesaj);
				}
			}
		});

	}).on('click', '.ceviri_diller_buton', function () {
		ceviri_diller_buton_click = !ceviri_diller_buton_click;
	}).on('mouseleave', '.ceviri_diller_buton', function () {
		if (!ceviri_diller_buton_click) {
			$(this).addClass('ceviri_diller_buton_width0');
		}
	});
	//islem2=uis_ceviri_input&atn=adi,urun_grup,124&tag=input&cdil=en&ekledegistir=ekle
	$(document).on('click', '.ceviri_diller_buton > span', function (e) {//vcd_ekle/degistir

		e.preventDefault();

		let _this = $(this);
		let _ceviri_wrap = _this.closest('.ceviri_dil_wrap');
		let _ceviri = _ceviri_wrap.find('.ceviri');
		let tagname = _ceviri.prop('tagName').toLowerCase();
		let atn = _ceviri.data("ceviridil");
		let cdil = _this.data('dc');
		let ekledegistir = _this.hasClass('vcd_ekle') ? 'ekle' : 'degistir';

		_this.addClass('vcd_burada');
		$('.ceviri_burada').removeClass('ceviri_burada');
		_ceviri_wrap.addClass('ceviri_burada');

		/*ceviri ai değişkenler*/
		let t0 = atn.split(',')[0];
		let t0text = _ceviri_wrap.find('input.ceviri').val();
		let iziModalceviriAIHtml = tagname == 'input' ? '<span class="btn btn-dark ai_ceviri_getir" data-hedef="' + cdil + '" data-text="' + t0text + '">AI</span>' : '';
		/*ceviri ai değişkenler sonu*/

		let postData = {
			islem: 'uis_ceviri_input',
			atn: atn,
			tag: tagname,
			cdil: cdil,
			ekledegistir: ekledegistir,
		};

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function (response) {
				if (response.return != 0) {
					if (t0 != 'icerik') {
						let kaynak_dil = 'tr';
					}
					let iziModalHtml = '<button class="iziModal-button-close" data-izimodal-close="">x</button>';
					iziModalHtml += iziModalceviriAIHtml
					iziModalHtml += response.mesaj;
					$('#izi_modal').iziModal('open');
					$('.iziModal-content').html(iziModalHtml);
					console.log(tagname);
					if (tagname == 'textarea') {
						/*tinymce.remove('.texticerik');*/
						// Önce mevcut editörleri temizleyelim
						document.querySelectorAll('.texticerik_a').forEach(function (el) {
							if (el.__jodit) {
								el.__jodit.destruct();
							}
						});

						let editor = window.joditEkle('.texticerik_a', {
							width: '100%',
							inline: false
						});

						// Referansı global olarak tutalım
						window.activeJoditEditor = editor;
					}
				}
			}
		});
	});


	$(document).on('click', '.ai_ceviri_getir', function () {//yukarıda .text_ceviri_buton ajax tekrarlandı
		let __this = $(this);
		let _input = __this.closest('.iziModal-content').find('.uis_ajax.uis_ajax_ceviri .ajax_text');
		let ceviri_text = __this.attr("data-text");
		let hedef_dil = __this.attr("data-hedef");
		if (ceviri_text.length > 1) {

			let postData = {
				islem: 'uis_text_ceviri',
				hedef_dil: hedef_dil,
				ceviri_text: ceviri_text,
			};

			$.ajax({
				data: postData,
				dataType: 'json',
				success: function (response) {
					console.log(response);
					if (response.return != 0) {
						_input.val(response.mesaj);
					}
				}
			});
		}
	});



	$(document).on('click', '.ceviri_kaydet_input', function () {
		let _this = $(this);
		let _this_wrap = _this.closest('.uis_ajax');
		let _input_textarea = _this_wrap.find('.ajax_text');
		let tagname = _input_textarea.prop('tagName').toLowerCase();
		let _cdiln = _input_textarea.data('cdiln');
		let _alan = _input_textarea.data('a');
		let _tablo = _input_textarea.data('t');
		let _tn = _input_textarea.data('tn');
		let _yeni = _input_textarea.data('yeni');

		let _yazi;
		if (tagname == 'input') {
			_yazi = _input_textarea.val();
		} else {
			if (window.activeJoditEditor && _input_textarea.hasClass('texticerik_a')) {
				try {
					_yazi = window.activeJoditEditor.getEditorValue();
					console.log('Jodit aktif editörden içerik alındı');
				} catch (e) {
					console.warn('Jodit aktif editörden içerik alınamadı:', e);
					_yazi = textarea_icerik_getir(_input_textarea);
				}
			} else {
				_yazi = textarea_icerik_getir(_input_textarea);
			}
		}

		let postData = {
			islem: 'uis_ceviri_kaydet',
			cdiln: _cdiln,
			a: _alan,
			t: _tablo,
			tn: _tn,
			yazi: _yazi,
			yeni: _yeni
		};

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function (response) {
				console.log(response.mesaj);
				if (response.return == 1) {
					$('.iziModal-content').html('√ Kaydedildi.');
					$('.ceviri_burada .vcd_burada').addClass('vcd_degistir').removeClass('vcd_ekle vcd_burada');
					iziToast.info({
						timeout: 1000,
						position: 'topRight',
						message: '√ Kaydedildi.'
					});
					setTimeout(function () {
						$('#izi_modal').iziModal('close');
					}, 800);
				} else {
					$('.iziModal-content').html('Değişiklik yapılmadı.');
				}
			}
		});

	});

	$(document).on('click', '.ceviri_sil_input', function () {
		let _this = $(this);
		let siln = _this.data('siln');

		let postData = {
			islem: 'uis_ceviri_kaydet',
			siln: siln
		};

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function (response) {
				if (response.return == 1) {
					$('.iziModal-content').html('√ Silindi.');
					$('.ceviri_burada .vcd_burada').removeClass('vcd_degistir vcd_burada').addClass('vcd_ekle');
					iziToast.error({ timeout: 1000, position: 'topRight', message: '√ Silindi.' });
					setTimeout(function () {
						$('#izi_modal').iziModal('close');
					}, 800);
				} else {
					$('.iziModal-content').html('Değişiklik yapılmadı.');
				}
			}
		});
	});


	$(document).on('click', '.textarea_ekle span', function () {
		let $span = $(this);
		let $container = $span.closest('.textarea_ekle');
		let textareaName = $container.attr('data-textarea-name');

		if (textareaName) {
			let $textarea = $('textarea[name="' + textareaName + '"]');
			if ($textarea.length) {
				let currentVal = $textarea.val().trim();
				let newValue = $span.text().trim() + ' ++ ';
				if (currentVal !== "") {
					if (!currentVal.endsWith('++') && !currentVal.endsWith('++ ')) {
						newValue = currentVal + ' ++ ' + newValue;
					} else {
						newValue = currentVal + ' ' + newValue;
					}
				}
				$textarea.val(newValue).focus();

				$span.remove();
			}
		}
	}).on('click', '.textarea_ekle span a', function (e) {
		e.preventDefault();
	});
});