<?php session_start();

require_once 'includes.php';

// Recaptcha private anahtarı
$private_key = '6LdIQM0SAAAAAHAEnAYlIrwRKfjLRh2a8oIY_PmW';
$user_id = $_GET["user"];
$ticket_key = $_GET["key"];
$ticket_type = $_GET["type"];
$username = trim($_POST["username"]);

if ($_POST["admin_action"] == "checkusername") {
    checkUsername(); // pa-users.php içinde tanımlı
    exit;
}
if ($ticket_id = $ADMIN->USER->validateTicket($user_id, $ticket_key, $ticket_type)) {
    if ($_POST["admin_action"] == "complete_registration") {
        $captcha = recaptcha_check_answer($private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

        if (strlen($username) < 6) {
            $resultText = $GT->KULLANICI_ADI_MIN_ALTI_KARAKTER;
        } else if ($ADMIN->USER->getUserByUsername($username)) {
            $resultText = $GT->FARKLI_KULLANICI_ADI_GIRIN;
        } else if (!$captcha->is_valid) {
            $resultText = $GT->CAPTCHA_HATASI;
        } else {
            $password = $_POST["password"];

            if ($ADMIN->USER->completeRegistration($user_id, $username, $password) && $ADMIN->USER->closeTicket($ticket_id)) {
                postMessage($GT->KAYDINIZ_BASARILI);
                $ADMIN->AUTHENTICATION->authenticate($username, $password);
                $ADMIN->AUTHORIZATION->authorize();
                header("Location:admin.php?page=dashboard");
                exit;
            }
        }
    }

    $complete_registration->render();
} else {
    header("Location:login.php");
}