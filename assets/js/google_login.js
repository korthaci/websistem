function handleCredentialResponse(response) {

    $.ajax({
        data: {
            islem: 'google_login',
            id_token: response.credential
        },
        dataType: 'json',
        success: function (res) {
            if (res.status === "success") {
                location.reload();
            }
        },
        error: function (xhr, status, error) {
            console.log("AJAX error");
            console.log({
                http_status: xhr.status,
                status: status,
                error: error,
                response: xhr.responseText
            });
        }
    });

}

$(window).on('load', function () {

	const googleBtn = document.getElementById("hiddenGoogleBtn");

	if (googleBtn) {
		google.accounts.id.renderButton(
			googleBtn,
			{
				theme: "outline",
				size: "large"
			}
		);
	}

	$(".google_login_buton").on("click", function (e) {
		e.preventDefault();

		if (googleBtn) {
			$(googleBtn)
				.find('div[role="button"]')
				.click();
		}
	});

});