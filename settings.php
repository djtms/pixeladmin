<?php	require_once 'includes.php';

if(isset($_POST["admin_action"]) == "Kaydet")
{
	$isSmtp = (isset($_POST["smtp"]) && (trim($_POST["smtp"]) != "")) ? ' checked="checked" ' : "";
	
	$succes	= 	set_option("siteAddress",$_POST["siteAddress"],"pa_settings") &&
				set_option("siteTitle",$_POST["siteTitle"],"pa_settings") &&
				set_option("description",$_POST["description"],"pa_settings") &&
				set_option("keywords",$_POST["keywords"],"pa_settings") &&
				set_option("analystics",$_POST["analystics"],"pa_settings") &&
				set_option("isSmtpMail", $isSmtp,"pa_settings") &&
				set_option("mailHost",$_POST["mailHost"],"pa_settings") &&
				set_option("mailPort",$_POST["mailPort"],"pa_settings") &&
				set_option("mailUser",$_POST["mailUser"],"pa_settings") &&
				set_option("getMailAddress",$_POST["getMailAddress"],"pa_settings") &&
				set_option("mailPassword",$_POST["mailPassword"],"pa_settings") &&
				set_option("facebook",$_POST["facebook"],"pa_settings") &&
				set_option("twitter",$_POST["twitter"],"pa_settings");

	$message = $succes ? "Ayarlarınız Başarıyla Kaydedildi!" : "Hata Oluştu!";

	postMessage($message,!$succes);
}


$master->addScript("js/pages/settings.js");
$settings->stg = get_optiongroup("pa_settings");
$settings->render();