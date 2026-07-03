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

    google.accounts.id.initialize({
        client_id: js_vars.gloginc,
        callback: handleCredentialResponse,
        auto_select: false
    });

    google.accounts.id.renderButton(
        document.getElementById("hiddenGoogleBtn"),
        { theme: "outline", size: "large" }
    );

    $(".google_login_buton").on("click", function (e) {
        e.preventDefault();
        $("#hiddenGoogleBtn").find('div[role="button"]').click();
    });

});