
$(function(){

	function yorum_getir(t, tn) {
		var data_ = 'm_islem=yorum_getir&m=yorumlar_form&t='+t+'&tn='+tn;
		$.ajax({
			data: data_,
			success: function (yaz) {
				if (yaz!='!') {
					$('#yorumlar').hide().html(yaz).slideDown();
				} else {
					console.log(yaz);
				}
			}
		});
	}

	var gonderilenYorumlar = [];

	function yorum_yaz() {
		if (!$('#yorumgonder').prop('disabled') && $('#yorumtext').val().length >= 2) {
			var yeniYorum = $('#yorum_formu').serialize().toString();

			if (gonderilenYorumlar.includes(yeniYorum)) {
				console.log("Bu yorum zaten gönderildi.");
				return false;
			}

			$('#yorumgonder').prop('disabled', true);

			setTimeout(()=>{
				$('#yorumgonder').prop('disabled', false);
			},3000);
			setTimeout(()=>{
				$('.y0').fadeOut(600);
			}, 3000);

			var yorum_container = $('#yorum_formu').closest('[data-yorumt]');
			console.log(yorum_container);
			var t = yorum_container.attr('data-yorumt');
			var tn = yorum_container.attr('data-yorumtn');
			var data_ = 'm_islem=yorum_gonder&m=yorumlar_form&t='+t+'&tn='+tn;
			$.ajax({
				data: data_ + '&' + $('#yorum_formu').serialize(),
				success: function (yaz) {
					gonderilenYorumlar.push(yeniYorum);
					$('#yorumlar').css('opacity', '0.4').prepend(yaz).animate({'opacity':'1'}, 600);
					$('#yorum_formu textarea').val("").css('opacity', '0.4').animate({'opacity':'1'}, 300);
					console.log(yaz);
				}
			});
		}
		return false;
	}

	$('#yorumgonder').click(yorum_yaz);

	var yorum_yazildi = false;
	$(window).scroll(function() {
		if (!yorum_yazildi && document.querySelector("#yorumlar")) {
			console.log('yorumlar yazılıyor');
			var element = $('#yorumlar');
			var _t = element.data('t');
			var _tn = element.data('tn');
			var threshold = $(window).scrollTop() + $(window).height();
	
			if (threshold > element.offset().top) {
				yorum_getir(_t, _tn);
				yorum_yazildi=true;
				console.log('yorumlar yazıldı');
			}
		}
	});


});