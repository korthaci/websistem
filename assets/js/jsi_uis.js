$(document).ready(function() {
	
	$('.yeniac').on('click', function(){
		let form_ = $('.yeni').find('form');
		//form_.show();
		form_.css({'display':'inline-block'});
		let input = form_.find('input[type=text]')[0];
		if (input) {
			input.focus();
		}
	});
	$('.dyokac_next, .dyokac').on('click', function(){
		$(this).hide().next().show();
	});
	
	if ($.fn.db_text_kaydet) {
		$().db_text_kaydet();
	}

	setInterval(() => $.ajax({ url: 'api/', method: 'POST' }), 160000);
	/*setInterval(() => fetch('api/', { method: 'POST' }), 160000);*/

/*** * *  dosyalar **************** */

	function dosya_getir(options = {}) {
		window.dosya_getir = dosya_getir;
		let defaults = {
			element: false,
			s: 0,
			dd: '',
			yazSelector: '',
			img: 0, // Default to showing all file types, not just images
			nn: 0,
			t: '',
			a: ''
		};

		let params = { ...defaults, ...options };

		if (params.element) {
			let $el = $(params.element);
			params.s = $el.data('sayi') ?? params.s;
			params.dd = $el.data('dd') ?? params.dd;
			params.img = $el.data('img') ?? params.img;
			params.yazSelector = $el.data('yaz') ?? params.yazSelector;
			params.nn = $el.data('nn') ?? params.nn;
			params.t = $el.data('t') ?? params.t;
			params.a = $el.data('a') ?? params.a;
		}

		$.ajax({
			url: 'api/',
			type: 'POST',
			dataType: 'json',
			data: {
				islem: "dosyagetir",
				sayi: params.s,
				dd_: params.dd,
				img_: params.img,
				t_: params.t,
				a_: params.a,
				nn_: params.nn
			},
			success: function(response) {
				//console.log('Dosya getir yanıtı:', response);
				
				if (response.return === 0) {
					iziToast.error({
						title: 'Hata',
						message: response.mesaj,
						position: 'topRight'
					});
				} else {
					if (response.html && response.html !== '') {
						iziToast.success({
							message: response.mesaj,
							position: 'topRight',
							timeout: 2000
						});
					}
					if (params.yazSelector && response.html) {
						$(params.yazSelector).html(response.html);
					}
				}
			},
			error: function(xhr, status, error) {
				console.error('Dosya getir hatası:', error, xhr.responseText);
				iziToast.error({
					title: 'AJAX Hatası',
					message: error,
					position: 'topRight'
				});
			}
		});
	}

	setTimeout(() => {
        $('.trigger_click').first().trigger('click');
    }, 0);


	$(document).on('click', '.dosya_getir, [data-getir]', function() {
		let $el = $(this);
		let yaz = $el.attr("data-yaz") || '';
		let _sayi = $el.attr("data-sayi") || 0;
		if (yaz) {
			$(yaz).html("");
		}
		dosya_getir({
			element: this,
			s: _sayi
		});

	}).on("keyup", "#foto_filtrele", function (){
		let val = $.trim(this.value);
		if (val === "") {
			$('.__dd, .__dd2').show();
		} else {
			$('.__dd, .__dd2').hide();
			$(".__dd[title*=" + val + "], .__dd2[title*=" + val + "]").show();
		}

	}).on("click", '[data-dosyalar] .__dd_acilisliste', function(){
		let _this = $(this);
		let datadosya = _this.attr('data-dosya');
		let resim_dizin = _this.attr('data-dizin');
		let _nn = _this.attr('data-nn');
		let _t = _this.attr('data-t');
		let _a = _this.attr('data-a');

		let _link = {
			islem: 'resim_a_l',
			dizin: resim_dizin,
			dosya: datadosya,
			t_: _t,
			a_: _a,
			nn_: _nn
		};

		$.ajax({
			data: _link,
			dataType: 'json',
			success: function(response) {
				console.log(response);
				if (response.return === 0) {
					iziToast.error({
						title: 'Hata',
						message: response.mesaj,
						position: 'topRight'
					});
				} else if (response.return === 2) {
					_this.css({'opacity':0.4});
					iziToast.success({
						message: response.mesaj,
						position: 'topRight',
						timeout: 2000
					});
				} else if (response.return === 1) {
					$('.__dd_acilisliste').css({'opacity':0.4});
					_this.css({'opacity':1});
					iziToast.success({
						message: response.mesaj,
						position: 'topRight',
						timeout: 2000
					});
				}
			},
			error: function(xhr, status, error) {
				console.error('resim_a_l:', error, xhr.responseText);
				iziToast.error({
					title: 'AJAX Hatası',
					message: error,
					position: 'topRight'
				});
			}
		});

	}).on("click", "[data-dosyalar] .__dd_sil", function() {

		let _this = $(this);
		let resim_dizin = _this.attr('data-dizin');
		let datadosya = _this.attr('data-dosya');
		let _nn = _this.attr('data-nn');
		let _t = _this.attr('data-t');
		let _a = _this.attr('data-a');

		let datasil = _this.attr('data-sil');
		let datareload = _this.attr('data-reload') == 1;

		let _link = {
			islem: 'dosyasil',
			dizin: resim_dizin,
			dosya: datadosya,
			nn: _nn, 
			nn_t: _t,
			nn_a: _a
		};
		
		let nta = _this.attr("data-nta");
		if (nta !== undefined && nta !== false) {
			_link.nta = nta;
		}
		
		if (datasil == "1") {
			$.ajax({
				data: _link,
				dataType: 'json',
				success: function(response) {
					console.log(response);
					if (response.return === 1 || response.return === 2) {
						_this.parent('.__dd, .__dd2').remove();
						iziToast.success({
							message: response.mesaj,
							position: 'topRight',
							timeout: 2000
						});
					} else {
						iziToast.error({
							message: response.mesaj || '! Silinemedi',
							position: 'topRight',
							timeout: 2000
						});
					}
					if (datareload) {
						location.reload();
					}
				},
				error: function(xhr, status, error) {
					console.error('Dosya silme hatası:', error, xhr.responseText);
					iziToast.error({
						message: 'Silme sırasında hata oluştu.',
						position: 'topRight',
						timeout: 2000
					});
				}
			});
		}
		_this.attr("data-sil", "1").css({ "opacity": "1" });
	});

	/*** * *  dosyalar sonu **************** */


	$(document).on('click', '.modulkur, .bilesenkur', function() {
		let m = $(this).attr('data-m');
		let islem = $(this).hasClass('modulkur') ? 'uis_modulkur' : 'uis_bilesenkur';

		$.ajax({
			data: { islem: islem, m: m },
			dataType: 'json',
			success: function(response) {
				if (response.return != 0) {
					location.reload();
				}
			}
		});
	});


	function data_ntax() {
		let rules = [];
		$('[data-ntax]').each(function () {
			let items = $(this).attr('data-ntax').split(',');
			items.forEach(function (item) {
				item = item.trim();
				if (item) rules.push(item);
			});
		});
		rules.forEach(function (rule) {
			if (rule.startsWith('.')) {
				let cls = rule.substring(1);
				if (cls) $('.' + cls).removeClass(cls);
			} else if (rule.startsWith('#')) {
				let id = rule.substring(1);
				if (id) $('#' + id).removeAttr('id');
			} else if (/^[a-zA-Z0-9_-]+$/.test(rule)) {
				$('[data-' + rule + ']').removeAttr('data-' + rule);
			}
		});
	}
	data_ntax();



});