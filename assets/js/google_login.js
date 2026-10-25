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

function loadGoogleSDK() {
    if (window.googleSDKPromise) return window.googleSDKPromise;

    window.googleSDKPromise = new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = 'https://accounts.google.com/gsi/client';
        script.async = true;
        script.defer = true;
        script.setAttribute('data-cfasync', 'false');
        script.onload = () => {
            resolve();
        };
        document.head.appendChild(script);
    });

    return window.googleSDKPromise;
}

function initializeGoogleComponents() {
    loadGoogleSDK().then(() => {
        const googleBtn = document.getElementById("hiddenGoogleBtn");
        if (googleBtn && typeof google !== 'undefined') {
            google.accounts.id.renderButton(
                googleBtn,
                { theme: "outline", size: "large" }
            );
        }

        if (typeof google !== 'undefined' && window.js_vars?.gloginc) {
            google.accounts.id.initialize({
                client_id: window.js_vars.gloginc,
                callback: handleCredentialResponse,
                use_fedcm_for_prompt: true,
                itp_support: true
            });
            google.accounts.id.prompt();
        }
    });

    removeLazyTriggers();
}

function removeLazyTriggers() {
    ['pointerdown', 'mousemove', 'touchstart', 'scroll', 'keydown'].forEach(evt => {
        window.removeEventListener(evt, initializeGoogleComponents);
    });
}

['pointerdown', 'mousemove', 'touchstart', 'scroll', 'keydown'].forEach(evt => {
    window.addEventListener(evt, initializeGoogleComponents, { passive: true });
});

$(document).ready(function() {
    $(document).on("click", ".google_login_buton", function (e) {
        e.preventDefault();
        
        loadGoogleSDK().then(() => {
            const hiddenBtn = $("#hiddenGoogleBtn");
            if (hiddenBtn.find('div[role="button"]').length > 0) {
                hiddenBtn.find('div[role="button"]').click();
            } else {
                google.accounts.id.prompt();
            }
        });
    });
});
