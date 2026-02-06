(function($) {
	$.fn.db_text_kaydet = function(options) {
		var settings = $.extend({
			successMessage: '√ Kaydedildi.',
			errorMessage: 'Kaydedilirken bir sorun oluştu.',
			noChangeMessage: 'Değer değişmedi.',
			ajaxUrl: 'api/'
		}, options);

		// Event delegation kullanarak tüm body'ye event listener ekle
		$(document).off('change.dbtext focusout.dbtext keydown.dbtext click.dbtext input.dbtext', '.dbtext');
		$(document).on('change.dbtext focusout.dbtext keydown.dbtext click.dbtext input.dbtext', '.dbtext', function(e) {
			let _this = $(this);
			
			// Element için veri sakla
			if (!_this.data('dbtext-initialized')) {
				_this.data('dbtext-initialized', true);
				_this.data('lastSavedValue', _this.val());
				_this.data('isDirty', false);
				_this.data('isProcessing', false);
			}
			
			var lastSavedValue = _this.data('lastSavedValue');
			var isDirty = _this.data('isDirty');
			var isProcessing = _this.data('isProcessing');

			_this.removeClass('b_0010');

			if (e.type === 'click') {
				_this.parent().find('.dbtextok').remove();
				_this.parent('span').removeClass('ok_');
			}

			if (e.type === 'input' || e.type === 'keydown') {
				isDirty = _this.val() !== lastSavedValue;
				_this.data('isDirty', isDirty);
				
				if (e.type === 'keydown' && e.which === 13) {
					e.preventDefault();
					_this.blur();
				}
			}
			
			// Handle change event specifically for date and datetime inputs
			if (e.type === 'change') {
				// Check if this is a date or datetime input
				let inputType = _this.attr('type');
				if (inputType === 'date' || inputType === 'datetime-local') {
					isDirty = _this.val() !== lastSavedValue;
					_this.data('isDirty', isDirty);
					
					// Trigger the save functionality immediately for date inputs
					if (isDirty && !isProcessing) {
						saveDbTextData(_this);
						return;
					}
				}
			}
			
			if (e.type === 'focusout') {
				if (!isDirty || isProcessing || _this.val() === lastSavedValue) {
					_this.data('isDirty', false);
					return;
				}

				saveDbTextData(_this);
			}
		});
		
		// Extract the save functionality to a reusable function
		function saveDbTextData(_this) {
			_this.data('isProcessing', true);

			let postData = {
				islem: 'uis_dbtext',
				n: _this.attr("data-n"),
				t: _this.attr("data-t"),
				a: _this.attr("data-a") || '',
				deger: _this.val()
			};

			let datatipi = _this.data('datatipi');
			if (datatipi !== undefined) {
				postData.datatipi = datatipi;
			}
			let datajson = _this.data('json');
			if (datajson !== undefined) {
				postData.datajson = datajson;
			}
			let nta = _this.attr("data-nta");
			if (nta !== undefined && nta !== false) {
				postData.nta = nta;
			}

			_this.css({ opacity: '0.3' });
			_this.parent('span').removeClass('ok_');

			$.ajax({
				url: settings.ajaxUrl,
				method: 'POST',
				data: postData,
				dataType: 'json',
				success: function(response) {
					console.log(response.mesaj);
					_this.data('isProcessing', false);
					_this.animate({ opacity: '1' }, 400);
					if (response.return === 1) {
						_this.after('<span class="dbtextok"></span>');
						_this.parent('span').addClass('ok_');
						iziToast.info({
							timeout: 4000,
							position: 'topRight',
							title: getPlaceholderTitle(_this),
							message: response.mesaj || settings.successMessage
						});
						_this.data('lastSavedValue', postData.deger);
						_this.data('isDirty', false);
					} else {
						_this.parent('span').removeClass('ok_');
						iziToast.error({
							timeout: 5000,
							position: 'topRight',
							title: getPlaceholderTitle(_this),
							message: response.mesaj || settings.errorMessage
						});
					}
				},
				error: function(xhr, status, error) {
					_this.data('isProcessing', false);
					_this.animate({ opacity: '1' }, 400);
					_this.parent('span').removeClass('ok_');
					iziToast.error({
						timeout: 5000,
						position: 'topRight',
						title: getPlaceholderTitle(_this),
						message: settings.errorMessage
					});
				},
				complete: function() {
					_this.data('isDirty', false);
					_this.data('isProcessing', false);
				}
			});
		}

		function getPlaceholderTitle(_element) {
			let a = _element.attr("data-a") || '';
			let _placeholder = _element.attr("placeholder") || a;
			_placeholder = _placeholder === a ? _placeholder : _placeholder.toUpperCase().replace(/ *\([^)]*\) */g, "").replace(/_/g, " ");
			return _placeholder !== a ? _placeholder : a.toUpperCase().replace(/_/g, " ");
		}

		return this;
	};
})(jQuery);


