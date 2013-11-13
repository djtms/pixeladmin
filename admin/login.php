<?php

require_once 'includes.php';

$loginAlert = "";

if ($_POST["admin_action"] == "Giriş") {
    login(false);
}

function login($captcha_used_correctly) {
    global $ADMIN;
    global $loginAlert;

    $username = $_POST["username"];
    $password = $_POST["password"];

    // Bilgilerin bizim formumuzdan gelip gelmediğini kontrol et
    if ($_SESSION["validatePageKey"] != $_POST["VPK"]) {
        $loginAlert = "* Güvensiz Form!";
    } else {
        // Kimlik doğrulaması yap
        $authentication_status = $ADMIN->AUTHENTICATION->authenticate($username, $password, $captcha_used_correctly);

        // Kimlik doğrulaması hatasız yapılmış ise
        if ($authentication_status === true) {
            // Yetkilerini al
            $ADMIN->AUTHORIZATION->authorize();

            add_log("giriş yaptı");
            $_SESSION["USE_CAPTCHA"] = false;
            if (isset($_SESSION["back_to"])) {
                header("Location:" . $_SESSION["back_to"]);
                unset($_SESSION["back_to"]);
                exit;
            } else {
                header("Location:admin.php?page=dashboard");
            }
        }
        // Captcha kullanarak tekrar giriş yapmak gerekiyorsa.
        else if ($authentication_status === "login_with_captcha") {
            $_SESSION["USE_CAPTCHA"] = true;
        }
        // Hesap henüz aktif edilmemişse
        else if ($authentication_status === "account_not_activated") {
            $loginAlert = "* Hesabınız henüz aktif edilmemiş!";
        } else {
            $loginAlert = "*Yanlış kullanıcı adı veya şifre!";
        }
    }
}

// Eğer kişi authenticate olmuşsa onu dashboard'a yönlendir.
if ($ADMIN->AUTHENTICATION->isAuthenticated()) {
    header("Location:admin.php?page=dashboard");
    exit;
}


if ($_SESSION["USE_CAPTCHA"] === true) {
    $username = $_POST["username"];

    $public_key = '6LdIQM0SAAAAAIb1A3MVnO14HHEZ2Tf3oM-apN_c';
    $private_key = '6LdIQM0SAAAAAHAEnAYlIrwRKfjLRh2a8oIY_PmW';

    $resp = recaptcha_check_answer($private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

    if ($resp->is_valid) {
        login(true);
    }

    $capcha_html = '<label>Captcha Kontrolü</label>';
    $capcha_html .= '<div id="recaptcha_widget" style="display:none">';
    $capcha_html .= '<div id="recaptcha_image"></div>';
    $capcha_html .= '<a id="get_new_captcha" href="javascript:Recaptcha.reload()">Yenile</a>';
    $capcha_html .= '<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />';
    $capcha_html .= '</div><script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=' . $public_key . '"></script>';
}

$validatePageKey = uniqid() . randomString();
$_SESSION["validatePageKey"] = $validatePageKey;

$login->render();
