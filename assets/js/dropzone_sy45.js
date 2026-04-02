
/*
 * Generic Clickable Dropzone System
 * 
 * Usage Examples:
 * 
 * 1. Separate form element (traditional):
 *    <img class="dropzone_img" data-dropzone-target="next" />
 *    <form class="dropzone">...</form>
 * 
 * 2. Direct img dropzone (new - simplified):
 *    <img class="dropzone dropzone_img" 
 *         src="current-image.jpg"
 *         data-dtip="resim" 
 *         data-maxfiles="1" 
 *         data-accept=".png,.jpg,.jpeg"
 *         data-imgyaz="1"
 *         data-nn="123"
 *         data-nn_a="logo"
 *         data-nn_t="ufirma" />
 * 
 * 3. Direct img with custom settings:
 *    <img class="dropzone dropzone_img" 
 *         src="profile.jpg"
 *         data-dtip="resim" 
 *         data-dd="resim/_profiles"
 *         data-maxfiles="1"
 *         data-yg="600"
 *         data-rb="300"
 *         data-adi="profile_123"
 *         data-imgyaz="1" />
 * 
 * Note: When using img as dropzone directly:
 * - data-dd defaults to "resim/_firmalogo"
 * - data-yg defaults to "800" 
 * - data-rb defaults to "440"
 * - data-adi auto-generated as "{data-nn_a}_{data-nn}" if not provided
 * - Image src updates automatically after upload
 */

Dropzone.autoDiscover = false;

