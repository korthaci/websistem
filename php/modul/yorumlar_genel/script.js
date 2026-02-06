
$(function(){

	var gonderilenYorumlar = [];

	function yorum_yaz() {
		if (!$('#yorumgonder_genel').prop('disabled') && $('#yorumtext_genel').val().length >= 2) {
			var yeniYorum = $('#yorum_formu_genel').serialize().toString();

			if (gonderilenYorumlar.includes(yeniYorum)) {
				console.log("Bu yorum zaten gönderildi.");
				return false;
			}

			$('#yorumgonder_genel').prop('disabled', true);

			setTimeout(()=>{
				$('#yorumgonder_genel').prop('disabled', false);
			},3000);
			setTimeout(()=>{
				$('.y0').fadeOut(600);
			}, 3000);

			var data_ = 'm_islem=yorum_gonder&m=yorumlar_genel';
			$.ajax({
				data: data_ + '&' + $('#yorum_formu_genel').serialize(),
				success: function (yaz) {
					gonderilenYorumlar.push(yeniYorum);
					$('#yorumtext_genel').before(yaz);
					$('#yorum_formu_genel').hide(400);
					console.log(yaz);
				}
			});
		}
		return false;
	}

	$('#yorumgonder_genel').click(function(e){
		e.preventDefault();
		yorum_yaz();
	});

});