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
                console.log("Google login başarılı:", res);
            } else {
                console.log("Google login başarısız:", res.msg);
            }
        },
        error: function () {
            console.log("AJAX error");
        }
    });

}

$(window).on('load', function () {
    google.accounts.id.initialize({
        client_id: js_vars.gloginc,//"672040115870-qslv03u844ib3klqgk8cl4b78adg8f63.apps.googleusercontent.com",
        callback: handleCredentialResponse,
        auto_select: false
    });

    // Gizli Google butonu
    google.accounts.id.renderButton(
        document.getElementById("hiddenGoogleBtn"),
        { theme: "outline", size: "large" }
    );

    $(".google_login_buton").on("click", function (e) {
        e.preventDefault();
        $("#hiddenGoogleBtn").find('div[role="button"]').click();
    });
});
