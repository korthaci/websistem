
$(function () {

	$('#liste_emailadresi').focus(function () {
		$('.ele_bilgi').html("");
	});

	$('#email_l_ekle').click(function () {

		$('#email_l_ekle').prop("disabled", true);
		setTimeout(function () { $('#email_l_ekle').prop("disabled", false); }, 2000);

		let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
		let liste_email_adresi = $('#liste_emailadresi').val();
		let m = $('#liste_emailadresi').data("m");

		let data_ = new FormData();
		data_.append("m_islem", "emailekle");
		data_.append("m", m);
		data_.append("liste_emailadresi", liste_email_adresi);

		if (liste_email_adresi.match(emailRegex)) {
			$.ajax({
				data: data_,
				success: function (yaz) {
					$('.ele_bilgi').html(yaz);
				}
			});
		} else {
			$('#liste_emailadresi').css({ 'backgroundColor': '#f2ff8d' });
			$('.ele_bilgi').html('Geçerli bir mail adresi girin.!');
		}
	});
});