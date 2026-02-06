
$(function(){
	$('#iletisimformgonder').click(function (event) {
		event.preventDefault();
		$("#iletisimformgonder").prop("disabled",true);
			$.ajax({
				data:$('#iletisim_formu').serialize(),
				success:function(yaz){
					iziToast.info({position: 'center',timeout: 4000,title: ' ',message: yaz});
			}
			});
			setTimeout(() => {
				$("#iletisimformgonder").prop("disabled",false);			
			}, 4000);
		return false;
	});
});