let disableChange = false;
let isMouseDown = false;
let startX = 0;

$('#nestable3').mousedown(function (e) {
	isMouseDown = true;
	startX = e.pageX;
});
/*
$(document).mouseup(function() {
	isMouseDown = false;
});
*/
$('#nestable3').mouseover(function () {
	disableChange = true;
});

$('#nestable3').mousemove(function (e) {
	if (isMouseDown) {
		let currentX = e.pageX;
		let delta = currentX - startX;

		if (Math.abs(delta) >= 20) {
			disableChange = false;
		}
	}
});

function nestable_getIdList() {
	let idList = '';
	idList += $('.n-dd-list').length;
	$('.n-dd-item').each(function () {
		idList += $(this).data('id');
	});
	return idList;
}

let idList = nestable_getIdList().toString();

jQuery('#nestable3').nestable({
	group: 1,
}).on('change', function (e) {
	/*if (disableChange){disableChange=false;return;}*/
	let list = e.length ? e : $(e.target);
	let json_string = window.JSON.stringify(list.nestable('serialize'));

	let newIDList = nestable_getIdList().toString();

	if (newIDList != idList || !disableChange) {
		$.ajax({
			data: {
				islem: 'uis_menu_nestable',
				json: json_string
			},
			success: function (yaz) {
				console.log(yaz);
			}
		});
	}
});

$(document).on('click', '.menu_ekle_buton', function () {
	let t__ = $(this).data('t');
	let selectedOptions = $('.menu_' + t__ + '_select option:selected').map(function () {
		return $(this).val();
	}).get();

	let postData = {
		islem: 'uis_menu_ekle_sil',
		t: t__,
		menu_degerler: selectedOptions,
	};

	$.ajax({
		data: postData,
		dataType: 'json',
		success: function (response) {
			let yaz_sayi = parseInt(response.mesaj);
			if (yaz_sayi > 0) {
				selectedOptions.forEach(function (optionValue) {
					let optionText = $('.menu_' + t__ + '_select option[value="' + optionValue + '"]').text();

					let nestable_ekle = '<li class="n-dd-item n-dd3-item" data-id="' + optionValue + '"><div class="n-dd-handle n-dd3-handle"> </div><div class="n-dd3-content">' + optionText + '<div class="n-dd3-sil" data-n="' + optionValue + '" data-adi="' + optionText + '">x</div></div></li>';

					$('.n-dd').append(nestable_ekle);
					$('.menu_' + t__ + '_select option[value="' + optionValue + '"]').attr({ 'disabled': 'disabled' });
				});

				$('#nestable3').nestable('refresh');
				/*$('.menu_' + t__ + '_select").trigger("chosen:updated");*/
				location.reload();
			}
		},
		error: function (xhr, status, error) {
			console.error(error);
		}
	});
});

$(document).on('click', '.n-dd3-sil', function () {
	let _this = $(this);
	let sil_n = _this.data('n');
	let sil_adi = _this.data('adi');

	let postData = {
		islem: 'uis_menu_ekle_sil',
		sil_n: sil_n
	};

	$.ajax({
		data: postData,
		dataType: 'json',
		success: function (response) {

			if (response.return == 1) {
				/*$('.menu_sayfa_select option[data-sn="' + sil_n + '"]').prop("disabled", true);
				$('.menu_sayfa_select').trigger("chosen:updated");*/
				$('#nestable3').nestable('refresh');
				_this.closest('.n-dd-item').fadeOut(200, function () {
					_this.remove();
				});
				location.reload();
			}
		}
	});
});

$(document).on('click', '.menu_dis_link_duzenle', function () {
	let _this = $(this);
	let mb_ekle_nn = _this.attr('data-mno');
	let mb_ekle_adi = (mb_ekle_nn === 'yeni') ? '' : _this.attr('data-adi');
	let mb_ekle_link = (mb_ekle_nn === 'yeni') ? '' : _this.attr('data-link');
	let mb_baslik_yazi = (mb_ekle_nn === 'yeni') ? 'ekle' : 'duzenle';

	let izi_modal_content = '<button class="iziModal-button-close" data-izimodal-close="">x</button>';
	izi_modal_content += '<h3>Bağlantı ' + mb_baslik_yazi + '</h3>' +
		'<div class="menu_baglanti_div">' +
		'<div class="dib" data-placeholder="Link adı"><input type="text" class="menu_baglanti_input_adi g_230_" placeholder="Link adı" value="' + mb_ekle_adi + '" /> </div> ' +
		'<div class="dib" data-placeholder="Link url"><input type="text" class="menu_baglanti_input_link g_230_" placeholder="Link url" value="' + mb_ekle_link + '" />   </div>  ' +
		'<span class="btn btn-sm btn-primary menu_baglanti_ekle" data-mno="' + mb_ekle_nn + '">⏎</span>' +
		'</div>';

	$('.iziModal-content').html(izi_modal_content);
	$('#izi_modal').iziModal('open');
});

$(document).on('click', '.menu_baglanti_ekle', function () {

	let _this = $(this);
	let mb_ekle_nn = _this.attr('data-mno');
	let menu_baglanti_div = _this.closest('.menu_baglanti_div');
	let mb_ekle_adi = menu_baglanti_div.find('.menu_baglanti_input_adi').val();
	let mb_ekle_link = menu_baglanti_div.find('.menu_baglanti_input_link').val();

	let postData = {
		islem: 'uis_menu_baglanti_ekle',
		mb_ekle_nn: mb_ekle_nn,
		mb_ekle_adi: mb_ekle_adi,
		mb_ekle_link: mb_ekle_link,
	};

	$.ajax({
		data: postData,
		dataType: 'json',
		success: function (response) {
			if (response.return == 1) {
				location.reload();
			} else {
				console.log(response);
			}
		}
	});
});