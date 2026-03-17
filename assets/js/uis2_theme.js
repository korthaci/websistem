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
        panel.slideDown().attr('data-uis-open', 'true');
        $('html, body').animate({scrollTop: panel.offset().top - 150}, 500);
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

        /* if (!confirm('Bu yedeği silmek istediğinize emin misiniz?')) {
            return;
        } */

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
                if ($('#blok-yedek-listesi-panel').attr('data-uis-open') === 'true') loadBlockBackups(tema);
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

        if (panel.attr('data-uis-open') === 'true') {
            panel.slideUp().attr('data-uis-open', 'false');
        } else {
            loadBlockBackups(tema);
            panel.slideDown(400, function() {
                panel.attr('data-uis-open', 'true');
                $('html, body').animate({
                    scrollTop: panel.offset().top - 100
                }, 600);
            });
        }
    });

    function loadBlockBackups(tema) {
        const body = $('#blok-yedek-liste-body');
        const originalRow = body.find('tr:first').prop('outerHTML');
        body.html(originalRow + '<tr><td colspan="3" class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...</td></tr>');

        $.post(API_URL, {
            islem: 'uis_tema',
            islem_tip: 'blok_yedek_listesi',
            tema: tema
        }, function(response) {
            body.find('tr:not(:first)').remove();
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


    /* Dynamic UI Position Adjuster */
    const $uiMain = $('.__ui [data-main], .__ui main#main');
    const $sidebar = $('.ws_sidebar');

    if ($uiMain.length && $sidebar.length) {
        const storedOffset = localStorage.getItem('uis_main_offset') || 0;
        if (storedOffset != 0) {
            $uiMain.css('margin-top', storedOffset + 'px');
        }

        const updatePos = (delta) => {
            let current = parseInt($uiMain.css('margin-top')) || 0;
            let newVal = current + delta;
            $uiMain.css('margin-top', newVal + 'px');
            localStorage.setItem('uis_main_offset', newVal);
        };

        const showSwitch = () => {
            if ($('#ws-ui-pos-switch').length) return;
            const $switch = $(`
                <div id="ws-ui-pos-switch" title="UI Ayarlayıcıyı Aç" style="position:absolute; bottom:60px; left:12px; z-index:9999; cursor:pointer; color:#8994a1; font-size:14px; opacity:0.5; transition:all 0.3s; display:flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:4px; background:rgba(255,255,255,0.05);" onmouseover="this.style.opacity='1'; this.style.background='rgba(255,255,255,0.1)';" onmouseout="this.style.opacity='0.5'; this.style.background='rgba(255,255,255,0.05)';">
                    ↕
                </div>
            `);
            $switch.on('click', function () {
                $(this).remove();
                showAdjuster();
            });
            $sidebar.append($switch);
        };

        const showAdjuster = () => {
            if ($('#ws-ui-pos-adjuster').length) return;
            const $adjuster = $(`
                <div id="ws-ui-pos-adjuster" style="position:absolute; bottom:60px; left:12px; z-index:9999; display:flex; flex-direction:column; gap:4px; background:rgba(15,23,42,0.9); backdrop-filter:blur(8px); padding:6px 4px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                    <button class="adj-btn up" aria-label="Yukarı" title="Yukarı (+Shift+Alt+Up)" style="background:rgba(255,255,255,0.05); border:0; color:#cbd5e1; width:22px; height:22px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; outline:none;" onmouseover="this.style.background='rgba(255,255,255,0.15)';" onmouseout="this.style.background='rgba(255,255,255,0.05)';">
                        <i class="fas fa-chevron-up" style="font-size: 10px;"></i>
                    </button>
                    <button class="adj-btn reset" aria-label="Sıfırla" title="Sıfırla" style="background:rgba(255,255,255,0.05); border:0; color:#cbd5e1; width:22px; height:22px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; outline:none;" onmouseover="this.style.background='rgba(15,118,110,0.3)';" onmouseout="this.style.background='rgba(255,255,255,0.05)';">
                        <i class="fas fa-undo-alt" style="font-size: 9px;"></i>
                    </button>
                    <button class="adj-btn down" aria-label="Aşağı" title="Aşağı (+Shift+Alt+Down)" style="background:rgba(255,255,255,0.05); border:0; color:#cbd5e1; width:22px; height:22px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; outline:none;" onmouseover="this.style.background='rgba(255,255,255,0.15)';" onmouseout="this.style.background='rgba(255,255,255,0.05)';">
                        <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                    </button>
                    <button class="adj-btn close" title="Kapat" style="background:rgba(239,68,68,0.15); border:0; color:#f87171; width:22px; height:22px; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; outline:none; margin-top:2px;" onmouseover="this.style.background='rgba(239,68,68,0.3)';" onmouseout="this.style.background='rgba(239,68,68,0.15)';">
                        <i class="fas fa-times" style="font-size: 9px;"></i>
                    </button>
                </div>
            `);

            $adjuster.find('.up').on('click', () => updatePos(-25));
            $adjuster.find('.down').on('click', () => updatePos(25));
            $adjuster.find('.reset').on('click', () => {
                $uiMain.css('margin-top', '');
                localStorage.removeItem('uis_main_offset');
            });
            $adjuster.find('.close').on('click', function () {
                $adjuster.remove();
                showSwitch();
            });

            $sidebar.append($adjuster);
        };

        showSwitch();

        $(document).on('keydown', function (e) {
            if (e.altKey && e.shiftKey) {
                if (e.key === 'ArrowUp') { e.preventDefault(); updatePos(-25); }
                if (e.key === 'ArrowDown') { e.preventDefault(); updatePos(25); }
            }
        });
    }


});