function textarea_icerik_getir(_this_button_or_textarea) {
	let __this = $(_this_button_or_textarea);
	let __replace1 = '', __replace2 = '';

	let _textarea = __this.hasClass('ajax_text') ?
		__this :
		__this.closest('.uis_ajax_textarea').find('textarea');

	let id_ = id_belirle(_textarea);

	// Handle prompt for replacement if __prompt class is present
	if (_textarea.hasClass('__prompt')) {
		// window.prompt() is generally not allowed in sandboxed iframes.
		// For production, consider implementing a custom modal for user input instead.
		__replace1 = prompt("replace 1", "") || '';
		__replace2 = prompt("replace 2", "") || '';
	}

	let icerik = '';
	if ($('#' + id_ + '.texticerik').length && typeof tinyMCE !== 'undefined' && tinyMCE.get(id_)) {
		icerik = tinyMCE.get(id_).getContent();
	} else if ($('#' + id_ + '.jodit_editor, #' + id_ + '.jodit_initialized, #' + id_ + '.texticerik_a').length && typeof Jodit !== 'undefined') {
		let joditElement = document.getElementById(id_);
		let joditInstance = joditElement ? Jodit.make(joditElement) : null;
		
		if (joditInstance && joditInstance.getEditorValue) {
			icerik = joditInstance.getEditorValue().trim();
		} else if (joditElement && joditElement.__jodit && joditElement.__jodit.getEditorValue) {
			icerik = joditElement.__jodit.getEditorValue().trim();
		} else {
			console.warn("Jodit instance not found for ID:", id_, "Falling back to raw textarea value.");
			icerik = $('#' + id_).val();
		}
	} else {
		icerik = $('#' + id_).val();
	}

	if (__replace1 && __replace2) {
		icerik = icerik.replace(new RegExp(__replace1, 'g'), __replace2);
	}

	return icerik;
}


