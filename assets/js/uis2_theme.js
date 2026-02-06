$(function () {
    const API_URL = js_vars.local___ + '/api/index.php';

    // Etkinleştirme
    $(document).on('click', '.tema-etkinlestir', function () {
        const _this = $(this);
        const tema = _this.data('tema');

        if (!confirm(tema + ' temasını etkinleştirmek istediğinize emin misiniz?')) return;

        _this.prop('disabled', true).text('İşleniyor...');

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'activate',
            tema: tema
        }, function (response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                setTimeout(() => location.href = js_vars.local___ + '/ui/temalar?activated=' + tema, 1000);
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
                _this.prop('disabled', false).text('Etkinleştir');
            }
        }, 'json');
    });

    // Kopyala
    $(document).on('click', '.tema-kopyala', function () {
        const tema = $(this).data('tema');
        const yeniAd = prompt(tema + ' temasını kopyalamak için yeni bir isim girin:', tema + '_kopya');

        if (!yeniAd) return;

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'clone',
            tema: tema,
            yeni_ad: yeniAd
        }, function (response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                setTimeout(() => location.reload(), 1000);
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
            }
        }, 'json');
    });

    // Sil
    $(document).on('click', '.tema-sil', function () {
        const _this = $(this);
        const tema = _this.data('tema');

        if (!confirm(tema + ' temasını kalıcı olarak silmek istediğinize emin misiniz?')) return;

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'delete',
            tema: tema
        }, function (response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                _this.closest('.uis-col').fadeOut(function () { $(this).remove(); });
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
            }
        }, 'json');
    });

    // Listeyi Yenile
    $(document).on('click', '.tema-tazele, .uis-tazele', function () {
        location.reload();
    });

    // Kopyalama Aracı (Clipboard)
    $(document).on('click', '.uis-kopyala', function () {
        const text = $(this).data('kopyala');
        if (!text) return;

        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);

        iziToast.show({
            title: 'Kopyalandı',
            message: text,
            position: 'topRight',
            color: 'blue',
            timeout: 2000
        });
    });

    // Modül Kurulumu
    $(document).on('click', '.modulkur', function () {
        const _this = $(this);
        const modul = _this.data('m');

        if (!confirm(modul + ' modülünü kurmak istediğinize emin misiniz?')) return;

        _this.prop('disabled', true).text('Kuruluyor...');

        $.post(API_URL, {
            islem: 'uis_modul',
            islem_tip: 'install',
            modul: modul
        }, function (response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                setTimeout(() => location.reload(), 1000);
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
                _this.prop('disabled', false).text('Kur');
            }
        }, 'json');
    });

    // Yedek Listesi Göster/Gizle
    $(document).on('click', '.tema-yedek-listesi-btn', function () {
        const panel = $('#yedek-listesi-panel');
        if (panel.is(':visible')) {
            panel.slideUp();
        } else {
            panel.slideDown();
        }
    });

    // Yedek Geri Yükle
    $(document).on('click', '.tema-yedek-geri-yukle', function () {
        const _this = $(this);
        const tema = _this.data('tema');
        const dosya = _this.data('dosya');
        const tarih = _this.data('tarih');
        const tarihOkunabilir = _this.closest('tr').find('td:first').text();

        if (!confirm('Bu yedeği geri yüklemek istediğinize emin misiniz?\n\nTarih: ' + tarihOkunabilir + '\n\nMevcut dosya içeriği de yedeklenecektir.')) {
            return;
        }

        _this.prop('disabled', true).text('Yükleniyor...');

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'yedek_geri_yukle',
            tema: tema,
            dosya: dosya,
            tarih: tarih
        }, function (response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                setTimeout(() => location.reload(), 1000);
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
                _this.prop('disabled', false).text('Geri Yükle');
            }
        }, 'json');
    });

    // Yedek Sil
    $(document).on('click', '.tema-yedek-sil', function () {
        const _this = $(this);
        const tema = _this.data('tema');
        const dosya = _this.data('dosya');
        const tarih = _this.data('tarih');

        /*if (!confirm('Bu yedeği silmek istediğinize emin misiniz?')) {
            return;
        }*/

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'yedek_sil',
            tema: tema,
            dosya: dosya,
            tarih: tarih
        }, function (response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                _this.closest('tr').fadeOut(function () { $(this).remove(); });
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
            }
        }, 'json');
    });
});
