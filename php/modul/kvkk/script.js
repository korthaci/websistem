$(function(){

	$('.cerez_onayla').on('click',function(){
		if (typeof gtag === 'function') {
			gtag('consent', 'update', {
				'ad_storage': 'granted',
				'ad_user_data': 'granted',
				'ad_personalization': 'granted',
				'analytics_storage': 'granted'
			});
		}
		
		$('.kvkk_container').fadeOut();
	});

});
