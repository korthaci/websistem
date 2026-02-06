document.addEventListener('DOMContentLoaded', function() {
    const el = document.querySelector('.uppyUploader');
    if (!el) return;

    const uppy = new Uppy.Uppy({
        restrictions: {
            allowedFileTypes: ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.jfif'],
            maxNumberOfFiles: 40,
        },
        autoProceed: true,
		closeAfterFinish: true,
		width: '100%',
    })
    .use(Uppy.Dashboard, {
        inline: true,
        target: '.uppyUploader',
        note: el.dataset.yazi,
        showProgressDetails: true,
        proudlyDisplayPoweredByUppy: false,
    })
    .use(Uppy.XHRUpload, {
        endpoint: 'api/',
        method: 'post',
        formData: true
    })
    .use(Uppy.GoogleDrive, {
        target: Uppy.Dashboard,
        companionUrl: 'https://companion.uppy.io'
    })
    .use(Uppy.Webcam, {
        target: Uppy.Dashboard,
        modes: ['picture'],
        mirror: true
    });

    uppy.setMeta({
        islem: 'dyukle',
        dd_: el.dataset.dd_,
        bk: el.dataset.bk,
        adi: el.dataset.adi,
        ow: el.dataset.ow,
        yg: el.dataset.yg,
        rb: el.dataset.rb,
        filigran: el.dataset.filigran,
        nn: el.dataset.nn,
        nn_t: el.dataset.nn_t,
        nn_a: el.dataset.nn_a,
        dtip: el.dataset.dtip
    });

    uppy.on('complete', (result) => {
        console.log('Yükleme bitti:', result);

		let tum_dosyalari_getir = $('.dosya_getir[data-sayi="0"]');

		if (tum_dosyalari_getir.length > 0) {
			$('.dosya_getir[data-sayi="0"]').trigger('click');
		} else if (typeof dosya_getir === 'function') {
            dosya_getir({
                element: el,
                yazSelector: el.dataset.c_dosya_gyaz
            });
        }
    });
});