$(function(){
	jQuery(".dropzone").each(function(){
		let maxFiles = 40;
		if(this.hasAttribute('data-maxfiles')) {
			let val = parseInt(this.getAttribute('data-maxfiles'), 10);
			if(!isNaN(val) && val > 0) maxFiles = val;
		}

		let dosyaTip = this.getAttribute('data-dtip') || 'dosya';
		let acceptedFiles = ".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.jfif,.rtf,.odt,.ppt,.pptx,.zip,.rar,.csv,.tsv,.udf";
		if(dosyaTip === 'resim') {
			acceptedFiles = ".jpg,.jpeg,.png,.gif,.webp,.jfif";
		}
		// Custom accepted files override based on data-accept attribute
		if(this.hasAttribute('data-accept')) {
			acceptedFiles = this.getAttribute('data-accept');
		}
		let data_reload = this.hasAttribute('data-reload');

		let dictDefaultMessage = this.getAttribute('data-yazi') || "+";
		let dictRemoveFile = this.getAttribute('data-removefile') || "Dosyayı Sil";
		let imgyaz = false;
		const dropzoneElement = this;
		const isImgElement = this.tagName === 'IMG';

		// Special handling for IMG elements
		if (isImgElement && !this.hasAttribute('data-dd')) {
			// Set default directory for image uploads if not specified
			this.setAttribute('data-dd', 'resim/_firmalogo');
		}
		if (isImgElement && !this.hasAttribute('data-yg')) {
			this.setAttribute('data-yg', '800');
		}
		if (isImgElement && !this.hasAttribute('data-rb')) {
			this.setAttribute('data-rb', '440');
		}
		if (isImgElement && !this.hasAttribute('data-bk')) {
			this.setAttribute('data-bk', '0');
		}
		if (isImgElement && !this.hasAttribute('data-ow')) {
			this.setAttribute('data-ow', '1');
		}
		if (isImgElement && !this.hasAttribute('data-filigran')) {
			this.setAttribute('data-filigran', '0');
		}

		$(this).dropzone({
			url : "api/",
			method: "post",
			acceptedFiles: acceptedFiles,
			maxFiles: maxFiles,
			addRemoveLinks: false,
			dictDefaultMessage: dictDefaultMessage,
			dictRemoveFile: dictRemoveFile,
			dictCancelUpload: "İptal Et",
			clickable: true, // Ensure dropzone is clickable even when hidden			
			init: function () {
				this.on("sending", function(file, xhr, formData) {
					//console.log(this);
					formData.append("islem", "dyukle");
					formData.append("dd_", this.element.getAttribute('data-dd'));
					
					if (this.element.hasAttribute('data-nta')) {
						formData.append("nta", this.element.getAttribute('data-nta'));
					}
					if (this.element.hasAttribute('data-nn')) {
						formData.append("nn", this.element.getAttribute('data-nn'));
					}
					if (this.element.hasAttribute('data-nn_a')) {
						formData.append("nn_a", this.element.getAttribute('data-nn_a'));
					}
					if (this.element.hasAttribute('data-nn_t')) {
						formData.append("nn_t", this.element.getAttribute('data-nn_t'));
					}
					formData.append("dtip", dosyaTip);
					formData.append("yg", this.element.getAttribute('data-yg') || '1920');
					
					if (this.element.hasAttribute('data-rb')) {
						formData.append("rb", this.element.getAttribute('data-rb'));
					}
					
					if (this.element.hasAttribute('data-bk')) {
						formData.append("bk", this.element.getAttribute('data-bk'));
					}
					
					if (this.element.hasAttribute('data-adi')) {
						formData.append("adi", this.element.getAttribute('data-adi'));
					} else if (isImgElement && this.element.hasAttribute('data-nn')) {
						// Auto-generate filename for img elements based on ID and table
						let nn = this.element.getAttribute('data-nn');
						let table = this.element.getAttribute('data-nn_t') || 'item';
						let field = this.element.getAttribute('data-nn_a') || 'image';
						formData.append("adi", `${field}_${nn}`);
					}
					
					if (this.element.hasAttribute('data-ow')) {
						formData.append("ow", this.element.getAttribute('data-ow'));
					}
					
					if (this.element.hasAttribute('data-filigran')) {
						formData.append("filigran", this.element.getAttribute('data-filigran'));
					}

					if (maxFiles === 1 && this.element.hasAttribute('data-imgyaz')) {
						imgyaz = true;
					}
				});

				/*this.on("removedfile", function(file) {
					console.log("Dosya kaldırıldı:", file.name);
					fetch('api/', {
						method: 'POST',
						body: JSON.stringify({ 
							islem: 'dosyasil', 
							filename: file.name 
						}),
						headers: {
							'Content-Type': 'application/json'
						}
					})
					.then(response => response.json())
					.then(data => {
						console.log('Silme sonucu:', data);
					})
					.catch(error => {
						console.error('Silme hatası:', error);
					});
				});*/

				this.on("queuecomplete", function() {
					this.removeAllFiles(true); //false sadece yüklenmemişleri siler

					let tum_dosyalari_getir = $('.dosya_getir[data-sayi="0"]');

					if (tum_dosyalari_getir.length > 0) {
						$('.dosya_getir[data-sayi="0"]').trigger('click');
					} else if (this.element.hasAttribute('data-c_dosya_getir') && this.element.hasAttribute('data-c_dosya_gyaz')) {
						if (typeof dosya_getir === 'function') {
							let dd = this.element.getAttribute('data-c_dosya_getir');
							let yazSelector = this.element.getAttribute('data-c_dosya_gyaz');
							if (dd && yazSelector) {
								dosya_getir({
									element: this.element,
									yazSelector: yazSelector
								});
							}								
						} else {
							console.log('!fn:dosya_getir');
						}
					}
				});

				this.on("complete",    function(file) {
				});
				this.on("addedfile", function(file) {
					if (this.files.length > maxFiles) {
						this.removeFile(this.files[0]);
					}
				});
			},
			success: function(file, response) {
				//console.log(response);
				let json;
				try {
					json = (typeof response === 'object') ? response : JSON.parse(response);
				} catch (e) {
					iziToast.error({
						title: 'Hata',
						message: 'Sunucudan beklenmeyen yanıt: ' + response,
						position: 'topRight',
						timeout: 5000
					});
					return;
				}
				if (json.return == 1) {
					if (data_reload) {
						location.reload();
					} else {
						if (imgyaz) {
							let imgTarget = dropzoneElement.getAttribute('data-img-target');
							let cacheBusterSrc = (json.dosya || response.dosya).split('?')[0] + '?v=' + new Date().getTime();
							
							if (imgTarget) {
								let imgElement = document.querySelector(imgTarget);
								if (imgElement && imgElement.tagName === 'IMG') {
									imgElement.src = cacheBusterSrc;
									imgElement.style.display = 'block';
								}
							} else if (isImgElement) {
								// If the dropzone IS the img element, update its src directly
								dropzoneElement.src = cacheBusterSrc;
								dropzoneElement.style.display = 'block';
							} else {
								// Fallback to old behavior if no target specified
								dropzoneElement.setAttribute('src', cacheBusterSrc);
							}
						}
						iziToast.success({ 
							message: '.' + (json.dosya || response.dosya)
								.split('?')[0]
								.split('.').pop()
								+ ': ' + (json.mesaj || 'Yükleme başarılı.'),
							position: 'topRight',
							timeout: 3000
						});
						//console.log(json);
					}
				} else {
					iziToast.error({
						title: 'Hata',
						message: json.mesaj || 'Yükleme başarısız.',
						position: 'topRight',
						timeout: 5000
					});
				}
			}
		});
	});

	// Global handler for triggering hidden dropzones
	$(document).on('click', '.dropzone[style*="display: none"], .dropzone[style*="display:none"]', function(e) {
		e.preventDefault();
		// Get the dropzone instance and trigger file dialog
		const dropzoneInstance = Dropzone.forElement(this);
		if (dropzoneInstance) {
			dropzoneInstance.hiddenFileInput.click();
		}
	});

	// Generic handler for clickable images that trigger dropzones
	$(document).on('click', '.dropzone_img, .dropzone-clickable-image, .logo-clickable', function(e) {
		e.preventDefault();
		const target = $(this).attr('data-dropzone-target');
		let dropzoneForm;
		
		// Determine which dropzone to target based on data attribute
		switch(target) {
			case 'next':
				dropzoneForm = $(this).next('.dropzone');
				break;
			case 'prev':
				dropzoneForm = $(this).prev('.dropzone');
				break;
			case 'parent':
				dropzoneForm = $(this).parent().find('.dropzone');
				break;
			case 'sibling':
				dropzoneForm = $(this).siblings('.dropzone');
				break;
			default:
				// If target is a selector, use it directly
				if (target && (target.startsWith('#') || target.startsWith('.'))) {
					dropzoneForm = $(target);
				} else {
					// Default behavior: look for next dropzone
					dropzoneForm = $(this).next('.dropzone');
				}
		}
		
		if (dropzoneForm.length > 0) {
			dropzoneForm.trigger('click');
		}
	});

	// Generic error and load handling for clickable images
	$(document).on('error', '.dropzone_img, .dropzone-clickable-image, .logo-clickable', function() {
		$(this).hide();
	});
	
	$(document).on('load', '.dropzone_img, .dropzone-clickable-image, .logo-clickable', function() {
		if (this.complete && this.naturalHeight !== 0) {
			$(this).show();
		}
	});

});