$(document).ready(function() {

	$('.selectyaz').trigger('click');


	$(document).on('click', '.db01', function() {
		let _this = $(this);
		let confirm_mesaj_active = _this.hasClass('confirm');
		let data_confirm_text = _this.attr('data-confirm') || '√';
		let data_reload = _this.data('reload') !== undefined;
		let izitoast_position = _this.attr('data-izi_position') || 'topRight';

		if (confirm_mesaj_active && !confirm(data_confirm_text)) {
			return;
		}

		let postData = {
			islem: 'uis_db01',
			n: _this.attr("data-n"),
			t: _this.attr("data-t"),
			a: _this.attr("data-a")
		};
		let nta = _this.attr("data-nta");
		if (nta !== undefined && nta !== false) {
			postData.nta = nta;
		}

		_this.addClass('y0');

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function(response) {
				_this.removeClass('y0');
				if (response.return === 1) {
					if (data_reload) {
						location.reload();
					} else {

						const classMap = {
							'var0': 'var1', 'var1': 'var0',
							'mvar0': 'mvar1', 'mvar1': 'mvar0',
							'ok0': 'ok1', 'ok1': 'ok0',
							'kvar0': 'kvar1', 'kvar1': 'kvar0',
							'yvar0': 'yvar1', 'yvar1': 'yvar0'
						};
						
						let currentClass = '';
						for (const className in classMap) {
							if (_this.hasClass(className)) {
								currentClass = className;
								break;
							}
						}
						
						if (currentClass && classMap[currentClass]) {
							const newClass = classMap[currentClass];
							_this.removeClass(currentClass).addClass(newClass);
						}
					}
					iziToast.info({
						timeout: 1000,
						position: izitoast_position,
						message: response.mesaj || '√ Seçenek başarıyla güncellendi.'
					});
				} else {
					iziToast.error({
						timeout: 5000,
						position: izitoast_position,
						message: response.mesaj || 'Seçenek güncellenemedi.'
					});
				}
			},
			error: function(xhr, status, error) {
				_this.removeClass('y0');
				iziToast.error({
					timeout: 5000,
					position: izitoast_position,
					message: 'Sunucu bağlantı hatası veya işlem başarısız.'
				});
			}
		});
	});

	$(document).on('change', '.dbselect', function() {
		let _this = $(this);

		let postData = {
			islem: 'uis_dbtext',
			n: _this.attr("data-n"),
			t: _this.attr("data-t"),
			a: _this.attr("data-a"),
			deger: _this.val()
		};
		let nta = _this.attr("data-nta");
		if (nta !== undefined && nta !== false) {
			postData.nta = nta;
		}

		let placeholderTitle = getPlaceholderTitleForSelect(_this);

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function(response) {
				if (response.return === 1) {
					iziToast.info({
						timeout: 4000,
						position: 'topRight',
						title: placeholderTitle,
						message: response.mesaj || '√ Seçim kaydedildi.'
					});
				} else if (response.return === 2) {
					iziToast.info({
						timeout: 2000,
						position: 'topRight',
						title: placeholderTitle,
						message: response.mesaj || 'Değer değişmedi.'
					});
				} else {
					iziToast.error({
						timeout: 5000,
						position: 'topRight',
						title: placeholderTitle,
						message: response.mesaj || 'Seçim kaydedilirken bir hata oluştu.'
					});
				}
			},
			error: function(xhr, status, error) {
				iziToast.error({
					timeout: 5000,
					position: 'topRight',
					title: placeholderTitle,
					message: 'Sunucu bağlantı hatası veya işlem başarısız.'
				});
			}
		});

		function getPlaceholderTitleForSelect(_element) {
			let a = _element.attr("data-a") || '';
			let _placeholder = _element.attr("placeholder");
			if (_placeholder) {
				return _placeholder.toUpperCase().replace(/ *\([^)]*\) */g, "").replace(/_/g, " ");
			}
			return a.replace("_no", " n").replace(/_/g, " ").toUpperCase();
		}
	});


	$(document).on('click', '.selectyaz', function() {
		let _this = $(this);
		var ngetir = _this.attr("data-ngetir") || '0';
		var tgetir = _this.attr("data-tgetir") || '';
		var nested_ugw = _this.attr("data-nested-ugw") || '';
		var getirfiltre = _this.attr("data-getirfiltre") || '';

		_this.removeClass('selectyaz');

		let postData = {
			islem: 'uis_selectyaz',
			tgetir: tgetir,
			ngetir: ngetir,
			getirfiltre: getirfiltre,
			nested_ugw: nested_ugw
		};

		$.ajax({
			data: postData,
			dataType: 'html',
			success: function(htmlResponse) {
				_this.html(htmlResponse);

				if (_this.hasClass('option0sil')) {
					_this.find('option[value="0"]').remove();
				}

				var _chosen_trigger = (_this.find('option').length > 10);
				if ($.fn.chosen && _chosen_trigger) {
					_this.chosen({
						disable_search_threshold: 10
					}).trigger('chosen:updated');
				}
			},
			error: function(xhr, status, error) {
				iziToast.error({
					timeout: 5000,
					position: 'topRight',
					title: 'Veri Yükleme Hatası',
					message: 'Select verileri yüklenirken bir sorun oluştu.'
				});
				_this.addClass('selectyaz');
			}
		});
	});


	$(document).on('click', '.sil', function() {
		let _this = $(this);
		let data_sil_attr = _this.attr('data-sil');

		if (data_sil_attr !== "1") {
			_this.attr("data-sil", "1").css({ "opacity": "1" });
			return;
		}
		
		let data_reload = _this.data('reload') !== undefined;
		let confirm_mesaj_active = _this.data('confirm') !== undefined;
		let data_confirm_text = _this.attr('data-confirm') || '√';
		let bt = (_this.attr('data-bt') || '').trim();
		data_confirm_text = bt.length ? `Dikkat! Bu işlem "${bt}" ile bağlantılı verileri de etkileyebilir. Emin misiniz?` : data_confirm_text;

		if (confirm_mesaj_active && !confirm(data_confirm_text)) {
			_this.attr("data-sil", "0").css({ "opacity": "0.3" });
			return;
		}

		_this.addClass('y0');

		let postData = {
			islem: 'uis_sil',
			n: _this.attr("data-n"),
			t: _this.attr("data-t")
		};
		let nt = _this.attr("data-nt");
		if (nt !== undefined && nt !== false) {
			postData.nt = nt;
		}

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function(response) {
				_this.removeClass('y0');
				if (response.return === 1) {
					iziToast.success({
						timeout: 3000,
						position: 'topRight',
						message: response.mesaj || '√ Silme işlemi başarılı.'
					});

					if (data_reload) {
						location.reload();
					} else {
						if (_this.closest('tr').length > 0) {
							_this.closest('tr').fadeOut(300, function() {
								$(this).remove();
							});
						}
						if ($('.sil_tr').length && _this.closest('.sil_tr').length) {
							_this.closest('.sil_tr').fadeOut(300, function() {
								$(this).remove();
							});
						}
					}
				} else {
					_this.attr("data-sil", "0").css({ "opacity": "0.3" });
					iziToast.error({
						timeout: 5000,
						position: 'topRight',
						message: response.mesaj || 'Silme işleminde bir hata oluştu.'
					});
				}
			},
			error: function(xhr, status, error) {
				_this.removeClass('y0');
				_this.attr("data-sil", "0").css({ "opacity": "0.3" });

				console.error("AJAX Error Status:", status);
				console.error("HTTP Status Code:", xhr.status);
				console.error("HTTP Status Text:", xhr.statusText);
				/*console.error("Raw Response:", xhr.responseText);*/

				let hataMesaji = '';

				if (status === 'timeout') {
					hataMesaji = 'İstek zaman aşımına uğradı. Sunucu yanıt vermedi.';
				} else if (status === 'parsererror') {
					hataMesaji = 'Sunucudan beklenen formatta veri gelmedi (JSON parse hatası).';
				} else if (xhr.status === 0) {
					hataMesaji = 'Sunucuya ulaşılamadı. İnternet bağlantınızı kontrol edin.';
				} else {
					hataMesaji = `Sunucu hatası: ${xhr.status} - ${xhr.statusText}`;
				}

				iziToast.error({
					timeout: 5000,
					position: 'topRight',
					message: hataMesaji
				});
			}
		});
	});

	$(document).on('click', '.textkaydet', function() {
		db_textarea_handler($(this));
	});

	function db_textarea_handler(_this_element) {
		var _this = $(_this_element);

		var _n = _this.data('n');
		var _t = _this.data('t');
		var _a = _this.data('a');

		let _icerik = textarea_icerik_getir(_this);

		let nta = _this.attr("data-nta");

		let postData = {
			islem: 'uis_dbtextarea',
			n: _n,
			t: _t,
			a: _a,
			deger: _icerik
		};

		if (nta !== undefined && nta !== false) {
			postData.nta = nta;
		}

		var originalButtonText = _this.text();

		_this.addClass('is-loading').html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Yükleniyor...</span></div>');
		_this.removeClass('primary-btn is-success is-danger');

		$.ajax({
			data: postData,
			dataType: 'json',
			success: function(response) {
				let yaz;
				try {
					yaz = typeof response === 'string' ? JSON.parse(response) : response;
				} catch (e) {
					console.error('Invalid JSON response:', e);
					yaz = { return: 0, mesaj: 'Geçersiz yanıt formatı.' };
				}

				if (yaz.return === 1) {
					_this.removeClass('is-loading is-danger').addClass('is-success btn-primary');
					_this.html(yaz.mesaj || 'Kaydedildi!');

					iziToast.success({
						timeout: 3000,
						position: 'topRight',
						title: 'Metin Alanı',
						message: yaz.mesaj || 'Metin başarıyla kaydedildi.'
					});

					if (_this.attr('data-sayi')) {
						var ia = _this.attr('data-sayi');
						if (ia.length < 2) {
							setTimeout(function(){
								$('._accordion').eq(parseInt(ia)).trigger("click");
							}, 400);
						}
					}
				} else {
					_this.removeClass('is-loading is-success btn-primary').addClass('is-danger');
					_this.html(yaz.mesaj || 'Hata!');

					iziToast.error({
						timeout: 5000,
						position: 'topRight',
						title: 'Metin Alanı Hatası',
						message: yaz.mesaj || 'Metin kaydedilirken bir hata oluştu.'
					});
				}

				setTimeout(function() {
					_this.removeClass('is-success is-danger btn-primary').addClass('primary-btn').html(originalButtonText);
				}, 4000);
			},
			error: function(xhr, status, error) {
				_this.removeClass('is-loading is-success btn-primary').addClass('is-danger');
				_this.html('Hata!');

				iziToast.error({
					timeout: 5000,
					position: 'topRight',
					title: 'Sunucu Bağlantı Hatası',
					message: 'Metin kaydedilirken sunucuya ulaşılamadı.'
				});

				setTimeout(function() {
					_this.removeClass('is-success is-danger btn-primary').addClass('primary-btn').html(originalButtonText);
				}, 4000);
			}
		});
	}




	const _json_form_debug = false;

	$(document).on('click', '.json_input_dizi_modal', function() {
		let _this = $(this);
		let jsonData = {};
		let nta = _this.attr("data-nta");
		
		let base64JsonAttr = _this.attr('data-json-b64');
		
		if (base64JsonAttr) {
			try {
				let decodedJson = atob(base64JsonAttr);
				jsonData = JSON.parse(decodedJson);
				if (_json_form_debug) console.log('Successfully parsed base64 JSON data:', jsonData);
			} catch (e) {
				if (_json_form_debug) {
					console.error('Error decoding base64 JSON:', e);
					console.error('Base64 string:', base64JsonAttr);
				}
				jsonData = {};
			}
		} else {
			let dataJsonAttr = _this.attr('data-json');
			let jsonType = _this.attr('data-json-type') || 'unknown';
			
			if (_json_form_debug) console.log('Using legacy approach. Raw data-json attribute:', dataJsonAttr);
			
			try {
				if (dataJsonAttr) {
					jsonData = JSON.parse(dataJsonAttr);
					if (_json_form_debug) console.log('Parsed JSON data:', jsonData);
				}
			} catch (e) {
				if (_json_form_debug) console.error('Error parsing JSON data:', e);
				jsonData = {};
			}
		}
		
		let modalId = 'jsonEditModal_' + Math.floor(Math.random() * 1000000);
		
	let $modal = $('<div>', {
		'id': modalId,
		'class': 'modal fade',
		'tabindex': '-1',
		'role': 'dialog',
		'aria-labelledby': modalId + 'Label'
	});
		
		$modal.html(`
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="${modalId}Label">JSON Düzenleyici <a data-toggle=".json_kullanim_sekilleri">(*)</a></h5>
					</div>
					<div class="dyok json_kullanim_sekilleri m-2">
						<code>
							Örnek kullanım şekilleri :<br>
							(Anahtar) : class , (Değer) : eklenecek class'lar<br>
							(Anahtar) : data-arama_class , (Değer) : eklenecek class'lar<br>
							(Anahtar) : data-arama_select_multiple , (Değer) : 1 veya 0)<br>
							(Anahtar) : data-arama_daterange , (Değer) : 1 veya 0)<br>
							(Anahtar) : data-nested-ugw , (Değer) : ust_grup_no)<br>
							(Anahtar) : data-getirfiltre , (Değer) : yayin,1)<br>
						</code>
					</div>

					<div class="modal-body">
						<form id="jsonForm_${modalId}" onsubmit="return false;">
							<div id="jsonInputs_${modalId}">
								<!-- JSON inputs will be added here dynamically -->
							</div>
							<div class="form-group mt-3">
								<button type="button" class="btn btn-sm btn-info" id="addJsonField_${modalId}" aria-label="Yeni JSON alan çifti ekle">+ Alan Ekle</button>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						${_json_form_debug ? '<button type="button" class="btn btn-info mr-2 show-raw-json" data-toggle="collapse" data-target="#rawJsonData_' + modalId + '">Ham Veriyi Göster</button>' : ''}
						<button type="button" class="btn btn-primary json_input_dizi_kaydet" data-modal-id="${modalId}" data-nta="${nta}">Değişiklikleri Kaydet</button>
					</div>
				</div>
			</div>
		`);
		
		if (_json_form_debug) {
			let $rawDataSection = $('<div>', {
				'id': 'rawJsonData_' + modalId,
				'class': 'collapse mt-3 w-100'
			});
			
			let $cardBody = $('<div>', {'class': 'card card-body bg-light'})
				.appendTo($rawDataSection);
			
			$('<h6>').text('Ham JSON Veri:').appendTo($cardBody);
			
			let rawDataContent = '';
			if (base64JsonAttr) {
				try {
					rawDataContent = 'Base64: ' + base64JsonAttr + '\n\nDecoded: ' + atob(base64JsonAttr);
				} catch (e) {
					rawDataContent = 'Error decoding base64: ' + e.message;
				}
			} else {
				rawDataContent = 'No data';
			}
			
			$('<pre>', {
				'class': 'small',
				'style': 'max-height: 200px; overflow: auto;',
				'text': rawDataContent
			}).appendTo($cardBody);
			
			$modal.find('.modal-footer').append($rawDataSection);
		}
		
		$('body').append($modal);
		
		function addJsonField(key = '', value = '') {
			let fieldId = 'field_' + Math.floor(Math.random() * 1000000);
			let keyId = 'key_' + fieldId;
			let valueId = 'value_' + fieldId;
			
			let row = $('<div>', {
				'class': 'row form-row mb-2',
				'id': fieldId,
				'role': 'group',
				'aria-label': 'JSON öğe çifti'
			});
			
			let keyCol = $('<div>', {'class': 'col-5'}).appendTo(row);
			let valueCol = $('<div>', {'class': 'col-5'}).appendTo(row);
			let btnCol = $('<div>', {'class': 'col-2'}).appendTo(row);
			
			$('<label>', {
				'for': keyId,
				'class': 'sr-only',
				'text': 'Anahtar'
			}).appendTo(keyCol);
			
			$('<input>', {
				'type': 'text',
				'id': keyId,
				'class': 'form-control form-control-sm json-key',
				'value': key,
				'placeholder': 'Anahtar',
				'aria-label': 'Anahtar'
			}).appendTo(keyCol);
			
			$('<label>', {
				'for': valueId,
				'class': 'sr-only',
				'text': 'Değer'
			}).appendTo(valueCol);
			
			$('<input>', {
				'type': 'text',
				'id': valueId,
				'class': 'form-control form-control-sm json-value',
				'value': value,
				'placeholder': 'Değer',
				'aria-label': 'Değer'
			}).appendTo(valueCol);
			
			$('<button>', {
				'type': 'button',
				'class': 'btn btn-sm btn-danger remove-json-field',
				'data-field': fieldId,
				'html': '×',
				'aria-label': 'Bu JSON öğe çiftini sil',
				'title': 'Bu JSON öğe çiftini sil'
			}).appendTo(btnCol);
			
			$modal.find('#jsonInputs_' + modalId).append(row);
		}
		
		if (_json_form_debug) {
			let jsonType = _this.attr('data-json-type') || 'bilinmiyor';
			let debugInfo = $('<div class="alert alert-info small mb-3">')
				.html('<strong>Hata Ayıklama Bilgisi:</strong><br>JSON Tipi: ' + jsonType + 
					'<br>Base64 veri uzunluğu: ' + (base64JsonAttr ? base64JsonAttr.length : 0) + 
					'<br>Nesne anahtarları: ' + (jsonData ? Object.keys(jsonData).join(', ') : 'yok'));
		
			$modal.find('#jsonForm_' + modalId).prepend(debugInfo);
		}
		
		if (typeof jsonData === 'object' && jsonData !== null && Object.keys(jsonData).length > 0) {
			if (_json_form_debug) console.log('JSON verilerinden alanlar ekleniyor:', jsonData);

			const keys = Object.keys(jsonData).sort();
			for (const key of keys) {
				let value = jsonData[key];

				if (typeof value !== 'string') {
					value = JSON.stringify(value);
				}
				
				if (_json_form_debug) console.log(`Alan ekleniyor - Anahtar: "${key}", Değer: "${value}"`);
				addJsonField(key, value);
			}
		} else {
			if (_json_form_debug) console.log('JSON verisi bulunamadı, boş alan ekleniyor');
			addJsonField();
		}
		
		$modal.on('click', '#addJsonField_' + modalId, function() {
			addJsonField();
		});
		
		$modal.on('click', '.remove-json-field', function() {
			$('#' + $(this).data('field')).remove();
		});
		
		$modal.on('keydown', '.json-key, .json-value', function(e) {
			if (e.which === 13) {
				e.preventDefault();
				$modal.find('.json_input_dizi_kaydet').click();
				return false;
			}
		});
		
		$modal.on('keydown', function(e) {
			if (e.which === 9) {
				const focusableElements = $(this).find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible');
				const firstElement = focusableElements.first();
				const lastElement = focusableElements.last();
				
				if (e.shiftKey && document.activeElement === firstElement[0]) {
					e.preventDefault();
					lastElement.focus();
				} 
				else if (!e.shiftKey && document.activeElement === lastElement[0]) {
					e.preventDefault();
					firstElement.focus();
				}
			}
			else if (e.which === 27) {
				$modal.modal('hide');
			}
		});
		
		if (Object.keys(jsonData).length === 0) {
			addJsonField();
			
			if (_json_form_debug) {
				let rawData = base64JsonAttr ? 
					'Base64 (kodlanmış): ' + base64JsonAttr + 
					'\n\nÇözülmüş: ' + atob(base64JsonAttr) : 
					'Boş (veri bulunamadı)';
				
				$modal.find('#jsonInputs_' + modalId).append(
					'<div class="alert alert-info small">'+
					'<p>JSON verisi bulunamadı veya JSON ayrıştırılamadı. Ham veri:</p>'+
					'<pre class="small" style="max-height:100px;overflow:auto">' + 
					$('<div>').text(rawData).html() + 
					'</pre></div>'
				);
			}
		}
		
		if (_json_form_debug) console.log('Modal açılıyor, ID:', modalId);
		try {
			$modal.on('shown.bs.modal', function () {
				$(this).removeAttr('aria-hidden');
				$(this).find('.json-key:first').focus();
			});
			
			$modal.modal('show');
		} catch (error) {
			if (_json_form_debug) console.error('Modal gösterme hatası:', error);
			setTimeout(function() {
				try {
					$modal.modal('show');
				} catch (e) {
					if (_json_form_debug) console.error('Modal yeniden gösterme denemesi başarısız:', e);
				}
			}, 100);
		}
		
		$modal.on('hidden.bs.modal', function() {
			$(this).remove();
		});
	});


	
	$(document).on('click', '.json_input_dizi_kaydet', function() {
		let _this = $(this);
		let modalId = _this.attr('data-modal-id');
		let modal = $('#' + modalId);
		let jsonObject = {};
		
		if (_json_form_debug) console.log('Formdan JSON verileri toplanıyor...');
		
		// Collect all JSON key-value pairs from the form
		modal.find('.form-row').each(function() {
			let keyInput = $(this).find('.json-key');
			let valueInput = $(this).find('.json-value');
			
			if (!keyInput.length || !valueInput.length) {
				if (_json_form_debug) console.log('Satırda eksik giriş elementi:', this);
				return; // Skip this row
			}
			
			let key = keyInput.val().trim();
			let value = valueInput.val().trim();
			
			if (_json_form_debug) console.log(`Alan işleniyor - Anahtar: "${key}", Değer: "${value}"`);
			
			if (key) {
				jsonObject[key] = value;
			}
		});
		
		// Convert the object to a JSON string
		let jsonString = JSON.stringify(jsonObject);
		
		let nta = _this.attr("data-nta");
		
		let postData = {
			islem: 'uis_json_dizi',
			deger: jsonString
		};
		
		if (nta !== undefined && nta !== false) {
			postData.nta = nta;
		}
		
		// Show loading state
		let originalButtonText = _this.text();
		_this.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...');
		_this.prop('disabled', true);
		
		if (_json_form_debug) console.log('Sunucuya veri gönderiliyor:', postData);
		
		$.ajax({
			url: 'api/',
			method: 'POST',
			data: postData,
			dataType: 'json',
			success: function(response) {
				if (_json_form_debug) console.log('Sunucu yanıtı:', response);
				if (response.return === 1) {
					iziToast.success({
						timeout: 3000,
						position: 'topRight',
						title: 'JSON Kaydedildi',
						message: response.mesaj || '√ Değerler başarıyla kaydedildi.'
					});
					
					// Update the JSON data attribute on the original element
					let jsonLink = $('.json_input_dizi_modal[data-nta="' + nta + '"]');
					if (jsonLink.length) {
						// Convert JSON string to base64 for storage
						let base64JsonString = btoa(jsonString);
						jsonLink.attr('data-json-b64', base64JsonString);
						
						if (jsonLink.attr('data-json')) {
							jsonLink.removeAttr('data-json');
						}
						
						let jsonItemCount = Object.keys(jsonObject).length;
						let countSpan = jsonLink.find('.json_item_sayi');
						if (countSpan.length) {
							countSpan.text(jsonItemCount);
						} else {
							jsonLink.append(' <span class="json_item_sayi">' + jsonItemCount + '</span>');
						}
					}
					
					// Close the modal
					modal.modal('hide');
					
				} else {
					iziToast.error({
						timeout: 5000,
						position: 'topRight',
						title: 'Kaydetme Hatası',
						message: response.mesaj || '! Değerler kaydedilemedi.'
					});
					
					// Reset button state
					_this.html(originalButtonText);
					_this.prop('disabled', false);
				}
			},
			error: function(xhr, status, error) {
				if (_json_form_debug) console.error('Ajax Hatası:', error);
				
				iziToast.error({
					timeout: 5000,
					position: 'topRight',
					title: 'Sunucu Hatası',
					message: 'Sunucu bağlantı hatası veya işlem başarısız.'
				});
				
				// Reset button state
				_this.html(originalButtonText);
				_this.prop('disabled', false);
			}
		});
	});



});
