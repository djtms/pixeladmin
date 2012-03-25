<?php require_once 'includes.php'; 

$loginAlert = "";

if($_POST["admin_action"] == "Giriş")
{
	login(false);
}

function login($captcha_used_correctly)
{
	global $ADMIN;
	global $loginAlert;
	
	$username = $_POST["username"];
	$password = $_POST["password"];
	
	if($_SESSION["validatePageKey"] != $_POST["VPK"])
	{
		$loginAlert = "*Güvensiz Form!";
	}
	else
	{
		$login_status =  $ADMIN->USER->login($username,$password, $captcha_used_correctly);
			
		if($login_status === true)
		{
			add_log("giriş yaptı");
			$_SESSION["USE_CAPTCHA"] = false;
			header("Location:admin.php?page=dashboard");
		}
		else if($login_status === "login_with_captcha")
		{
			$_SESSION["USE_CAPTCHA"] = true;
		}
		else
		{
			$loginAlert = "*Yanlış kullanıcı adı veya şifre";
		}
	}
}


if($_SESSION["USE_CAPTCHA"] === true)
{
	$username = $_POST["username"];
	
	$public_key = '6LdIQM0SAAAAAIb1A3MVnO14HHEZ2Tf3oM-apN_c';
	$private_key = '6LdIQM0SAAAAAHAEnAYlIrwRKfjLRh2a8oIY_PmW';

	$resp = recaptcha_check_answer ($private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

	if ($resp->is_valid)
	{
		login(true);
	}

	$capcha_html  = '<label>Captcha Kontrolü</label>';
	$capcha_html .= '<div id="recaptcha_widget" style="display:none">';
	$capcha_html .= '<div id="recaptcha_image"></div>';
	$capcha_html .= '<a id="get_new_captcha" href="javascript:Recaptcha.reload()">Yenile</a>';
	$capcha_html .= '<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />';
	$capcha_html .= '</div><script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=' . $public_key . '"></script>';
}

$validatePageKey = uniqid() . randomString();
$_SESSION["validatePageKey"] = $validatePageKey;

$login->render();
