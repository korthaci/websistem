$(function () {
    const API_URL = js_vars.local___ + '/api/index.php';

    // ── Theme Management ──
    const currentTheme = localStorage.getItem('uis-theme') || 'uis-light';
    $('body').addClass(currentTheme);
    updateThemeIcon(currentTheme);

    $(document).on('click', '.uis-theme-toggle', function (e) {
        e.preventDefault();
        if ($('body').hasClass('uis-dark')) {
            $('body').removeClass('uis-dark').addClass('uis-light');
            localStorage.setItem('uis-theme', 'uis-light');
            updateThemeIcon('uis-light');
        } else {
            $('body').removeClass('uis-light').addClass('uis-dark');
            localStorage.setItem('uis-theme', 'uis-dark');
            updateThemeIcon('uis-dark');
        }
    });

    function updateThemeIcon(theme) {
        const icon = $('.uis-theme-toggle i');
        if (theme === 'uis-dark') {
            icon.removeClass('fa-moon').addClass('fa-sun');
        } else {
            icon.removeClass('fa-sun').addClass('fa-moon');
        }
    }


    // Etkinleştirme
    $(document).on('click', '.tema-etkinlestir', function (e) {
        e.preventDefault();
        e.stopPropagation();
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
    $(document).on('click', '.tema-kopyala', function (e) {
        e.preventDefault();
        e.stopPropagation();
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
    $(document).on('click', '.tema-sil', function (e) {
        e.preventDefault();
        e.stopPropagation();
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
    $(document).on('click', '.tema-tazele, .uis-tazele', function (e) {
        e.preventDefault();
        e.stopPropagation();
        location.reload();
    });

    // Kopyalama Aracı (Clipboard)
    $(document).on('click', '.uis-kopyala', function (e) {
        e.preventDefault();
        e.stopPropagation();
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
    $(document).on('click', '.modulkur', function (e) {
        e.preventDefault();
        e.stopPropagation();
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
    $(document).on('click', '.tema-yedek-listesi-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const panel = $('#yedek-listesi-panel');
        if (panel.is(':visible')) {
            panel.slideUp();
        } else {
            panel.slideDown();
            $('html, body').animate({ scrollTop: panel.offset().top - 150 }, 500);
        }
    });

    // Yedek Geri Yükle
    $(document).on('click', '.tema-yedek-geri-yukle', function (e) {
        e.preventDefault();
        e.stopPropagation();
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
    $(document).on('click', '.tema-yedek-sil', function (e) {
        e.preventDefault();
        e.stopPropagation();
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

    // ── Blok Yedekleme & Geri Yükleme (SNAPSHOT) ──

    // Blok Yedekle
    $(document).on('click', '.tema-blok-yedek-al', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const _this = $(this);
        const tema = _this.data('tema');

        _this.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Yedekleniyor...');

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'blok_yedek_al',
            tema: tema
        }, function(response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                // Eğer panel açıksa listeyi güncelle
                if ($('#blok-yedek-listesi-panel').is(':visible')) loadBlockBackups(tema);
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
            }
            _this.prop('disabled', false).html('<i class="fa-solid fa-cloud-upload"></i> Blokları Yedekle');
        }, 'json');
    });

    // Blok Yedek Listesini Göster
    $(document).on('click', '.tema-blok-listesi-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const panel = $('#blok-yedek-listesi-panel');
        const tema = $(this).data('tema');

        if (panel.is(':visible')) {
            panel.slideUp();
        } else {
            loadBlockBackups(tema);
            panel.slideDown(400, function() {
                $('html, body').animate({
                    scrollTop: panel.offset().top - 100
                }, 600);
            });
        }
    });

    function loadBlockBackups(tema) {
        const body = $('#blok-yedek-liste-body');
        // Orijinal satırını sakla, diğerlerini temizle
        const originalRow = body.find('tr:first').prop('outerHTML');
        body.html(originalRow + '<tr><td colspan="3" class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...</td></tr>');

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'blok_yedek_listesi',
            tema: tema
        }, function(response) {
            body.find('tr:not(:first)').remove(); // Yükleniyor yazısını sil
            if (response.return == 1 && response.yedekler.length > 0) {
                response.yedekler.forEach(yedek => {
                    body.append(`
                        <tr>
                            <td><b>${yedek.ad}</b></td>
                            <td>${yedek.tarih_okunabilir}</td>
                            <td>
                                <button type="button" class="uis-btn uis-btn-sm uis-btn-outline tema-blok-yedek-yukle" 
                                        data-tema="${tema}" data-yedek="${yedek.ad}">
                                    Geri Yükle
                                </button>
                            </td>
                        </tr>
                    `);
                });
            } else if (response.return == 1) {
                body.append('<tr><td colspan="3" class="text-center">Henüz manuel yedek alınmamış.</td></tr>');
            }
        }, 'json');
    }

    // Blok Yedeği Geri Yükle (Snapshot)
    $(document).on('click', '.tema-blok-yedek-yukle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const _this = $(this);
        const tema = _this.data('tema');
        const yedek = _this.data('yedek');

        if (!confirm('Bu blok yedeğini geri yüklemek istediğinize emin misiniz? Mevcut bloklar silinecektir.')) return;

        _this.prop('disabled', true).text('Yükleniyor...');

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'blok_yedek_geri_yukle',
            tema: tema,
            yedek_ad: yedek
        }, function(response) {
            if (response.return == 1) {
                iziToast.success({ title: 'Başarılı', message: response.mesaj });
                setTimeout(() => location.reload(), 1000);
            } else {
                iziToast.error({ title: 'Hata', message: response.mesaj });
                _this.prop('disabled', false).text('Geri Yükle');
            }
        }, 'json');
    });
});
