<?php	require_once 'includes.php';

if(isset($_POST["admin_action"]) == "Kaydet")
{
	$isSmtp = (isset($_POST["smtp"]) && (trim($_POST["smtp"]) != "")) ? ' checked="checked" ' : "";
	
	$succes	= 	set_option("admin_siteAddress",$_POST["siteAddress"],"pa_settings") &&
				set_option("admin_siteTitle",$_POST["siteTitle"],"pa_settings") &&
				set_option("admin_description",$_POST["description"],"pa_settings") &&
				set_option("admin_keywords",$_POST["keywords"],"pa_settings") &&
				set_option("admin_analystics",$_POST["analystics"],"pa_settings") &&
				set_option("admin_isSmtpMail", $isSmtp,"pa_settings") &&
				set_option("admin_mailHost",$_POST["mailHost"],"pa_settings") &&
				set_option("admin_mailPort",$_POST["mailPort"],"pa_settings") &&
				set_option("admin_mailUser",$_POST["mailUser"],"pa_settings") &&
				set_option("admin_getMailAddress",$_POST["getMailAddress"],"pa_settings") &&
				set_option("admin_mailPassword",$_POST["mailPassword"],"pa_settings") &&
				set_option("admin_facebook",$_POST["facebook"],"pa_settings") &&
				set_option("admin_twitter",$_POST["twitter"],"pa_settings");

	$message = $succes ? "Ayarlarınız Başarıyla Kaydedildi!" : "Hata Oluştu!";

	postMessage($message,!$succes);
}


$master->addScript("js/pages/settings.js");
$settings->stg = get_optiongroup("pa_settings");
$settings->render();